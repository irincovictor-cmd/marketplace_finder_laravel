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
        ]);

        $query = DiscoveredListing::query()->where('location_status', 'bohol');
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

        return response()->json([
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
                'category' => $item->category,
                'first_seen_at' => $item->first_seen_at?->toIso8601String(),
                'last_seen_at' => $item->last_seen_at?->toIso8601String(),
            ]),
        ]);
    }
}
