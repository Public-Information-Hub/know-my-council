<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\IngestionDashboardController;
use App\Http\Controllers\Api\CouncilLookupController;

Route::get('/health', function (Request $request) {
    return response()->json([
        'status' => 'ok',
        'service' => 'knowmycouncil-api',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::get('/version', function () {
    return response()->json([
        'name' => config('app.name'),
        'version' => config('knowmycouncil.version'),
        'commit' => config('knowmycouncil.commit_sha'),
        'environment' => config('app.env'),
    ]);
});

Route::get('/councils/{slug}', [CouncilLookupController::class, 'show'])
    ->where('slug', '[A-Za-z0-9\-]+');

Route::get('/admin/ingestion-summary', [IngestionDashboardController::class, 'index']);
