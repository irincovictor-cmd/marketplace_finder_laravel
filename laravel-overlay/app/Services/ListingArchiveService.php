<?php

namespace App\Services;

use App\Models\DiscoveredListing;
use Illuminate\Support\Str;

class ListingArchiveService
{
    public function store(array $items): int
    {
        $count = 0;
        foreach ($items as $item) {
            if (! is_array($item) || empty($item['title']) || ! is_numeric($item['price'] ?? null)) {
                continue;
            }
            // Stable URLs or provider IDs support NEW / SEEN tracking across runs.
            // Skip non-identifiable records; never merge unrelated listings by title.
            $identity = $item['url'] ?: ($item['id'] ?? '');
            if (! is_string($identity) || trim($identity) === '') {
                continue;
            }
            $key = hash('sha256', $identity);
            $title = Str::limit(strip_tags((string) $item['title']), 250, '');
            $record = DiscoveredListing::firstOrNew(['source_key' => $key]);
            if (! $record->exists) {
                $record->first_seen_at = now();
            }
            $record->fill([
                'title' => $title,
                'price' => (float) $item['price'],
                'city' => $item['city'] ?: null,
                'listing_url' => $item['url'] ?: null,
                'category' => $this->category($title),
                'last_seen_at' => now(),
            ]);
            $record->save();
            $count++;
        }
        return $count;
    }

    // Provisional UI grouping; NOT equivalent to FlipRadar's detailed classifier.
    private function category(string $title): string
    {
        $s = mb_strtolower($title);
        if (preg_match('/\b(iphone|samsung|galaxy|redmi|poco|iqoo|realme|oneplus|pixel|phone|oppo|vivo)\b/u', $s)) return 'Phones';
        if (preg_match('/\b(laptop|notebook|thinkpad|macbook|ideapad|aspire)\b/u', $s)) return 'Laptops';
        if (preg_match('/\b(ps[345]|playstation|xbox|nintendo|switch)\b/u', $s)) return 'Consoles';
        if (preg_match('/\b(rtx|gtx|radeon|ryzen|motherboard|gpu|cpu|ssd|monitor)\b/u', $s)) return 'PC Parts';
        return 'Other';
    }
}
