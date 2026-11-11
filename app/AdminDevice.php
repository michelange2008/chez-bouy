<?php

namespace App;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

class AdminDevice
{
    public const COOKIE = 'admin_device';

    public static function remember(Request $request, User $user): void
    {
        $token = Str::random(64);
        $now = now();
        $expires = $now->copy()->addMonthsNoOverflow(6);
        Cache::put(self::key($token), ['user_id' => $user->id, 'until' => $expires->timestamp], $expires);
        $request->session()->put('admin_device_until', $expires->timestamp);
        Cookie::queue(cookie(self::COOKIE, $token, (int) ceil($now->diffInMinutes($expires)), secure: $request->isSecure() || config('session.secure'), httpOnly: true, sameSite: 'lax'));
    }

    public static function restore(Request $request, string $token): ?User
    {
        $device = Cache::get(self::key($token));
        if ($device === null || $device['until'] <= now()->timestamp) {
            return null;
        }

        $user = User::whereKey($device['user_id'])->where('role', 'admin')->where('is_active', true)->first();
        if ($user) {
            $request->session()->put('admin_device_until', $device['until']);
        }

        return $user;
    }

    public static function forget(Request $request): void
    {
        $token = $request->cookie(self::COOKIE);
        if (is_string($token)) {
            Cache::forget(self::key($token));
            Cookie::expire(self::COOKIE);
        }
        $request->session()->forget('admin_device_until');
    }

    private static function key(string $token): string
    {
        return 'admin-device:'.hash('sha256', $token);
    }
}
