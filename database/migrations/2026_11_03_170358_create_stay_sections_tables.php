<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stay_sections', function (Blueprint $table) {
            $table->id();
            $table->string('page');
            $table->unsignedInteger('position')->default(0);
            $table->boolean('published')->default(false);
            $table->json('translations');
            $table->timestamps();
            $table->index(['page', 'position']);
        });
        Schema::create('photo_stay_section', function (Blueprint $table) {
            $table->foreignId('photo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stay_section_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->primary(['photo_id', 'stay_section_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('photo_stay_section');
        Schema::dropIfExists('stay_sections');
    }
};
