<?php

namespace App;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class DeleteUser
{
    public function handle(User $user): void
    {
        DB::transaction(function () use ($user): void {
            DB::connection(config('session.connection'))
                ->table(config('session.table', 'sessions'))
                ->where('user_id', $user->id)->delete();
            DB::table('password_reset_tokens')->where('email', $user->email)->delete();
            $user->delete();
        });

        $key = 'login-code:'.hash('sha256', $user->email);
        Cache::forget($key);
        RateLimiter::clear($key.':send');
        RateLimiter::clear($key.':verify');
    }
}
