<?php

use App\Http\Controllers\Api\V1\AreaController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CallController;
use App\Http\Controllers\Api\V1\ChatController;
use App\Http\Controllers\Api\V1\CmsController;
use App\Http\Controllers\Api\V1\FaqController as PublicFaqController;
use App\Http\Controllers\Api\V1\IntroScreenController;
use App\Http\Controllers\Api\V1\ProviderController;
use App\Http\Controllers\Api\V1\Provider\ServiceRequestController as ProviderServiceRequestController;
use App\Http\Controllers\Api\V1\Provider\QuotationController as ProviderQuotationController;
use App\Http\Controllers\Api\V1\ServiceRequestController as ClientServiceRequestController;
use App\Http\Controllers\Api\V1\PaymentController as ClientPaymentController;
use App\Http\Controllers\Api\V1\QuotationController as ClientQuotationController;
use App\Http\Controllers\Api\V1\WalletController as ClientWalletController;
use App\Http\Controllers\Api\V1\RateController as ClientRateController;
use App\Http\Controllers\Api\V1\RegisterController;
use App\Http\Controllers\Api\V1\CategoryController as PublicCategoryController;
use App\Http\Controllers\Api\V1\SubCategoryController as PublicSubCategoryController;
use App\Http\Controllers\Api\V1\CityController as PublicCityController;
use App\Http\Controllers\Api\V1\CountryController as PublicCountryController;
use App\Http\Controllers\Api\V1\Admin\AreaController as AdminAreaController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\CityController;
use App\Http\Controllers\Api\V1\Admin\CountryController;
use App\Http\Controllers\Api\V1\Admin\DashboardController;
use App\Http\Controllers\Api\V1\Admin\FaqController;
use App\Http\Controllers\Api\V1\Admin\IntroScreenController as AdminIntroScreenController;
use App\Http\Controllers\Api\V1\Admin\NotificationController;
use App\Http\Controllers\Api\V1\NotificationController as PublicNotificationController;
use App\Http\Controllers\Api\V1\ProfileController;
use App\Http\Controllers\Api\V1\Admin\PaymentController;
use App\Http\Controllers\Api\V1\Admin\PayoutController;
use App\Http\Controllers\Api\V1\Admin\PermissionController;
use App\Http\Controllers\Api\V1\Admin\PrivacyPolicyController;
use App\Http\Controllers\Api\V1\Admin\ProviderController as AdminProviderController;
use App\Http\Controllers\Api\V1\Admin\ChatController as AdminChatController;
use App\Http\Controllers\Api\V1\Admin\ProviderDocumentController;
use App\Http\Controllers\Api\V1\Admin\QuotationController as AdminQuotationController;
use App\Http\Controllers\Api\V1\Admin\RateController;
use App\Http\Controllers\Api\V1\Admin\TermsController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\ServiceRequestController;
use App\Http\Controllers\Api\V1\Admin\SettingController;
use App\Http\Controllers\Api\V1\Admin\SubCategoryController;
use App\Http\Controllers\Api\V1\Admin\SupportTicketController as AdminSupportTicketController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Admin\WalletController;
use App\Http\Controllers\Api\V1\SupportTicketController;
use App\Http\Controllers\Api\V1\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // ─── Public Auth Routes ───────────────────────────────────────────────────
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/social-login', [AuthController::class, 'socialLogin']);
    Route::post('/register/client', [RegisterController::class, 'client']);
    Route::post('/register/provider', [RegisterController::class, 'provider']);

    Route::middleware('throttle:6,1')->group(function () {
        Route::post('/check-phone', [AuthController::class, 'checkPhone']);
        Route::post('/forget-password', [AuthController::class, 'forgetPassword']);
        Route::post('/verify-code', [AuthController::class, 'verifyCode']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });

    // ─── Public Reference/CMS Routes ──────────────────────────────────────────
    Route::get('/intro-screens', [IntroScreenController::class, 'index']);
    Route::get('/countries', [PublicCountryController::class, 'index']);
    Route::get('/cities', [PublicCityController::class, 'index']);
    Route::get('/areas', [AreaController::class, 'index']);
    Route::get('/categories', [PublicCategoryController::class, 'index']);
    Route::get('/categories/{category}/sub-categories', [PublicSubCategoryController::class, 'byCategory']);
    Route::get('/sub-categories', [PublicSubCategoryController::class, 'index']);
    Route::get('/terms-and-conditions', [CmsController::class, 'terms']);
    Route::get('/privacy-policy', [CmsController::class, 'privacy']);
    Route::get('/faqs', [PublicFaqController::class, 'index']);
    Route::get('/providers', [ProviderController::class, 'index']);
    Route::get('/providers/{provider}', [ProviderController::class, 'show']);

    // ─── Authenticated Routes ─────────────────────────────────────────────────
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/change-password', [AuthController::class, 'changePassword']);
        Route::get('/user', fn(Request $request) => $request->user());

        // ─── Profile Self-Service Routes ───────────────────────────────────────
        Route::get('/profile', [ProfileController::class, 'show']);
        Route::patch('/profile', [ProfileController::class, 'update']);

        // ─── Provider Self-Service Routes ─────────────────────────────────────
        Route::prefix('provider')->middleware('provider')->group(function () {
            Route::get('profile', [ProviderController::class, 'profile']);
            Route::patch('profile', [ProviderController::class, 'updateProfile']);
            Route::patch('availability/available', [ProviderController::class, 'available']);
            Route::patch('availability/unavailable', [ProviderController::class, 'unavailable']);
            Route::post('service-requests', [ProviderServiceRequestController::class, 'store']);
            Route::patch('service-requests/{serviceRequest}/status', [ProviderServiceRequestController::class, 'updateStatus']);
            Route::post('quotations/{quotation}/bids', [ProviderQuotationController::class, 'storeBid']);
        });

        // ─── Chats (client <-> provider) ──────────────────────────────────────
        Route::prefix('chats')->group(function () {
            Route::get('/', [ChatController::class, 'index']);
            Route::post('/', [ChatController::class, 'store']);
            Route::get('/{chatRoom}/messages', [ChatController::class, 'messages']);
            Route::post('/{chatRoom}/messages', [ChatController::class, 'sendMessage']);
            Route::delete('/{chatRoom}', [ChatController::class, 'destroy']);
            Route::post('/{chatRoom}/calls/start', [CallController::class, 'start']);
            Route::patch('/{chatRoom}/calls/{call}/end', [CallController::class, 'end']);
        });

        // ─── Notifications (self-service) ─────────────────────────────────────
        Route::prefix('notifications')->group(function () {
            Route::get('/', [PublicNotificationController::class, 'index']);
            Route::patch('/enable', [PublicNotificationController::class, 'enable']);
            Route::patch('/disable', [PublicNotificationController::class, 'disable']);
        });

        // ─── Wishlist (self-service) ───────────────────────────────────────────
        Route::prefix('wishlist')->group(function () {
            Route::get('/', [WishlistController::class, 'index']);
            Route::post('/', [WishlistController::class, 'store']);
            Route::delete('/{itemType}/{itemId}', [WishlistController::class, 'destroy']);
        });

        // ─── Service Requests (client create/cancel/confirm/checkout; both read) ──
        Route::prefix('service-requests')->group(function () {
            Route::get('/', [ClientServiceRequestController::class, 'index']);
            Route::get('/{serviceRequest}', [ClientServiceRequestController::class, 'show']);
            Route::post('/{serviceRequest}/feedback', [ClientRateController::class, 'store']);
            Route::middleware('customer')->group(function () {
                Route::post('/', [ClientServiceRequestController::class, 'store']);
                Route::patch('/{serviceRequest}/status', [ClientServiceRequestController::class, 'updateStatus']);
                Route::post('/{serviceRequest}/checkout', [ClientPaymentController::class, 'checkout']);
            });
        });

        Route::post('/payments/{payment}/confirm', [ClientPaymentController::class, 'confirm'])->middleware('customer');

        // ─── Quotations (client create/approve-bid; both read) ─────────────────
        Route::prefix('quotations')->group(function () {
            Route::get('/', [ClientQuotationController::class, 'index']);
            Route::get('/{quotation}', [ClientQuotationController::class, 'show']);
            Route::middleware('customer')->group(function () {
                Route::post('/', [ClientQuotationController::class, 'store']);
                Route::patch('/{quotation}/bids/{bid}/approve', [ClientQuotationController::class, 'approveBid']);
            });
        });

        // ─── Wallet (self-service) ──────────────────────────────────────────────
        Route::get('/wallet', [ClientWalletController::class, 'show']);

        // ─── Feedback / Ratings (self-service) ─────────────────────────────────
        Route::get('/feedbacks', [ClientRateController::class, 'index']);
        Route::get('/feedbacks/{rate}', [ClientRateController::class, 'show']);

        // ─── Support Tickets (client/provider/admin <-> admin support team) ───
        Route::prefix('support-tickets')->group(function () {
            Route::get('/', [SupportTicketController::class, 'index']);
            Route::post('/', [SupportTicketController::class, 'store']);
            Route::get('/{supportTicket}/replies', [SupportTicketController::class, 'replies']);
            Route::post('/{supportTicket}/replies', [SupportTicketController::class, 'sendReply']);
            Route::patch('/{supportTicket}/close', [SupportTicketController::class, 'close']);
            Route::patch('/{supportTicket}/reopen', [SupportTicketController::class, 'reopen']);
        });

        // ─── Admin Routes ─────────────────────────────────────────────────────
        Route::prefix('admin')->middleware('admin')->group(function () {

            // Dashboard
            Route::get('dashboard', [DashboardController::class, 'index']);

            // Users
            Route::patch('users/{user}/block', [UserController::class, 'block']);
            Route::patch('users/{user}/unblock', [UserController::class, 'unblock']);
            Route::patch('users/{user}/change-password', [UserController::class, 'changePassword']);
            Route::get('users/{user}/wishlist', [UserController::class, 'wishlist']);
            Route::apiResource('users', UserController::class);

            // Providers
            Route::patch('providers/{provider}/verify', [AdminProviderController::class, 'verify']);
            Route::patch('providers/{provider}/unverify', [AdminProviderController::class, 'unverify']);
            Route::apiResource('providers', AdminProviderController::class)->only(['index', 'show', 'update', 'destroy']);

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

            // Areas
            Route::apiResource('areas', AdminAreaController::class);

            // CMS: Intro Screens
            Route::apiResource('intro-screens', AdminIntroScreenController::class);

            // CMS: Terms & Conditions / Privacy Policy (singleton)
            Route::get('terms-and-conditions', [TermsController::class, 'show']);
            Route::put('terms-and-conditions', [TermsController::class, 'update']);
            Route::get('privacy-policy', [PrivacyPolicyController::class, 'show']);
            Route::put('privacy-policy', [PrivacyPolicyController::class, 'update']);

            // CMS: FAQs
            Route::apiResource('faqs', FaqController::class);

            // Service Requests
            Route::patch('service-requests/{serviceRequest}/status', [ServiceRequestController::class, 'updateStatus']);
            Route::get('service-requests', [ServiceRequestController::class, 'index']);
            Route::get('service-requests/{serviceRequest}', [ServiceRequestController::class, 'show']);
            Route::post('service-requests', [ServiceRequestController::class, 'store']);

            // Quotations
            Route::get('quotations', [AdminQuotationController::class, 'index']);
            Route::get('quotations/{quotation}', [AdminQuotationController::class, 'show']);
            Route::post('quotations', [AdminQuotationController::class, 'store']);
            Route::post('quotations/{quotation}/bids', [AdminQuotationController::class, 'storeBid']);
            Route::patch('quotations/{quotation}/bids/{bid}/approve', [AdminQuotationController::class, 'approveBid']);

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
            Route::post('rates', [RateController::class, 'store']);
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

            // Chats (view-only)
            Route::get('chats', [AdminChatController::class, 'index']);
            Route::get('chats/{chatRoom}/messages', [AdminChatController::class, 'messages']);

            // Support Tickets
            Route::get('support-tickets', [AdminSupportTicketController::class, 'index']);
            Route::get('support-tickets/{supportTicket}', [AdminSupportTicketController::class, 'show']);
            Route::get('support-tickets/{supportTicket}/replies', [AdminSupportTicketController::class, 'replies']);
            Route::post('support-tickets/{supportTicket}/replies', [AdminSupportTicketController::class, 'sendReply']);
            Route::patch('support-tickets/{supportTicket}/close', [AdminSupportTicketController::class, 'close']);
            Route::patch('support-tickets/{supportTicket}/reopen', [AdminSupportTicketController::class, 'reopen']);
        });
    });
});
