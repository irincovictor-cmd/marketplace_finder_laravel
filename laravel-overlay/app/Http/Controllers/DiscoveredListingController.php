<?php

namespace App\Http\Controllers;

use App\Models\DiscoveredListing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DiscoveredListingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $params = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'in:Electronics,Phones,Laptops,Cameras,Consoles,PC Parts,Other'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'sort' => ['nullable', 'in:newest,price_low,price_high,first_seen'],
            'found_within_days' => ['nullable', 'integer', 'in:1,7,30'],
            'batch' => ['nullable', 'uuid'],
        ]);

        $totalArchived = DiscoveredListing::query()->count();
        $boholArchived = DiscoveredListing::query()->where('location_status', 'bohol')->count();
        $query = DiscoveredListing::query()->where('location_status', 'bohol');
        if (filled($params['batch'] ?? null)) {
            $query->where('last_scan_batch', $params['batch']);
        }
        if (filled($params['q'] ?? null)) {
            $query->where('title', 'like', '%'.addcslashes($params['q'], '%_\\').'%');
        }
        if (($params['category'] ?? null) === 'Electronics') {
            $query->whereIn('category', ['Phones', 'Laptops', 'Cameras', 'PC Parts', 'Consoles', 'Electronics']);
        } elseif (filled($params['category'] ?? null)) {
            $query->where('category', $params['category']);
        }
        if (isset($params['found_within_days'])) {
            $query->where('first_seen_at', '>=', now()->subDays((int) $params['found_within_days']));
        }
        if (isset($params['min_price'])) {
            $query->where('price', '>=', $params['min_price']);
        }
        if (isset($params['max_price'])) {
            $query->where('price', '<=', $params['max_price']);
        }
        if (isset($params['min_price'], $params['max_price']) && (float) $params['min_price'] > (float) $params['max_price']) {
            return response()->json(['message' => 'Minimum price cannot exceed maximum price.'], 422);
        }
        match ($params['sort'] ?? 'newest') {
            'price_low' => $query->orderBy('price')->orderByDesc('last_seen_at'),
            'price_high' => $query->orderByDesc('price')->orderByDesc('last_seen_at'),
            'first_seen' => $query->orderByDesc('first_seen_at'),
            default => $query->orderByDesc('last_seen_at'),
        };

        // Excluded records stay in SQLite for diagnostics, never shown as Bohol deals.
        $hiddenCount = DiscoveredListing::query()->where('location_status', '!=', 'bohol')->count();

        $matchingCount = (clone $query)->reorder()->count();

        return response()->json([
            'diagnostics' => [
                'stored_total' => $totalArchived,
                'bohol_total' => $boholArchived,
                'matching_filters' => $matchingCount,
                'hidden_outside_or_unknown' => $hiddenCount,
                'keyword_supplied' => filled($params['q'] ?? null),
                'source' => 'local_sqlite',
            ],
            'excluded_unverified' => $hiddenCount,
            'location_policy' => 'bohol_only',
            'items' => $query->limit(100)->get()->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'price' => (float) $item->price,
                'city' => $item->city,
                'province' => $item->province,
                'location_status' => $item->location_status,
                'url' => $item->listing_url,
                'image_url' => $item->image_url,
                'category' => $item->category,
                'first_seen_at' => $item->first_seen_at?->toIso8601String(),
                'last_seen_at' => $item->last_seen_at?->toIso8601String(),
            ]),
        ]);
    }
    /**
     * Restore thumbnails already captured by the original FlipRadar browser.
     * Only update existing rows with a matching Marketplace listing URL.
     * No Apify requests or remote image downloads are performed.
     */
    public function restoreImages(Request $request): JsonResponse
    {
        $data = $request->validate([
            'images' => ['required', 'array', 'max:100'],
            'images.*.listing_url' => ['required', 'string', 'url', 'max:2048'],
            'images.*.image_url' => ['required', 'string', 'url', 'max:4096'],
        ]);

        $updated = 0;
        foreach ($data['images'] as $item) {
            $link = parse_url($item['listing_url']);
            $photo = parse_url($item['image_url']);
            if (! is_array($link) || ! is_array($photo)) continue;

            $host = strtolower($link['host'] ?? '');
            $photoHost = strtolower($photo['host'] ?? '');
            $isListing = strtolower($link['scheme'] ?? '') === 'https'
                && ($host === 'facebook.com' || str_ends_with($host, '.facebook.com'))
                && preg_match('#^/marketplace/item/([0-9]+)/?$#', $link['path'] ?? '');
            $isPhoto = strtolower($photo['scheme'] ?? '') === 'https'
                && ! isset($photo['user']) && ! isset($photo['pass'])
                && ($photoHost === 'fbcdn.net' || str_ends_with($photoHost, '.fbcdn.net')
                    || $photoHost === 'fbsbx.com' || str_ends_with($photoHost, '.fbsbx.com'));
            if (! $isListing || ! $isPhoto) continue;

            // Search by exact item identity, irrespective of URL trailing slash.
            $identifier = $this->listingId($item['listing_url']);
            $candidates = [
                'https://www.facebook.com/marketplace/item/'.$identifier,
                'https://www.facebook.com/marketplace/item/'.$identifier.'/',
                'https://facebook.com/marketplace/item/'.$identifier,
                'https://facebook.com/marketplace/item/'.$identifier.'/',
                'https://m.facebook.com/marketplace/item/'.$identifier,
                'https://m.facebook.com/marketplace/item/'.$identifier.'/',
            ];
            $updated += DiscoveredListing::query()
                ->whereIn('listing_url', $candidates)
                ->whereNull('image_url')
                ->update(['image_url' => $item['image_url']]);
        }

        return response()->json(['updated' => $updated]);
    }

    private function listingId(string $link): string
    {
        preg_match('#/marketplace/item/([0-9]+)#', parse_url($link, PHP_URL_PATH) ?: '', $matches);
        return $matches[1] ?? '';
    }

}
