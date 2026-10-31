<?php

namespace Tests\Feature;

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PhotoSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_une_photo_est_non_publiee_par_defaut_et_sa_description_est_facultative(): void
    {
        $photoId = DB::table('photos')->insertGetId(['filename' => 'photos/chalet.jpg']);
        DB::table('photo_translations')->insert([
            'photo_id' => $photoId, 'locale' => 'fr', 'title' => 'Le chalet',
        ]);

        $this->assertDatabaseHas('photos', ['id' => $photoId, 'published' => false]);
        $this->assertDatabaseHas('photo_translations', ['photo_id' => $photoId, 'description' => null]);
    }

    public function test_une_photo_ne_peut_pas_avoir_deux_traductions_dans_la_meme_langue(): void
    {
        $photoId = DB::table('photos')->insertGetId(['filename' => 'photos/chalet.jpg']);
        $translation = ['photo_id' => $photoId, 'locale' => 'fr', 'title' => 'Le chalet'];
        DB::table('photo_translations')->insert($translation);

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('photo_translations')->insert($translation);
    }

    public function test_le_code_d_emplacement_est_unique(): void
    {
        DB::table('photo_locations')->insert(['code' => 'public', 'name' => 'Accueil']);

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('photo_locations')->insert(['code' => 'public', 'name' => 'Autre accueil']);
    }

    public function test_une_photo_ne_peut_pas_etre_associee_deux_fois_au_meme_emplacement(): void
    {
        $photoId = DB::table('photos')->insertGetId(['filename' => 'photos/chalet.jpg']);
        $locationId = DB::table('photo_locations')->insertGetId(['code' => 'public', 'name' => 'Accueil']);
        $association = ['photo_id' => $photoId, 'photo_location_id' => $locationId, 'position' => 1];
        DB::table('photo_location')->insert($association);

        $this->expectException(UniqueConstraintViolationException::class);

        DB::table('photo_location')->insert([...$association, 'position' => 2]);
    }

    public function test_supprimer_un_emplacement_conserve_les_photos_et_les_autres_associations(): void
    {
        $photoId = DB::table('photos')->insertGetId(['filename' => 'photos/chalet.jpg']);
        $publicId = DB::table('photo_locations')->insertGetId(['code' => 'public', 'name' => 'Accueil']);
        $arrivalId = DB::table('photo_locations')->insertGetId(['code' => 'arrivee', 'name' => 'Arrivée']);
        DB::table('photo_location')->insert([
            ['photo_id' => $photoId, 'photo_location_id' => $publicId, 'position' => 1],
            ['photo_id' => $photoId, 'photo_location_id' => $arrivalId, 'position' => 4],
        ]);

        DB::table('photo_locations')->where('id', $publicId)->delete();

        $this->assertDatabaseHas('photos', ['id' => $photoId]);
        $this->assertDatabaseMissing('photo_location', ['photo_location_id' => $publicId]);
        $this->assertDatabaseHas('photo_location', ['photo_id' => $photoId, 'photo_location_id' => $arrivalId, 'position' => 4]);
    }

    public function test_supprimer_une_photo_supprime_ses_traductions_et_associations(): void
    {
        $photoId = DB::table('photos')->insertGetId(['filename' => 'photos/chalet.jpg']);
        $locationId = DB::table('photo_locations')->insertGetId(['code' => 'public', 'name' => 'Accueil']);
        DB::table('photo_translations')->insert(['photo_id' => $photoId, 'locale' => 'fr', 'title' => 'Le chalet']);
        DB::table('photo_location')->insert(['photo_id' => $photoId, 'photo_location_id' => $locationId]);

        DB::table('photos')->where('id', $photoId)->delete();

        $this->assertDatabaseMissing('photo_translations', ['photo_id' => $photoId]);
        $this->assertDatabaseMissing('photo_location', ['photo_id' => $photoId]);
        $this->assertDatabaseHas('photo_locations', ['id' => $locationId]);
    }
}
