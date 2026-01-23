<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContactController as ApiContactController;
use App\Http\Controllers\Api\CampaignController as ApiCampaignController;
use App\Http\Controllers\Api\MessageController as ApiMessageController;
use App\Http\Controllers\Api\WebhookController as ApiWebhookController;
use App\Http\Controllers\Api\TemplateController as ApiTemplateController;
use App\Http\Controllers\Api\WabaAccountController as ApiWabaAccountController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Meta WhatsApp Webhook (public route - no authentication)
Route::match(['get', 'post'], '/webhooks/meta', [WebhookController::class, 'handle'])->name('webhooks.meta');

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
*/
Route::prefix('v1')->group(function () {

    // Public routes (no authentication required)
    Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

    // WhatsApp Webhooks
    Route::get('/webhook/whatsapp', [ApiWebhookController::class, 'verify'])->name('api.webhook.verify');
    Route::post('/webhook/whatsapp', [ApiWebhookController::class, 'handle'])->name('api.webhook.handle');

    // Protected routes (require authentication)
    Route::middleware('auth:sanctum')->group(function () {

        // Auth
        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
            Route::get('/me', [AuthController::class, 'me'])->name('api.auth.me');
            Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
        });

        // Contacts
        Route::prefix('contacts')->group(function () {
            Route::get('/', [ApiContactController::class, 'index'])->name('api.contacts.index');
            Route::post('/', [ApiContactController::class, 'store'])->name('api.contacts.store');
            Route::get('/{id}', [ApiContactController::class, 'show'])->name('api.contacts.show');
            Route::put('/{id}', [ApiContactController::class, 'update'])->name('api.contacts.update');
            Route::delete('/{id}', [ApiContactController::class, 'destroy'])->name('api.contacts.destroy');
            Route::post('/bulk-import', [ApiContactController::class, 'bulkImport'])->name('api.contacts.bulk-import');
        });

        // Campaigns
        Route::prefix('campaigns')->group(function () {
            Route::get('/', [ApiCampaignController::class, 'index'])->name('api.campaigns.index');
            Route::post('/', [ApiCampaignController::class, 'store'])->name('api.campaigns.store');
            Route::get('/{id}', [ApiCampaignController::class, 'show'])->name('api.campaigns.show');
            Route::put('/{id}', [ApiCampaignController::class, 'update'])->name('api.campaigns.update');
            Route::delete('/{id}', [ApiCampaignController::class, 'destroy'])->name('api.campaigns.destroy');
            Route::get('/{id}/stats', [ApiCampaignController::class, 'stats'])->name('api.campaigns.stats');
        });

        // Messages
        Route::prefix('messages')->group(function () {
            Route::get('/', [ApiMessageController::class, 'index'])->name('api.messages.index');
            Route::get('/conversation/{contactId}', [ApiMessageController::class, 'conversation'])->name('api.messages.conversation');
            Route::post('/send-text', [ApiMessageController::class, 'sendText'])->name('api.messages.send-text');
            Route::post('/send-template', [ApiMessageController::class, 'sendTemplate'])->name('api.messages.send-template');
            Route::get('/{id}/status', [ApiMessageController::class, 'status'])->name('api.messages.status');
        });

        // Templates
        Route::prefix('templates')->group(function () {
            Route::get('/', [ApiTemplateController::class, 'index'])->name('api.templates.index');
            Route::get('/{id}', [ApiTemplateController::class, 'show'])->name('api.templates.show');
            Route::get('/waba/{wabaAccountId}', [ApiTemplateController::class, 'byWabaAccount'])->name('api.templates.by-waba');
        });

        // WABA Accounts
        Route::prefix('waba-accounts')->group(function () {
            Route::get('/', [ApiWabaAccountController::class, 'index'])->name('api.waba-accounts.index');
            Route::get('/{id}', [ApiWabaAccountController::class, 'show'])->name('api.waba-accounts.show');
            Route::get('/{id}/stats', [ApiWabaAccountController::class, 'stats'])->name('api.waba-accounts.stats');
        });

    });
});
