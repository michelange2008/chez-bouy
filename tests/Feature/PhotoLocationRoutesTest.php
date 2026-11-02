<?php

namespace Tests\Feature;

use App\Models\Photo;
use App\Models\PhotoLocation;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class PhotoLocationRoutesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['app.locale' => 'fr', 'app.fallback_locale' => 'fr']);
    }

    public function test_les_six_emplacements_sont_installes_et_correspondent_a_des_routes(): void
    {
        $expected = ['home' => 'Accueil', 'stay.arrivee' => 'Arrivée', 'stay.installation' => 'Installation', 'stay.appartement' => 'Séjour', 'stay.depart' => 'Départ', 'stay.chamonix' => 'Chamonix'];
        $this->assertSame($expected, PhotoLocation::query()->orderBy('id')->pluck('name', 'route_name')->all());
        foreach (array_keys($expected) as $routeName) {
            $this->assertTrue(Route::has($routeName));
        }
    }

    public function test_la_gestion_des_emplacements_n_est_plus_accessible(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $location = PhotoLocation::query()->firstOrFail();
        $this->get('/admin/emplacements')->assertNotFound();
        $this->get("/admin/emplacements/$location->id/modifier")->assertNotFound();
        $this->post('/admin/emplacements', ['code' => 'invalide', 'name' => 'Invalide'])->assertNotFound();
        $this->patch("/admin/emplacements/$location->id", ['route_name' => 'invalide'])->assertNotFound();
        $this->delete("/admin/emplacements/$location->id")->assertNotFound();
        $this->get('/admin/photos')->assertOk()->assertDontSee('/admin/emplacements', false);
        $this->get('/admin/photos/ajouter')->assertOk()->assertSee('name="location_id"', false)
            ->assertSee('Sans emplacement')->assertSee('Arrivée')->assertSee('Séjour')->assertDontSee('name="locations[', false);
        $this->assertDatabaseCount('photo_locations', 6);
    }

    public function test_la_selection_remplace_l_emplacement_et_l_option_vide_le_retire(): void
    {
        $photo = Photo::factory()->create();
        $home = PhotoLocation::query()->where('route_name', 'home')->sole();
        $arrival = PhotoLocation::query()->where('route_name', 'stay.arrivee')->sole();
        $photo->locations()->attach($home);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['published' => '0', 'translations' => ['fr' => ['title' => 'Photo']]];
        $this->patch("/admin/photos/$photo->id", [...$data, 'location_id' => $arrival->id])->assertRedirect('/admin/photos');
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photo->id, 'photo_location_id' => $arrival->id]);
        $this->assertDatabaseMissing('photo_location', ['photo_id' => $photo->id, 'photo_location_id' => $home->id]);
        $this->get("/admin/photos/$photo->id/modifier")->assertOk()->assertSee('value="'.$arrival->id.'" selected', false);
        $this->patch("/admin/photos/$photo->id", [...$data, 'location_id' => ''])->assertRedirect('/admin/photos');
        $this->assertDatabaseMissing('photo_location', ['photo_id' => $photo->id]);
        $this->assertModelExists($photo);
    }

    public function test_un_emplacement_invalide_ou_multiple_est_refuse_sans_modifier_la_photo(): void
    {
        $photo = Photo::factory()->create();
        $home = PhotoLocation::query()->where('route_name', 'home')->sole();
        $photo->locations()->attach($home);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        foreach ([999, [$home->id, 2]] as $locationId) {
            $this->patch("/admin/photos/$photo->id", ['published' => '0', 'translations' => ['fr' => ['title' => 'Photo']], 'location_id' => $locationId])->assertSessionHasErrors('location_id');
        }
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photo->id, 'photo_location_id' => $home->id]);
    }

    public function test_la_position_est_modifiable_et_determine_l_ordre_du_filtre(): void
    {
        $location = PhotoLocation::query()->where('route_name', 'home')->sole();
        $photo = Photo::factory()->create(['filename' => 'photos/premiere.jpg']);
        $other = Photo::factory()->create(['filename' => 'photos/seconde.jpg']);
        $photo->locations()->attach($location, ['position' => 9]);
        $other->locations()->attach($location, ['position' => 5]);
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $data = ['published' => '0', 'translations' => ['fr' => ['title' => 'Première']], 'location_id' => $location->id];
        $this->patch("/admin/photos/$photo->id", [...$data, 'position' => 2])->assertRedirect('/admin/photos');
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photo->id, 'position' => 2]);
        $this->get("/admin/photos/$photo->id/modifier")->assertOk()->assertSee('value="2"', false);
        $this->get('/admin/photos?location='.$location->id)->assertOk()->assertSeeInOrder(['Première', 'photos/seconde.jpg']);
        foreach ([-1, 'abc', 1.5, 2147483648] as $position) {
            $this->patch("/admin/photos/$photo->id", [...$data, 'position' => $position])->assertSessionHasErrors('position');
        }
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photo->id, 'position' => 2]);
        $this->patch("/admin/photos/$photo->id", [...$data, 'position' => 0])->assertRedirect('/admin/photos');
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photo->id, 'position' => 0]);
    }

    public function test_la_base_refuse_deux_emplacements_pour_une_photo(): void
    {
        $photo = Photo::factory()->create();
        $locations = PhotoLocation::query()->orderBy('id')->take(2)->get();
        $photo->locations()->attach($locations[0]);
        $this->expectException(UniqueConstraintViolationException::class);
        $photo->locations()->attach($locations[1]);
    }
}
