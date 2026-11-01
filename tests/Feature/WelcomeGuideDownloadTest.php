<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WelcomeGuideDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['stay.welcome_guides' => [], 'app.locale' => 'fr']);
    }

    public function test_le_bouton_telecharge_le_guide_de_la_langue_selectionnee(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        foreach (['fr', 'en', 'it'] as $locale) {
            $guide = File::factory()->create(['locale' => $locale]);
            Storage::disk('local')->put($guide->filename, 'Guide '.$locale);
            config(['stay.welcome_guides.'.$locale => $guide->id]);
        }
        foreach (['fr', 'en', 'it'] as $locale) {
            $this->withSession(['locale' => $locale])->get('/espace')->assertOk()->assertSee('href="'.route('stay.guide').'"', false);
            $response = $this->get('/espace/guide')->assertOk()->assertDownload('guide-accueil-'.$locale.'.pdf')->assertHeader('X-Content-Type-Options', 'nosniff');
            $this->assertSame('Guide '.$locale, $response->streamedContent());
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/espace/guide')->assertOk();
    }

    public function test_le_telechargement_est_reserve_aux_comptes_actifs_autorises(): void
    {
        $this->get('/espace/guide')->assertRedirect(route('login'));
        foreach ([['role' => 'visiteur'], ['role' => 'locataire', 'is_active' => false]] as $attributes) {
            $this->actingAs(User::factory()->create($attributes))->get('/espace/guide')->assertForbidden();
        }
    }

    public function test_un_guide_absent_ne_propose_pas_de_lien_et_retourne_404(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        $this->get('/espace')->assertOk()->assertSee('Le guide d’accueil n’est pas disponible dans cette langue.')->assertDontSee('href="'.route('stay.guide').'"', false);
        $this->get('/espace/guide')->assertNotFound();
        $guide = File::factory()->create(['locale' => 'fr']);
        config(['stay.welcome_guides.fr' => $guide->id]);
        $this->get('/espace/guide')->assertNotFound();
        Storage::disk('local')->assertMissing($guide->filename);
    }

    public function test_un_document_non_designe_ou_d_une_autre_langue_ne_peut_pas_etre_telecharge(): void
    {
        Storage::fake('local');
        $other = File::factory()->create(['locale' => 'en']);
        Storage::disk('local')->put($other->filename, 'Other document');
        $this->actingAs(User::factory()->create(['role' => 'locataire']))->get('/espace/guide?file='.$other->id)->assertNotFound();
        config(['stay.welcome_guides.fr' => $other->id]);
        $this->get('/espace/guide')->assertNotFound();
        Storage::disk('local')->assertExists($other->filename);
    }
}
