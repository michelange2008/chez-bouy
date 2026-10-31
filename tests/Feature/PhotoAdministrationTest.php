<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Models\PhotoLocation;
use App\Models\User;
use App\PhotoImage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    public function test_les_pages_et_actions_sont_reservees_aux_administrateurs(): void
    {
        $photo = Photo::factory()->create();
        $location = PhotoLocation::factory()->create();
        $this->get('/admin/photos')->assertRedirect('/connexion');
        $this->get('/admin/emplacements')->assertRedirect('/connexion');
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        foreach (['/admin/photos', '/admin/photos/ajouter', "/admin/photos/$photo->id/modifier", "/admin/photos/$photo->id/image", '/admin/emplacements', "/admin/emplacements/$location->id/modifier"] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/photos', [])->assertForbidden();
        $this->patch("/admin/photos/$photo->id", [])->assertForbidden();
        $this->delete("/admin/photos/$photo->id")->assertForbidden();
        $this->post('/admin/emplacements', [])->assertForbidden();
        $this->patch("/admin/emplacements/$location->id", [])->assertForbidden();
        $this->delete("/admin/emplacements/$location->id")->assertForbidden();
        $this->assertModelExists($photo);
        $this->assertModelExists($location);
    }

    public function test_un_admin_peut_gerer_les_emplacements_sans_supprimer_les_photos(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/emplacements', ['code' => 'public', 'name' => 'Accueil'])->assertRedirect('/admin/emplacements');
        $location = PhotoLocation::query()->sole();
        $this->get('/admin/emplacements')->assertOk()->assertSee('Accueil');
        $this->get("/admin/emplacements/$location->id/modifier")->assertOk();
        $this->patch("/admin/emplacements/$location->id", ['code' => 'public', 'name' => 'Accueil public'])->assertRedirect('/admin/emplacements');
        $this->assertDatabaseHas('photo_locations', ['id' => $location->id, 'name' => 'Accueil public']);
        $photo = Photo::factory()->create();
        $photo->locations()->attach($location, ['position' => 2]);
        $this->delete("/admin/emplacements/$location->id")->assertRedirect('/admin/emplacements');
        $this->assertModelMissing($location);
        $this->assertModelExists($photo);
        $this->assertDatabaseCount('photo_location', 0);
    }

    public function test_les_codes_d_emplacement_sont_valides_et_uniques(): void
    {
        PhotoLocation::factory()->create(['code' => 'public']);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/emplacements', ['code' => 'public', 'name' => 'Doublon'])->assertSessionHasErrors('code');
        $this->post('/admin/emplacements', ['code' => '../arrivée', 'name' => 'Arrivée'])->assertSessionHasErrors('code');
        $this->assertDatabaseCount('photo_locations', 1);
    }

    public function test_import_modification_et_suppression_d_une_photo(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $location = PhotoLocation::factory()->create();
        $this->get('/admin/photos/ajouter')->assertOk()->assertSee('Français')->assertSee('Italiano');
        $this->post('/admin/photos', [
            'image' => UploadedFile::fake()->image('chalet.jpg'), 'published' => '1',
            'translations' => ['fr' => ['title' => 'Le chalet'], 'en' => ['title' => 'The chalet', 'description' => 'Mountain view']],
            'locations' => [['id' => $location->id, 'position' => 3]],
        ])->assertRedirect('/admin/photos');
        $photo = Photo::query()->sole();
        $oldFilename = $photo->filename;
        Storage::disk('local')->assertExists($oldFilename);
        $this->assertDatabaseHas('photos', ['id' => $photo->id, 'published' => true]);
        $this->assertDatabaseHas('photo_translations', ['photo_id' => $photo->id, 'locale' => 'en', 'title' => 'The chalet']);
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photo->id, 'photo_location_id' => $location->id, 'position' => 3]);
        $this->get('/admin/photos')->assertOk()->assertSee('Le chalet');
        $this->get("/admin/photos/$photo->id/modifier")->assertOk()->assertSee('The chalet');
        $this->get("/admin/photos/$photo->id/image")->assertOk()->assertHeader('Content-Type', 'image/webp');
        $this->patch("/admin/photos/$photo->id", [
            'image' => UploadedFile::fake()->image('nouvelle.png'), 'published' => '0',
            'translations' => ['fr' => ['title' => 'Vue du chalet'], 'en' => ['title' => null, 'description' => null]],
        ])->assertRedirect('/admin/photos');
        $photo->refresh();
        Storage::disk('local')->assertMissing($oldFilename);
        Storage::disk('local')->assertExists($photo->filename);
        $this->assertDatabaseHas('photos', ['id' => $photo->id, 'published' => false]);
        $this->assertDatabaseHas('photo_translations', ['photo_id' => $photo->id, 'title' => 'Vue du chalet']);
        $this->assertDatabaseMissing('photo_translations', ['photo_id' => $photo->id, 'locale' => 'en']);
        $this->assertDatabaseCount('photo_location', 0);
        $this->delete("/admin/photos/$photo->id")->assertRedirect('/admin/photos');
        $this->assertModelMissing($photo);
        $this->assertDatabaseCount('photo_translations', 0);
        Storage::disk('local')->assertMissing($photo->filename);
    }

    public function test_les_photos_sont_filtrees_et_triees_par_emplacement(): void
    {
        $location = PhotoLocation::factory()->create();
        $last = Photo::factory()->create(['filename' => 'photos/last.jpg']);
        $first = Photo::factory()->create(['filename' => 'photos/first.jpg']);
        Photo::factory()->create(['filename' => 'photos/other.jpg']);
        $location->photos()->attach([$last->id => ['position' => 9], $first->id => ['position' => 1]]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/admin/photos?location='.$location->id)->assertOk()
            ->assertSeeInOrder(['photos/first.jpg', 'photos/last.jpg'])->assertDontSee('photos/other.jpg');
    }

    public function test_la_validation_refuse_les_fichiers_et_traductions_invalides(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/photos', [
            'image' => UploadedFile::fake()->create('script.svg', 1, 'image/svg+xml'),
            'published' => '1', 'translations' => ['fr' => ['title' => ''], 'en' => ['description' => 'Without title']],
            'locations' => [['id' => 999, 'position' => -1]],
        ])->assertSessionHasErrors(['image', 'translations.fr.title', 'translations.en.title', 'locations.0.id', 'locations.0.position']);
        $this->assertDatabaseCount('photos', 0);
        Storage::disk('local')->assertDirectoryEmpty('photos');
    }

    public function test_la_modification_sans_fichier_conserve_l_image_et_change_l_ordre(): void
    {
        Storage::fake('local');
        $photo = Photo::factory()->create();
        Storage::disk('local')->put($photo->filename, 'image');
        $location = PhotoLocation::factory()->create();
        $photo->locations()->attach($location, ['position' => 5]);
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->patch("/admin/photos/$photo->id", [
                'published' => '0', 'translations' => ['fr' => ['title' => 'Chalet']],
                'locations' => [['id' => $location->id, 'position' => 1]],
            ])->assertRedirect('/admin/photos');
        $this->assertDatabaseHas('photos', ['id' => $photo->id, 'filename' => $photo->filename]);
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photo->id, 'position' => 1]);
        Storage::disk('local')->assertExists($photo->filename);
    }

    public function test_import_avec_traductions_vides_et_emplacement_non_coche(): void
    {
        Storage::fake('local');
        $location = PhotoLocation::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/photos', [
                'image' => UploadedFile::fake()->image('photo.jpg'),
                'published' => '0',
                'translations' => [
                    'fr' => ['title' => 'Photo', 'description' => ''],
                    'en' => ['title' => '', 'description' => ''],
                    'it' => ['title' => '', 'description' => ''],
                ],
                'locations' => [
                    ['id' => (string) $location->id, 'position' => '0'],
                    ['position' => '0'],
                ],
            ])->assertSessionHasNoErrors()->assertRedirect('/admin/photos');
        $this->assertDatabaseCount('photos', 1);
        $this->assertDatabaseCount('photo_translations', 1);
        $this->assertDatabaseCount('photo_location', 1);
    }

    public function test_conversion_webp_et_miniature_sans_agrandissement(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/photos', [
                'image' => UploadedFile::fake()->image('panorama.png', 1200, 600),
                'published' => '0', 'translations' => ['fr' => ['title' => 'Panorama']],
            ])->assertSessionHasNoErrors()->assertRedirect('/admin/photos');

        $photo = Photo::query()->sole();
        $main = getimagesizefromstring(Storage::disk('local')->get($photo->filename));
        $thumbnailPath = PhotoImage::thumbnailPath($photo->filename);
        $thumbnail = getimagesizefromstring(Storage::disk('local')->get($thumbnailPath));
        $this->assertSame([1200, 600, 'image/webp'], [$main[0], $main[1], $main['mime']]);
        $this->assertSame([480, 240, 'image/webp'], [$thumbnail[0], $thumbnail[1], $thumbnail['mime']]);
        $this->assertLessThanOrEqual(1024 * 1024, Storage::disk('local')->size($photo->filename));
        $this->get("/admin/photos/$photo->id/image?thumbnail=1")->assertOk()->assertHeader('Content-Type', 'image/webp');

        $this->delete("/admin/photos/$photo->id")->assertRedirect('/admin/photos');
        Storage::disk('local')->assertMissing([$photo->filename, $thumbnailPath]);
    }

    public function test_un_original_non_prepare_est_refuse_sans_ecriture(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post('/admin/photos', [
                'image' => UploadedFile::fake()->image('original.jpg', 2400, 1600)->size(4096),
                'published' => '0', 'translations' => ['fr' => ['title' => 'Original']],
            ])->assertSessionHasErrors('image');

        $this->assertDatabaseCount('photos', 0);
        Storage::disk('local')->assertDirectoryEmpty('photos');
    }
}
