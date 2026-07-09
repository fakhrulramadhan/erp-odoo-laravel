<?php

use Illuminate\Support\Facades\Route;

// Catch-all route for React SPA
Route::get('{any?}', fn() => view('app'))
    ->where('any', '.*')
    ->name('spa');
