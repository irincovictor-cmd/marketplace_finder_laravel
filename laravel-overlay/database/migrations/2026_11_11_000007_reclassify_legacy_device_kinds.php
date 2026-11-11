<?php

use App\Services\ListingArchiveService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        $classify = app(ListingArchiveService::class);
        // Repair only previously "Other" classified records, preserving listing
        // identity, first-seen, last-seen, location status and saved records.
        DB::table('discovered_listings')->where('category', 'Other')
            ->select(['id', 'title'])->orderBy('id')->chunkById(100, function ($rows) use ($classify) {
                foreach ($rows as $row) {
                    $category = $classify->category($row->title);
                    if ($category !== 'Other') {
                        DB::table('discovered_listings')->where('id', $row->id)
                            ->update(['category' => $category]);
                    }
                }
            });
    }

    public function down(): void
    {
        // Historical values cannot be safely reconstructed; intentionally no-op.
    }
};
