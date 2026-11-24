<?php

namespace App;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Chaque navigateur reçoit son propre jeton après validation du code par e-mail.
 * Son empreinte sert de clé dans le cache : à la déconnexion, supprimer cette
 * entrée révoque ce navigateur, y compris si une copie du cookie existe encore.
 */
class RememberedDevice
{
    // Les noms historiques préservent les appareils déjà mémorisés par les admins.
    public const SESSION_EXPIRY = 'admin_device_until';

    public const SESSION_ROLE = 'remembered_device_role';

    public const COOKIE = 'admin_device';

    public static function remember(Request $request, User $user): void
    {
        self::forget($request);
        $token = Str::random(64);
        $now = now();
        $expires = $now->copy()->addMonthsNoOverflow($user->role === 'admin' ? 6 : 3);
        Cache::put(self::key($token), ['user_id' => $user->id, 'until' => $expires->timestamp, 'role' => $user->role], $expires);
        $request->session()->put([self::SESSION_EXPIRY => $expires->timestamp, self::SESSION_ROLE => $user->role]);
        Cookie::queue(cookie(self::COOKIE, $token, (int) ceil($now->diffInMinutes($expires)), secure: $request->isSecure() || config('session.secure'), httpOnly: true, sameSite: 'lax'));
    }

    /**
     * Vérifie les droits actuels du compte et reprend l’échéance fixée lors
     * de la validation du code. Une visite ne prolonge donc pas la période de mémorisation.
     */
    public static function restore(Request $request, string $token): ?User
    {
        $device = Cache::get(self::key($token));
        if ($device === null || $device['until'] <= now()->timestamp) {
            return null;
        }

        $role = $device['role'] ?? 'admin';
        $user = User::whereKey($device['user_id'])->where('role', $role)->first();
        if (! $user?->canSignIn()) {
            return null;
        }

        $request->session()->put([self::SESSION_EXPIRY => $device['until'], self::SESSION_ROLE => $role]);

        return $user;
    }

    public static function forget(Request $request): void
    {
        $token = $request->cookie(self::COOKIE);
        if (is_string($token)) {
            Cache::forget(self::key($token));
            Cookie::expire(self::COOKIE);
        }
        $request->session()->forget([self::SESSION_EXPIRY, self::SESSION_ROLE]);
    }

    private static function key(string $token): string
    {
        return 'admin-device:'.hash('sha256', $token);
    }
}
