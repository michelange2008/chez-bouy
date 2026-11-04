<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Models\PhotoLocation;
use App\Models\StaySection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LocalizedStayPhotosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    public function test_les_selections_et_leur_ordre_sont_independants_et_peuvent_partager_une_photo(): void
    {
        Storage::fake('local');
        $section = StaySection::factory()->create(['published' => true]);
        $french = Photo::factory()->create(['published' => true]);
        $english = Photo::factory()->create(['published' => true]);
        $shared = Photo::factory()->create(['published' => true]);
        foreach ([$french, $english, $shared] as $photo) {
            Storage::disk('local')->put($photo->filename, 'image');
        }
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['published' => '1', 'translations' => $section->translations,
            'photos' => ['fr' => [$french->id, $shared->id], 'en' => [$shared->id, $english->id], 'it' => []]];
        $this->patch(route('admin.stay.update', $section), $data)->assertRedirect();
        $this->assertSame([$french->id, $shared->id], $section->photosForLocale('fr')->get()->modelKeys());
        $this->assertSame([$shared->id, $english->id], $section->photosForLocale('en')->get()->modelKeys());
        $this->get(route('admin.stay.edit', $section))->assertOk()->assertSee('name="photos[fr][]"', false)->assertSee('name="photos[en][]"', false)->assertSee('name="photos[it][]"', false);
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        $this->get('/espace/arrivee')->assertOk()->assertSeeInOrder([route('stay.photos.image', $french), route('stay.photos.image', $shared)])->assertDontSee(route('stay.photos.image', $english));
        $this->withSession(['locale' => 'en'])->get('/espace/arrivee')->assertOk()->assertSeeInOrder([route('stay.photos.image', $shared), route('stay.photos.image', $english)])->assertDontSee(route('stay.photos.image', $french));
        $this->get(route('stay.photos.image', $french))->assertNotFound();
        $this->get(route('stay.photos.image', $english))->assertOk();
        $this->withSession(['locale' => 'it'])->get('/espace/arrivee')->assertOk()->assertDontSee('<figure>', false);
        $this->get(route('stay.photos.image', $shared))->assertNotFound();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data['photos']['en'] = [];
        $this->patch(route('admin.stay.update', $section), $data)->assertRedirect();
        $this->assertSame([$french->id, $shared->id], $section->photosForLocale('fr')->get()->modelKeys());
        $this->assertSame([], $section->photosForLocale('en')->get()->modelKeys());
    }

    public function test_la_migration_reprend_les_photos_existantes_dans_les_trois_langues(): void
    {
        $migration = require database_path('migrations/2026_11_04_172534_localize_stay_section_photos.php');
        $migration->down();
        $section = StaySection::factory()->create();
        $photo = Photo::factory()->create();
        DB::table('photo_stay_section')->insert(['photo_id' => $photo->id, 'stay_section_id' => $section->id, 'position' => 7]);
        $migration->up();
        foreach (['fr', 'en', 'it'] as $locale) {
            $this->assertDatabaseHas('photo_stay_section', ['photo_id' => $photo->id, 'stay_section_id' => $section->id, 'locale' => $locale, 'position' => 7]);
        }
        $this->assertDatabaseCount('photo_stay_section', 3);
    }

    public function test_l_import_associe_la_photo_uniquement_a_la_langue_d_origine(): void
    {
        Storage::fake('local');
        $section = StaySection::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->patch(route('admin.stay.update', $section), ['published' => '0', 'translations' => $section->translations, 'action' => 'en'])
            ->assertRedirect(route('admin.photos.create', ['section' => $section->id, 'section_locale' => 'en']));
        $this->get(route('admin.photos.create', ['section' => $section->id, 'section_locale' => 'en']))->assertOk()->assertSee('name="section_locale" value="en"', false);
        $this->post(route('admin.photos.store'), ['image' => UploadedFile::fake()->image('instructions.jpg'), 'published' => '1', 'section_id' => $section->id, 'section_locale' => 'en', 'translations' => ['fr' => ['title' => 'Instructions en anglais']]])
            ->assertRedirect(route('admin.stay.edit', ['section' => $section->id, 'section_locale' => 'en']));
        $photo = Photo::query()->sole();
        $this->assertDatabaseHas('photo_stay_section', ['photo_id' => $photo->id, 'locale' => 'en']);
        $this->assertDatabaseCount('photo_stay_section', 1);
        Storage::disk('local')->assertExists($photo->filename);
    }

    public function test_les_langues_invalides_et_doublons_sont_refuses(): void
    {
        $section = StaySection::factory()->create();
        $photo = Photo::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([['es' => [$photo->id]], ['fr' => [$photo->id, $photo->id]], ['en' => [9999]]] as $photos) {
            $this->patch(route('admin.stay.update', $section), ['published' => '0', 'translations' => $section->translations, 'photos' => $photos])->assertSessionHasErrors();
        }
        $this->post(route('admin.photos.store'), ['section_id' => $section->id, 'section_locale' => 'es'])->assertSessionHasErrors('section_locale');
        $this->assertDatabaseCount('photo_stay_section', 0);
    }

    public function test_la_phototheque_reserve_les_emplacements_a_l_accueil(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $photo = Photo::factory()->create();
        $arrival = PhotoLocation::query()->where('route_name', 'stay.arrivee')->sole();
        $home = PhotoLocation::query()->where('route_name', 'home')->sole();
        $this->get(route('admin.photos.create'))->assertOk()->assertDontSee('<option value="'.$arrival->id.'"', false)->assertSee('<option value="'.$home->id.'"', false);
        $data = ['published' => '0', 'translations' => ['fr' => ['title' => 'Photo']]];
        $this->patch(route('admin.photos.update', $photo), [...$data, 'location_id' => $arrival->id])->assertSessionHasErrors('location_id');
        $this->patch(route('admin.photos.update', $photo), [...$data, 'location_id' => $home->id, 'position' => 3])->assertRedirect();
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photo->id, 'photo_location_id' => $home->id, 'position' => 3]);
    }
}
