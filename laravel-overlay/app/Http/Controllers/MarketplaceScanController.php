<?php

namespace App\Http\Controllers;

use App\Services\MarketplaceScanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MarketplaceScanController extends Controller
{
    public function scan(Request $request, MarketplaceScanService $service): JsonResponse
    {
        if (! app()->environment('local', 'testing') || ! config('marketplace.scan_enabled')) {
            return response()->json(['message' => 'Live scanning is disabled.'], 503);
        }

        $data = $request->validate([
            'keyword' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[a-zA-Z0-9 +._-]+$/'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_km' => ['required', 'integer', 'between:1,100'],
            'sort_by' => ['required', 'in:creation_time_descend,distance_ascend'],
            'strict_filtering' => ['sometimes', 'boolean'],
            'confirm_paid_scan' => ['required', 'accepted'],
        ]);

        if ((string) config('marketplace.apify_token') === '') {
            return response()->json(['message' => 'Configure APIFY_TOKEN on the server first.'], 503);
        }

        try {
            $result = $service->scan($data);
        } catch (RuntimeException $e) {
            // Do not expose provider responses, request URLs or secrets.
            return response()->json(['message' => 'Marketplace scan failed. Check server configuration or provider status.'], 502);
        }

        return response()->json([
            ...$result,
            'keyword' => $data['keyword'],
            'notice' => 'Results and geography are unverified; charges depend on Apify billing.',
        ]);
    }
}
