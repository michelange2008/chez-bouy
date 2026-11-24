<?php

namespace App\Http\Controllers;

use App\Console\Commands\SendRentalCodeReminders;
use Illuminate\Console\Command;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

class RentalCodeReminderCronController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $password = config('services.infomaniak.webcron_password');
        abort_unless(is_string($password) && $password !== '', 404);

        if ($request->getUser() !== 'webcron' || ! hash_equals($password, (string) $request->getPassword())) {
            return response('Authentification requise.', 401, [
                'WWW-Authenticate' => 'Basic realm="Chez Bouy Webcron"',
                'Cache-Control' => 'no-store',
            ]);
        }

        $exitCode = Artisan::call(SendRentalCodeReminders::class);

        return $exitCode === Command::SUCCESS
            ? response('', 204, ['Cache-Control' => 'no-store'])
            : response('Échec du rappel des codes.', 500, ['Cache-Control' => 'no-store']);
    }
}
