<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegalPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_la_page_est_publique_et_traduite(): void
    {
        foreach (['fr' => 'Informations légales', 'en' => 'Legal information', 'it' => 'Informazioni legali'] as $locale => $title) {
            $this->withSession(['locale' => $locale])->get('/informations-legales')
                ->assertOk()->assertSee($title)->assertSee('aria-current="page"', false);
        }
    }

    public function test_le_lien_est_present_sur_les_pages_publiques_locataire_et_admin(): void
    {
        $link = 'href="'.route('legal').'"';
        $this->get('/')->assertOk()->assertSee($link, false);
        $this->get('/connexion')->assertOk()->assertSee($link, false);
        $this->actingAs(User::factory()->create(['role' => 'locataire']))
            ->get('/espace')->assertOk()->assertSee($link, false);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin')->assertOk()->assertSee($link, false);
    }

    public function test_les_informations_configurees_sont_echappees_et_les_champs_vides_signales(): void
    {
        config(['legal.editor.name' => '<script>Editeur</script>', 'legal.host.name' => null]);
        $this->withSession(['locale' => 'fr'])->get('/informations-legales')
            ->assertOk()->assertSee('<script>Editeur</script>')
            ->assertDontSee('<script>Editeur</script>', false)->assertSee('À compléter');
    }
}
