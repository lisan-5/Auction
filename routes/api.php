<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\BidController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\ChapaPaymentController;
use App\Http\Controllers\Api\AuctionController;
use App\Http\Controllers\Api\StatsController;

Route::prefix('v1')->group(function () {

    // --------------------
    // Public Auth Routes
    // --------------------

    Route::prefix('password')->group(function () {
        Route::post('/forgot', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'sendResetLink'])->name('api.password.forgot');
        Route::post('/reset', [\App\Http\Controllers\Api\ForgotPasswordController::class, 'reset'])->name('api.password.reset');
    });

    // Register endpoint uses no verification (creates user, no emails sent)
    Route::post('/register', [AuthController::class, 'register'])->name('api.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.login');

    // Email Verification (API)
    Route::prefix('auth/verify')->group(function () {
        Route::post('/request', [\App\Http\Controllers\Api\EmailVerificationApiController::class, 'requestCode'])
            ->name('api.verify.request');
        Route::post('/complete', [\App\Http\Controllers\Api\EmailVerificationApiController::class, 'complete'])
            ->name('api.verify.complete');
        Route::post('/resend', [\App\Http\Controllers\Api\EmailVerificationApiController::class, 'resend'])
            ->name('api.verify.resend');
        Route::get('/magic/{token}', [\App\Http\Controllers\Api\EmailVerificationApiController::class, 'magic'])
            ->name('api.verify.magic');
    });

    // --------------------
    // Public Auction Routes
    // --------------------
    Route::get('/auctions', [AuctionController::class, 'index'])->name('api.auctions.index');
    Route::get('/auctions/{auction}', [AuctionController::class, 'show'])->name('api.auctions.show');
    // Auction stats endpoint (NEW)
    Route::get('/auctions/{auction}/stats', [StatsController::class, 'show'])->name('api.auctions.stats');

    // Public bidding read endpoints
    Route::get('/auctions/{auction}/bids', [BidController::class, 'index'])->name('api.auctions.bids.index');
    Route::get('/auctions/{auction}/highest', [BidController::class, 'highest'])->name('api.auctions.bids.highest');

    // --------------------
    // Protected Routes (Auth Required)
    // --------------------
    Route::middleware('auth:sanctum')->group(function () {

        // User Auth Management
        Route::get('/me', [AuthController::class, 'me'])->name('api.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.logout');

        // Auction Management (Artists can create, update, delete their auctions)
        Route::post('/auctions', [AuctionController::class, 'store'])->name('api.auctions.store');
        Route::put('/auctions/{auction}', [AuctionController::class, 'update'])->name('api.auctions.update');
        Route::delete('/auctions/{auction}', [AuctionController::class, 'destroy'])->name('api.auctions.destroy');

        // Bidding System (write)
        Route::post('/auctions/{auction}/bids', [BidController::class, 'store'])->name('api.auctions.bids.store');

        // Notifications
        Route::get('/notifications', [NotificationController::class, 'index'])->name('api.notifications.index');

        // Optional: Get bids for an auction (useful for real-time updates)
        // Route::get('/auctions/{auction}/bids', [BidController::class, 'index'])->name('api.auctions.bids.index');

        // Payment Management
        Route::post('/payments', [ChapaPaymentController::class, 'initiatePayment'])->name('api.payments.initiate');
        Route::get('/payments/{tx_ref}', [ChapaPaymentController::class, 'show'])->name('api.payments.show');

        // Admin Routes (Role-based access)
        Route::middleware('role:admin')->group(function () {
            Route::get('/admin/test', function () {
                return response()->json(['message' => 'You are an admin', 'timestamp' => now()]);
            })->name('api.admin.test');

            // Additional admin routes can be added here
            // Route::get('/admin/users', [AdminController::class, 'users'])->name('api.admin.users');
            // Route::get('/admin/auctions', [AdminController::class, 'auctions'])->name('api.admin.auctions');

            // Admin-only bid management
            Route::delete('/bids/{bid}', [BidController::class, 'destroy'])->name('api.bids.destroy');
        });
    });

    // --------------------
    // Public Webhook (External Service Callbacks)
    // --------------------
    Route::post('/payments/webhook', [ChapaPaymentController::class, 'handleCallback'])
        ->name('api.chapa.webhook');
});
