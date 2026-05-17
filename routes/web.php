<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingPageController;
use App\Http\Controllers\NewsletterSubscriptionController;
use App\Http\Controllers\PaymentReturnController;
use App\Http\Controllers\PostCommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostFeedController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\Web\CampaignDetailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('admin.dashboard'); // Redirect to dashboard if authenticated
    }

    return redirect()->route('login'); // Redirect to login if not authenticated
})->name('home');

Route::get('/home', [HomeController::class, 'home'])->name('web.home');

// Route::resource('clients', ClientController::class)->only(['store']);
Route::prefix('clients')->group(function () {
    Route::get('/generate/id/{id}', [ClientController::class, 'generateQrcodeById'])->name('clients.generate-qrcode-by-id');
    Route::get('/microsite/{eventCode}/{token}', [ClientController::class, 'micrositePinaco'])->name('clients.microsite.pinaco');
    Route::post('/microsite/{eventCode}/attendance/{token}', [ClientController::class, 'updateAttendancePinaco'])->name('clients.microsite.pinaco.attendance');
    // Route::get('/qrcode/{qrcode}', [ClientController::class, 'viewQrcode'])->name('clients.view-qrcode');
    Route::get('/qrcode/id/{id}', [ClientController::class, 'viewQrcodeById'])->name('clients.view-qrcode-by-id');
    Route::get('/card/view/{cardId}/{clientId}', [ClientController::class, 'viewCard'])->name('clients.view-card');
    Route::get('/document/view/{clientId}', [ClientController::class, 'viewDocumentPdf'])->name('clients.view-document-pdf');
    Route::post('/{slug}/store', [ClientController::class, 'store'])->name('clients.store');
});

Route::prefix('register')->group(function () {
    Route::get('/{slug}', [LandingPageController::class, 'register'])->name('landing_pages.register');
    Route::get('/{slug}/success/{qrcode}', [LandingPageController::class, 'success'])->name('landing_pages.success');
});

Route::match(['get', 'post'], '/payment/return', PaymentReturnController::class);

/* CAMPAIGN DETAILS */
Route::prefix('campaign_details')->group(function () {
    /* POSTMARK */
    Route::get('/view-email/{campaign_detail}', [CampaignDetailController::class, 'viewEmail'])->name('campaign_details.view-email');
});

/* CHANGE LANGUAGE */
Route::get('/change-language/{locale}', [HomeController::class, 'changeLanguage'])->name('change-language');

/* COMMON */
Route::get('/placeholder/qrcode', [HomeController::class, 'getPlaceholderQrcode'])->name('get-placeholder-qrcode');

Route::prefix('users')->group(function () {
    Route::get('/verify/{prefix}/{verify_token}', [UserController::class, 'verify'])
        ->name('users.verify')
        ->middleware('signed');
});

/* CUSTOMIZE */
/* tba-event-1110 */
Route::get('/qrcodes/{eventCode}/{qrcode}', [ClientController::class, 'viewQrcodes']);
Route::get('/download-qrcodes/{eventCode}', [ClientController::class, 'downloadQrcodeImages'])->name('qrcodes.download-images');

/* hidec-2025 */
// Route::get('/certificate/{eventCode}/{qrcode}', [ClientController::class, 'getCert']);

// Route::get('/posts/feed', [PostFeedController::class, 'index'])->name('posts.feed');
// Route::resource('posts', PostController::class)->only('show');
// Route::resource('users', UserController::class)->only('show');
// Route::resource('posts.comments', PostCommentController::class)->only('index');

// Route::get('newsletter-subscriptions/unsubscribe', [NewsletterSubscriptionController::class, 'unsubscribe'])->name('newsletter-subscriptions.unsubscribe');

// Route::middleware(['auth', 'role:admin'])->group(function () {
//     Route::get('/scan', function () {
//         view('scan');
//     })->name('scan');
// });

// Route::get('/test', [HomeController::class, 'test']);
