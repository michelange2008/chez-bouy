<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_stay_section', function (Blueprint $table) {
            $table->string('locale', 2)->default('fr');
            $table->index('photo_id');
        });
        Schema::table('photo_stay_section', function (Blueprint $table) {
            $table->dropPrimary();
            $table->primary(['photo_id', 'stay_section_id', 'locale']);
        });
        foreach (['en', 'it'] as $locale) {
            DB::table('photo_stay_section')->insertUsing(
                ['photo_id', 'stay_section_id', 'position', 'locale'],
                DB::table('photo_stay_section')->where('locale', 'fr')
                    ->select('photo_id', 'stay_section_id', 'position')->selectRaw('? as locale', [$locale])
            );
        }
    }

    /** Reverting keeps only the French selection and discards language-specific associations. */
    public function down(): void
    {
        DB::table('photo_stay_section')->where('locale', '!=', 'fr')->delete();
        Schema::table('photo_stay_section', function (Blueprint $table) {
            $table->dropPrimary();
            $table->primary(['photo_id', 'stay_section_id']);
        });
        Schema::table('photo_stay_section', function (Blueprint $table) {
            $table->dropColumn('locale');
            $table->dropIndex(['photo_id']);
        });
    }
};
