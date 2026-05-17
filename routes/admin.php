<?php

use App\Http\Controllers\Admin\AudioController;
use App\Http\Controllers\Admin\ApiClientLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\CampaignController;
use App\Http\Controllers\Admin\CampaignDetailController;
use App\Http\Controllers\Admin\CardController;
use App\Http\Controllers\Admin\CardDetailController;
use App\Http\Controllers\Admin\CheckinController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\CustomFieldTemplateController;
use App\Http\Controllers\Admin\EmailController;
use App\Http\Controllers\Admin\EmailSenderController;
use App\Http\Controllers\Admin\EmailTemplateController;
use App\Http\Controllers\Admin\EventAreaController;
use App\Http\Controllers\Admin\EventPromoCodeController;
use App\Http\Controllers\Admin\EventTicketController;
use App\Http\Controllers\Admin\MediaLibraryController;
use App\Http\Controllers\Admin\ShowDashboard;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\EventFileController;
use App\Http\Controllers\Admin\EventSettingController;
use App\Http\Controllers\Admin\HistoryController;
use App\Http\Controllers\Admin\ImpExpFileController;
use App\Http\Controllers\Admin\LabelController;
use App\Http\Controllers\Admin\LabelDetailController;
use App\Http\Controllers\Admin\LandingPageController;
use App\Http\Controllers\Admin\LanguageDefineController;
use App\Http\Controllers\Admin\LuckyDrawClientController;
use App\Http\Controllers\Admin\LuckyDrawController;
use App\Http\Controllers\Admin\LuckyDrawRewardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\OrderController;

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', ShowDashboard::class)->name('dashboard');
    Route::get('api-client-logs', [ApiClientLogController::class, 'index'])->name('api-client-logs.index');
});

/* LOG VIEWER */
Route::get('logs', function () {
    if (auth()->check()) {
        if (auth()->user()->isSysAdmin()) {
            return redirect('/'.trim(config('log-viewer.route_path', 'log-viewer'), '/'));
        }

        abort(404);
    }
})->name('logs');

Route::middleware([
    'auth',
    'web.except.routes',
    'role:admin'
])->group(function () {
    /* COMPANYS */
    Route::resource('companys', CompanyController::class);
    Route::prefix('companys')->group(function () {
        Route::post('sync-event-settings/{company}', [CompanyController::class, 'syncEventSetting'])->name('companys.sync-event-settings');
    });

    /* EVENTS */
    Route::resource('events', EventController::class);
    Route::prefix('events')->group(function () {
        Route::post('upload-medias/{event}', [EventController::class, 'uploadMedias'])->name('events.upload-medias');
        Route::get('/data/get-list-by/{companyId}', [EventController::class, 'getListByCompanyId'])->name('events.get-list-by-company-id');
        Route::put('update-custom_checkin_messages/{event}', [EventController::class, 'updateCustomCheckinMessages'])->name('events.update-custom_checkin_messages');
        Route::post('clone/{event}', [EventController::class, 'clone'])->name('events.clone');
        Route::post('update-features/{event}', [EventController::class, 'updateFeatures'])->name('events.update-features');
        Route::post('remove-feature/{event}', [EventController::class, 'removeFeature'])->name('events.remove-feature');
        Route::get('{event}/tickets', [EventTicketController::class, 'index'])->name('events.tickets.index');
        Route::post('{event}/tickets', [EventTicketController::class, 'store'])->name('events.tickets.store');
        Route::put('{event}/tickets/{ticket}', [EventTicketController::class, 'update'])->name('events.tickets.update');
        Route::delete('{event}/tickets/{ticket}', [EventTicketController::class, 'destroy'])->name('events.tickets.destroy');
        Route::get('{event}/promo-codes', [EventPromoCodeController::class, 'index'])->name('events.promo-codes.index');
        Route::post('{event}/promo-codes', [EventPromoCodeController::class, 'store'])->name('events.promo-codes.store');
        Route::put('{event}/promo-codes/{promoCode}', [EventPromoCodeController::class, 'update'])->name('events.promo-codes.update');
        Route::delete('{event}/promo-codes/{promoCode}', [EventPromoCodeController::class, 'destroy'])->name('events.promo-codes.destroy');
    });

    Route::prefix('tickets')->group(function () {
        Route::get('/', [EventTicketController::class, 'selectEventIndex'])->name('tickets.index');
        Route::get('/select-event-to-manage', [EventTicketController::class, 'selectEventToManage'])->name('tickets.select-event-to-manage');
    });

    /* EVENT AREAS */
    Route::resource('event_areas', EventAreaController::class);

    /* EVENT SETTINGS */
    Route::resource('event_settings', EventSettingController::class)->only(['update', 'store', 'destroy']);
    Route::prefix('event_settings')->group(function () {
        Route::post('sync-settings/{event}', [EventSettingController::class, 'syncSettings'])->name('event_settings.sync-settings');
        Route::put('event_settings/{eventSetting}', [EventSettingController::class, 'updateValue'])->name('event_settings.update-value');
    });

    /* EVENT FILES */
    Route::prefix('event_files')->group(function () {
        Route::post('upload/{event}', [EventFileController::class, 'upload'])->name('event_files.upload');
    });

    /* CUSTOM FIELD TEMPLATES */
    Route::resource('custom_field_templates', CustomFieldTemplateController::class)->only(['update', 'store', 'destroy']);
    Route::prefix('custom_field_templates')->group(function () {
        Route::post('/update-orders', [CustomFieldTemplateController::class, 'updateOrders'])->name('custom_field_templates.update-orders');
    });

    /* CHECKIN */
    Route::prefix('checkins')->group(function () {
        Route::get('/config/{event}', [CheckinController::class, 'config'])->name('checkins.config');
        Route::get('/render-background/{event}/{screen}/{msg}', [CheckinController::class, 'renderBackground'])->name('checkins.render-background');
        Route::delete('/destroy-all/{event}', [CheckinController::class, 'destroyAll'])->name('checkins.destroy-all');
    });

    /* CLIENTS */
    Route::resource('clients', ClientController::class)->only(['destroy']);
    Route::get('clients/data/{event}', [ClientController::class, 'getData'])->name('clients.data');
    Route::prefix('clients')->group(function () {
        Route::get('/import/{event}', [ClientController::class, 'import'])->name('clients.import');
        Route::post('/upload/{event}', [ClientController::class, 'upload'])->name('clients.upload');
        Route::delete('/destroy-all/{event}', [ClientController::class, 'destroyAll'])->name('clients.destroy-all');
    });

    /* IMP EXP FILE */
    Route::prefix('imp_exp_files')->group(function () {
        Route::get('/progress/{imp_exp_file}', [ImpExpFileController::class, 'getProgress'])->name('imp_exp_files.progress');
    });

    /* CAMPAIGNS */
    Route::resource('campaigns', CampaignController::class)->only(['index', 'edit', 'update', 'store', 'destroy']);
    Route::prefix('campaigns')->group(function () {
        Route::get('/select-event-to-create', [CampaignController::class, 'selectEventToCreate'])->name('campaigns.select-event-to-create');
        Route::get('/create/{event}', [CampaignController::class, 'create'])->name('campaigns.create');
        Route::get('/history/{campaign}', [CampaignController::class, 'viewHistory'])->name('campaigns.history');
        Route::get('/progress/{campaign}', [CampaignController::class, 'getProgress'])->name('campaigns.progress');
        Route::get('/send-mail-table/{campaign}', [CampaignController::class, 'getSendMailTable'])->name('campaigns.send-mail-table');
        Route::get('/history-table/{campaign}', [CampaignController::class, 'getHistoryTable'])->name('campaigns.history-table');
        Route::post('/sync-campaign-detail/{campaign}', [CampaignController::class, 'syncCampaignDetail'])->name('campaigns.sync-campaign-detail');
        Route::post('/cancel/{campaign}', [CampaignController::class, 'cancel'])->name('campaigns.cancel');
        Route::post('/clone/{campaign}', [CampaignController::class, 'clone'])->name('campaigns.clone');
    });

    /* CAMPAIGN DETAILS */
    Route::prefix('campaign_details')->group(function () {
        Route::post('/send-mail/{campaign}', [CampaignDetailController::class, 'sendMail'])->name('campaign_details.send-mail');
        Route::post('/update-field', [CampaignDetailController::class, 'updateField'])->name('campaign_details.update-field');
    });

    /* EMAILS */
    Route::prefix('emails')->group(function () {
        // Route::get('/export-report/{event}', [EmailController::class, 'exportReport'])->name('emails.export-report');
        Route::get('/export-error-emails/{campaign}', [EmailController::class, 'exportErrorEmail'])->name('emails.export-error-emails');
        Route::post('/cancel/{campaign}', [EmailController::class, 'cancelByCampaign'])->name('emails.cancel-by-campaign');
        Route::post('/change-status/{email}', [EmailController::class, 'changeStatus'])->name('emails.change-status');
    });

    /* EMAIL SENDERS */
    Route::resource('email_senders', EmailSenderController::class)->only(['index', 'edit']);
    Route::prefix('email_senders')->group(function () {
        /* vì default method update ở đây lấy put nên không set default được */
        Route::post('/update/{senderId}', [EmailSenderController::class, 'update'])->name('email_senders.update');
    });

    /* EMAIL TEMPLATES */
    Route::resource('email_templates', EmailTemplateController::class)->only(['index']);
    Route::prefix('email_templates')->group(function () {
        /* POSTMARK */
        Route::get('/re-sync-postmark-templates', [EmailTemplateController::class, 'reSyncPostmarkTemplates'])->name('email_templates.re-sync-postmark-templates');
        Route::get('/sync-postmark-template/{templateId}', [EmailTemplateController::class, 'syncPostmarkTemplate'])->name('email_templates.sync-postmark-template');
        Route::get('/view-postmark-template/{templateId}', [EmailTemplateController::class, 'viewPostmarkTemplate'])->name('email_templates.view-postmark-template');
        Route::get('/edit-postmark-template/{templateId}', [EmailTemplateController::class, 'editPostmarkTemplate'])->name('email_templates.edit-postmark-template');
        Route::post('/update-postmark-template/{templateId}', [EmailTemplateController::class, 'updatePostmarkTemplate'])->name('email_templates.update-postmark-template');
        Route::post('/send-test-postmark-template/{templateId}', [EmailTemplateController::class, 'sendTestPostmarkTemplate'])->name('email_templates.send-test-postmark-template');
    });

    /* LABEL */
    Route::resource('labels', LabelController::class)->only(['index', 'update', 'store']);
    Route::prefix('labels')->group(function () {
        Route::get('/select-event-to-create', [LabelController::class, 'selectEventToCreate'])->name('labels.select-event-to-create');
        Route::get('/create/{event}', [LabelController::class, 'create'])->name('labels.create');
        Route::get('/edit/{label}', [LabelController::class, 'edit'])->name('labels.edit');
        Route::get('/render-label/{label}', [LabelController::class, 'renderLabel'])->name('labels.render-label');
        Route::post('/update-live/{label}', [LabelController::class, 'updateLive'])->name('labels.update-live');
        Route::post('/clone/{label}', [LabelController::class, 'clone'])->name('labels.clone');
    });

    /* LABEL DETAILS */
    Route::resource('label_details', LabelDetailController::class)->only(['store', 'update']);

    /* CARDS */
    Route::resource('cards', CardController::class)->only(['index', 'update', 'store']);
    Route::prefix('cards')->group(function () {
        Route::get('/select-event-to-create', [CardController::class, 'selectEventToCreate'])->name('cards.select-event-to-create');
        Route::get('/create/{event}', [CardController::class, 'create'])->name('cards.create');
        Route::get('/edit/{card}', [CardController::class, 'edit'])->name('cards.edit');
        Route::get('/render-background/{event}/{card}', [CardController::class, 'renderBackground'])->name('cards.render-background');
        Route::get('/progress/{card}', [CardController::class, 'getProgress'])->name('cards.progress');
        Route::get('/download-images/{card}', [CardController::class, 'downloadCardImages'])->name('cards.download-images');
        Route::post('/generate/{card}', [CardController::class, 'generate'])->name('cards.generate');
        Route::get('/get-full-screen/{card}', [CardController::class, 'getFullScreen'])->name('cards.get-full-screen');
    });

    /* CARD DETAILS */
    Route::resource('card_details', CardDetailController::class)->only(['store', 'update']);
    Route::prefix('card_details')->group(function () {
        // Route::post('/update-by-custom-field-template/{custom_field_template}', [CardDetailController::class, 'updateByCustomFieldTemplate'])->name('card_details.update-by-custom-field-template');
    });

    /* LANDING PAGES */
    Route::resource('landing_pages', LandingPageController::class)->only(['index', 'update', 'store', 'destroy']);
    Route::prefix('landing_pages')->group(function () {
        Route::get('/select-event-to-create', [LandingPageController::class, 'selectEventToCreate'])->name('landing_pages.select-event-to-create');
        Route::get('/create/{event}', [LandingPageController::class, 'create'])->name('landing_pages.create');
        Route::get('/edit/{landing_page}', [LandingPageController::class, 'edit'])->name('landing_pages.edit');
        Route::post('/clone/{landing_page}', [LandingPageController::class, 'clone'])->name('landing_pages.clone');
        Route::post('/update-show-language-selection/{landing_page}', [LandingPageController::class, 'updateShowLanguageSelection'])->name('landing_pages.update-show-language-selection');
    });

    /* LANGUAGES DEFINES */
    // Route::resource('language_defines', LanguageDefineController::class)->only(['update', 'store']);
    Route::prefix('language_defines')->group(function () {
        Route::get('/generate-lang/{event}', [LanguageDefineController::class, 'generateLang'])->name('language_defines.generate-lang');
        Route::post('/edit-value', [LanguageDefineController::class, 'editValue'])->name('language_defines.edit-value');
    });

    /* LUCKY DRAW */
    Route::resource('lucky_draws', LuckyDrawController::class)->only(['index', 'edit', 'update', 'store', 'destroy']);
    Route::prefix('lucky_draws')->group(function () {
        Route::get('/select-event-to-create', [LuckyDrawController::class, 'selectEventToCreate'])->name('lucky_draws.select-event-to-create');
        Route::get('/create/{event}', [LuckyDrawController::class, 'create'])->name('lucky_draws.create');

        Route::get('/view-raffle/{lucky_draw}', [LuckyDrawController::class, 'viewRaffle'])->name('lucky_draws.view-raffle');
        Route::get('/export-raffle/{lucky_draw}', [LuckyDrawController::class, 'exportExcelRaffleResult'])->name('lucky_draws.export-raffle');
        Route::post('/update-raffle', [LuckyDrawController::class, 'updateRaffle'])->name('lucky_draws.update-raffle');
        /* UPLOAD EVENT IMAGE*/
        Route::post('upload/background', [LuckyDrawController::class, 'uploadBackground'])->name('lucky-draw.upload-background');
        /* DELETE EVENT IMAGE */
        Route::delete('delete/background', [LuckyDrawController::class, 'deleteBackground'])->name('lucky-draw.delete-background');
        Route::post('/cancel-reward', [LuckyDrawController::class, 'cancelReward'])->name('lucky-draw.cancel-reward');
    });

    /* LUCKY DRAW REWARD */
    Route::resource('lucky_draw_rewards', LuckyDrawRewardController::class)->only(['destroy']);
    Route::prefix('lucky_draw_rewards')->group(function () {
        Route::post('/store/{lucky_draw}', [LuckyDrawRewardController::class, 'store'])->name('lucky_draw_rewards.store');
        Route::post('/upload/{lucky_draw}', [LuckyDrawRewardController::class, 'upload'])->name('lucky_draw_rewards.upload');
        Route::post('/update/{lucky_draw_reward}', [LuckyDrawRewardController::class, 'update'])->name('lucky_draw_rewards.update');
        Route::post('/update-assignee/{lucky_draw_reward}', [LuckyDrawRewardController::class, 'updateAssignee'])->name('lucky_draw_rewards.update-assignee');
        Route::post('/update-assignees/{lucky_draw_reward}', [LuckyDrawRewardController::class, 'updateAssignees'])->name('lucky_draw_rewards.update-assignees');
        Route::delete('/reset/{lucky_draw}', [LuckyDrawRewardController::class, 'destroyAllByLuckyDraw'])->name('lucky_draw_rewards.reset');
    });

    /* LUCKY DRAW CLIENT */
    Route::resource('lucky_draw_clients', LuckyDrawClientController::class)->only(['destroy']);
    Route::prefix('lucky_draw_clients')->group(function () {
        Route::post('/sync/{lucky_draw}', [LuckyDrawClientController::class, 'sync'])->name('lucky_draw_clients.sync');
        Route::post('/reset', [LuckyDrawClientController::class, 'reset'])->name('lucky-draw-client.reset');
    });

    /* AUDIO */
    Route::resource('audios', AudioController::class)->only(['store', 'update']);
    Route::prefix('audios')->group(function () {
        Route::get('/set-to-event/{event}', [AudioController::class, 'setToEvent'])->name('audios.set-to-event');
    });

    /* HISTORY */
    Route::resource('histories', HistoryController::class)->only(['index']);

    /* Users */
    Route::resource('users', UserController::class)->only(['index', 'create', 'edit', 'update', 'store']);
    Route::prefix('users')->group(function () {
        Route::get('/generate-login-qrcode/{user}', [UserController::class, 'generateLoginQrcode'])->name('users.generate-login-qrcode');
        Route::post('/send-verification/{user}', [UserController::class, 'sendVerification'])->name('users.send-verification');
        Route::post('/sign-out/{user}', [UserController::class, 'signOut'])->name('users.sign-out');
    });

    /* MEDIA */
    Route::resource('media', MediaLibraryController::class)->only(['index', 'create', 'store', 'destroy']);
});

Route::middleware([
    'auth',
    'web.except.routes',
    'role:admin|user'
])->group(function () {
    /* EVENT */
    Route::resource('events', EventController::class)->only(['index']);

    /* CLIENTS */
    Route::resource('clients', ClientController::class)->only(['update', 'store']);
    Route::prefix('clients')->group(function () {
        Route::get('/index/{event}', [ClientController::class, 'index'])->name('clients.index');
        Route::get('/create/{event}', [ClientController::class, 'create'])->name('clients.create');
        Route::get('/edit/{client}', [ClientController::class, 'edit'])->name('clients.edit');
        Route::post('/generate/{event}', [ClientController::class, 'generate'])->name('clients.generate');
        Route::post('/generate-qrcodes/{event}', [ClientController::class, 'generateQrcodeImages'])->name('clients.generate-qrcodes');
        Route::get('/export/template-import/{event}', [ClientController::class, 'exportTemplateImport'])->name('clients.export-template-import');
        Route::get('/export/list/{event}', [ClientController::class, 'exportList'])->name('clients.export-list');
        Route::get('/export-qrcodes/{event}', [ClientController::class, 'exportQrcodes'])->name('clients.export-qrcodes');
        Route::get('/fill-qrcode/{event}', [ClientController::class, 'fillQrcode'])->name('clients.fill-qrcode');
        Route::get('/get-template-qrcodes/{event}', [ClientController::class, 'getTemplateQrcodes'])->name('clients.get-template-qrcodes');
        Route::get('/download/qrcodes/{event}', [ClientController::class, 'downloadQrcodeImages'])->name('clients.download-qrcodes');
        Route::post('/save-print', [ClientController::class, 'savePrint'])->name('clients.save-print');
        Route::post('/send-email/{client}', [ClientController::class, 'sendClientEmail'])->name('clients.send-email');
        Route::post('/generate-card/{client}', [ClientController::class, 'generateClientCard'])->name('clients.generate-card');
        /* customize */
        /* hidec-vn */
        Route::get('/export/lucky-draw-list/{event}', [ClientController::class, 'exportHidecVn'])->name('clients.lucky-draw-list');
    });

    /* CHECKIN */
    Route::prefix('checkins')->group(function () {
        Route::get('/index/{event}', [CheckinController::class, 'index'])->name('checkins.index');
        Route::post('/checkin', [CheckinController::class, 'checkin'])->name('checkins.checkin');
        Route::get('/export/check-in-out/{event}/{qrcode?}', [CheckinController::class, 'exportCheckInOutReport'])->name('checkins.export-check-in-out');
        Route::get('/export/checkin_count/{event}/{qrcode?}', [CheckinController::class, 'exportCheckInCount'])->name('checkins.export-checkin_count');
        Route::delete('/destroy-by-qrcode/{event}/{qrcode}', [CheckinController::class, 'destroyByQrcode'])->name('checkins.destroy-by-qrcode');
        Route::delete('/destroy-by-client/{clientId}', [CheckinController::class, 'destroyByClient'])->name('checkins.destroy-by-client');
    });

    /* REPORT */
    Route::resource('reports', ReportController::class)->only(['index']);
    // Route::get('/reports/get-clients-table/{event}', [ReportController::class, 'getClientTable'])->name('reports.get-clients-table');
    Route::prefix('reports')->group(function () {
        Route::get('/get-clients-table/{event}', [ReportController::class, 'getClientTable'])->name('reports.get-clients-table');
        Route::get('/event/{event}', [ReportController::class, 'report'])->name('reports.report');
        Route::get('/render-report/{event}', [ReportController::class, 'renderReport']);
    });

    /* ORDERS (VIDEC 2026) */
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/mark-paid', [OrderController::class, 'markPaid'])->name('mark-paid');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::post('/{order}/refund', [OrderController::class, 'refund'])->name('refund');
        Route::post('/{order}/resend-email', [OrderController::class, 'resendEmail'])->name('resend-email');
        Route::get('/export/csv', [OrderController::class, 'export'])->name('export');
    });

    /* EMAILS */
    Route::prefix('emails')->group(function () {
        Route::get('/export-report/{event}', [EmailController::class, 'exportReport'])->name('emails.export-report');
    });
});

/* MEDIA */
Route::resource('media', MediaLibraryController::class)->only(['show']);

// Route::resource('posts', PostController::class);
// Route::delete('/posts/{post}/thumbnail', [PostThumbnailController::class, 'destroy'])->name('posts_thumbnail.destroy');
// Route::resource('comments', CommentController::class)->only(['index', 'edit', 'update', 'destroy']);
