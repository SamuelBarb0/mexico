<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $platformAdmin = \App\Models\Role::create([
            'name' => 'Platform Super Admin',
            'slug' => 'platform-super-admin',
            'description' => 'Full access to all platform features',
            'scope' => 'platform',
            'tenant_id' => null,
        ]);

        $tenantAdmin = \App\Models\Role::create([
            'name' => 'Tenant Admin',
            'slug' => 'admin',
            'description' => 'Full access to tenant features',
            'scope' => 'tenant',
            'tenant_id' => null,
        ]);

        $manager = \App\Models\Role::create([
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Manage campaigns and contacts',
            'scope' => 'tenant',
            'tenant_id' => null,
        ]);

        $operator = \App\Models\Role::create([
            'name' => 'Operator',
            'slug' => 'operator',
            'description' => 'Basic operations only',
            'scope' => 'tenant',
            'tenant_id' => null,
        ]);

        $platformPermissions = \App\Models\Permission::whereIn('module', ['platform'])->get();
        $platformAdmin->permissions()->attach($platformPermissions);

        $tenantAdminPermissions = \App\Models\Permission::whereIn('module', ['users', 'clients', 'contacts', 'campaigns', 'waba', 'reports'])->get();
        $tenantAdmin->permissions()->attach($tenantAdminPermissions);

        $managerPermissions = \App\Models\Permission::whereIn('slug', [
            'contacts.view', 'contacts.create', 'contacts.edit',
            'campaigns.view', 'campaigns.create', 'campaigns.edit', 'campaigns.execute',
            'clients.view',
            'waba.view',
            'reports.view'
        ])->get();
        $manager->permissions()->attach($managerPermissions);

        $operatorPermissions = \App\Models\Permission::whereIn('slug', [
            'contacts.view',
            'campaigns.view',
            'clients.view'
        ])->get();
        $operator->permissions()->attach($operatorPermissions);
    }
}
