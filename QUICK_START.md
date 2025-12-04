# 🚀 Quick Start - Sistema Multitenant

## ✅ Instalación Completada

El sistema multitenant ha sido instalado exitosamente con:

- ✅ 12 tablas creadas (tenants, users, roles, permissions, clients, contacts, campaigns, waba_accounts, etc.)
- ✅ 24 permisos configurados
- ✅ 4 roles creados
- ✅ 1 tenant demo
- ✅ 2 usuarios de prueba

## 🔐 Credenciales de Acceso

### Platform Administrator (Acceso Total)
```
Email: platform@admin.com
Password: password123
Tipo: Platform Super Admin
```
Este usuario tiene acceso completo a toda la plataforma y puede gestionar todos los tenants.

### Demo Tenant Admin
```
Email: demo@admin.com
Password: password123
Tenant: Demo Company
Tipo: Tenant Admin
```
Este usuario tiene acceso completo al tenant "Demo Company".

## 🎯 Cómo Usar

### 1. Iniciar el Servidor

```bash
php artisan serve
```

Abre tu navegador en: http://localhost:8000

### 2. Iniciar Sesión

Ve a la página de login y usa una de las credenciales de arriba.

### 3. Probar el Sistema

**Con el Platform Admin:**
- Puedes crear y gestionar múltiples tenants
- Asignar límites de uso a cada tenant
- Ver todos los usuarios del sistema

**Con el Demo Admin:**
- Solo verás datos del tenant "Demo Company"
- Puedes crear clientes, contactos, campañas
- Gestionar usuarios del tenant
- Ver reportes del tenant

## 📊 Estructura del Sistema

### Tenants Creados

| Nombre | Slug | Status | Límites |
|--------|------|--------|---------|
| Demo Company | demo-company | Active | 10 users, 1000 contacts, 50 campaigns |

### Roles Disponibles

1. **Platform Super Admin** (Scope: Platform)
   - Acceso total a la plataforma
   - Gestión de todos los tenants

2. **Tenant Admin** (Scope: Tenant)
   - Acceso completo al tenant
   - Todos los permisos de gestión

3. **Manager** (Scope: Tenant)
   - Gestión de contactos y campañas
   - Ver clientes y WABA accounts
   - Ejecutar campañas

4. **Operator** (Scope: Tenant)
   - Solo lectura
   - Ver contactos, campañas y clientes

### Permisos por Módulo

**Users (4):** view, create, edit, delete
**Clients (4):** view, create, edit, delete
**Contacts (4):** view, create, edit, delete
**Campaigns (5):** view, create, edit, delete, execute
**WABA (4):** view, create, edit, delete
**Platform (2):** manage tenants, manage roles
**Reports (1):** view

## 🔧 Comandos Útiles

### Crear un Nuevo Tenant

```bash
php artisan tinker
```

```php
use App\Models\Tenant;
use App\Models\TenantLimit;

$tenant = Tenant::create([
    'name' => 'Nueva Empresa',
    'slug' => 'nueva-empresa',
    'status' => 'active',
    'trial_ends_at' => now()->addDays(30)
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
    'tenant_id' => 1, // ID del tenant
    'name' => 'Juan Pérez',
    'email' => 'juan@empresa.com',
    'password' => Hash::make('password123'),
    'user_type' => 'tenant_user',
    'is_active' => true,
]);

$role = Role::where('slug', 'manager')->first();
$user->assignRole($role);
```

### Ver Datos de un Tenant

```php
$tenant = Tenant::find(1);
$tenant->users; // Usuarios del tenant
$tenant->limits; // Límites de uso
$tenant->clients; // Clientes
$tenant->contacts; // Contactos
$tenant->campaigns; // Campañas
```

### Verificar Permisos

```php
$user = User::find(1);
$user->hasPermission('campaigns.create'); // true/false
$user->hasRole('admin'); // true/false
$user->isPlatformAdmin(); // true/false
```

## 🎨 Próximos Pasos

### Panel de Administración de Tenants
Crea controladores y vistas para:
- Listar tenants
- Crear/editar tenants
- Gestionar límites de uso
- Ver estadísticas por tenant

### Dashboard
Implementa un dashboard que muestre:
- Métricas del tenant actual
- Gráficas de campañas
- Estadísticas de contactos
- Uso de límites

### API REST
Crea endpoints para:
- CRUD de tenants (solo platform admin)
- CRUD de usuarios
- CRUD de contactos, clientes, campañas
- Reportes y analytics

### Integraciones
- WhatsApp Business API
- Sistema de notificaciones
- Exportación de datos
- Webhooks

## 📖 Documentación Completa

Para información detallada sobre la arquitectura, modelos, y ejemplos de código, consulta:

👉 **[MULTITENANT_SETUP.md](MULTITENANT_SETUP.md)**

## 🐛 Troubleshooting

### Error de Conexión a MySQL
Asegúrate de que MySQL esté corriendo en XAMPP y que las credenciales en `.env` sean correctas.

### Error de Permisos
Verifica que el middleware esté aplicado en las rutas:
```php
Route::middleware(['auth', 'tenant.set', 'tenant.status'])->group(function () {
    // Rutas protegidas
});
```

### Aislamiento de Datos No Funciona
Asegúrate de que los modelos usen el trait `BelongsToTenant`:
```php
use App\Traits\BelongsToTenant;

class MiModelo extends Model
{
    use BelongsToTenant;
}
```

## 📞 Soporte

Si tienes problemas o preguntas, revisa:
1. Logs de Laravel: `storage/logs/laravel.log`
2. Documentación completa: `MULTITENANT_SETUP.md`
3. Migraciones: `database/migrations/`
4. Seeders: `database/seeders/`

---

**¡Listo para usar! 🎉**
