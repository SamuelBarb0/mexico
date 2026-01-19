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
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\MessageTemplateController;
use App\Http\Controllers\ContactImportController;
use App\Http\Controllers\InboxController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Registro de usuarios
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

// Stripe Webhook (must be outside auth middleware)
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handleWebhook'])->name('stripe.webhook');

// API Documentation (Swagger UI)
Route::get('/api-docs', function () {
    return file_get_contents(public_path('api-docs/index.html'));
})->name('api.docs');

Route::get('/api-docs/openapi.json', function () {
    return response()->file(public_path('api-docs/openapi.json'), [
        'Content-Type' => 'application/json'
    ]);
})->name('api.docs.spec');

// Rutas protegidas con autenticación y validación de tenant
Route::middleware(['auth', 'tenant.set', 'tenant.status'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de Usuario
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');

    // Módulo de Clientes
    Route::resource('clients', ClientController::class);

    // Módulo de Contactos
    Route::get('/contacts', [ContactController::class, 'index'])->name('contacts.index');
    Route::get('/contacts/create', [ContactController::class, 'create'])->name('contacts.create');
    Route::post('/contacts', [ContactController::class, 'store'])->name('contacts.store')->middleware('subscription.limits:contacts');
    Route::get('/contacts/{contact}', [ContactController::class, 'show'])->name('contacts.show');
    Route::get('/contacts/{contact}/edit', [ContactController::class, 'edit'])->name('contacts.edit');
    Route::put('/contacts/{contact}', [ContactController::class, 'update'])->name('contacts.update');
    Route::delete('/contacts/{contact}', [ContactController::class, 'destroy'])->name('contacts.destroy');

    Route::get('/contacts-import', [ContactImportController::class, 'index'])->name('contacts.import');
    Route::post('/contacts-import/upload', [ContactImportController::class, 'upload'])->name('contacts.import.upload')->middleware('subscription.limits:contacts');
    Route::post('/contacts-import/process', [ContactImportController::class, 'process'])->name('contacts.import.process')->middleware('subscription.limits:contacts');
    Route::post('/contacts-import/cancel', [ContactImportController::class, 'cancel'])->name('contacts.import.cancel');

    // Módulo de Inbox/Conversaciones
    Route::get('/inbox', [InboxController::class, 'index'])->name('inbox.index');
    Route::get('/inbox/{contact}', [InboxController::class, 'show'])->name('inbox.show');
    Route::post('/inbox/{contact}/send', [InboxController::class, 'sendMessage'])->name('inbox.send')->middleware('subscription.limits:messages');
    Route::get('/inbox/stats', [InboxController::class, 'stats'])->name('inbox.stats');

    // Módulo de Campañas
    Route::get('/campaigns', [CampaignController::class, 'index'])->name('campaigns.index');
    Route::get('/campaigns/create', [CampaignController::class, 'create'])->name('campaigns.create');
    Route::post('/campaigns', [CampaignController::class, 'store'])->name('campaigns.store')->middleware('subscription.limits:campaigns');
    Route::get('/campaigns/{campaign}', [CampaignController::class, 'show'])->name('campaigns.show');
    Route::get('/campaigns/{campaign}/edit', [CampaignController::class, 'edit'])->name('campaigns.edit');
    Route::put('/campaigns/{campaign}', [CampaignController::class, 'update'])->name('campaigns.update');
    Route::delete('/campaigns/{campaign}', [CampaignController::class, 'destroy'])->name('campaigns.destroy');
    Route::post('/campaigns/{campaign}/prepare', [CampaignController::class, 'prepare'])->name('campaigns.prepare');
    Route::post('/campaigns/{campaign}/execute', [CampaignController::class, 'execute'])->name('campaigns.execute')->middleware('subscription.limits:messages');
    Route::get('/campaigns/{campaign}/progress', [CampaignController::class, 'progress'])->name('campaigns.progress');
    Route::get('/campaigns/{campaign}/metrics', [CampaignController::class, 'metrics'])->name('campaigns.metrics');

    // Módulo de WABA Accounts
    Route::get('/waba-accounts', [WabaAccountController::class, 'index'])->name('waba-accounts.index');
    Route::get('/waba-accounts/create', [WabaAccountController::class, 'create'])->name('waba-accounts.create');
    Route::get('/waba-accounts/create-manual', [WabaAccountController::class, 'createManual'])->name('waba-accounts.create-manual');
    Route::post('/waba-accounts/facebook/callback', [WabaAccountController::class, 'facebookCallback'])->name('waba-accounts.facebook.callback');
    Route::post('/waba-accounts', [WabaAccountController::class, 'store'])->name('waba-accounts.store')->middleware('subscription.limits:waba_accounts');
    Route::get('/waba-accounts/{waba_account}', [WabaAccountController::class, 'show'])->name('waba-accounts.show');
    Route::get('/waba-accounts/{waba_account}/edit', [WabaAccountController::class, 'edit'])->name('waba-accounts.edit');
    Route::put('/waba-accounts/{waba_account}', [WabaAccountController::class, 'update'])->name('waba-accounts.update');
    Route::delete('/waba-accounts/{waba_account}', [WabaAccountController::class, 'destroy'])->name('waba-accounts.destroy');

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
        Route::patch('tenants/{tenant}/toggle-status', [TenantController::class, 'toggleStatus'])->name('tenants.toggle-status');

        // Gestión de Usuarios
        Route::resource('users', AdminUserController::class);
        Route::patch('users/{user}/toggle-status', [AdminUserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Gestión de Planes de Suscripción
        Route::resource('plans', SubscriptionPlanController::class);
        Route::patch('plans/{plan}/toggle', [SubscriptionPlanController::class, 'toggle'])->name('plans.toggle');
    });
});
