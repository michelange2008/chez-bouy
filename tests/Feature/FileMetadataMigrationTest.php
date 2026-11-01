<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class FileMetadataMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_migration_conserve_le_pdf_et_les_metadonnees_francaises(): void
    {
        $migration = require database_path('migrations/2026_11_01_114109_simplify_file_metadata.php');
        $migration->down();
        $fileId = DB::table('files')->insertGetId(['filename' => 'file/livret.pdf']);
        DB::table('file_translations')->insert([
            ['file_id' => $fileId, 'locale' => 'en', 'title' => 'Welcome guide', 'description' => 'Welcome'],
            ['file_id' => $fileId, 'locale' => 'fr', 'title' => 'Livret', 'description' => 'Bienvenue'],
        ]);
        $englishId = DB::table('files')->insertGetId(['filename' => 'file/english.pdf']);
        DB::table('file_translations')->insert(['file_id' => $englishId, 'locale' => 'en', 'title' => 'English guide']);
        $emptyId = DB::table('files')->insertGetId(['filename' => 'file/sans-titre.pdf']);

        $migration->up();

        $this->assertDatabaseHas('files', ['id' => $fileId, 'filename' => 'file/livret.pdf', 'title' => 'Livret', 'description' => 'Bienvenue', 'locale' => 'fr']);
        $this->assertDatabaseHas('files', ['id' => $englishId, 'title' => 'English guide', 'locale' => 'en', 'description' => null]);
        $this->assertDatabaseHas('files', ['id' => $emptyId, 'title' => 'file/sans-titre.pdf', 'locale' => 'fr']);
        $this->assertFalse(Schema::hasTable('file_translations'));
    }
}
