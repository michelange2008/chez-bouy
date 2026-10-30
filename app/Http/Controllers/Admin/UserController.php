<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users', [
            'users' => User::query()->orderBy('name')->orderBy('first_name')->orderBy('id')->paginate(20),
        ]);
    }

    public function save(Request $request)
    {
        $request->merge([
            'email' => is_string($request->input('email'))
                ? Str::lower(trim($request->input('email'))) : $request->input('email'),
        ]);
        $data = $request->validateWithBag('createUser', [
            ...$this->profileRules(),
            'email' => ['required', 'string', 'email', 'max:254', Rule::unique('users', 'email')],
        ], $this->messages());

        $user = new User;
        $this->assignProfile($user, $data);
        $user->email = $data['email'];
        // Le cast « hashed » du modèle chiffre irréversiblement ce secret aléatoire.
        $user->password = Str::random(64);
        $user->save();

        return redirect()->route('admin.users')->with('status', __('users.created'));
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            ...$this->profileRules(),
            'email' => ['prohibited'],
        ], $this->messages());

        if ($user->is($request->user()) && ($data['role'] !== 'admin' || ! (bool) $data['is_active'])) {
            throw ValidationException::withMessages(['role' => __('users.keep_own_access')]);
        }

        // Liste explicite : ni email, ni password, ni statistiques ne sont modifiés.
        $this->assignProfile($user, $data);
        $user->save();
        if (! $user->is_active) {
            Cache::forget('login-code:'.hash('sha256', $user->email));
        }

        return redirect()->route('admin.users')->with('status', __('users.updated'));
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            throw ValidationException::withMessages(['user' => __('users.cannot_delete_self')]);
        }

        $key = 'login-code:'.hash('sha256', $user->email);
        $user->delete();
        Cache::forget($key);

        return redirect()->route('admin.users')->with('status', __('users.deleted'));
    }

    private function assignProfile(User $user, array $data): void
    {
        $user->name = $data['name'];
        $user->first_name = $data['first_name'] ?? null;
        $user->role = $data['role'];
        $user->is_active = (bool) $data['is_active'];
    }

    private function profileRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'first_name' => ['nullable', 'string', 'max:255'],
            'role' => ['required', Rule::in(['locataire', 'admin'])],
            'is_active' => ['required', 'boolean'],
        ];
    }

    private function messages(): array
    {
        // Messages propres à cette interface, sans dépendre de règles non traduites.
        return [
            'name.required' => __('users.name_required'),
            'name.string' => __('users.name_invalid'),
            'name.max' => __('users.name_invalid'),
            'first_name.string' => __('users.first_name_invalid'),
            'first_name.max' => __('users.first_name_invalid'),
            'email.required' => __('users.email_invalid'),
            'email.string' => __('users.email_invalid'),
            'email.email' => __('users.email_invalid'),
            'email.max' => __('users.email_invalid'),
            'email.unique' => __('users.email_unique'),
            'email.prohibited' => __('users.email_immutable'),
            'role.required' => __('users.role_invalid'),
            'role.in' => __('users.role_invalid'),
            'is_active.required' => __('users.active_invalid'),
            'is_active.boolean' => __('users.active_invalid'),
        ];
    }
}
