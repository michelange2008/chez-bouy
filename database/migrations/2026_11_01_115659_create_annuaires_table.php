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
        Schema::create('annuaires', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 255);
            $table->string('fonction', 255)->nullable();
            $table->string('telephone', 50)->nullable();
            $table->string('mail', 255)->nullable();
            $table->text('adresse_postale')->nullable();
            $table->text('observations')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annuaires');
    }
};
