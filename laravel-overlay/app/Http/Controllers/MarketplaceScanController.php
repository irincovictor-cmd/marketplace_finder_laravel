<?php

namespace App\Http\Controllers;

use App\Services\MarketplaceScanService;
use App\Services\ListingArchiveService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MarketplaceScanController extends Controller
{
    public function scan(Request $request, MarketplaceScanService $service, ListingArchiveService $archive): JsonResponse
    {
        if (! app()->environment('local', 'testing') || ! config('marketplace.scan_enabled')) {
            return response()->json(['message' => 'Live scanning is disabled.'], 503);
        }

        $data = $request->validate([
            'keyword' => ['nullable', 'string', 'min:2', 'max:80', 'regex:/^[a-zA-Z0-9 +._-]+$/'],
            'category' => ['nullable', 'in:Electronics,Phones,Laptops,Cameras,Consoles,PC Parts,Other'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['required', 'integer', 'between:1,100'],
            'sort_by' => ['required', 'in:creation_time_descend,distance_ascend,price_ascend,price_descend'],
            'strict_filtering' => ['sometimes', 'boolean'],
            'confirm_paid_scan' => ['required', 'accepted'],
        ]);

        if (isset($data['min_price'], $data['max_price']) && $data['min_price'] > $data['max_price']) {
            return response()->json(['message' => 'Minimum price cannot exceed maximum price.'], 422);
        }

        if ((string) config('marketplace.apify_token') === '') {
            return response()->json(['message' => 'Configure APIFY_TOKEN on the server first.'], 503);
        }

        try {
            $result = $service->scan($data);
        } catch (RuntimeException $e) {
            // Do not expose provider responses, request URLs or secrets.
            return response()->json(['message' => 'Marketplace scan failed. Check server configuration or provider status.'], 502);
        }

        $stored = $archive->store($result['listings']);

        // Aggregate diagnostics only. Do not log tokens, raw provider records,
        // seller details or listing URLs.
        $bohol = 0;
        $outside = 0;
        $verify = 0;
        foreach ($result['listings'] as $item) {
            $status = app(\\App\\Services\\BoholLocationService::class)->assess(
                $item['city'] ?? null,
                $item['province'] ?? null,
                $item['location_text'] ?? null,
                $item['latitude'] ?? null,
                $item['longitude'] ?? null
            )['status'];
            if ($status === 'bohol') $bohol++;
            elseif ($status === 'outside') $outside++;
            else $verify++;
        }

        return response()->json([
            ...$result,
            'archived_count' => $stored,
            'diagnostics' => [
                'raw_count' => $result['raw_count'],
                'normalized_count' => $result['returned'],
                'archived_count' => $stored,
                'bohol_count' => $bohol,
                'outside_count' => $outside,
                'unverified_count' => $verify,
            ],
            'keyword' => $data['keyword'] ?? '',
            'category' => $data['category'] ?? null,
            'notice' => 'Results and geography are unverified; charges depend on Apify billing.',
        ]);
    }
}
