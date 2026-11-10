<?php

use App\Http\Controllers\ProfitCalculatorController;
use App\Http\Controllers\MarketplaceScanController;
use App\Http\Controllers\MarketplaceCollectionsController;
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


 // Local-only, single-user development collections. Add authentication before deployment.
Route::get('/collections/saved', [MarketplaceCollectionsController::class, 'savedIndex'])->name('marketplace.saved.index');
Route::post('/collections/saved', [MarketplaceCollectionsController::class, 'saveListing'])->name('marketplace.saved.store');
Route::delete('/collections/saved/{savedListing}', [MarketplaceCollectionsController::class, 'removeSaved'])->name('marketplace.saved.delete');
Route::get('/collections/watchlist', [MarketplaceCollectionsController::class, 'watchlistIndex'])->name('marketplace.watchlist.index');
Route::post('/collections/watchlist', [MarketplaceCollectionsController::class, 'saveWatchlist'])->name('marketplace.watchlist.store');
Route::delete('/collections/watchlist/{watchlistEntry}', [MarketplaceCollectionsController::class, 'removeWatchlist'])->name('marketplace.watchlist.delete');

// Original FlipRadar is retained, unchanged, at /flipradar/index.html.
// Never execute paid Apify scans during boot, tests, or on initial page load.
