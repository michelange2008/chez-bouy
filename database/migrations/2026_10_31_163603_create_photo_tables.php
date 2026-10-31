<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('photos', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->boolean('published')->default(false);
            $table->timestamps();
        });

        Schema::create('photo_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 20);
            $table->string('title');
            $table->longText('description')->nullable();
            $table->unique(['photo_id', 'locale']);
        });

        Schema::create('photo_locations', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('photo_location', function (Blueprint $table) {
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('photo_location_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['photo_id', 'photo_location_id']);
            $table->index(['photo_location_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('photo_location');
        Schema::dropIfExists('photo_translations');
        Schema::dropIfExists('photo_locations');
        Schema::dropIfExists('photos');
    }
};
