<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\WabaAccountController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\StripeWebhookController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Admin\SubscriptionPlanController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\ContactImportController;
use App\Http\Controllers\InboxController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Stripe Webhook (must be outside auth middleware)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

// Rutas protegidas con autenticación y validación de tenant
Route::middleware(['auth', 'tenant.set', 'tenant.status'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Módulo de Clientes
    Route::resource('clients', ClientController::class);

    // Módulo de Contactos
    Route::resource('contacts', ContactController::class);
    Route::get('/contacts-import', [ContactImportController::class, 'index'])->name('contacts.import');
    Route::post('/contacts-import/upload', [ContactImportController::class, 'upload'])->name('contacts.import.upload');
    Route::post('/contacts-import/process', [ContactImportController::class, 'process'])->name('contacts.import.process');
    Route::post('/contacts-import/cancel', [ContactImportController::class, 'cancel'])->name('contacts.import.cancel');

    // Módulo de Inbox/Conversaciones
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/{contact}', [InboxController::class, 'show'])->name('inbox.show');
    Route::get('/inbox/stats', [InboxController::class, 'stats'])->name('inbox.stats');

    // Módulo de Campañas
    Route::resource('campaigns', CampaignController::class);
    Route::post('/campaigns/{campaign}/prepare', [CampaignController::class, 'prepare'])->name('campaigns.prepare');
    Route::post('/campaigns/{campaign}/execute', [CampaignController::class, 'execute'])->name('campaigns.execute');
    Route::get('/campaigns/{campaign}/metrics', [CampaignController::class, 'metrics'])->name('campaigns.metrics');

    // Módulo de WABA Accounts
    Route::resource('waba-accounts', WabaAccountController::class);

    // Módulo de Plantillas de Mensajes
    Route::get('/templates/create', [MessageTemplateController::class, 'create'])->name('templates.create');
    Route::get('/templates/{template}/edit', [MessageTemplateController::class, 'edit'])->name('templates.edit');
    Route::post('/templates/{template}/submit', [MessageTemplateController::class, 'submit'])->name('templates.submit');
    Route::post('/templates/{template}/sync', [MessageTemplateController::class, 'sync'])->name('templates.sync');
    Route::post('/templates/sync-all', [MessageTemplateController::class, 'syncAll'])->name('templates.sync-all');
    Route::resource('templates', MessageTemplateController::class)->except(['create', 'edit']);

    // Subscription Management
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
        Route::get('/plans/{plan}/checkout', [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::post('/plans/{plan}/subscribe', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/change-plan', [SubscriptionController::class, 'changePlan'])->name('change-plan');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
        Route::get('/invoices/{invoice}', [SubscriptionController::class, 'invoice'])->name('invoice');
        Route::get('/invoices/{invoice}/download', [SubscriptionController::class, 'downloadInvoice'])->name('invoice.download');
    });

    // Payment Methods
    Route::prefix('payment-methods')->name('payment-methods.')->group(function () {
        Route::get('/', [PaymentMethodController::class, 'index'])->name('index');
        Route::post('/', [PaymentMethodController::class, 'store'])->name('store');
        Route::post('/{paymentMethod}/set-default', [PaymentMethodController::class, 'setDefault'])->name('set-default');
        Route::delete('/{paymentMethod}', [PaymentMethodController::class, 'destroy'])->name('destroy');
    });

    // Panel de Administración de Tenants (Solo Platform Admin)
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::resource('tenants', TenantController::class);

        // Gestión de Planes de Suscripción
        Route::resource('plans', SubscriptionPlanController::class);
        Route::patch('plans/{plan}/toggle', [SubscriptionPlanController::class, 'toggle'])->name('plans.toggle');
    });
});
