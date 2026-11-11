<?php

use App\Services\ListingIntentService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('discovered_listings', function (Blueprint $table) {
            $table->string('listing_intent', 20)->default('reviewable')->index();
        });

        $classifier = app(ListingIntentService::class);
        DB::table('discovered_listings')->orderBy('id')
            ->select(['id', 'title', 'category'])->chunkById(100, function ($rows) use ($classifier) {
                foreach ($rows as $row) {
                    DB::table('discovered_listings')->where('id', $row->id)
                        ->update(['listing_intent' => $classifier->assess($row->title, $row->category)['status']]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('discovered_listings', fn (Blueprint $table) => $table->dropColumn('listing_intent'));
    }
};
