<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->longText('description')->nullable();
            $table->string('locale', 20)->nullable();
        });

        DB::table('files')->orderBy('id')->chunkById(100, function (Collection $files): void {
            foreach ($files as $file) {
                $translations = DB::table('file_translations')->where('file_id', $file->id)->orderBy('id')->get();
                $translation = $translations->firstWhere('locale', 'fr') ?? $translations->first();
                DB::table('files')->where('id', $file->id)->update([
                    'title' => $translation?->title ?? $file->filename,
                    'description' => $translation?->description,
                    'locale' => $translation?->locale ?? 'fr',
                ]);
            }
        });

        Schema::table('files', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->string('locale', 20)->nullable(false)->change();
        });
        Schema::dropIfExists('file_translations');
    }

    public function down(): void
    {
        Schema::create('file_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('file_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 20);
            $table->string('title');
            $table->longText('description')->nullable();
            $table->unique(['file_id', 'locale']);
        });
        DB::table('files')->orderBy('id')->chunkById(100, function (Collection $files): void {
            foreach ($files as $file) {
                DB::table('file_translations')->insert([
                    'file_id' => $file->id,
                    'locale' => $file->locale,
                    'title' => $file->title,
                    'description' => $file->description,
                ]);
            }
        });
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn(['title', 'description', 'locale']);
        });
    }
};
