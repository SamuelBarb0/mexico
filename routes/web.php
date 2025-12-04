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

    // Módulo de Campañas
    Route::resource('campaigns', CampaignController::class);
    Route::post('/campaigns/{campaign}/execute', [CampaignController::class, 'execute'])->name('campaigns.execute');

    // Módulo de WABA Accounts
    Route::resource('waba-accounts', WabaAccountController::class);

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
