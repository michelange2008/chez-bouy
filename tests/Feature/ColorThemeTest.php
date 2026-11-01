<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ColorThemeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_le_site_public_et_l_espace_locataire_utilisent_la_palette_publique(): void
    {
        $this->get('/')->assertOk()->assertSee('data-theme="public"', false);
        $this->get('/connexion')->assertOk()->assertSee('data-theme="public"', false);
        $this->actingAs(User::factory()->create(['role' => 'locataire']))
            ->get('/espace')->assertOk()->assertSee('data-theme="public"', false);
    }

    public function test_la_palette_admin_depend_de_la_page_et_non_du_role_connecte(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['/admin', '/admin/utilisateurs', '/admin/emplacements', '/admin/photos', '/admin/fichiers', '/admin/notes', '/admin/annuaire', '/admin/notes/ajouter', '/admin/annuaire/ajouter'] as $url) {
            $this->get($url)->assertOk()->assertSee('data-theme="admin"', false);
        }
        $this->get('/')->assertOk()->assertSee('data-theme="public"', false);
        $this->get('/espace')->assertOk()->assertSee('data-theme="public"', false);
    }
}
