<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\PasswordResetController;
use App\Http\Controllers\Api\Auth\SessionController;
use App\Http\Controllers\Api\Auth\TwoFactorChallengeController;
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

Route::middleware('web')->prefix('auth')->group(function (): void {
    Route::get('/csrf-cookie', [SessionController::class, 'csrfCookie']);

    Route::post('/register', [SessionController::class, 'register'])->middleware('throttle:6,1');
    Route::post('/login', [SessionController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/logout', [SessionController::class, 'logout']);

    Route::get('/me', [SessionController::class, 'me'])->middleware('auth');
    Route::patch('/profile', [SessionController::class, 'updateProfile'])->middleware('auth');
    Route::patch('/password', [SessionController::class, 'updatePassword'])->middleware('auth');
    Route::post('/email-verification-notification', [SessionController::class, 'sendVerificationNotification'])->middleware('auth');

    Route::get('/verify-email/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('auth.email.verify');

    Route::post('/forgot-password', [PasswordResetController::class, 'requestLink'])->middleware('throttle:6,1');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->middleware('throttle:6,1');

    Route::post('/two-factor/confirm', [TwoFactorChallengeController::class, 'confirm'])->middleware('throttle:12,1');
    Route::post('/two-factor/resend', [TwoFactorChallengeController::class, 'resend'])->middleware('throttle:6,1');
    Route::get('/two-factor/{challenge}/{token}', [TwoFactorChallengeController::class, 'magicLink'])
        ->middleware('signed')
        ->name('auth.two-factor.magic-link');
});
