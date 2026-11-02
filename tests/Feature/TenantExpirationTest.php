<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class TenantExpirationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['cache.default' => 'array', 'session.driver' => 'array']);
        $this->travelTo(now()->setDate(2026, 11, 2)->setTime(12, 0));
    }

    public function test_la_purge_respecte_les_deux_ans_et_preserve_les_administrateurs_et_leurs_notes(): void
    {
        $expired = User::factory()->create(['role' => 'locataire', 'created_at' => now()->subYears(2), 'last_login_at' => now()]);
        $inactive = User::factory()->create(['role' => 'locataire', 'is_active' => false, 'created_at' => now()->subYears(3)]);
        $recent = User::factory()->create(['role' => 'locataire', 'created_at' => now()->subYears(2)->addSecond()]);
        $admin = User::factory()->create(['role' => 'admin', 'created_at' => now()->subYears(4)]);
        $note = Note::factory()->create(['author_id' => $admin->id]);

        $this->artisan('users:purge-expired --dry-run')->expectsOutput('2 compte(s) à supprimer.')->assertSuccessful();
        $this->assertModelExists($expired);
        $this->artisan('users:purge-expired')->expectsOutput('2 compte(s) supprimé(s).')->assertSuccessful();
        $this->assertModelMissing($expired);
        $this->assertModelMissing($inactive);
        $this->assertModelExists($recent);
        $this->assertModelExists($admin);
        $this->assertSame($admin->id, $note->fresh()->author_id);
        $this->artisan('users:purge-expired')->expectsOutput('0 compte(s) supprimé(s).')->assertSuccessful();
    }

    public function test_un_compte_cree_le_29_fevrier_expire_le_28_fevrier_deux_ans_plus_tard(): void
    {
        $user = User::factory()->create(['role' => 'locataire', 'created_at' => '2024-02-29 12:00:00']);
        $this->travelTo(now()->setDate(2026, 2, 28)->setTime(11, 59, 59));
        $this->assertFalse($user->hasExpired());
        $this->travel(1)->seconds();
        $this->artisan('users:purge-expired')->assertSuccessful();
        $this->assertModelMissing($user);
    }

    public function test_la_suppression_manuelle_et_la_purge_nettoient_les_donnees_de_connexion(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        DB::table('sessions')->insert(['id' => 'admin-session', 'user_id' => $admin->id, 'payload' => '', 'last_activity' => now()->timestamp]);
        foreach (['manual', 'automatic'] as $mode) {
            $user = User::factory()->create(['role' => 'locataire', 'created_at' => now()->subYears(3)]);
            DB::table('sessions')->insert(['id' => $mode, 'user_id' => $user->id, 'payload' => '', 'last_activity' => now()->timestamp]);
            DB::table('password_reset_tokens')->insert(['email' => $user->email, 'token' => 'old-token']);
            $key = 'login-code:'.hash('sha256', $user->email);
            Cache::put($key, 'code', 600);
            RateLimiter::hit($key.':send', 600);
            RateLimiter::hit($key.':verify', 600);

            if ($mode === 'manual') {
                $this->actingAs($admin)->delete('/admin/utilisateurs/'.$user->id)->assertRedirect(route('admin.users'));
            } else {
                $this->artisan('users:purge-expired')->assertSuccessful();
            }

            $this->assertModelMissing($user);
            $this->assertDatabaseMissing('sessions', ['id' => $mode]);
            $this->assertDatabaseMissing('password_reset_tokens', ['email' => $user->email]);
            $this->assertNull(Cache::get($key));
            $this->assertSame(0, RateLimiter::attempts($key.':send'));
            $this->assertSame(0, RateLimiter::attempts($key.':verify'));
        }
        $this->assertDatabaseHas('sessions', ['id' => 'admin-session']);
    }

    public function test_un_locataire_expire_ne_recoit_pas_de_code_et_ne_peut_plus_se_connecter(): void
    {
        Mail::fake();
        $user = User::factory()->create(['role' => 'locataire', 'created_at' => now()->subYears(2)]);
        $this->post('/connexion', ['email' => $user->email])->assertRedirect(route('code.form'));
        Mail::assertNothingSent();
        Cache::put('login-code:'.hash('sha256', $user->email), Hash::make('123456'), 600);
        $this->withSession(['login_email' => $user->email])
            ->post('/connexion/code', ['code' => '123456'])->assertSessionHasErrors('code');
        $this->assertGuest();
    }

    public function test_une_session_existante_ne_donne_plus_acces_apres_expiration(): void
    {
        $user = User::factory()->create(['role' => 'locataire', 'created_at' => now()->subYears(2)->addSecond()]);
        $this->actingAs($user)->get('/espace')->assertOk();
        $this->travel(1)->seconds();
        $this->get('/espace')->assertForbidden();
        $this->get('/espace/guide')->assertForbidden();
        $admin = User::factory()->create(['role' => 'admin', 'created_at' => now()->subYears(4)]);
        $this->actingAs($admin)->get('/admin')->assertOk();
    }
}
