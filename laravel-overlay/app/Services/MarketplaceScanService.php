<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class MarketplaceScanService
{
    /**
     * Execute exactly one manually approved actor run.
     *
     * Actor inputs match the original FlipRadar one-keyword scan.
     * No data cleaning, model classification or location-confidence inference
     * is performed here; those behaviors require dedicated parity tests.
     */
    public function scan(array $input): array
    {
        $token = (string) config('marketplace.apify_token', '');
        if ($token === '') {
            throw new RuntimeException('Apify token is not configured.');
        }

        $payload = [
            'latitude' => (float) $input['latitude'],
            'longitude' => (float) $input['longitude'],
            'radiusKm' => (int) $input['radius_km'],
            'daysSinceListed' => '7',
            'sortBy' => $input['sort_by'],
            'maxPagesPerUrl' => 1,
            'getListingDetails' => false,
            'getAllListingPhotos' => false,
            'strictFiltering' => (bool) ($input['strict_filtering'] ?? false),
            'proxy' => ['useApifyProxy' => true, 'apifyProxyCountry' => 'PH'],
        ];

        if (filled($input['keyword'] ?? null)) {
            $payload['searchKeyword'] = $input['keyword'];
        }
        if (isset($input['min_price'])) {
            $payload['minPrice'] = (int) $input['min_price'];
        }
        if (isset($input['max_price'])) {
            $payload['maxPrice'] = (int) $input['max_price'];
        }

        // The actor's filters-based search has no documented Facebook category ID input.
        // We classify archived listings locally and must not claim provider-level
        // Electronics/category coverage or Facebook-equivalent ranking.
        $query = http_build_query([
            'clean' => 'true',
            'limit' => 60,
            'maxTotalChargeUsd' => config('marketplace.max_charge_usd', 0.10),
        ], '', '&', PHP_QUERY_RFC3986);

        // The API credential goes in Authorization, not a URL or application log.
        $response = Http::withToken($token)
            ->acceptJson()
            ->timeout(150)
            ->post('https://api.apify.com/v2/acts/curious_coder~facebook-marketplace/run-sync-get-dataset-items?' . $query, $payload);

        if (! $response->successful() || ! is_array($response->json()) || ! array_is_list($response->json())) {
            throw new RuntimeException('Marketplace provider failed or returned an invalid response.');
        }

        $items = [];
        foreach (array_slice($response->json(), 0, 60) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $title = (string) ($item['marketplace_listing_title'] ?? '');
            $priceData = $item['listing_price'] ?? [];
            $amount = is_array($priceData) ? ($priceData['amount'] ?? null) : null;
            if ($title === '' || ! is_numeric($amount) || (float) $amount <= 0) {
                continue;
            }
            if (($item['is_sold'] ?? false) || ($item['is_pending'] ?? false)) {
                continue;
            }

            // Return a deliberately narrow schema. URLs are never trusted blindly.
            $candidateUrl = $item['listingUrl'] ?? $item['listing_url'] ?? $item['url'] ?? null;
            $url = $this->marketplaceUrl($candidateUrl);
            $location = $item['location'] ?? [];
            $geo = is_array($location) ? ($location['reverse_geocode'] ?? []) : [];
            $geo = is_array($geo) ? $geo : [];
            $city = $geo['city'] ?? (is_array($location) ? ($location['city'] ?? '') : '');
            $province = $geo['state'] ?? (is_array($location) ? ($location['state'] ?? '') : '');
            $lat = is_array($location) ? ($location['latitude'] ?? null) : null;
            $lon = is_array($location) ? ($location['longitude'] ?? null) : null;
            $locationText = data_get($item, 'location_text.text', '');
            $items[] = [
                'id' => (string) ($item['id'] ?? $item['listing_id'] ?? ''),
                'title' => mb_substr($title, 0, 250),
                'price' => (float) $amount,
                'city' => mb_substr((string) $city, 0, 100),
                'province' => mb_substr((string) $province, 0, 100),
                'location_text' => mb_substr((string) $locationText, 0, 200),
                'latitude' => is_numeric($lat) ? (float) $lat : null,
                'longitude' => is_numeric($lon) ? (float) $lon : null,
                'url' => $url,
                'image_url' => $this->safeImageUrl($item['primary_listing_photo_url'] ?? null),
            ];
        }

        return ['listings' => $items, 'returned' => count($items), 'raw_count' => count($response->json())];
    }

    /**
     * Only display public HTTPS Marketplace CDN images. Never fetch URLs on
     * the server (untrusted actor data could otherwise trigger SSRF).
     */
    private function safeImageUrl(mixed $candidate): ?string
    {
        if (! is_string($candidate) || strlen($candidate) > 4096) {
            return null;
        }
        $url = parse_url($candidate);
        if (! is_array($url) || strtolower($url['scheme'] ?? '') !== 'https'
            || isset($url['user']) || isset($url['pass'])) {
            return null;
        }
        $host = strtolower($url['host'] ?? '');
        if ($host !== 'fbcdn.net' && ! str_ends_with($host, '.fbcdn.net')
            && $host !== 'fbsbx.com' && ! str_ends_with($host, '.fbsbx.com')) {
            return null;
        }
        return $candidate;
    }

    private function marketplaceUrl(mixed $candidate): ?string
    {
        if (! is_string($candidate) || strlen($candidate) > 2048) {
            return null;
        }
        $parts = parse_url($candidate);
        if (! is_array($parts) || strtolower($parts['scheme'] ?? '') !== 'https') {
            return null;
        }
        $host = strtolower($parts['host'] ?? '');
        if ($host !== 'facebook.com' && ! str_ends_with($host, '.facebook.com')) {
            return null;
        }
        return $candidate;
    }
}
