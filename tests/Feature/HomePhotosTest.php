<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Models\PhotoLocation;
use App\Models\PhotoTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomePhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    private function photo(string $routeName, bool $published, int $position, string $title): Photo
    {
        $photo = Photo::factory()->create(['published' => $published]);
        $photo->locations()->attach(PhotoLocation::query()->where('route_name', $routeName)->sole(), ['position' => $position]);
        $translation = new PhotoTranslation;
        $translation->locale = 'fr';
        $translation->title = $title;
        $photo->translations()->save($translation);
        Storage::disk('local')->put($photo->filename, 'image '.$title);

        return $photo;
    }

    public function test_l_accueil_affiche_les_photos_publiees_dans_l_ordre_avec_leur_legende(): void
    {
        Storage::fake('local');
        $last = $this->photo('home', true, 9, 'Dernière photo');
        $first = $this->photo('home', true, 1, 'Première photo');
        $samePosition = $this->photo('home', true, 1, 'Deuxième photo');
        $this->photo('home', false, 0, 'Brouillon secret');
        $this->photo('stay.arrivee', true, 0, 'Photo privée');
        $this->get('/')->assertOk()->assertSeeInOrder(['Première photo', 'Deuxième photo', 'Dernière photo'])
            ->assertSee('<figcaption>Première photo</figcaption>', false)
            ->assertSee(route('home.photos.image', $first))->assertDontSee('Brouillon secret')->assertDontSee('Photo privée');
        Storage::disk('local')->assertExists([$last->filename, $first->filename, $samePosition->filename]);
    }

    public function test_toutes_les_photos_restent_accessibles_dans_la_galerie(): void
    {
        Storage::fake('local');
        for ($position = 1; $position <= 7; $position++) {
            $this->photo('home', true, $position, 'Vue '.$position);
        }
        $response = $this->get('/')->assertOk()->assertSee('Toutes les photos (7)')
            ->assertSee('Vue 7')->assertSee('data-gallery-dialog', false);
        $this->assertSame(7, substr_count($response->getContent(), 'data-gallery-photo '));
    }

    public function test_la_legende_est_traduite_echappee_et_utilise_le_francais_en_secours(): void
    {
        Storage::fake('local');
        $photo = $this->photo('home', true, 0, 'Le chalet');
        $translation = new PhotoTranslation;
        $translation->locale = 'en';
        $translation->title = '<script>The chalet</script>';
        $photo->translations()->save($translation);
        $this->withSession(['locale' => 'en'])->get('/')->assertOk()->assertSee($translation->title)->assertDontSee($translation->title, false);
        $this->withSession(['locale' => 'it'])->get('/')->assertOk()->assertSee('Le chalet');
        Storage::disk('local')->assertExists($photo->filename);
    }

    public function test_seules_les_images_publiees_de_l_accueil_sont_accessibles_publiquement(): void
    {
        Storage::fake('local');
        $public = $this->photo('home', true, 0, 'Public');
        $draft = $this->photo('home', false, 0, 'Brouillon');
        $private = $this->photo('stay.arrivee', true, 0, 'Privée');
        $unassigned = Photo::factory()->create(['published' => true]);
        Storage::disk('local')->put($unassigned->filename, 'Sans emplacement');
        $response = $this->get(route('home.photos.image', $public))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame('image Public', $response->streamedContent());
        foreach ([$draft, $private, $unassigned] as $photo) {
            $this->get(route('home.photos.image', $photo))->assertNotFound();
        }
        $public->published = false;
        $public->save();
        $this->get(route('home.photos.image', $public))->assertNotFound();
        Storage::disk('local')->assertExists($public->filename);
    }

    public function test_un_fichier_manquant_affiche_un_placeholder_et_retourne_404(): void
    {
        Storage::fake('local');
        $photo = $this->photo('home', true, 0, 'Photo manquante');
        Storage::disk('local')->delete($photo->filename);
        $this->get('/')->assertOk()->assertSee('Photo à ajouter')->assertSee('Photo manquante')->assertDontSee(route('home.photos.image', $photo));
        $this->get(route('home.photos.image', $photo))->assertNotFound();
        Storage::disk('local')->assertMissing($photo->filename);
    }

    public function test_l_accueil_sans_photo_reste_accessible(): void
    {
        $this->get('/')->assertOk()->assertDontSee('<figure>', false);
    }
}
