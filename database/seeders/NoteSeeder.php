<?php

namespace Database\Seeders;

use App\Models\Note;
use Illuminate\Database\Seeder;

class NoteSeeder extends Seeder
{
    public function run(): void
    {
        Note::factory()->count(3)->create();
    }
}
