<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['cache.default' => 'array', 'session.driver' => 'array']);
    }

    private function prepareCode(User $user): void
    {
        Cache::put(
            'login-code:'.hash('sha256', $user->email),
            Hash::make('123456'),
            now()->addMinutes(10),
        );
    }

    public function test_un_compte_neuf_n_a_aucune_connexion(): void
    {
        $user = User::factory()->create()->fresh();

        $this->assertSame(0, $user->login_count);
        $this->assertNull($user->last_login_at);
    }

    public function test_chaque_connexion_reussie_met_a_jour_les_statistiques(): void
    {
        $this->travelTo(now()->startOfSecond());
        $user = User::factory()->create(['role' => 'locataire']);

        $this->prepareCode($user);
        $this->withSession(['login_email' => $user->email])
            ->post('/connexion/code', ['code' => '123456'])
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($user);
        $user->refresh();
        $this->assertSame(1, $user->login_count);
        $this->assertTrue($user->last_login_at->equalTo(now()));

        // Consulter une page n'est pas une nouvelle connexion.
        $this->get('/espace')->assertOk();
        $this->assertSame(1, $user->fresh()->login_count);

        $this->post('/deconnexion')->assertRedirect(route('home'));
        $this->travel(1)->hours();
        $this->prepareCode($user);
        $this->withSession(['login_email' => $user->email])
            ->post('/connexion/code', ['code' => '123456'])
            ->assertRedirect(route('dashboard'));

        $user->refresh();
        $this->assertSame(2, $user->login_count);
        $this->assertTrue($user->last_login_at->equalTo(now()));
    }

    public function test_un_code_incorrect_ne_modifie_pas_les_statistiques(): void
    {
        $date = now()->subDay()->startOfSecond();
        $user = User::factory()->create([
            'role' => 'locataire',
            'login_count' => 3,
            'last_login_at' => $date,
        ]);
        $this->prepareCode($user);

        $this->withSession(['login_email' => $user->email])
            ->post('/connexion/code', ['code' => '654321'])
            ->assertSessionHasErrors('code');

        $this->assertGuest();
        $user->refresh();
        $this->assertSame(3, $user->login_count);
        $this->assertTrue($user->last_login_at->equalTo($date));
    }
}