<?php

use App\Http\Controllers\ProfitCalculatorController;
use App\Http\Controllers\MarketplaceScanController;
use Illuminate\Support\Facades\Route;

// Laravel-native dashboard (first migration slice).
Route::view('/', 'marketplace.dashboard')->name('marketplace.dashboard');

// Stateful browser clients receive Laravel's CSRF token in the Blade template.
Route::post('/calculator/estimate', [ProfitCalculatorController::class, 'calculate'])
    ->middleware('throttle:60,1')
    ->name('marketplace.calculator');

// Runs a paid provider request only after an explicit opt-in and approval.
// Its Docker port is bound to localhost; do not expose this unauthenticated route publicly.
Route::post('/marketplace/scan', [MarketplaceScanController::class, 'scan'])
    ->middleware('throttle:2,1')
    ->name('marketplace.scan');

// Original FlipRadar is retained, unchanged, at /flipradar/index.html.
// Never execute paid Apify scans during boot, tests, or on initial page load.
