<?php

use App\Http\Controllers\ProfitCalculatorController;
use App\Http\Controllers\MarketplaceScanController;
use App\Http\Controllers\ApifySettingsController;
use App\Http\Controllers\MarketplaceCollectionsController;
use App\Http\Controllers\DiscoveredListingController;
use Illuminate\Support\Facades\Route;

// Laravel-native dashboard (first migration slice).
Route::view('/', 'marketplace.dashboard')->name('marketplace.dashboard');

Route::get('/listings', [DiscoveredListingController::class, 'index'])->name('marketplace.listings');
Route::get('/listings/manual-status-history', [DiscoveredListingController::class, 'manualStatusHistory'])->name('marketplace.listings.status-history');
Route::patch('/listings/{listing}/manual-status', [DiscoveredListingController::class, 'updateManualStatus'])->name('marketplace.listings.manual-status');
Route::get('/listings/rejected', [DiscoveredListingController::class, 'rejected'])
    ->name('marketplace.listings.rejected');
Route::get('/listings/location-audit', [DiscoveredListingController::class, 'locationAudit'])
    ->middleware('throttle:30,1')
    ->name('marketplace.listings.location-audit');
Route::post('/listings/restore-images', [DiscoveredListingController::class, 'restoreImages'])
    ->middleware('throttle:10,1')
    ->name('marketplace.listings.restore-images');

// Stateful browser clients receive Laravel's CSRF token in the Blade template.
Route::post('/calculator/estimate', [ProfitCalculatorController::class, 'calculate'])
    ->middleware('throttle:60,1')
    ->name('marketplace.calculator');

// Local-only credential management. Never exposes API key in GET or logs.
Route::get('/marketplace/api-key', [ApifySettingsController::class, 'status'])->name('marketplace.api-key.status');
Route::post('/marketplace/api-key', [ApifySettingsController::class, 'save'])->middleware('throttle:10,1')->name('marketplace.api-key.save');
Route::delete('/marketplace/api-key', [ApifySettingsController::class, 'remove'])->middleware('throttle:10,1')->name('marketplace.api-key.remove');

// Runs a paid provider request only after an explicit opt-in and approval.
// Its Docker port is bound to localhost; do not expose this unauthenticated route publicly.
Route::get('/marketplace/scan-plan', [MarketplaceScanController::class, 'preview'])
    ->middleware('throttle:30,1')
    ->name('marketplace.scan-plan');
Route::post('/marketplace/scan', [MarketplaceScanController::class, 'scan'])
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
