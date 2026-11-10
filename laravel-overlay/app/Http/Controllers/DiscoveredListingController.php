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
            'category' => ['nullable', 'in:Phones,Laptops,Consoles,PC Parts,Other'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'sort' => ['nullable', 'in:newest,price_low,price_high'],
        ]);

        $query = DiscoveredListing::query();
        if (filled($params['q'] ?? null)) {
            $query->where('title', 'like', '%'.addcslashes($params['q'], '%_\\').'%');
        }
        if (filled($params['category'] ?? null)) {
            $query->where('category', $params['category']);
        }
        if (isset($params['max_price'])) {
            $query->where('price', '<=', $params['max_price']);
        }
        match ($params['sort'] ?? 'newest') {
            'price_low' => $query->orderBy('price')->orderByDesc('last_seen_at'),
            'price_high' => $query->orderByDesc('price')->orderByDesc('last_seen_at'),
            default => $query->orderByDesc('last_seen_at'),
        };

        return response()->json([
            'items' => $query->limit(100)->get()->map(fn ($item) => [
                'id' => $item->id,
                'title' => $item->title,
                'price' => (float) $item->price,
                'city' => $item->city,
                'url' => $item->listing_url,
                'category' => $item->category,
                'first_seen_at' => $item->first_seen_at?->toIso8601String(),
                'last_seen_at' => $item->last_seen_at?->toIso8601String(),
            ]),
        ]);
    }
}
