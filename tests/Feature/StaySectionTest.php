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

class StaySectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    private function data(): array
    {
        return ['published' => '1', 'translations' => [
            'fr' => ['title' => 'Entrée', 'body' => "Ouvrez le portail.\nEntrez."],
            'en' => ['title' => 'Entrance', 'body' => 'Open the gate.'],
            'it' => ['title' => 'Ingresso', 'body' => 'Aprite il cancello.'],
        ]];
    }

    public function test_l_administrateur_cree_modifie_et_supprime_une_section_sans_supprimer_les_photos(): void
    {
        Storage::fake('local');
        $photo = Photo::factory()->create();
        $other = Photo::factory()->create();
        Storage::disk('local')->put($photo->filename, 'photo');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->get(route('admin.stay'))->assertOk()->assertSee('Pages locataire');
        $this->get(route('admin.stay.create', 'arrivee'))->assertOk()->assertSee('translations[fr][body]', false);

        $this->post(route('admin.stay.store', 'arrivee'), [...$this->data(), 'photos' => ['fr' => [$other->id, $photo->id]]])->assertRedirect(route('admin.stay.show', 'arrivee'));
        $section = StaySection::query()->sole();
        $this->assertSame([$other->id, $photo->id], $section->photos->modelKeys());
        $this->assertSame('Entrance', $section->translations['en']['title']);
        $this->get(route('admin.stay.show', 'arrivee'))->assertOk()->assertSee('Entrée');
        $this->get(route('admin.stay.edit', $section))->assertOk()->assertSee('Open the gate.');
        $this->patch(route('admin.stay.update', $section), [...$this->data(), 'published' => '0', 'photos' => ['fr' => [$photo->id]]])->assertRedirect();
        $this->assertFalse($section->fresh()->published);
        $this->assertSame([$photo->id], $section->fresh()->photos->modelKeys());
        $this->delete(route('admin.stay.destroy', $section))->assertRedirect();
        $this->assertModelMissing($section);
        $this->assertDatabaseCount('photo_stay_section', 0);
        $this->assertModelExists($photo);
        Storage::disk('local')->assertExists($photo->filename);
    }

    public function test_l_ajout_d_une_photo_enregistre_la_section_et_revient_avec_la_photo_associee(): void
    {
        Storage::fake('local');
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $response = $this->post(route('admin.stay.store', 'arrivee'), [...$this->data(), 'action' => 'fr']);
        $section = StaySection::query()->sole();
        $response->assertRedirect(route('admin.photos.create', ['section' => $section->id, 'section_locale' => 'fr']));
        $this->get(route('admin.photos.create', ['section' => $section->id, 'section_locale' => 'fr']))->assertOk()->assertSee('name="section_id"', false);
        $this->post(route('admin.photos.store'), [
            'image' => UploadedFile::fake()->image('entree.jpg'),
            'published' => '1', 'section_id' => $section->id, 'section_locale' => 'fr',
            'translations' => ['fr' => ['title' => 'Entrée en photo']],
        ])->assertRedirect(route('admin.stay.edit', ['section' => $section->id, 'section_locale' => 'fr']));
        $photo = Photo::query()->sole();
        $this->assertSame([$photo->id], $section->photos->modelKeys());
        Storage::disk('local')->assertExists($photo->filename);
        $this->get(route('admin.stay.edit', $section))->assertOk()->assertSee('Entrée en photo');
    }

    public function test_une_erreur_de_validation_conserve_le_retrait_des_photos(): void
    {
        $section = StaySection::factory()->create();
        $photo = Photo::factory()->create();
        $section->photos()->attach($photo);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $this->from(route('admin.stay.edit', $section))->patch(route('admin.stay.update', $section), [
            'published' => '0', 'translations' => ['fr' => ['body' => '']],
        ])->assertSessionHasErrors('translations.fr.body');
        $this->get(route('admin.stay.edit', $section))->assertOk()->assertDontSee('value="'.$photo->id.'" checked', false);
        $this->patch(route('admin.stay.update', $section), $this->data())->assertRedirect();
        $this->assertDatabaseCount('photo_stay_section', 0);
    }

    public function test_le_deplacement_reordonne_uniquement_la_page_concernee(): void
    {
        $first = StaySection::factory()->create(['position' => 1]);
        $second = StaySection::factory()->create(['position' => 2]);
        $otherPage = StaySection::factory()->create(['page' => 'depart', 'position' => 5]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->patch(route('admin.stay.move', $second), ['direction' => 'up'])->assertRedirect();
        $this->assertSame([$second->id, $first->id], StaySection::query()->where('page', 'arrivee')->orderBy('position')->pluck('id')->all());
        $this->patch(route('admin.stay.move', $second), ['direction' => 'up'])->assertRedirect();
        $this->assertSame(1, $second->fresh()->position);
        $this->patch(route('admin.stay.move', $second), ['direction' => 'down'])->assertRedirect();
        $this->assertSame(2, $second->fresh()->position);
        $this->assertSame(5, $otherPage->fresh()->position);
        $this->patch(route('admin.stay.move', $second), ['direction' => 'invalid'])->assertSessionHasErrors('direction');
    }

    public function test_les_donnees_invalides_ne_modifient_pas_la_section(): void
    {
        $section = StaySection::factory()->create();
        $photo = Photo::factory()->create();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([
            ['published' => 'bad'],
            ['translations' => ['fr' => ['body' => '']]],
            ['translations' => ['fr' => ['body' => str_repeat('x', 50001)]]],
            ['translations' => [...$this->data()['translations'], 'es' => ['body' => 'No']]],
            ['photos' => ['fr' => [$photo->id, $photo->id]]],
            ['photos' => ['fr' => [99999]]],
        ] as $invalid) {
            $this->patch(route('admin.stay.update', $section), [...$this->data(), ...$invalid])->assertSessionHasErrors();
        }
        $this->assertFalse($section->fresh()->published);
        $this->assertDatabaseCount('photo_stay_section', 0);
        $this->post('/admin/pages-locataire/inconnue', $this->data())->assertNotFound();
    }

    public function test_les_locataires_voient_les_sections_publiees_dans_l_ordre_et_la_langue_choisie(): void
    {
        $last = StaySection::factory()->create(['position' => 9, 'published' => true, 'translations' => ['fr' => ['title' => 'Dernière', 'body' => 'Texte final']]]);
        StaySection::factory()->create(['position' => 1, 'published' => true, 'translations' => $this->data()['translations']]);
        StaySection::factory()->create(['translations' => ['fr' => ['body' => 'Brouillon secret']]]);
        StaySection::factory()->create(['page' => 'depart', 'published' => true, 'translations' => ['fr' => ['body' => 'Autre page']]]);
        $this->actingAs(User::factory()->create(['role' => 'locataire']));

        $this->get('/espace/arrivee')->assertOk()->assertSeeInOrder(['Ouvrez le portail.', 'Texte final'])->assertDontSee('Brouillon secret')->assertDontSee('Autre page');
        $this->withSession(['locale' => 'en'])->get('/espace/arrivee')->assertOk()->assertSee('Open the gate.')->assertSee('Texte final');
        $this->withSession(['locale' => 'it'])->get('/espace/arrivee')->assertOk()->assertSee('Aprite il cancello.');
        $last->translations = ['fr' => ['title' => '<script>title</script>', 'body' => '<script>body</script>']];
        $last->save();
        $this->get('/espace/arrivee')->assertDontSee('<script>body</script>', false)->assertDontSee('<script>title</script>', false);
    }

    public function test_les_photos_sont_ordonnees_et_les_brouillons_et_fichiers_absents_proteges(): void
    {
        Storage::fake('local');
        $section = StaySection::factory()->create(['published' => true]);
        $first = Photo::factory()->create(['published' => true]);
        $second = Photo::factory()->create(['published' => true]);
        $draft = Photo::factory()->create();
        foreach ([$first, $second, $draft] as $photo) {
            Storage::disk('local')->put($photo->filename, 'image');
        }
        $translation = new PhotoTranslation;
        $translation->locale = 'fr';
        $translation->title = '<script>Photo</script>';
        $first->translations()->save($translation);
        $section->photos()->attach([$second->id => ['position' => 5], $first->id => ['position' => 1], $draft->id => ['position' => 0]]);
        $this->actingAs(User::factory()->create(['role' => 'locataire']));

        $this->get('/espace/arrivee')->assertOk()->assertSeeInOrder([route('stay.photos.image', $first), route('stay.photos.image', $second)])
            ->assertDontSee(route('stay.photos.image', $draft))->assertSee('<script>Photo</script>')->assertDontSee('<script>Photo</script>', false);
        $response = $this->get(route('stay.photos.image', $first))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertSame('image', $response->streamedContent());
        $this->get(route('stay.photos.image', $draft))->assertNotFound();
        Storage::disk('local')->delete($second->filename);
        $this->get(route('stay.photos.image', $second))->assertNotFound();
        $this->get('/espace/arrivee')->assertOk()->assertDontSee(route('stay.photos.image', $second));
        $section->published = false;
        $section->save();
        $this->get(route('stay.photos.image', $first))->assertNotFound();
        $section->delete();
        $this->get(route('stay.photos.image', $first))->assertNotFound();
    }

    public function test_les_visiteurs_et_locataires_ne_peuvent_pas_administrer_les_sections(): void
    {
        $section = StaySection::factory()->create();
        $photo = Photo::factory()->create(['published' => true]);
        $this->get(route('admin.stay'))->assertRedirect(route('login'));
        $this->post(route('admin.stay.store', 'arrivee'), $this->data())->assertRedirect(route('login'));
        $this->get(route('stay.photos.image', $photo))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(['role' => 'locataire']));
        $this->get(route('admin.stay'))->assertForbidden();
        $this->get(route('admin.stay.edit', $section))->assertForbidden();
        $this->post(route('admin.stay.store', 'arrivee'), $this->data())->assertForbidden();
        $this->patch(route('admin.stay.update', $section), $this->data())->assertForbidden();
        $this->patch(route('admin.stay.move', $section), ['direction' => 'up'])->assertForbidden();
        $this->delete(route('admin.stay.destroy', $section))->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => 'locataire', 'is_active' => false]));
        $this->get(route('stay.photos.image', $photo))->assertForbidden();
        $this->assertModelExists($section);
    }
}
