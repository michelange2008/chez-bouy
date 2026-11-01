<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileAdministrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    public function test_les_fichiers_sont_reserves_aux_administrateurs(): void
    {
        $file = File::factory()->create();
        $this->get('/admin/fichiers')->assertRedirect('/connexion');
        $this->get("/admin/fichiers/$file->id/telecharger")->assertRedirect('/connexion');
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        foreach (['/admin/fichiers', '/admin/fichiers/ajouter', "/admin/fichiers/$file->id/modifier", "/admin/fichiers/$file->id/telecharger"] as $url) {
            $this->get($url)->assertForbidden();
        }
        $this->post('/admin/fichiers', [])->assertForbidden();
        $this->patch("/admin/fichiers/$file->id", [])->assertForbidden();
        $this->delete("/admin/fichiers/$file->id")->assertForbidden();
        $this->assertModelExists($file);
    }

    public function test_import_remplacement_et_suppression_d_un_fichier_prive(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get('/admin/fichiers/ajouter')->assertOk()->assertSee('Français')->assertSee('Italiano');
        $this->post('/admin/fichiers', [
            'upload' => UploadedFile::fake()->createWithContent('guide.txt', 'Bienvenue'),
            'title' => 'Guidebook', 'description' => null, 'locale' => 'en',
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/fichiers');
        $file = File::query()->sole();
        $oldFilename = $file->filename;
        $this->assertStringStartsWith('file/', $oldFilename);
        Storage::disk('local')->assertExists($oldFilename);
        $this->assertDatabaseHas('files', ['id' => $file->id, 'title' => 'Guidebook', 'locale' => 'en', 'description' => null]);
        $this->get('/admin/fichiers')->assertOk()->assertSee('Guide');
        $this->get("/admin/fichiers/$file->id/modifier")->assertOk()->assertSee('Guidebook');
        $response = $this->get("/admin/fichiers/$file->id/telecharger")->assertOk()->assertDownload();
        $this->assertSame('Bienvenue', $response->streamedContent());
        $this->patch("/admin/fichiers/$file->id", [
            'upload' => UploadedFile::fake()->createWithContent('guide.txt', 'Nouveau guide'),
            'title' => 'Nouveau guide', 'description' => 'Bienvenue', 'locale' => 'fr',
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/fichiers');
        $file->refresh();
        Storage::disk('local')->assertMissing($oldFilename);
        Storage::disk('local')->assertExists($file->filename);
        $this->assertDatabaseHas('files', ['id' => $file->id, 'title' => 'Nouveau guide', 'description' => 'Bienvenue', 'locale' => 'fr']);
        $this->delete("/admin/fichiers/$file->id")->assertRedirect('/admin/fichiers');
        $this->assertModelMissing($file);
        Storage::disk('local')->assertMissing($file->filename);
    }

    public function test_modifier_les_metadonnees_conserve_le_fichier_et_ne_traduit_pas_son_titre(): void
    {
        Storage::fake('local');
        $file = File::factory()->create();
        Storage::disk('local')->put($file->filename, 'Guide');
        $this->actingAs(User::factory()->create(['role' => 'admin']))->patch("/admin/fichiers/$file->id", [
            'title' => 'Welcome guide', 'description' => 'Welcome', 'locale' => 'en',
        ])->assertRedirect('/admin/fichiers');
        $this->assertDatabaseHas('files', ['id' => $file->id, 'filename' => $file->filename, 'title' => 'Welcome guide', 'locale' => 'en']);
        Storage::disk('local')->assertExists($file->filename);
        $this->withSession(['locale' => 'it'])->get('/admin/fichiers')->assertOk()->assertSee('Welcome guide')->assertSee('English');
    }

    public function test_la_validation_refuse_les_champs_manquants_et_invalides(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->post('/admin/fichiers', [])->assertSessionHasErrors(['upload', 'title', 'locale']);
        $this->post('/admin/fichiers', [
            'upload' => UploadedFile::fake()->create('guide.pdf', 10241),
            'title' => str_repeat('a', 256), 'description' => str_repeat('a', 50001), 'locale' => 'de',
        ])->assertSessionHasErrors(['upload', 'title', 'description', 'locale']);
        $this->post('/admin/fichiers', [
            'upload' => 'file/other.pdf', 'title' => 'Guide', 'locale' => 'fr',
        ])->assertSessionHasErrors('upload');
        $this->assertDatabaseCount('files', 0);
        Storage::disk('local')->assertDirectoryEmpty('file');
    }

    public function test_un_fichier_absent_retourne_404(): void
    {
        Storage::fake('local');
        $file = File::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get("/admin/fichiers/$file->id/telecharger")->assertNotFound();
        Storage::disk('local')->assertMissing($file->filename);
    }

    public function test_les_textes_sont_echappes(): void
    {
        $file = File::factory()->create(['title' => '<script>titre</script>', 'description' => '<script>description</script>']);
        $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/admin/fichiers')
            ->assertOk()->assertSee($file->title)->assertSee($file->description)
            ->assertDontSee($file->title, false)->assertDontSee($file->description, false);
    }

    public function test_deux_versions_linguistiques_possedent_des_fichiers_distincts(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach (['fr', 'en', 'it'] as $locale) {
            $this->post('/admin/fichiers', [
                'upload' => UploadedFile::fake()->createWithContent('guide.txt', $locale),
                'title' => 'Guide '.$locale, 'locale' => $locale,
            ])->assertSessionHasNoErrors()->assertRedirect('/admin/fichiers');
        }
        $this->assertDatabaseCount('files', 3);
        $files = File::query()->get();
        $this->assertCount(3, $files->pluck('filename')->unique());
        $english = $files->firstWhere('locale', 'en');
        $this->delete("/admin/fichiers/$english->id")->assertRedirect('/admin/fichiers');
        Storage::disk('local')->assertMissing($english->filename);
        foreach ($files->where('locale', '!=', 'en') as $file) {
            Storage::disk('local')->assertExists($file->filename);
            $this->assertModelExists($file);
        }
    }
}
