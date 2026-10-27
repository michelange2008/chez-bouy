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
            Mail::raw("Votre code Chez Bouy : {$code}\nValable 10 minutes, utilisable une seule fois.\nSi vous n'avez rien demandé, ignorez cet e-mail.",
                fn ($message) => $message->to($email)->subject('Votre code de connexion — Chez Bouy'));
        }, 60);

        return redirect()->route('code.form')->with('status',
            'Si cette adresse est autorisée, un code vous a été envoyé. Patientez une minute avant un nouvel envoi.');
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
                'code' => 'Code incorrect, expiré ou limite atteinte. Après 5 essais, patientez 10 minutes.',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();
        $request->session()->forget('login_email');
        return redirect()->route($user->role === 'admin' ? 'admin' : 'dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
