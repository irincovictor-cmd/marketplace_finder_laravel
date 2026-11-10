<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WatchlistEntry extends Model
{
    protected $fillable = ['keyword', 'max_price', 'enabled'];

    protected function casts(): array
    {
        return ['max_price' => 'decimal:2', 'enabled' => 'boolean'];
    }
}
