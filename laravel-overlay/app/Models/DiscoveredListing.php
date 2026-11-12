<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class DiscoveredListing extends Model
{
    protected $fillable = [
        'source_key', 'title', 'price', 'manual_status', 'manual_status_updated_at', 'listing_intent', 'city', 'listing_url', 'image_url', 'category',
        'first_seen_at', 'last_seen_at', 'last_scan_batch', 'first_scan_batch', 'province', 'location_text', 'latitude', 'longitude', 'location_status',
    ];

    /** Shared deal eligibility gate for browsing and watchlist matching. */
    public function scopeActiveBohol(Builder $query): Builder
    {
        return $query->where('location_status', 'bohol')
            ->where('listing_intent', 'reviewable')
            ->whereIn('manual_status', ['unverified', 'available']);
    }

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'manual_status_updated_at' => 'datetime',
        ];
    }
}
