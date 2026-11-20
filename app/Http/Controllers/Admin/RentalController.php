<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SaveRentalRequest;
use App\InfomaniakCalendar;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class RentalController extends Controller
{
    public function __construct(private InfomaniakCalendar $calendar) {}

    public function index(): Response
    {
        $today = now(InfomaniakCalendar::TIMEZONE)->toDateString();
        $rentals = Rental::with('user:id,name,first_name')
            ->orderByRaw('departure < ?', [$today])
            ->orderByRaw('CASE WHEN departure >= ? THEN arrival END ASC', [$today])
            ->orderByDesc('arrival')->orderByDesc('id')->paginate(20);

        return response()->view('admin.rentals', compact('rentals', 'today'))->header('Cache-Control', 'private, no-store');
    }

    public function create(): Response
    {
        return $this->form(new Rental);
    }

    public function edit(Rental $rental): Response
    {
        return $this->form($rental);
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
                $data = $request->validated();
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
        $users = $rental->exists ? collect() : User::orderBy('name')->orderBy('first_name')->orderBy('id')
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
