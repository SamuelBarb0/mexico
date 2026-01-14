<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\WabaAccount;
use App\Models\User;
use App\Models\Message;
use App\Observers\ContactObserver;
use App\Observers\CampaignObserver;
use App\Observers\WabaAccountObserver;
use App\Observers\UserObserver;
use App\Observers\MessageObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register model observers for automatic limit tracking
        Contact::observe(ContactObserver::class);
        Campaign::observe(CampaignObserver::class);
        WabaAccount::observe(WabaAccountObserver::class);
        User::observe(UserObserver::class);
        Message::observe(MessageObserver::class);
    }
}
