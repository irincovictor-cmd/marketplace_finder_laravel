<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            $table->string('manual_status', 20)->default('unverified')->index();
            $table->timestamp('manual_status_updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            $table->dropIndex(['manual_status']);
            $table->dropColumn(['manual_status', 'manual_status_updated_at']);
        });
    }
};
