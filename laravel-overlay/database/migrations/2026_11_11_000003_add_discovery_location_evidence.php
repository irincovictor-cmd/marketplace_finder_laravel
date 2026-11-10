<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            $table->string('province', 100)->nullable();
            $table->string('location_text', 200)->nullable();
            $table->decimal('latitude', 10, 6)->nullable();
            $table->decimal('longitude', 10, 6)->nullable();
            $table->string('location_status', 20)->default('verify')->index();
        });
    }
    public function down(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            $table->dropIndex(['location_status']);
            $table->dropColumn(['province', 'location_text', 'latitude', 'longitude', 'location_status']);
        });
    }
};
