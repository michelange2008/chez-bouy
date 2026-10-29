<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class CodeLoginController extends Controller
{
    public function send(Request $request)
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:254']]);
        $email = mb_strtolower(trim($data['email']));
        $key = 'login-code:'.hash('sha256', $email);
        $request->session()->put('login_email', $email);

        // Limite par adresse, en complément de la limite par IP des routes.
        RateLimiter::attempt($key.':send', 1, function () use ($key, $email) {
            $user = User::where('email', $email)->first();
            if (! $user || ! in_array($user->role, ['locataire', 'admin'], true)) {
                return;
            }

            $code = (string) random_int(100000, 999999);
            Cache::lock($key.':lock', 15)->block(5, function () use ($key, $code) {
                Cache::put($key, Hash::make($code), now()->addMinutes(10));
            });
            Mail::raw(__('site.mail_body', ['code' => $code]),
                fn ($message) => $message->to($email)->subject(__('site.mail_subject')));
        }, 60);

        return redirect()->route('code.form')->with('status',
            __('site.code_sent'));
    }

    public function verify(Request $request)
    {
        $data = $request->validate(['code' => ['required', 'regex:/^[0-9]{6}$/']]);
        $email = $request->session()->get('login_email');
        if (! is_string($email)) {
            return redirect()->route('login');
        }
        $key = 'login-code:'.hash('sha256', $email);

        // Le verrou empêche deux validations simultanées du même code.
        $user = Cache::lock($key.':lock', 15)->block(5, function () use ($key, $email, $data) {
            if (RateLimiter::tooManyAttempts($key.':verify', 5)) {
                return null;
            }
            RateLimiter::hit($key.':verify', 600);
            $hash = Cache::get($key);
            if (! $hash || ! Hash::check($data['code'], $hash)) {
                return null;
            }
            Cache::forget($key);
            $user = User::where('email', $email)->first();
            if (! $user || ! in_array($user->role, ['locataire', 'admin'], true)) {
                return null;
            }
            RateLimiter::clear($key.':verify');
            return $user;
        });

        if (! $user) {
            throw ValidationException::withMessages([
                'code' => __('site.invalid_code'),
            ]);
        }

        Auth::login($user);
        // Incrément SQL atomique : deux connexions ne perdent pas une unité.
        $user->increment('login_count', 1, ['last_login_at' => now()]);
        $request->session()->regenerate();
        $request->session()->forget('login_email');
        return redirect()->route($user->role === 'admin' ? 'admin' : 'dashboard');
    }

    public function logout(Request $request)
    {
        $locale = $request->session()->get('locale', config('app.locale', 'fr'));
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put('locale', $locale);
        return redirect()->route('home');
    }
}
