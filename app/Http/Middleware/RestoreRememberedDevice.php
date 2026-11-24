<?php

namespace App\Http\Middleware;

use App\RememberedDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Intervient avant le contrôle d’accès des routes pour qu’un appareil reconnu
 * puisse ouvrir directement une page protégée. L’échéance conservée en session
 * impose un nouveau code même si la session est encore ouverte à la fin de la période de mémorisation.
 */
class RestoreRememberedDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $until = $request->session()->get(RememberedDevice::SESSION_EXPIRY);
        $role = $request->session()->get(RememberedDevice::SESSION_ROLE, 'admin');
        $user = $request->user();
        $token = $request->cookie(RememberedDevice::COOKIE);
        if ($until !== null && ($until <= now()->timestamp || $user?->role !== $role || ! $user->canSignIn())) {
            RememberedDevice::forget($request);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } elseif (is_string($token) && Auth::guest() && ! $request->routeIs('logout')) {
            $user = RememberedDevice::restore($request, $token);
            if ($user) {
                Auth::login($user);
                $request->session()->forget(['login_email', 'remember_device_role']);
                $user->increment('login_count', 1, ['last_login_at' => now()]);
            } else {
                RememberedDevice::forget($request);
            }
        }

        return $next($request);
    }
}
