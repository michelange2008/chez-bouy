<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessControlTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Ces tests vérifient les accès sans nécessiter un build Vite.
        $this->withoutVite();
    }

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->get('/espace')
            ->assertRedirect(route('login'));

        $this->get('/admin')
            ->assertRedirect(route('login'));
    }

    public function test_un_locataire_accede_a_son_espace(): void
    {
        $locataire = User::factory()->create([
            'role' => 'locataire',
        ]);

        $this->actingAs($locataire)
            ->get('/espace')
            ->assertOk();
    }

    public function test_un_locataire_ne_peut_pas_acceder_a_l_administration(): void
    {
        $locataire = User::factory()->create([
            'role' => 'locataire',
        ]);

        $this->actingAs($locataire)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_un_admin_accede_aux_deux_espaces(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/espace')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk();
    }
}