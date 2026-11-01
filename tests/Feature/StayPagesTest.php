<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StayPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        'arrivee' => "Arriver à l'appartement",
        'installation' => "S'installer dans l'appartement",
        'appartement' => "Petit tour de l'appartement",
        'depart' => 'Le jour du départ',
        'chamonix' => 'Séjourner à Chamonix',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    public function test_le_sommaire_et_les_pages_sont_accessibles_aux_locataires_et_administrateurs(): void
    {
        foreach (['locataire', 'admin'] as $role) {
            $this->actingAs(User::factory()->create(['role' => $role]));
            $summary = $this->get('/espace')->assertOk();
            foreach (self::PAGES as $page => $title) {
                $summary->assertSee($title)->assertSee(route('stay.'.$page));
                $this->get('/espace/'.$page)->assertOk()->assertSee($title)
                    ->assertSee('Retour au sommaire')->assertSee('href="'.route('dashboard').'"', false)
                    ->assertSee('data-theme="public"', false);
            }
        }
    }

    public function test_les_visiteurs_doivent_se_connecter(): void
    {
        foreach (array_keys(self::PAGES) as $page) {
            $this->get('/espace/'.$page)->assertRedirect(route('login'));
        }
    }

    public function test_les_comptes_inactifs_et_les_roles_non_autorises_sont_refuses(): void
    {
        foreach ([['role' => 'locataire', 'is_active' => false], ['role' => 'visiteur']] as $attributes) {
            $this->actingAs(User::factory()->create($attributes));
            foreach (array_keys(self::PAGES) as $page) {
                $this->get('/espace/'.$page)->assertForbidden();
            }
        }
    }

    public function test_les_titres_et_le_retour_suivent_la_langue_du_sejour(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        foreach (['en' => ['Arriving at the apartment', 'Back to contents'], 'it' => ['Arrivare all’appartamento', 'Torna all’indice']] as $locale => [$title, $back]) {
            $this->withSession(['locale' => $locale])->get('/espace')->assertOk()->assertSee($title);
            $this->get('/espace/arrivee')->assertOk()->assertSee($title)->assertSee($back);
        }
    }
}
