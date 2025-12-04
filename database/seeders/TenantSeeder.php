<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $demoTenant = \App\Models\Tenant::create([
            'name' => 'Demo Company',
            'slug' => 'demo-company',
            'domain' => 'demo.localhost',
            'status' => 'active',
            'settings' => [
                'timezone' => 'America/Mexico_City',
                'language' => 'es',
            ],
            'trial_ends_at' => now()->addDays(30),
        ]);

        \App\Models\TenantLimit::create([
            'tenant_id' => $demoTenant->id,
            'max_users' => 10,
            'max_contacts' => 1000,
            'max_campaigns' => 50,
            'max_waba_accounts' => 2,
            'max_messages_per_month' => 10000,
            'max_storage_mb' => 1024,
        ]);

        $adminUser = \App\Models\User::create([
            'tenant_id' => $demoTenant->id,
            'name' => 'Demo Admin',
            'email' => 'demo@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'user_type' => 'tenant_admin',
            'is_active' => true,
        ]);

        $adminRole = \App\Models\Role::where('slug', 'admin')->first();
        $adminUser->assignRole($adminRole);

        $platformAdmin = \App\Models\User::create([
            'tenant_id' => null,
            'name' => 'Platform Admin',
            'email' => 'platform@admin.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
            'user_type' => 'platform_admin',
            'is_active' => true,
        ]);

        $platformRole = \App\Models\Role::where('slug', 'platform-super-admin')->first();
        $platformAdmin->assignRole($platformRole);
    }
}
