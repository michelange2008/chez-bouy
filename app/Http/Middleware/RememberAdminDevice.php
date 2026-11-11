<?php

namespace App\Http\Middleware;

use App\AdminDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Intervient avant le contrôle d’accès des routes pour qu’un appareil reconnu
 * puisse ouvrir directement une page protégée. L’échéance conservée en session
 * impose un nouveau code même si la session est encore ouverte après six mois.
 */
class RememberAdminDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $until = $request->session()->get('admin_device_until');
        $token = $request->cookie(AdminDevice::COOKIE);
        if ($until !== null && ($until <= now()->timestamp || $request->user()?->role !== 'admin' || ! $request->user()->is_active)) {
            AdminDevice::forget($request);
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        } elseif (is_string($token) && Auth::guest() && ! $request->routeIs('logout')) {
            $user = AdminDevice::restore($request, $token);
            if ($user) {
                Auth::login($user);
                $request->session()->forget('login_email');
                $user->increment('login_count', 1, ['last_login_at' => now()]);
            } else {
                AdminDevice::forget($request);
            }
        }

        return $next($request);
    }
}
