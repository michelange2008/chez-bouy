<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = ['admin', 'admin.users', 'admin.photos', 'admin.files'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_les_visiteurs_doivent_se_connecter(): void
    {
        foreach (self::PAGES as $route) {
            $this->get(route($route))->assertRedirect(route('login'));
        }
    }

    public function test_les_locataires_n_ont_pas_acces_aux_pages_admin(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'locataire', 'is_active' => true]));
        foreach (self::PAGES as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_les_admins_inactifs_n_ont_pas_acces(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => false]));
        foreach (self::PAGES as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_chaque_page_admin_affiche_le_menu_et_la_page_active(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin', 'is_active' => true]));
        foreach (self::PAGES as $route) {
            $response = $this->get(route($route))->assertOk()->assertViewIs($route);
            foreach (['admin.users', 'admin.photos', 'admin.files'] as $link) {
                $response->assertSee('href="'.route($link).'"', false);
            }
            if ($route !== 'admin') {
                $this->assertMatchesRegularExpression(
                    '~href="'.preg_quote(route($route), '~').'"\s+aria-current="page"~',
                    $response->getContent(),
                );
            }
        }
    }
}
