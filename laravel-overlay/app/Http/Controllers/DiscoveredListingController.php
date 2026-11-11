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
            'availability' => ['nullable', 'in:active,all,unverified,available,pending,sold,unavailable'],
        ]);

        $totalArchived = DiscoveredListing::query()->count();
        $boholArchived = DiscoveredListing::query()->where('location_status', 'bohol')->count();
        $query = DiscoveredListing::query()->where('location_status', 'bohol')
            ->where('listing_intent', 'reviewable');
        $availability = $params['availability'] ?? 'active';
        if ($availability === 'active') {
            $query->whereIn('manual_status', ['unverified', 'available']);
        } elseif ($availability !== 'all') {
            $query->where('manual_status', $availability);
        }
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
                'hidden_non_sale' => DiscoveredListing::query()->where('location_status', 'bohol')->where('listing_intent', '!=', 'reviewable')->count(),
                'hidden_manual_status' => DiscoveredListing::query()->where('location_status', 'bohol')->where('listing_intent', 'reviewable')->whereIn('manual_status', ['pending', 'sold', 'unavailable'])->count(),
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
                'listing_intent' => $item->listing_intent,
                'manual_status' => $item->manual_status,
                'manual_status_updated_at' => $item->manual_status_updated_at?->toIso8601String(),
                'first_seen_at' => $item->first_seen_at?->toIso8601String(),
                'last_seen_at' => $item->last_seen_at?->toIso8601String(),
            ]),
        ]);
    }
    /**
     * User-reported availability, not a Facebook verification. It is never
     * reset by an Apify scan and does not delete the archived listing.
     */
    public function updateManualStatus(Request $request, DiscoveredListing $listing): JsonResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:unverified,available,pending,sold,unavailable'],
        ]);
        $listing->manual_status = $data['status'];
        $listing->manual_status_updated_at = $data['status'] === 'unverified' ? null : now();
        $listing->save();

        return response()->json([
            'id' => $listing->id,
            'manual_status' => $listing->manual_status,
            'manual_status_updated_at' => $listing->manual_status_updated_at?->toIso8601String(),
            'source' => 'user_reported',
        ]);
    }

    /** All manually tagged listings, including sold/removed, for easy undo. */
    public function manualStatusHistory(Request $request): JsonResponse
    {
        $params = $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
        ]);
        $page = DiscoveredListing::query()
            ->where('manual_status', '!=', 'unverified')
            ->orderByDesc('manual_status_updated_at')
            ->paginate(40, ['id', 'title', 'price', 'city', 'listing_url', 'image_url', 'manual_status', 'manual_status_updated_at'], 'page', (int) ($params['page'] ?? 1));
        return response()->json([
            'items' => $page->items(),
            'page' => $page->currentPage(),
            'pages' => $page->lastPage(),
            'total' => $page->total(),
            'source' => 'user_reported',
        ]);
    }

    /**
     * Paginated, read-only equivalent of the legacy rejected-listing inspector.
     * Scope by current scan batch when requested; enumerate ALL archived records
     * in that scope, and explain every failed Find deals rule.
     */
    public function rejected(Request $request, \App\Services\ListingFilterAuditService $audit): JsonResponse
    {
        $params = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'in:Electronics,Phones,Laptops,Cameras,Consoles,PC Parts,Other'],
            'min_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'found_within_days' => ['nullable', 'integer', 'in:1,7,30'],
            'batch' => ['nullable', 'uuid'],
            'reason' => ['nullable', 'in:outside_bohol,unverified_location,accessory,repair,wanted,other_intent,keyword,below_min_price,above_max_price,category,first_seen,manual_inactive'],
            'page' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);
        if (isset($params['min_price'], $params['max_price']) && (float) $params['min_price'] > (float) $params['max_price']) {
            return response()->json(['message' => 'Minimum price cannot exceed maximum price.'], 422);
        }

        $page = (int) ($params['page'] ?? 1);
        $pageSize = (int) ($params['per_page'] ?? 40);
        $reasonFilter = $params['reason'] ?? '';
        $cutoff = isset($params['found_within_days']) ? now()->subDays((int) $params['found_within_days']) : null;
        $query = DiscoveredListing::query();
        if (filled($params['batch'] ?? null)) {
            $query->where('last_scan_batch', $params['batch']);
        }

        $checked = 0;
        $rejected = 0;
        $selectedCount = 0;
        $reasonsCounts = array_fill_keys(array_keys(\App\Services\ListingFilterAuditService::REASONS), 0);
        $items = [];
        // Iterate in pages on the server, never impose a 100-record total cap.
        foreach ($query->orderByDesc('id')->cursor() as $listing) {
            $checked++;
            $reasons = $audit->reasons($listing, $params, $cutoff);
            if ($reasons === []) continue;
            $rejected++;
            foreach ($reasons as $reason) $reasonsCounts[$reason]++;
            if ($reasonFilter !== '' && ! in_array($reasonFilter, $reasons, true)) continue;
            $selectedCount++;
            if ($selectedCount > ($page - 1) * $pageSize && count($items) < $pageSize) {
                $items[] = $audit->explain($listing, $reasons);
            }
        }

        return response()->json([
            'read_only' => true,
            'scope' => filled($params['batch'] ?? null) ? 'scan_batch' : 'archive',
            'filters' => $params,
            'checked_count' => $checked,
            'rejected_count' => $rejected,
            'accepted_count' => $checked - $rejected,
            'matched_reason_count' => $selectedCount,
            'reason_counts' => $reasonsCounts,
            'reason_labels' => \App\Services\ListingFilterAuditService::REASONS,
            'page' => $page,
            'per_page' => $pageSize,
            'pages' => max(1, (int) ceil($selectedCount / $pageSize)),
            'items' => $items,
        ]);
    }

    /**
     * Read-only audit of archived location decisions. Never change listing
     * visibility or trust unknown geography automatically.
     */
    public function locationAudit(\App\Services\BoholLocationService $location): JsonResponse
    {
        $counts = DiscoveredListing::query()
            ->selectRaw('location_status, COUNT(*) AS total')
            ->groupBy('location_status')->pluck('total', 'location_status');

        $sample = DiscoveredListing::query()
            ->where('location_status', '!=', 'bohol')
            ->orderByDesc('last_seen_at')->limit(100)->get()
            ->map(function ($listing) use ($location) {
                $assessment = $location->assess(
                    $listing->city,
                    $listing->province,
                    $listing->location_text,
                    $listing->latitude === null ? null : (float) $listing->latitude,
                    $listing->longitude === null ? null : (float) $listing->longitude
                );
                return [
                    'id' => $listing->id,
                    'title' => $listing->title,
                    'price' => (float) $listing->price,
                    'category' => $listing->category,
                    'city' => $listing->city,
                    'province' => $listing->province,
                    'location_status' => $listing->location_status,
                    'reason' => $assessment['reason'],
                    'has_coordinates' => $listing->latitude !== null && $listing->longitude !== null,
                ];
            })->values();

        return response()->json([
            'counts' => [
                'bohol' => (int) ($counts['bohol'] ?? 0),
                'verify' => (int) ($counts['verify'] ?? 0),
                'outside' => (int) ($counts['outside'] ?? 0),
            ],
            'total_rejected' => (int) ($counts['verify'] ?? 0) + (int) ($counts['outside'] ?? 0),
            'sample_limit' => 100,
            'sample' => $sample,
            'read_only' => true,
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
