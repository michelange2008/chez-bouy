<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Models\PhotoTranslation;
use App\Models\StaySection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoIdentificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_le_nom_original_survit_a_la_compression_et_aux_modifications_sans_remplacement(): void
    {
        $data = ['published' => '1', 'translations' => ['fr' => ['title' => 'Instructions']]];
        $this->post(route('admin.photos.store'), [...$data, 'image' => UploadedFile::fake()->image('photo.webp'), 'original_filename' => 'instructions_en.jpg'])->assertRedirect();
        $photo = Photo::query()->sole();
        $this->assertSame('instructions_en.jpg', $photo->original_filename);
        $this->assertNotSame('instructions_en.jpg', basename($photo->filename));
        Storage::disk('local')->assertExists($photo->filename);
        $this->patch(route('admin.photos.update', $photo), [...$data, 'original_filename' => 'autre.jpg'])->assertRedirect();
        $this->assertSame('instructions_en.jpg', $photo->fresh()->original_filename);
        $this->patch(route('admin.photos.update', $photo), [...$data, 'image' => UploadedFile::fake()->image('instructions_it.png')])->assertRedirect();
        $this->assertSame('instructions_it.png', $photo->fresh()->original_filename);
    }

    public function test_sans_preparation_javascript_le_nom_du_fichier_importe_est_conserve(): void
    {
        $this->post(route('admin.photos.store'), ['published' => '0', 'translations' => ['fr' => ['title' => 'Entrée']], 'image' => UploadedFile::fake()->image('entree_fr.jpg')])->assertRedirect();
        $this->assertSame('entree_fr.jpg', Photo::query()->sole()->original_filename);
    }

    public function test_les_sections_affichent_le_nom_echappe_et_proposent_une_image_grande_hors_du_label(): void
    {
        $section = StaySection::factory()->create();
        $photo = Photo::factory()->create(['original_filename' => '<script>instructions_en</script>.jpg']);
        $old = Photo::factory()->create();
        $translation = new PhotoTranslation;
        $translation->locale = 'fr';
        $translation->title = 'Ancienne photo';
        $old->translations()->save($translation);
        $response = $this->get(route('admin.stay.edit', $section))->assertOk()
            ->assertSee('(<script>instructions_en</script>.jpg)')
            ->assertDontSee('<script>instructions_en</script>', false)
            ->assertDontSee('('.basename($old->filename).')')
            ->assertSee('href="'.route('admin.photos.image', $photo).'"', false)
            ->assertSee('data-photo-dialog', false);
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $this->assertSame(0, $xpath->query('//label//a[@data-photo-enlarge]')->length);
        $this->assertSame(6, $xpath->query('//a[@data-photo-enlarge]')->length);
    }

    public function test_un_nom_original_trop_long_est_refuse(): void
    {
        $this->post(route('admin.photos.store'), ['published' => '0', 'translations' => ['fr' => ['title' => 'Entrée']], 'image' => UploadedFile::fake()->image('image.jpg'), 'original_filename' => str_repeat('a', 256)])->assertSessionHasErrors('original_filename');
        $this->assertDatabaseCount('photos', 0);
    }
}
