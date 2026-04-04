<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'name' => config('app.name'),
        'message' => 'KnowMyCouncil API scaffold. See /api/health and /api/version.',
    ]);
});
