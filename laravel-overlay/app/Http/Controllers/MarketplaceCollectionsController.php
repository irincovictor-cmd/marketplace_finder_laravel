<?php

namespace App\Http\Controllers;

use App\Models\SavedListing;
use App\Models\WatchlistEntry;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MarketplaceCollectionsController extends Controller
{
    public function savedIndex(): JsonResponse
    {
        return response()->json(['items' => SavedListing::latest()->limit(250)->get()]);
    }

    public function saveListing(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:250'],
            'listing_url' => ['required', 'url', 'max:2048'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'city' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        if (! $this->isFacebookMarketplaceUrl($data['listing_url'])) {
            throw ValidationException::withMessages(['listing_url' => 'Use a valid HTTPS Facebook Marketplace listing URL.']);
        }

        $item = SavedListing::updateOrCreate(
            ['listing_url' => $data['listing_url']],
            [
                'title' => $data['title'],
                'price' => $data['price'] ?? null,
                'city' => $data['city'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]
        );

        return response()->json(['item' => $item], $item->wasRecentlyCreated ? 201 : 200);
    }

    public function removeSaved(SavedListing $savedListing): JsonResponse
    {
        $savedListing->delete();

        return response()->json(['deleted' => true]);
    }

    public function watchlistIndex(): JsonResponse
    {
        return response()->json(['items' => WatchlistEntry::orderBy('keyword')->limit(250)->get()]);
    }

    public function saveWatchlist(Request $request): JsonResponse
    {
        $data = $request->validate([
            'keyword' => ['required', 'string', 'min:2', 'max:80', 'regex:/^[a-zA-Z0-9 +._-]+$/'],
            'max_price' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'enabled' => ['required', 'boolean'],
        ]);

        $keyword = mb_strtolower(trim(preg_replace('/\s+/', ' ', $data['keyword'])));
        $item = WatchlistEntry::updateOrCreate(
            ['keyword' => $keyword],
            [
                'max_price' => $data['max_price'] ?? null,
                'enabled' => $data['enabled'],
            ]
        );

        return response()->json(['item' => $item], $item->wasRecentlyCreated ? 201 : 200);
    }

    public function removeWatchlist(WatchlistEntry $watchlistEntry): JsonResponse
    {
        $watchlistEntry->delete();

        return response()->json(['deleted' => true]);
    }

    private function isFacebookMarketplaceUrl(string $url): bool
    {
        $parts = parse_url($url);

        if (! is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https') {
            return false;
        }
        $host = strtolower($parts['host'] ?? '');
        $isFacebook = $host === 'facebook.com' || str_ends_with($host, '.facebook.com');

        return $isFacebook && str_starts_with($parts['path'] ?? '', '/marketplace/item/');
    }
}
