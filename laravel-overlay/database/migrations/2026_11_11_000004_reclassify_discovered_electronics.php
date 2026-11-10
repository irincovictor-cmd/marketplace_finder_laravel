<?php

use App\Services\ListingArchiveService;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Reapply the revised category classifier to older archived items.
        // No discovery or saved-listing rows are deleted or recreated.
        $classifier = app(ListingArchiveService::class);
        DB::table('discovered_listings')->select('id', 'title')->orderBy('id')
            ->chunkById(100, function ($rows) use ($classifier) {
                foreach ($rows as $row) {
                    DB::table('discovered_listings')->where('id', $row->id)->update([
                        'category' => $classifier->category($row->title),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Irreversible categorization change; preserve all existing values.
    }
};
