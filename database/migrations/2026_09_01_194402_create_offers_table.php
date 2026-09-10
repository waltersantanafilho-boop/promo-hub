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
        Schema::create('offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_id')->constrained()->restrictOnDelete();
            $table->foreignId('store_source_id')->constrained()->restrictOnDelete();
            $table->string('external_id');
            $table->text('url');
            $table->decimal('price', 19, 4);
            $table->decimal('original_price', 19, 4)->nullable();
            $table->char('currency', 3)->default('BRL');
            $table->enum('availability', ['in_stock', 'out_of_stock', 'unknown'])->default('unknown');
            $table->string('image_url')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->enum('status', ['active', 'expired', 'removed'])->default('active');
            $table->timestamps();

            $table->unique(['store_source_id', 'external_id']);
            $table->index(['status', 'last_checked_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offers');
    }
};
