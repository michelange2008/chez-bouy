<?php

namespace Tests\Feature;

use App\Models\StaySection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeSectionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    public function test_l_accueil_affiche_uniquement_ses_sections_publiees_dans_l_ordre_et_la_langue(): void
    {
        StaySection::factory()->create(['page' => 'home', 'position' => 2, 'published' => true, 'translations' => ['fr' => ['title' => 'Seconde section', 'body' => '**Bienvenue**']]]);
        StaySection::factory()->create(['page' => 'home', 'position' => 1, 'published' => true, 'translations' => ['fr' => ['body' => 'Première section'], 'en' => ['body' => 'First section']]]);
        StaySection::factory()->create(['page' => 'home', 'published' => false, 'translations' => ['fr' => ['body' => 'Brouillon secret']]]);
        StaySection::factory()->create(['published' => true, 'translations' => ['fr' => ['body' => 'Consignes privées']]]);
        $this->get('/')->assertOk()->assertSeeInOrder(['Première section', 'Seconde section'])->assertSee('<strong>Bienvenue</strong>', false)->assertDontSee('Brouillon secret')->assertDontSee('Consignes privées');
        $this->withSession(['locale' => 'en'])->get('/')->assertOk()->assertSee('First section')->assertSee('Seconde section');
    }

    public function test_l_administrateur_gere_les_sections_publiques_depuis_la_page_accueil(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('admin.stay'))->assertOk()->assertSee(route('admin.stay.show', 'home'));
        $this->get(route('admin.stay.create', 'home'))->assertOk()->assertDontSee('name="photos[', false);
        $data = ['published' => '1', 'translations' => ['fr' => ['title' => 'Présentation', 'body' => 'Notre accueil']]];
        $this->post(route('admin.stay.store', 'home'), $data)->assertRedirect(route('admin.stay.show', 'home'));
        $section = StaySection::query()->sole();
        $this->get('/')->assertOk()->assertSee('Notre accueil');
        $this->get(route('admin.stay.show', 'home'))->assertOk()->assertSee('href="'.route('home').'"', false);
        $second = StaySection::factory()->create(['page' => 'home', 'position' => 2]);
        $this->patch(route('admin.stay.move', $second), ['direction' => 'up'])->assertRedirect();
        $this->assertSame(1, $second->fresh()->position);
        $this->patch(route('admin.stay.update', $section), [...$data, 'action' => 'fr'])->assertSessionHasErrors('action');
        $this->delete(route('admin.stay.destroy', $section))->assertRedirect();
        $this->assertModelMissing($section);
    }
}
