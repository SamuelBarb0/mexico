<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Models\TenantLimit;
use App\Models\User;
use App\Models\Role;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateTestTenant extends Command
{
    protected $signature = 'tenant:create-test {name?} {email?}';
    protected $description = 'Crea un tenant de prueba sin suscripción';

    public function handle()
    {
        $name = $this->argument('name') ?? $this->ask('Nombre del tenant', 'Test Company');
        $email = $this->argument('email') ?? $this->ask('Email del admin', 'test@test.com');
        $slug = Str::slug($name);

        // Check if email already exists
        if (User::where('email', $email)->exists()) {
            $this->error("El email {$email} ya existe. Por favor usa otro email.");
            return 1;
        }

        // Check if slug already exists
        if (Tenant::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . rand(100, 999);
            $this->warn("El slug fue modificado a: {$slug}");
        }

        // Create tenant
        $tenant = Tenant::create([
            'name' => $name,
            'slug' => $slug,
            'domain' => $slug . '.localhost',
            'status' => 'active',
            'settings' => [
                'timezone' => 'America/Mexico_City',
                'language' => 'es',
            ],
            'trial_ends_at' => null, // Sin trial
        ]);

        $this->info("✓ Tenant creado: {$tenant->name} (ID: {$tenant->id})");

        // Create tenant limits (basic limits)
        TenantLimit::create([
            'tenant_id' => $tenant->id,
            'max_users' => 5,
            'max_contacts' => 500,
            'max_campaigns' => 10,
            'max_waba_accounts' => 1,
            'max_messages_per_month' => 1000,
            'max_storage_mb' => 512,
        ]);

        $this->info("✓ Límites básicos configurados");

        // Create admin user
        $adminUser = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make('password123'),
            'user_type' => 'tenant_admin',
            'is_active' => true,
        ]);

        $this->info("✓ Usuario admin creado: {$adminUser->email}");

        // Assign admin role
        $adminRole = Role::where('slug', 'admin')->first();
        if ($adminRole) {
            $adminUser->assignRole($adminRole);
            $this->info("✓ Rol de admin asignado");
        }

        $this->newLine();
        $this->info("═══════════════════════════════════════");
        $this->info("🎉 TENANT CREADO EXITOSAMENTE");
        $this->info("═══════════════════════════════════════");
        $this->table(
            ['Campo', 'Valor'],
            [
                ['Tenant', $tenant->name],
                ['Slug', $tenant->slug],
                ['Email', $adminUser->email],
                ['Password', 'password123'],
                ['Suscripción', '❌ Sin suscripción'],
                ['URL Login', url('/login')],
            ]
        );
        $this->newLine();
        $this->warn("⚠️  Este tenant NO tiene suscripción activa.");
        $this->info("💡 El usuario deberá seleccionar un plan al iniciar sesión.");

        return 0;
    }
}
