<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('discovered_listings', function (Blueprint $table) {
            $table->id();
            $table->string('source_key', 100)->unique();
            $table->string('title', 250);
            $table->decimal('price', 12, 2);
            $table->string('city', 100)->nullable();
            $table->string('listing_url', 2048)->nullable();
            $table->string('category', 30)->default('Other');
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamps();
            $table->index(['category', 'price']);
            $table->index('last_seen_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discovered_listings');
    }
};
