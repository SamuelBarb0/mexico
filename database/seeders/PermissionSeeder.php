<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            ['name' => 'View Users', 'slug' => 'users.view', 'description' => 'View users list', 'module' => 'users'],
            ['name' => 'Create Users', 'slug' => 'users.create', 'description' => 'Create new users', 'module' => 'users'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'description' => 'Edit existing users', 'module' => 'users'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'description' => 'Delete users', 'module' => 'users'],

            ['name' => 'View Clients', 'slug' => 'clients.view', 'description' => 'View clients list', 'module' => 'clients'],
            ['name' => 'Create Clients', 'slug' => 'clients.create', 'description' => 'Create new clients', 'module' => 'clients'],
            ['name' => 'Edit Clients', 'slug' => 'clients.edit', 'description' => 'Edit existing clients', 'module' => 'clients'],
            ['name' => 'Delete Clients', 'slug' => 'clients.delete', 'description' => 'Delete clients', 'module' => 'clients'],

            ['name' => 'View Contacts', 'slug' => 'contacts.view', 'description' => 'View contacts list', 'module' => 'contacts'],
            ['name' => 'Create Contacts', 'slug' => 'contacts.create', 'description' => 'Create new contacts', 'module' => 'contacts'],
            ['name' => 'Edit Contacts', 'slug' => 'contacts.edit', 'description' => 'Edit existing contacts', 'module' => 'contacts'],
            ['name' => 'Delete Contacts', 'slug' => 'contacts.delete', 'description' => 'Delete contacts', 'module' => 'contacts'],

            ['name' => 'View Campaigns', 'slug' => 'campaigns.view', 'description' => 'View campaigns list', 'module' => 'campaigns'],
            ['name' => 'Create Campaigns', 'slug' => 'campaigns.create', 'description' => 'Create new campaigns', 'module' => 'campaigns'],
            ['name' => 'Edit Campaigns', 'slug' => 'campaigns.edit', 'description' => 'Edit existing campaigns', 'module' => 'campaigns'],
            ['name' => 'Delete Campaigns', 'slug' => 'campaigns.delete', 'description' => 'Delete campaigns', 'module' => 'campaigns'],
            ['name' => 'Execute Campaigns', 'slug' => 'campaigns.execute', 'description' => 'Execute campaigns', 'module' => 'campaigns'],

            ['name' => 'View WABA Accounts', 'slug' => 'waba.view', 'description' => 'View WABA accounts', 'module' => 'waba'],
            ['name' => 'Create WABA Accounts', 'slug' => 'waba.create', 'description' => 'Create WABA accounts', 'module' => 'waba'],
            ['name' => 'Edit WABA Accounts', 'slug' => 'waba.edit', 'description' => 'Edit WABA accounts', 'module' => 'waba'],
            ['name' => 'Delete WABA Accounts', 'slug' => 'waba.delete', 'description' => 'Delete WABA accounts', 'module' => 'waba'],

            ['name' => 'Manage Tenants', 'slug' => 'tenants.manage', 'description' => 'Manage all tenants', 'module' => 'platform'],
            ['name' => 'Manage Roles', 'slug' => 'roles.manage', 'description' => 'Manage roles and permissions', 'module' => 'platform'],
            ['name' => 'View Reports', 'slug' => 'reports.view', 'description' => 'View reports and analytics', 'module' => 'reports'],
        ];

        foreach ($permissions as $permission) {
            \App\Models\Permission::create($permission);
        }
    }
}
