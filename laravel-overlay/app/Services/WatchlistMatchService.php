<?php

namespace App\Services;

use App\Models\DiscoveredListing;
use App\Models\WatchlistEntry;
use Illuminate\Database\Eloquent\Builder;

/**
 * Free matching against local SQLite archive. Not a live Facebook search.
 * Each word of a watch phrase must appear in the title; matches are possible
 * candidates and the user must verify exact model and availability.
 */
class WatchlistMatchService
{
    public function query(WatchlistEntry $watch): Builder
    {
        $query = DiscoveredListing::query()->activeBohol();

        if (! $watch->enabled) {
            return $query->whereRaw('1 = 0');
        }

        $phrase = mb_strtolower(trim((string) $watch->keyword));
        $tokens = preg_split('/\s+/u', $phrase, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (! $tokens) return $query->whereRaw('1 = 0');

        foreach ($tokens as $token) {
            // Parameterized SQL and literal LIKE wildcards prevent accidental
            // partial wildcard matching or injection from watch phrases.
            $query->where('title', 'like', '%'.addcslashes($token, '%_\\').'%');
        }
        if ($watch->max_price !== null) {
            $query->where('price', '<=', $watch->max_price);
        }

        return $query->orderByDesc('last_seen_at')->orderByDesc('id');
    }

    public function count(WatchlistEntry $watch): int
    {
        return $this->query($watch)->count();
    }
}
