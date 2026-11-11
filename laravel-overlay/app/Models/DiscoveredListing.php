<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DiscoveredListing extends Model
{
    protected $fillable = [
        'source_key', 'title', 'price', 'city', 'listing_url', 'image_url', 'category',
        'first_seen_at', 'last_seen_at', 'last_scan_batch', 'province', 'location_text', 'latitude', 'longitude', 'location_status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }
}
