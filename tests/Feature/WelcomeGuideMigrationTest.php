<?php

namespace Tests\Feature;

use App\Models\File;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WelcomeGuideMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_guides_existants_sont_reconnus_sans_designer_un_autre_document(): void
    {
        $migration = require database_path('migrations/2026_11_02_180252_add_purpose_to_files_table.php');
        $migration->down();
        $french = File::factory()->create(['id' => 1, 'locale' => 'fr', 'title' => "Livret d'accueil"]);
        $other = File::factory()->create(['id' => 4, 'locale' => 'en', 'title' => 'Document privé']);
        $italian = File::factory()->create(['id' => 6, 'locale' => 'it', 'title' => 'Guida du benvenuto']);

        $migration->up();

        $this->assertSame(File::WELCOME_GUIDE, $french->fresh()->purpose);
        $this->assertSame(File::WELCOME_GUIDE, $italian->fresh()->purpose);
        $this->assertNull($other->fresh()->purpose);
    }

    public function test_la_base_empeche_deux_guides_pour_la_meme_langue(): void
    {
        File::factory()->count(2)->create(['locale' => 'fr']);
        File::factory()->create(['locale' => 'en', 'purpose' => File::WELCOME_GUIDE]);
        File::factory()->create(['locale' => 'fr', 'purpose' => File::WELCOME_GUIDE]);

        $this->expectException(QueryException::class);
        File::factory()->create(['locale' => 'fr', 'purpose' => File::WELCOME_GUIDE]);
    }
}
