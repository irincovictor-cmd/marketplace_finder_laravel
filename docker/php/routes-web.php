<?php

use Illuminate\Support\Facades\Route;

// Phase 1: preserve the original FlipRadar interface while Laravel features migrate.
Route::redirect('/', '/flipradar/index.html');
