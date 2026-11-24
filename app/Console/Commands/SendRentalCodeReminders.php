<?php

namespace App\Console\Commands;

use App\InfomaniakCalendar;
use App\Models\Rental;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

class SendRentalCodeReminders extends Command
{
    protected $signature = 'rentals:remind-codes';

    protected $description = 'Rappeler aux admins d’envoyer les codes sept jours avant l’arrivée';

    public function handle(): int
    {
        $arrival = today(InfomaniakCalendar::TIMEZONE)->addDays(Rental::CODE_REMINDER_DAYS)->toDateString();
        $admins = User::query()->where('role', 'admin')->where('is_active', true)->get();
        $failed = false;

        $rentals = Rental::with('user:id,name,first_name')
            ->whereDate('arrival', $arrival)
            ->whereIn('type', Rental::TYPES_WITH_CODES)
            ->where('codes_sent', false)
            ->lazyById();

        foreach ($rentals as $rental) {
            foreach ($admins as $admin) {
                try {
                    if (! $this->sendReminder($rental, $admin, $arrival)) {
                        $failed = true;
                    }
                } catch (TransportExceptionInterface $exception) {
                    report($exception);
                    $failed = true;
                }
            }
        }

        if ($failed) {
            $this->error('Certains rappels n’ont pas pu être envoyés.');
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }

    private function sendReminder(Rental $rental, User $admin, string $arrival): bool
    {
        $key = "rental-code-reminder:{$rental->id}:{$admin->id}:{$arrival}";
        $result = Cache::lock($key.':sending', 600)->get(function () use ($key, $rental, $admin): bool {
            if (Cache::has($key)) {
                return true;
            }

            $body = __('rentals.code_reminder_email.body', [
                'guest' => $rental->guestName(),
                'arrival' => $rental->arrival->format('d/m/Y'),
                'url' => route('admin.rentals'),
            ], $admin->locale);

            $sent = Mail::raw($body, fn (Message $message) => $message->to($admin->email)
                ->subject(__('rentals.code_reminder_email.subject', [], $admin->locale)));

            if ($sent === null) {
                return false;
            }

            Cache::put($key, true, now()->addDay());

            return true;
        });

        return $result === true;
    }
}
