<?php

namespace Database\Seeders;

use App\Models\File;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class FileSeeder extends Seeder
{
    public function run(): void
    {
        $filename = 'file/exemple.txt';
        if (File::query()->where('filename', $filename)->exists()) {
            return;
        }
        if (! Storage::disk('local')->put($filename, 'Bienvenue chez Bouy !')) {
            throw new \RuntimeException('Impossible de créer le fichier exemple.');
        }
        $file = new File;
        $file->filename = $filename;
        $file->title = 'Bienvenue';
        $file->locale = 'fr';
        $file->save();
    }
}
