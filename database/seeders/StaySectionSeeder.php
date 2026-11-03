<?php

namespace Database\Seeders;

use App\Models\StaySection;
use Illuminate\Database\Seeder;

class StaySectionSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }
        foreach (StaySection::PAGES as $page) {
            if (! StaySection::query()->where('page', $page)->exists()) {
                StaySection::factory()->create(['page' => $page]);
            }
        }
    }
}
