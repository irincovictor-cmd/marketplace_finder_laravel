<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            // NULL means follow the automatic location assessment.
            $table->string('manual_location', 12)->nullable()->index();
            $table->timestamp('manual_location_reviewed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            $table->dropColumn(['manual_location', 'manual_location_reviewed_at']);
        });
    }
};
