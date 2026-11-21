<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRentalRequest;
use App\InfomaniakCalendar;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Mail\Message;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class RentalController extends Controller
{
    public function __construct(private InfomaniakCalendar $calendar) {}

    public function index(Request $request): Response
    {
        $today = now(InfomaniakCalendar::TIMEZONE)->toDateString();
        $showPast = $request->boolean('past');
        $rentals = Rental::with('user:id,name,first_name,email')
            ->where('departure', $showPast ? '<' : '>=', $today)
            ->orderBy('arrival', $showPast ? 'desc' : 'asc')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return response()->view('admin.rentals', compact('rentals', 'showPast'))->header('Cache-Control', 'private, no-store');
    }

    public function create(): Response
    {
        return $this->form(new Rental);
    }

    public function edit(Rental $rental): Response
    {
        return $this->form($rental);
    }

    public function sendAccessEmail(Rental $rental): RedirectResponse
    {
        $guest = $rental->user;

        if ($guest === null || blank($guest->email)) {
            return back()->withErrors(['access_email' => __('rentals.access_email_missing')]);
        }

        try {
            $sent = Mail::send('emails.rental-access', ['locale' => $guest->locale], function (Message $message) use ($guest): void {
                $message->to($guest->email)->subject(__('rentals.access_email.subject', [], $guest->locale));
            });
        } catch (TransportExceptionInterface $exception) {
            report($exception);

            return back()->withErrors(['access_email' => __('rentals.access_email_failed')]);
        }

        if ($sent === null) {
            return back()->withErrors(['access_email' => __('rentals.access_email_failed')]);
        }

        $rental->update(['mail_envoye' => true]);

        return back()->with('status', __('rentals.access_email_sent'));
    }

    public function store(SaveRentalRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $createdEventId = null;

        try {
            DB::transaction(function () use ($data, &$createdEventId): void {
                $user = $data['user_id'] === 'new'
                    ? User::create([
                        'name' => $data['new_name'],
                        'first_name' => $data['new_first_name'] ?? null,
                        'email' => $data['new_email'],
                        'password' => Str::random(64),
                    ])
                    : User::findOrFail($data['user_id']);

                $rental = new Rental(Arr::except($data, ['user_id', 'new_name', 'new_first_name', 'new_email']));
                $rental->user()->associate($user);
                $rental->guest_name = $user->fullName();
                $rental->save();

                $createdEventId = $this->syncEvent($rental);
                $rental->calendar_event_id = $createdEventId;
                $rental->save();
            });
        } catch (Throwable $exception) {
            if ($createdEventId !== null) {
                try {
                    $this->calendar->delete((string) $createdEventId, allowMissing: true);
                } catch (Throwable $cleanupException) {
                    report($cleanupException);
                }
            }

            return $this->calendarFailure($exception);
        }

        return $this->saved('created');
    }

    public function update(SaveRentalRequest $request, Rental $rental): RedirectResponse
    {
        try {
            DB::transaction(function () use ($request, $rental): void {
                $rental = Rental::lockForUpdate()->findOrFail($rental->id);
                $data = $request->safe()->only(['arrival', 'departure', 'type', 'amount']);
                $rental->fill([...$data, 'amount' => $data['amount'] ?? null]);

                $needsSync = $rental->calendar_event_id === null || $rental->isDirty(['arrival', 'departure', 'type']);
                $rental->save();

                if ($needsSync) {
                    $eventId = $this->syncEvent($rental);
                    if ($rental->calendar_event_id === null) {
                        $rental->calendar_event_id = $eventId;
                        $rental->save();
                    }
                }
            });
        } catch (HttpExceptionInterface $exception) {
            return $this->calendarFailure($exception);
        }

        return $this->saved('updated');
    }

    public function updateRemarks(Request $request, Rental $rental): RedirectResponse
    {
        $data = $request->validateWithBag('rentalRemarks'.$rental->id, [
            'remarks' => ['present', 'nullable', 'string', 'max:10000'],
        ], [], __('rentals.fields'));

        $rental->update($data);

        return back()->withFragment('rental-remarks-'.$rental->id)->with('status', __('rentals.updated'));
    }

    public function updatePayment(Request $request, Rental $rental, string $payment): JsonResponse|RedirectResponse
    {
        $rules = Arr::only(SaveRentalRequest::paymentRules(), [$payment.'_date', $payment.'_amount']);
        $data = $request->validate($rules, [], __('rentals.fields'));

        $result = DB::transaction(function () use ($rental, $payment, $data): array {
            $rental = Rental::lockForUpdate()->findOrFail($rental->id);
            abort_unless($rental->type === 'rental', 404);

            $rental->update($data);

            return ['payment' => $rental->paymentSummary($payment), 'total' => $rental->paymentTotalStatus()];
        });

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        return back();
    }

    public function updateChecklist(Request $request, Rental $rental, string $field): JsonResponse|RedirectResponse
    {
        $request->validate(['value' => ['required', 'boolean']]);
        $value = $request->boolean('value');

        DB::transaction(function () use ($rental, $field, $value): void {
            $rental = Rental::lockForUpdate()->findOrFail($rental->id);
            abort_if($rental->type === 'family' || ($rental->type === 'exchange' && in_array($field, ['contract_sent', 'contract_signed'], true)), 404);

            $rental->update([$field => $value]);
        });

        if ($request->expectsJson()) {
            return response()->json(['value' => $value, 'label' => __($value ? 'users.yes' : 'users.no')]);
        }

        return back();
    }

    public function destroy(Rental $rental): RedirectResponse
    {
        try {
            DB::transaction(function () use ($rental): void {
                $rental = Rental::lockForUpdate()->findOrFail($rental->id);
                $rental->delete();
                if ($rental->calendar_event_id !== null) {
                    $this->calendar->delete((string) $rental->calendar_event_id, allowMissing: true);
                }
            });
        } catch (HttpExceptionInterface $exception) {
            return $this->calendarFailure($exception);
        }

        return $this->saved('deleted');
    }

    private function form(Rental $rental): Response
    {
        $users = $rental->exists ? collect() : User::where('is_active', true)
            ->orderBy('name')
            ->orderBy('first_name')
            ->orderBy('id')
            ->get(['id', 'name', 'first_name', 'email']);

        return response()->view('admin.rentals.form', compact('rental', 'users'))->header('Cache-Control', 'private, no-store');
    }

    private function syncEvent(Rental $rental): int
    {
        $id = $rental->calendar_event_id;
        $eventId = $this->calendar->save([
            'title' => Str::limit(__('rentals.event_title', [
                'type' => __('rentals.types.'.$rental->type, [], 'fr'),
                'name' => $rental->guestName(),
            ], 'fr'), 255, ''),
            'start' => $rental->arrival->toDateString(),
            'end' => $rental->departure->toDateString(),
            'fullday' => true,
            'color' => Rental::TYPE_COLORS[$rental->type],
        ], $id === null ? null : (string) $id);

        abort_if($id === null && $eventId === null, 502, __('calendar.unavailable'));

        return $id ?? $eventId;
    }

    private function saved(string $message): RedirectResponse
    {
        return redirect()->route('admin.rentals')->with('status', __('rentals.'.$message));
    }

    private function calendarFailure(Throwable $exception): RedirectResponse
    {
        if (! $exception instanceof HttpExceptionInterface || ! in_array($exception->getStatusCode(), [404, 502, 503], true)) {
            throw $exception;
        }

        return back()->withInput()->withErrors([
            'calendar' => $exception->getStatusCode() === 404 ? __('rentals.event_missing') : $exception->getMessage(),
        ]);
    }
}
