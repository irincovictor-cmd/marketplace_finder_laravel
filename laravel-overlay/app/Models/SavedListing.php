<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedListing extends Model
{
    protected $fillable = ['title', 'listing_url', 'price', 'city', 'notes'];

    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }
}
