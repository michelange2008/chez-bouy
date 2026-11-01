<?php

namespace Database\Seeders;

use App\Models\Annuaire;
use Illuminate\Database\Seeder;

class AnnuaireSeeder extends Seeder
{
    public function run(): void
    {
        Annuaire::factory()->count(3)->create();
    }
}
