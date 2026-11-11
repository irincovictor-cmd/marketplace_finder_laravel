<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            $table->uuid('last_scan_batch')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            $table->dropIndex(['last_scan_batch']);
            $table->dropColumn('last_scan_batch');
        });
    }
};
