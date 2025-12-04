# Sistema Multitenant - Documentación

## Resumen de la Arquitectura

Este proyecto implementa una arquitectura multitenant completa en Laravel con:

- **Aislamiento de datos por tenant**: Cada cliente tiene sus propios datos completamente aislados
- **Sistema de roles y permisos**: Control granular de acceso
- **Límites de uso por tenant**: Control de recursos y capacidades
- **Panel de administración**: Gestión centralizada de tenants

## Estructura de Base de Datos

### Tablas Principales

#### `tenants`
Almacena información de cada tenant (cliente):
- `id`, `name`, `slug`, `domain`
- `status`: active, inactive, suspended
- `settings`: Configuración JSON
- `trial_ends_at`, `subscription_ends_at`

#### `tenant_limits`
Límites de uso por tenant:
- `max_users`, `max_contacts`, `max_campaigns`, `max_waba_accounts`
- `max_messages_per_month`, `max_storage_mb`
- Contadores actuales para cada límite

#### `users`
Usuarios del sistema:
- `tenant_id`: Asociación al tenant
- `user_type`: platform_admin, tenant_admin, tenant_user
- `is_active`: Estado del usuario

#### `roles` y `permissions`
Sistema de roles y permisos:
- `scope`: platform o tenant
- Relación many-to-many entre roles y permisos
- Relación many-to-many entre usuarios y roles

### Entidades de Negocio

Todas con aislamiento automático por tenant (`tenant_id`):

#### `clients`
Clientes del tenant con información de contacto

#### `contacts`
Contactos de WhatsApp:
- `phone`, `whatsapp_id`
- `tags`, `custom_fields`
- `status`: active, blocked, unsubscribed

#### `campaigns`
Campañas de mensajería:
- `type`: broadcast, drip, triggered
- `status`: draft, scheduled, active, paused, completed
- Métricas: sent_count, delivered_count, read_count

#### `waba_accounts`
Cuentas de WhatsApp Business API:
- `phone_number`, `phone_number_id`
- `quality_rating`: green, yellow, red
- `access_token` (encriptado)

## Roles y Permisos

### Roles de Plataforma

**Platform Super Admin** (`platform-super-admin`)
- Acceso completo a toda la plataforma
- Gestión de todos los tenants
- No requiere tenant_id

### Roles de Tenant

**Tenant Admin** (`admin`)
- Acceso completo a funcionalidades del tenant
- Permisos: users, clients, contacts, campaigns, waba, reports

**Manager** (`manager`)
- Gestión de campañas y contactos
- Permisos: contacts (view/create/edit), campaigns (view/create/edit/execute), clients (view only)

**Operator** (`operator`)
- Solo lectura
- Permisos: contacts (view), campaigns (view), clients (view)

### Permisos Disponibles

**Usuarios**: `users.view`, `users.create`, `users.edit`, `users.delete`

**Clientes**: `clients.view`, `clients.create`, `clients.edit`, `clients.delete`

**Contactos**: `contacts.view`, `contacts.create`, `contacts.edit`, `contacts.delete`

**Campañas**: `campaigns.view`, `campaigns.create`, `campaigns.edit`, `campaigns.delete`, `campaigns.execute`

**WABA**: `waba.view`, `waba.create`, `waba.edit`, `waba.delete`

**Plataforma**: `tenants.manage`, `roles.manage`

**Reportes**: `reports.view`

## Middleware de Seguridad

### `EnsureTenantIsSet`
- Verifica que el usuario tenga un tenant asignado
- Platform admins están exentos

### `CheckTenantStatus`
- Verifica que el tenant esté activo
- Verifica que el usuario esté activo
- Previene acceso si el tenant está suspendido

## Traits Implementados

### `BelongsToTenant`
Trait para modelos que pertenecen a un tenant:
- Asigna automáticamente `tenant_id` al crear
- Scope global para filtrar solo datos del tenant actual
- Métodos: `forTenant()`, `withoutTenantScope()`

Usar en modelos: Client, Contact, Campaign, WabaAccount

### `HasRolesAndPermissions`
Trait para el modelo User:
- `hasRole($role)`: Verifica si tiene un rol
- `hasPermission($permission)`: Verifica si tiene un permiso
- `assignRole($role)`: Asigna un rol
- `isPlatformAdmin()`, `isTenantAdmin()`, `isTenantUser()`

## Modelos Principales

### Tenant
```php
Tenant::create([
    'name' => 'Mi Empresa',
    'slug' => 'mi-empresa',
    'status' => 'active',
]);
```

Métodos:
- `isActive()`, `isSuspended()`
- `isOnTrial()`, `hasActiveSubscription()`

### User
```php
$user->assignRole('admin');
$user->hasPermission('campaigns.create'); // bool
$user->isPlatformAdmin(); // bool
```

### TenantLimit
```php
$limits = $tenant->limits;
$limits->canAddUser(); // bool
$limits->canSendMessage(); // bool
```

## Credenciales de Acceso

Después de ejecutar los seeders, tendrás estos usuarios:

### Platform Admin
- **Email**: platform@admin.com
- **Password**: password123
- **Tipo**: platform_admin
- Acceso a toda la plataforma

### Demo Tenant Admin
- **Email**: demo@admin.com
- **Password**: password123
- **Tenant**: Demo Company
- **Tipo**: tenant_admin
- Acceso completo al tenant demo

## Instrucciones de Instalación

### 1. Iniciar MySQL en XAMPP

Abre el panel de control de XAMPP e inicia MySQL.

### 2. Ejecutar Migraciones

```bash
php artisan migrate:fresh
```

### 3. Ejecutar Seeders

```bash
php artisan db:seed
```

Esto creará:
- 24 permisos
- 4 roles (platform-super-admin, admin, manager, operator)
- 1 tenant demo
- 2 usuarios (platform admin y tenant admin)

### 4. Iniciar Servidor

```bash
php artisan serve
```

## Uso del Sistema

### Crear un Nuevo Tenant

```php
use App\Models\Tenant;
use App\Models\TenantLimit;

$tenant = Tenant::create([
    'name' => 'Acme Corp',
    'slug' => 'acme-corp',
    'domain' => 'acme.tudominio.com',
    'status' => 'active',
    'trial_ends_at' => now()->addDays(30),
]);

TenantLimit::create([
    'tenant_id' => $tenant->id,
    'max_users' => 5,
    'max_contacts' => 500,
    'max_campaigns' => 20,
    'max_waba_accounts' => 1,
    'max_messages_per_month' => 5000,
    'max_storage_mb' => 512,
]);
```

### Crear Usuario para un Tenant

```php
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

$user = User::create([
    'tenant_id' => $tenant->id,
    'name' => 'John Doe',
    'email' => 'john@acme.com',
    'password' => Hash::make('password'),
    'user_type' => 'tenant_user',
    'is_active' => true,
]);

$role = Role::where('slug', 'manager')->first();
$user->assignRole($role);
```

### Verificar Permisos

```php
if ($user->hasPermission('campaigns.create')) {
    // Crear campaña
}

if ($user->isTenantAdmin()) {
    // Acceso de administrador
}
```

### Verificar Límites de Tenant

```php
$limits = auth()->user()->tenant->limits;

if ($limits->canAddContact()) {
    // Crear contacto
} else {
    // Mostrar mensaje de límite alcanzado
}
```

## Características de Seguridad

1. **Aislamiento Automático**: El trait `BelongsToTenant` filtra automáticamente datos por tenant
2. **Validación de Estado**: Middleware verifica que tenant y usuario estén activos
3. **Soft Deletes**: Todos los modelos principales usan soft deletes
4. **Tokens Encriptados**: Access tokens de WABA están ocultos
5. **Scopes de Consulta**: Previene acceso cross-tenant

## Próximos Pasos

1. Crear vistas para el panel de administración de tenants
2. Implementar API REST para gestión de tenants
3. Agregar sistema de notificaciones por email
4. Implementar facturación y pagos
5. Dashboard con métricas por tenant
6. Sistema de logs y auditoría

## Archivos Importantes

### Migraciones
- `2025_12_02_200752_create_tenants_table.php`
- `2025_12_02_200754_create_roles_table.php`
- `2025_12_02_200755_create_permissions_table.php`
- `2025_12_02_200757_add_tenant_fields_to_users_table.php`
- `2025_12_02_200759_create_tenant_limits_table.php`
- `2025_12_02_200945_create_clients_table.php`
- `2025_12_02_200947_create_contacts_table.php`
- `2025_12_02_200949_create_campaigns_table.php`
- `2025_12_02_200950_create_waba_accounts_table.php`

### Seeders
- `PermissionSeeder.php` - Crea todos los permisos
- `RoleSeeder.php` - Crea roles y asigna permisos
- `TenantSeeder.php` - Crea tenant demo y usuarios

### Traits
- `app/Traits/BelongsToTenant.php` - Aislamiento de datos
- `app/Traits/HasRolesAndPermissions.php` - Sistema de permisos

### Middleware
- `app/Http/Middleware/EnsureTenantIsSet.php`
- `app/Http/Middleware/CheckTenantStatus.php`

### Modelos
- `app/Models/Tenant.php`
- `app/Models/TenantLimit.php`
- `app/Models/User.php`
- `app/Models/Role.php`
- `app/Models/Permission.php`
- `app/Models/Client.php`
- `app/Models/Contact.php`
- `app/Models/Campaign.php`
- `app/Models/WabaAccount.php`

---

**Desarrollado con Laravel 11 + MySQL**
