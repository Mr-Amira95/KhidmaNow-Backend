<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\CityController;
use App\Http\Controllers\Api\V1\Admin\CountryController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\NotificationController;
use App\Http\Controllers\Api\V1\Admin\PaymentController;
use App\Http\Controllers\Api\V1\Admin\PayoutController;
use App\Http\Controllers\Api\V1\Admin\PermissionController;
use App\Http\Controllers\Api\V1\Admin\ProviderController;
use App\Http\Controllers\Api\V1\Admin\ProviderDocumentController;
use App\Http\Controllers\Api\V1\Admin\RateController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\ServiceRequestController;
use App\Http\Controllers\Api\V1\Admin\SettingController;
use App\Http\Controllers\Api\V1\Admin\SubCategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ─── Public Auth Routes ───────────────────────────────────────────────────
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
    Route::post('/verify-code', [AuthController::class, 'verifyCode']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    // ─── Authenticated Routes ─────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', fn(Request $request) => $request->user());

        // ─── Admin Routes ─────────────────────────────────────────────────────
        Route::prefix('admin')->middleware('admin')->group(function () {

            // Dashboard
            Route::get('dashboard', [DashboardController::class, 'index']);

            // Users
            Route::patch('users/{user}/block', [UserController::class, 'block']);
            Route::patch('users/{user}/unblock', [UserController::class, 'unblock']);
            Route::apiResource('users', UserController::class);

            // Providers
            Route::patch('providers/{provider}/verify', [ProviderController::class, 'verify']);
            Route::patch('providers/{provider}/unverify', [ProviderController::class, 'unverify']);
            Route::apiResource('providers', ProviderController::class)->only(['index', 'show', 'destroy']);

            // Provider Documents
            Route::patch('provider-documents/{providerDocument}/approve', [ProviderDocumentController::class, 'approve']);
            Route::patch('provider-documents/{providerDocument}/reject', [ProviderDocumentController::class, 'reject']);
            Route::get('provider-documents', [ProviderDocumentController::class, 'index']);
            Route::get('provider-documents/{providerDocument}', [ProviderDocumentController::class, 'show']);

            // Categories
            Route::apiResource('categories', CategoryController::class);

            // Sub-Categories
            Route::apiResource('sub-categories', SubCategoryController::class);

            // Countries
            Route::apiResource('countries', CountryController::class);

            // Cities
            Route::apiResource('cities', CityController::class);

            // Service Requests
            Route::patch('service-requests/{serviceRequest}/status', [ServiceRequestController::class, 'updateStatus']);
            Route::get('service-requests', [ServiceRequestController::class, 'index']);
            Route::get('service-requests/{serviceRequest}', [ServiceRequestController::class, 'show']);

            // Payments
            Route::get('payments', [PaymentController::class, 'index']);
            Route::get('payments/{payment}', [PaymentController::class, 'show']);

            // Payouts
            Route::patch('payouts/{payout}/status', [PayoutController::class, 'updateStatus']);
            Route::get('payouts', [PayoutController::class, 'index']);
            Route::get('payouts/{payout}', [PayoutController::class, 'show']);

            // Wallets
            Route::get('wallets', [WalletController::class, 'index']);
            Route::get('wallets/{wallet}', [WalletController::class, 'show']);

            // Rates / Reviews
            Route::get('rates', [RateController::class, 'index']);
            Route::get('rates/{rate}', [RateController::class, 'show']);
            Route::delete('rates/{rate}', [RateController::class, 'destroy']);

            // Roles
            Route::apiResource('roles', RoleController::class);

            // Permissions
            Route::apiResource('permissions', PermissionController::class);

            // Settings
            Route::get('settings', [SettingController::class, 'index']);
            Route::post('settings', [SettingController::class, 'store']);
            Route::put('settings', [SettingController::class, 'update']);
            Route::delete('settings/{setting}', [SettingController::class, 'destroy']);

            // Notifications
            Route::post('notifications/send', [NotificationController::class, 'send']);
            Route::get('notifications', [NotificationController::class, 'index']);
        });
    });
});
