<?php

use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\Auth\AuthenticateController;
use App\Http\Controllers\Api\V1\CheckinController;
use App\Http\Controllers\Api\V1\ClientController;
use App\Http\Controllers\Api\V1\CommentController;
use App\Http\Controllers\Api\V1\LandingPageController;
use App\Http\Controllers\Api\V1\LanguageController;
use App\Http\Controllers\Api\V1\LanguageDefineController;
use App\Http\Controllers\Api\V1\MediaController;
use App\Http\Controllers\Api\V1\PageAccessLogController;
use App\Http\Controllers\Api\V1\PostCommentController;
use App\Http\Controllers\Api\V1\PostController;
use App\Http\Controllers\Api\V1\PostLikeController;
use App\Http\Controllers\Api\V1\UserCommentController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\UserPostController;
use App\Http\Controllers\Api\V1\Webhooks\PostmarkController;
use App\Http\Controllers\Api\Videc\OnePayController;
use App\Http\Controllers\Api\Videc\PaymentController;
use App\Http\Controllers\Api\Videc\PortalController;
use App\Http\Controllers\Api\Videc\TicketCatalogController;
use App\Http\Controllers\Api\Videc\RegistrationFileController;
use App\Http\Controllers\Api\Videc\RegistrationController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

if (config('api_security.allow_debug_routes', false) && app()->environment(['local', 'testing'])) {
    Route::post('/woo/webhook', function (Request $r) {
        \Log::info('woo_ping', ['h' => $r->headers->all(), 'raw' => $r->getContent()]);

        return response()->json(['ok' => true]);
    });
}

Route::prefix('v1')->group(function () {
    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        // // Comments
        // Route::apiResource('comments', CommentController::class)->only('destroy');
        // Route::apiResource('posts.comments', PostCommentController::class)->only('store');

        // // Posts
        // Route::apiResource('posts', PostController::class)->only(['update', 'store', 'destroy']);
        // Route::post('/posts/{post}/likes', [PostLikeController::class, 'store'])->name('posts.likes.store');
        // Route::delete('/posts/{post}/likes', [PostLikeController::class, 'destroy'])->name('posts.likes.destroy');

        // Users
        Route::apiResource('users', UserController::class)->only('update');
        Route::apiResource('users', UserController::class)->only(['index', 'show']);

        // // Media
        // Route::apiResource('media', MediaController::class)->only(['store', 'destroy']);

        /* CHECKIN */
        Route::post('/checkin', [CheckinController::class, 'checkin']);
        Route::post('/multi-checkin', [CheckinController::class, 'multiCheckin']);
    });

    /* LANGUAGES */
    Route::apiResource('languages', LanguageController::class)->only(['index']);

    /* LANGUAGE DEFINES */
    Route::prefix('language_defines')->group(function () {
        Route::get('/{lang}/{file}', [LanguageDefineController::class, 'getLanguageFile']);
    });

    Route::post('/authenticate', [AuthenticateController::class, 'authenticate'])->name('authenticate');

    /* WEBHOOK */
    Route::prefix('webhook')->middleware(['api.basic.auth.webhook', 'throttle:webhook-inbound'])->group(function () {
        /* POSTMARK */
        Route::post('/postmark/send', [PostmarkController::class, 'handlePostmarkWebhook'])->name('postmark.webhook');
    });

    /* Assets */
    Route::prefix('assets')->group(function () {
        Route::get('/medias', [AssetController::class, 'getMedias']);
    });

    /* AUTH REGISTER */
    Route::middleware('api.basic.auth.register')->group(function () {
        /* PAGE ACCESS */
        Route::post('/page_access_logs/store', [PageAccessLogController::class, 'store']);

        /* LANDING PAGES */
        Route::prefix('landing_pages')->group(function () {
            Route::get('/slug/{slug}/{lang}', [LandingPageController::class, 'getBySlug']);
        });

        /* EVENTS */
        // Route::apiResource('events', EventController::class)->only(['detail']);

        /* CLIENTS */
        Route::prefix('clients')->group(function () {
            Route::get('/find', [ClientController::class, 'find']);
            Route::get('/generate-qrcode-on-setting/{event}', [ClientController::class, 'generateQrcodeOnSetting']);
            Route::post('/register', [ClientController::class, 'register']);
            Route::post('/search', [ClientController::class, 'search']);
            Route::post('/update', [ClientController::class, 'update']);
        });
    });

    Route::middleware(['api.basic.auth.client', 'throttle:client-upsert'])->group(function () {
        /* CLIENTS */
        Route::prefix('clients')->group(function () {
            Route::get('/qrcode', [ClientController::class, 'findByQrcode']);
            Route::get('/id/{id}', [ClientController::class, 'findById']);
            Route::post('/upsert', [ClientController::class, 'upsert']);
            Route::post('/upsert-by-id', [ClientController::class, 'upsertById']);
        });
    });

    if (config('api_security.allow_debug_routes', false) && app()->environment(['local', 'testing'])) {
        Route::get('/test/127492md9', [ClientController::class, 'test']);
        Route::post('/test/4389dfnlas', [ClientController::class, 'testSync']);
    }

    // // Comments
    // Route::apiResource('posts.comments', PostCommentController::class)->only('index');
    // Route::apiResource('users.comments', UserCommentController::class)->only('index');
    // Route::apiResource('comments', CommentController::class)->only(['index', 'show']);

    // // Posts
    // Route::apiResource('posts', PostController::class)->only(['index', 'show']);
    // Route::apiResource('users.posts', UserPostController::class)->only('index');

    // // Users
    // Route::apiResource('users', UserController::class)->only(['index', 'show']);

    // // Media
    // Route::apiResource('media', MediaController::class)->only('index');
});

Route::middleware('api.basic.auth.register')->group(function () {
    Route::get('/events/{event}/tickets', [TicketCatalogController::class, 'show']);
});

Route::prefix('registrations')->middleware(['api.basic.auth.register', 'throttle:registration-submit'])->group(function () {
    Route::post('/files/upload', [RegistrationFileController::class, 'uploadPublic'])
        ->middleware('throttle:registration-upload');
    Route::post('/draft', [RegistrationController::class, 'draft']);
    Route::post('/submit', [RegistrationController::class, 'submit']);
});

Route::middleware(['api.basic.auth.register', 'throttle:registration-submit'])->group(function () {
    Route::post('/payments/create', [PaymentController::class, 'createAttemptLegacy']);
    Route::get('/payments/status/{attempt}', [PaymentController::class, 'showAttempt']);
});

Route::prefix('orders')->middleware(['api.basic.auth.register', 'throttle:registration-submit'])->group(function () {
    Route::post('/{order}/apply-promo', [PaymentController::class, 'applyPromo']);
    Route::post('/{order}/remove-promo', [PaymentController::class, 'removePromo']);
    Route::post('/{order}/payment-attempts', [PaymentController::class, 'createAttempt']);
    Route::get('/{order}', [PaymentController::class, 'portalOrder']);
    Route::post('/{order}/repay', [PaymentController::class, 'repay']);
    Route::post('/{order}/cancel', [PaymentController::class, 'cancel']);
    Route::post('/{order}/refund', [PaymentController::class, 'refund']);
    Route::post('/{order}/change-ticket', [PaymentController::class, 'changeTicket']);
});

Route::prefix('portal')->middleware(['api.basic.auth.register', 'throttle:registration-submit'])->group(function () {
    Route::post('/login', [PortalController::class, 'login'])->middleware('throttle:portal-login');
    Route::post('/password', [PortalController::class, 'updatePassword']);
    Route::post('/profile', [PortalController::class, 'updateProfile']);
    Route::post('/files/upload', [RegistrationFileController::class, 'uploadPortal'])
        ->middleware('throttle:registration-upload');
    Route::get('/files/{fileId}/download', [RegistrationFileController::class, 'downloadPortal'])
        ->middleware('throttle:registration-upload');
    Route::get('/orders', [PortalController::class, 'orders']);
    Route::get('/orders/{order}', [PortalController::class, 'showOrder']);
    Route::post('/orders/{order}/repay', [PortalController::class, 'repay']);
    Route::post('/orders/{order}/buy-more', [PortalController::class, 'buyMore']);
});

Route::prefix('payments/onepay')->group(function () {
    Route::match(['get', 'post'], '/return', [OnePayController::class, 'paymentReturn'])->middleware('throttle:onepay-callback');
    Route::match(['get', 'post'], '/ipn', [OnePayController::class, 'ipn'])->middleware('throttle:onepay-callback');
    Route::post('/querydr', [OnePayController::class, 'queryDr'])->middleware(['api.basic.auth.register', 'throttle:onepay-querydr']);
});

Route::prefix('onepay')->group(function () {
    Route::match(['get', 'post'], '/return', [OnePayController::class, 'paymentReturn'])->middleware('throttle:onepay-callback');
    Route::match(['get', 'post'], '/ipn', [OnePayController::class, 'ipn'])->middleware('throttle:onepay-callback');
    Route::post('/querydr', [OnePayController::class, 'queryDr'])->middleware(['api.basic.auth.register', 'throttle:onepay-querydr']);
});
