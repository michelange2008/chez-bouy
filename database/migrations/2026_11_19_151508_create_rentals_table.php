<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name', 511);
            $table->unsignedBigInteger('calendar_event_id')->nullable()->unique();
            $table->date('arrival')->index();
            $table->date('departure');
            $table->string('type', 20);
            $table->decimal('amount', 10, 2)->nullable();
            $table->date('deposit_date')->nullable();
            $table->decimal('deposit_amount', 10, 2)->nullable();
            $table->date('balance_date')->nullable();
            $table->decimal('balance_amount', 10, 2)->nullable();
            $table->boolean('contract_sent')->default(false);
            $table->boolean('contract_signed')->default(false);
            $table->boolean('marie_notified')->default(false);
            $table->boolean('guide_sent')->default(false);
            $table->boolean('codes_sent')->default(false);
            $table->boolean('brings_linen')->default(false);
            $table->boolean('does_cleaning')->default(false);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
