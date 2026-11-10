<?php

namespace App\Services;

use App\Models\DiscoveredListing;
use Illuminate\Support\Str;

class ListingArchiveService
{
    public function __construct(private readonly BoholLocationService $location) {}

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
            $status = $this->location->assess(
                $item['city'] ?? null,
                $item['province'] ?? null,
                $item['location_text'] ?? null,
                isset($item['latitude']) && is_numeric($item['latitude']) ? (float) $item['latitude'] : null,
                isset($item['longitude']) && is_numeric($item['longitude']) ? (float) $item['longitude'] : null
            );
            $record->fill([
                'title' => $title,
                'price' => (float) $item['price'],
                'city' => $item['city'] ?: null,
                'province' => $item['province'] ?? null,
                'location_text' => $item['location_text'] ?? null,
                'latitude' => $item['latitude'] ?? null,
                'longitude' => $item['longitude'] ?? null,
                'location_status' => $status['status'],
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
    public function category(string $title): string
    {
        $s = mb_strtolower($title);
        if (preg_match('/\b(iphone|samsung|galaxy|redmi|poco|iqoo|realme|oneplus|pixel|phone|oppo|vivo)\b/u', $s)) return 'Phones';
        if (preg_match('/\b(laptop|notebook|thinkpad|macbook|ideapad|aspire)\b/u', $s)) return 'Laptops';
        if (preg_match('/\b(ps[345]|playstation|xbox|nintendo|switch)\b/u', $s)) return 'Consoles';
        if (preg_match('/\b(rtx|gtx|radeon|ryzen|motherboard|gpu|cpu|ssd|monitor)\b/u', $s)) return 'PC Parts';
        return 'Other';
    }
}
