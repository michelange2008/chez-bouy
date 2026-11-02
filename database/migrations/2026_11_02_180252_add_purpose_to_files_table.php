<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('purpose')->nullable();
            $table->unique(['locale', 'purpose']);
        });

        foreach ([
            ['id' => 1, 'locale' => 'fr', 'title' => "Livret d'accueil"],
            ['id' => 4, 'locale' => 'en', 'title' => 'Welcome guide'],
            ['id' => 6, 'locale' => 'it', 'title' => 'Guida du benvenuto'],
        ] as $guide) {
            DB::table('files')->where($guide)->update(['purpose' => 'welcome_guide']);
        }
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropUnique(['locale', 'purpose']);
            $table->dropColumn('purpose');
        });
    }
};
