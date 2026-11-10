<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Services\BoholLocationService;

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
        // Reassess older archived records. Unknown locations remain hidden,
        // while uniquely identifiable Bohol towns remain visible.
        $service = app(BoholLocationService::class);
        DB::table('discovered_listings')->select('id', 'city')->orderBy('id')->chunk(100, function ($rows) use ($service) {
            foreach ($rows as $row) {
                DB::table('discovered_listings')->where('id', $row->id)->update([
                    'location_status' => $service->assess($row->city)['status'],
                ]);
            }
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
