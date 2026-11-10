<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('saved_listings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 250);
            $table->string('listing_url', 2048)->unique();
            $table->decimal('price', 12, 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('watchlist_entries', function (Blueprint $table) {
            $table->id();
            $table->string('keyword', 80)->unique();
            $table->decimal('max_price', 12, 2)->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('watchlist_entries');
        Schema::dropIfExists('saved_listings');
    }
};
