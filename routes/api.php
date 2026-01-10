<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WebhookController;

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
