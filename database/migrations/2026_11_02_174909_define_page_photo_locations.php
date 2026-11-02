<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('photo_locations', function (Blueprint $table) {
            $table->renameColumn('code', 'route_name');
        });
        Schema::table('photo_location', function (Blueprint $table) {
            $table->unique('photo_id');
        });

        foreach ([
            'home' => 'Accueil',
            'stay.arrivee' => 'Arrivée',
            'stay.installation' => 'Installation',
            'stay.appartement' => 'Séjour',
            'stay.depart' => 'Départ',
            'stay.chamonix' => 'Chamonix',
        ] as $routeName => $name) {
            DB::table('photo_locations')->updateOrInsert(['route_name' => $routeName], [
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('photo_location', function (Blueprint $table) {
            $table->dropUnique(['photo_id']);
        });
        Schema::table('photo_locations', function (Blueprint $table) {
            $table->renameColumn('route_name', 'code');
        });
    }
};
