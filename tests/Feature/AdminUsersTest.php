<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue(
            $this->app->runningUnitTests(),
            'Environnement détecté : ' . $this->app->environment()
        );

        $this->withoutVite();
    }

    private function admin(): User
    {
        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin);
        return $admin;
    }

    private function profile(array $overrides = []): array
    {
        return array_replace([
            'name' => 'DUPONT',
            'first_name' => 'Alice',
            'role' => 'locataire',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_creation_avec_email_normalise_et_champs_limites(): void
    {
        $this->admin();
        Mail::shouldReceive('raw')->never();
        $this->post(route('admin.users.save'), [
            ...$this->profile(['first_name' => null]),
            'email' => '  ALICE@EXAMPLE.COM  ',
            'login_count' => 999,
        ])->assertRedirect(route('admin.users'));

        $user = User::where('email', 'alice@example.com')->firstOrFail();
        $this->assertSame('DUPONT', $user->name);
        $this->assertNull($user->first_name);
        $this->assertTrue($user->is_active);
        $this->assertSame('locataire', $user->role);
        $this->assertSame(0, $user->login_count);
        $this->assertNotEmpty($user->password);
        $this->assertFalse(Hash::needsRehash($user->password));
    }

    public function test_un_doublon_reouvre_le_formulaire_avec_erreur(): void
    {
        $this->admin();

        User::factory()->create([
            'email' => 'alice@example.com',
        ]);

        $this->from(route('admin.users'))
            ->post(route('admin.users.save'), [
                ...$this->profile(),
                'email' => 'ALICE@EXAMPLE.COM',
            ])
            ->assertRedirect(route('admin.users'))
            ->assertSessionHasErrors(['email'], null, 'createUser');

        $response = $this
            ->withCookie(config('session.cookie'), session()->getId())
            ->get(route('admin.users'));

        $response->assertOk();
        $response->assertSee('data-reopen="true"', false);

        $this->assertSame(
            1,
            User::where('email', 'alice@example.com')->count()
        );
    }

    public function test_la_validation_refuse_un_profil_invalide(): void
    {
        $this->admin();
        $this->post(route('admin.users.save'), [
            'email' => 'incorrect',
            'name' => '',
            'first_name' => str_repeat('a', 256),
            'role' => 'superadmin',
            'is_active' => 'peut-etre',
        ])->assertSessionHasErrors(['email', 'name', 'first_name', 'role', 'is_active'], null, 'createUser');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_modification_des_quatre_champs_et_preservation_du_reste(): void
    {
        $this->admin();
        $user = User::factory()->create(['role' => 'locataire', 'is_active' => true, 'login_count' => 5]);
        $email = $user->email;
        $password = $user->password;
        $key = 'login-code:' . hash('sha256', $email);
        Cache::put($key, Hash::make('123456'), 600);

        $this->patch(route('admin.users.update', $user), [
            ...$this->profile(['role' => 'admin', 'is_active' => '0']),
            'login_count' => 100,
            'password' => 'ne-pas-enregistrer',
        ])->assertRedirect(route('admin.users'));

        $user->refresh();
        $this->assertSame('DUPONT', $user->name);
        $this->assertSame('Alice', $user->first_name);
        $this->assertSame('admin', $user->role);
        $this->assertFalse($user->is_active);
        $this->assertSame($email, $user->email);
        $this->assertSame($password, $user->password);
        $this->assertSame(5, $user->login_count);
        $this->assertNull(Cache::get($key));
    }

    public function test_une_tentative_de_changement_email_est_refusee(): void
    {
        $this->admin();
        $user = User::factory()->create();
        $this->patch(route('admin.users.update', $user), [
            ...$this->profile(),
            'email' => 'autre@example.com',
        ])->assertSessionHasErrors('email');
        $this->assertSame($user->email, $user->fresh()->email);
    }

    public function test_suppression_et_invalidation_du_code(): void
    {
        $this->admin();
        $user = User::factory()->create();
        $key = 'login-code:' . hash('sha256', $user->email);
        Cache::put($key, Hash::make('123456'), 600);
        $this->delete(route('admin.users.destroy', $user))->assertRedirect(route('admin.users'));
        $this->assertModelMissing($user);
        $this->assertNull(Cache::get($key));
    }

    public function test_un_admin_ne_peut_pas_perdre_son_propre_acces(): void
    {
        $admin = $this->admin();
        $this->delete(route('admin.users.destroy', $admin))->assertSessionHasErrors('user');
        foreach ([['role' => 'locataire'], ['role' => 'admin', 'is_active' => '0']] as $changes) {
            $this->patch(route('admin.users.update', $admin), $this->profile($changes))
                ->assertSessionHasErrors('role');
        }
        $this->assertModelExists($admin);
        $this->assertTrue($admin->fresh()->is_active);
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_les_routes_sont_interdites_aux_locataires_et_admins_inactifs(): void
    {
        $target = User::factory()->create(['name' => 'Conserver']);
        foreach ([['role' => 'locataire', 'is_active' => true], ['role' => 'admin', 'is_active' => false]] as $profile) {
            $this->actingAs(User::factory()->create($profile));
            $this->get(route('admin.users'))->assertForbidden();
            $this->get(route('admin.users.edit', $target))->assertForbidden();
            $this->post(route('admin.users.save'), [...$this->profile(), 'email' => 'interdit@example.com'])->assertForbidden();
            $this->patch(route('admin.users.update', $target), $this->profile())->assertForbidden();
            $this->delete(route('admin.users.destroy', $target))->assertForbidden();
        }
        $this->assertSame('Conserver', $target->fresh()->name);
        $this->assertDatabaseMissing('users', ['email' => 'interdit@example.com']);
    }

    public function test_un_visiteur_ne_peut_pas_acceder_aux_actions(): void
    {
        $user = User::factory()->create();
        $this->get(route('admin.users.edit', $user))->assertRedirect(route('login'));
        $this->post(route('admin.users.save'), [])->assertRedirect(route('login'));
        $this->patch(route('admin.users.update', $user), [])->assertRedirect(route('login'));
        $this->delete(route('admin.users.destroy', $user))->assertRedirect(route('login'));
        $this->assertModelExists($user);
    }

    public function test_les_noms_sont_echappes_et_les_utilisateurs_sont_pagines(): void
    {
        $this->admin();
        User::factory()->create(['name' => '<script>alert(1)</script>']);
        User::factory()->count(22)->create(['name' => 'ZZZ']);
        $response = $this->get(route('admin.users'))->assertOk();
        $response->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
        $response->assertDontSee('<script>alert(1)</script>', false);
        $response->assertViewHas('users', fn($users) => $users->total() === 24 && $users->count() === 20);
        $this->get(route('admin.users', ['page' => 2]))->assertOk()
            ->assertViewHas('users', fn($users) => $users->count() === 4);
    }

    public function test_la_page_edit_affiche_email_sans_champ_modifiable(): void
    {
        $this->admin();
        $user = User::factory()->create();
        $this->get(route('admin.users.edit', $user))->assertOk()
            ->assertSee($user->email)->assertSee('readonly', false)
            ->assertDontSee('name="email"', false);
    }
}
