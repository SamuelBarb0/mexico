<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantLimit;
use App\Models\Contact;
use App\Models\Campaign;
use App\Models\WabaAccount;
use App\Models\User;
use App\Models\Message;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncTenantLimits extends Command
{
    protected $signature = 'tenant:sync-limits {--tenant= : Specific tenant ID to sync}';

    protected $description = 'Synchronize tenant limit counters with actual database counts';

    public function handle(): int
    {
        $tenantId = $this->option('tenant');

        $query = Tenant::query();
        if ($tenantId) {
            $query->where('id', $tenantId);
        }

        $tenants = $query->get();

        if ($tenants->isEmpty()) {
            $this->error('No tenants found.');
            return 1;
        }

        $this->info("Syncing limits for {$tenants->count()} tenant(s)...\n");

        foreach ($tenants as $tenant) {
            $this->syncTenantLimits($tenant);
        }

        $this->newLine();
        $this->info('Sync completed successfully!');

        return 0;
    }

    protected function syncTenantLimits(Tenant $tenant): void
    {
        $this->info("Processing Tenant #{$tenant->id}: {$tenant->name}");

        // Get or create tenant limits
        $limits = $tenant->limits;
        if (!$limits) {
            $this->warn("  - No limits record found, creating one...");
            $limits = TenantLimit::create([
                'tenant_id' => $tenant->id,
                'max_users' => 1,
                'max_contacts' => 100,
                'max_campaigns' => 3,
                'max_waba_accounts' => 1,
                'max_messages_per_month' => 500,
                'max_storage_mb' => 50,
            ]);
        }

        // Get actual counts
        $actualUsers = User::where('tenant_id', $tenant->id)->count();
        $actualContacts = Contact::where('tenant_id', $tenant->id)->count();
        $actualCampaigns = Campaign::where('tenant_id', $tenant->id)->count();
        $actualWabaAccounts = WabaAccount::where('tenant_id', $tenant->id)->count();

        // Count messages for current month
        $startOfMonth = Carbon::now()->startOfMonth();
        $actualMessagesThisMonth = Message::where('tenant_id', $tenant->id)
            ->where('created_at', '>=', $startOfMonth)
            ->count();

        // Show differences
        $this->table(
            ['Resource', 'Current Counter', 'Actual Count', 'Status'],
            [
                ['Users', $limits->current_users, $actualUsers, $limits->current_users == $actualUsers ? '✓' : '✗ MISMATCH'],
                ['Contacts', $limits->current_contacts, $actualContacts, $limits->current_contacts == $actualContacts ? '✓' : '✗ MISMATCH'],
                ['Campaigns', $limits->current_campaigns, $actualCampaigns, $limits->current_campaigns == $actualCampaigns ? '✓' : '✗ MISMATCH'],
                ['WABA Accounts', $limits->current_waba_accounts, $actualWabaAccounts, $limits->current_waba_accounts == $actualWabaAccounts ? '✓' : '✗ MISMATCH'],
                ['Messages (Month)', $limits->current_messages_this_month, $actualMessagesThisMonth, $limits->current_messages_this_month == $actualMessagesThisMonth ? '✓' : '✗ MISMATCH'],
            ]
        );

        // Update counters
        $limits->update([
            'current_users' => $actualUsers,
            'current_contacts' => $actualContacts,
            'current_campaigns' => $actualCampaigns,
            'current_waba_accounts' => $actualWabaAccounts,
            'current_messages_this_month' => $actualMessagesThisMonth,
        ]);

        // Also sync max limits from current subscription plan
        $subscription = $tenant->currentSubscription();
        if ($subscription && $subscription->plan) {
            $plan = $subscription->plan;
            $this->info("  - Syncing max limits from plan: {$plan->name}");

            $limits->update([
                'max_users' => $plan->max_users,
                'max_contacts' => $plan->max_contacts,
                'max_campaigns' => $plan->max_campaigns,
                'max_waba_accounts' => $plan->max_waba_accounts,
                'max_messages_per_month' => $plan->max_messages_per_month,
                'max_storage_mb' => $plan->max_storage_mb,
            ]);
        } else {
            $this->warn("  - No active subscription found, max limits not updated");
        }

        $this->info("  - Counters updated successfully");
        $this->newLine();
    }
}
