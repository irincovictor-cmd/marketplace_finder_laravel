<?php

namespace App\Services;

use App\Models\DiscoveredListing;
use Carbon\CarbonInterface;

class ListingFilterAuditService
{
    public const REASONS = [
        'outside_bohol' => 'Outside Bohol',
        'unverified_location' => 'Unverified location',
        'accessory' => 'Accessory or replacement part',
        'repair' => 'Repair service',
        'wanted' => 'Buying request',
        'other_intent' => 'Other non-sale classification',
        'keyword' => 'Keyword mismatch',
        'below_min_price' => 'Below minimum price',
        'above_max_price' => 'Above maximum price',
        'category' => 'Wrong category',
        'first_seen' => 'Outside date filter',
        'manual_inactive' => 'Manually marked pending, sold or unavailable',
    ];

    public function __construct(
        private readonly BoholLocationService $location,
        private readonly ListingIntentService $intent
    ) {}

    /** Return every reason a stored record fails the current Find deals filters. */
    public function reasons(DiscoveredListing $listing, array $filters, ?CarbonInterface $cutoff = null): array
    {
        $reasons = [];
        if ($listing->location_status === 'outside') {
            $reasons[] = 'outside_bohol';
        } elseif ($listing->location_status !== 'bohol') {
            $reasons[] = 'unverified_location';
        }
        if ($listing->listing_intent !== 'reviewable') {
            // Existing DB column preserves the decision made when archived.
            $intent = (string) $listing->listing_intent;
            $reasons[] = array_key_exists($intent, self::REASONS) ? $intent : 'other_intent';
        }
        if (in_array($listing->manual_status, ['pending', 'sold', 'unavailable'], true)) {
            $reasons[] = 'manual_inactive';
        }
        $q = trim((string) ($filters['q'] ?? ''));
        if ($q !== '' && mb_stripos($listing->title, $q) === false) {
            $reasons[] = 'keyword';
        }
        if (isset($filters['min_price']) && (float) $listing->price < (float) $filters['min_price']) {
            $reasons[] = 'below_min_price';
        }
        if (isset($filters['max_price']) && (float) $listing->price > (float) $filters['max_price']) {
            $reasons[] = 'above_max_price';
        }
        $category = $filters['category'] ?? null;
        if ($category === 'Electronics') {
            if (! in_array($listing->category, ['Phones', 'Laptops', 'Cameras', 'PC Parts', 'Consoles', 'Electronics'], true)) {
                $reasons[] = 'category';
            }
        } elseif ($category !== null && $category !== '' && $listing->category !== $category) {
            $reasons[] = 'category';
        }
        if (isset($filters['found_within_days'])) {
            $threshold = $cutoff ?? now()->subDays((int) $filters['found_within_days']);
            if ($listing->first_seen_at === null || $listing->first_seen_at->lt($threshold)) {
                $reasons[] = 'first_seen';
            }
        }
        return $reasons;
    }

    public function explain(DiscoveredListing $listing, array $reasons): array
    {
        $place = $this->location->assess(
            $listing->city,
            $listing->province,
            $listing->location_text,
            $listing->latitude === null ? null : (float) $listing->latitude,
            $listing->longitude === null ? null : (float) $listing->longitude
        );
        $intent = $this->intent->assess($listing->title, $listing->category);

        return [
            'id' => $listing->id,
            'title' => $listing->title,
            'price' => (float) $listing->price,
            'category' => $listing->category,
            'city' => $listing->city,
            'province' => $listing->province,
            'location_text' => $listing->location_text,
            'latitude' => $listing->latitude === null ? null : (float) $listing->latitude,
            'longitude' => $listing->longitude === null ? null : (float) $listing->longitude,
            'location_status' => $listing->location_status,
            'location_reason' => $place['reason'],
            'listing_intent' => $listing->listing_intent,
            'manual_status' => $listing->manual_status,
            'intent_reason' => $intent['reason'],
            'reasons' => $reasons,
            'reason_labels' => array_map(fn ($key) => self::REASONS[$key] ?? $key, $reasons),
            'url' => $listing->listing_url,
            'image_url' => $listing->image_url,
            'last_seen_at' => $listing->last_seen_at?->toIso8601String(),
        ];
    }
}
