<?php

use App\Http\Controllers\ProfitCalculatorController;
use Illuminate\Support\Facades\Route;

// Laravel-native dashboard (first migration slice).
Route::view('/', 'marketplace.dashboard')->name('marketplace.dashboard');

// Stateful browser clients receive Laravel's CSRF token in the Blade template.
Route::post('/calculator/estimate', [ProfitCalculatorController::class, 'calculate'])
    ->middleware('throttle:60,1')
    ->name('marketplace.calculator');

// Original FlipRadar is retained, unchanged, at /flipradar/index.html.
// Never execute paid Apify scans during boot, tests, or on initial page load.
