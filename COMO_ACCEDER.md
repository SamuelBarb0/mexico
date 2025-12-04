# 🎯 Cómo Acceder a los Módulos del Sistema Multitenant

## 🚀 Inicio Rápido

### 1. Iniciar el Servidor

```bash
php artisan serve
```

El servidor estará disponible en: **http://localhost:8000**

---

## 🔐 Iniciar Sesión

### Opción 1: Usuario del Tenant Demo

**Navegador:**
1. Abre http://localhost:8000/login
2. Email: `demo@admin.com`
3. Password: `password123`
4. Click en "Login"

**cURL:**
```bash
curl -X POST http://localhost:8000/login \
  -d "email=demo@admin.com&password=password123" \
  -c cookies.txt
```

### Opción 2: Platform Admin

**Navegador:**
1. Abre http://localhost:8000/login
2. Email: `platform@admin.com`
3. Password: `password123`

---

## 📋 Módulos Disponibles

Una vez autenticado, puedes acceder a estos endpoints:

### 🏠 Dashboard
```
http://localhost:8000/dashboard
```

### 👥 Clientes
```
GET    http://localhost:8000/clients          (Listar)
POST   http://localhost:8000/clients          (Crear)
GET    http://localhost:8000/clients/{id}     (Ver)
PUT    http://localhost:8000/clients/{id}     (Actualizar)
DELETE http://localhost:8000/clients/{id}     (Eliminar)
```

### 📞 Contactos
```
GET    http://localhost:8000/contacts
POST   http://localhost:8000/contacts
GET    http://localhost:8000/contacts/{id}
PUT    http://localhost:8000/contacts/{id}
DELETE http://localhost:8000/contacts/{id}
```

### 📢 Campañas
```
GET    http://localhost:8000/campaigns
POST   http://localhost:8000/campaigns
GET    http://localhost:8000/campaigns/{id}
PUT    http://localhost:8000/campaigns/{id}
DELETE http://localhost:8000/campaigns/{id}
POST   http://localhost:8000/campaigns/{id}/execute
```

### 📱 WABA Accounts
```
GET    http://localhost:8000/waba-accounts
POST   http://localhost:8000/waba-accounts
GET    http://localhost:8000/waba-accounts/{id}
PUT    http://localhost:8000/waba-accounts/{id}
DELETE http://localhost:8000/waba-accounts/{id}
```

### 🏢 Administración de Tenants (Solo Platform Admin)
```
GET    http://localhost:8000/admin/tenants
POST   http://localhost:8000/admin/tenants
GET    http://localhost:8000/admin/tenants/{id}
PUT    http://localhost:8000/admin/tenants/{id}
DELETE http://localhost:8000/admin/tenants/{id}
```

---

## 🧪 Ejemplos de Uso con cURL

### 1. Login
```bash
curl -X POST http://localhost:8000/login \
  -d "email=demo@admin.com&password=password123" \
  -c cookies.txt
```

### 2. Listar Clientes
```bash
curl -X GET http://localhost:8000/clients \
  -H "Accept: application/json" \
  -b cookies.txt
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "data": {
    "data": [],
    "current_page": 1,
    "per_page": 15,
    "total": 0
  },
  "tenant": "Demo Company"
}
```

### 3. Crear un Cliente
```bash
curl -X POST http://localhost:8000/clients \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{
    "name": "Juan Pérez",
    "company": "Acme Corp",
    "email": "juan@acme.com",
    "phone": "+52 123 456 7890",
    "country": "México",
    "status": "active"
  }'
```

**Respuesta Esperada:**
```json
{
  "success": true,
  "message": "Cliente creado exitosamente",
  "data": {
    "id": 1,
    "tenant_id": 1,
    "name": "Juan Pérez",
    "company": "Acme Corp",
    "email": "juan@acme.com",
    "phone": "+52 123 456 7890",
    "country": "México",
    "status": "active",
    "created_at": "2025-12-02T20:30:00.000000Z",
    "updated_at": "2025-12-02T20:30:00.000000Z"
  }
}
```

### 4. Ver un Cliente
```bash
curl -X GET http://localhost:8000/clients/1 \
  -H "Accept: application/json" \
  -b cookies.txt
```

### 5. Actualizar un Cliente
```bash
curl -X PUT http://localhost:8000/clients/1 \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -b cookies.txt \
  -d '{
    "name": "Juan Pérez Actualizado",
    "company": "Acme International"
  }'
```

### 6. Eliminar un Cliente
```bash
curl -X DELETE http://localhost:8000/clients/1 \
  -H "Accept: application/json" \
  -b cookies.txt
```

---

## 🧪 Ejemplos con Postman

### 1. Configurar Postman

**Variables de Entorno:**
- Variable: `base_url`
- Value: `http://localhost:8000`

### 2. Login

**Request:**
- Method: `POST`
- URL: `{{base_url}}/login`
- Body: `x-www-form-urlencoded`
  - email: `demo@admin.com`
  - password: `password123`

**Tests (Agregar esto en la pestaña Tests):**
```javascript
pm.test("Login successful", function () {
    pm.response.to.have.status(200);
});
```

### 3. Listar Clientes

**Request:**
- Method: `GET`
- URL: `{{base_url}}/clients`
- Headers:
  - Accept: `application/json`

Postman usará automáticamente las cookies de la sesión.

### 4. Crear Cliente

**Request:**
- Method: `POST`
- URL: `{{base_url}}/clients`
- Headers:
  - Content-Type: `application/json`
  - Accept: `application/json`
- Body: `raw (JSON)`

```json
{
  "name": "Juan Pérez",
  "company": "Acme Corp",
  "email": "juan@acme.com",
  "phone": "+52 123 456 7890",
  "country": "México",
  "status": "active"
}
```

---

## 🔒 Aislamiento de Datos por Tenant

### Ejemplo Práctico

**Escenario:**
- Usuario A del Tenant "Demo Company" crea un cliente
- Usuario B del Tenant "Otra Empresa" NO puede ver ese cliente

**Paso 1: Login como demo@admin.com**
```bash
curl -X POST http://localhost:8000/login \
  -d "email=demo@admin.com&password=password123" \
  -c cookies_demo.txt
```

**Paso 2: Crear un cliente**
```bash
curl -X POST http://localhost:8000/clients \
  -H "Content-Type: application/json" \
  -b cookies_demo.txt \
  -d '{"name": "Cliente Demo", "company": "Demo Corp"}'
```

**Paso 3: Listar clientes**
```bash
curl -X GET http://localhost:8000/clients \
  -H "Accept: application/json" \
  -b cookies_demo.txt
```

Verás el cliente creado.

**Paso 4: Login como otro tenant (cuando lo crees)**

Ese usuario NO verá el "Cliente Demo" porque pertenece a otro tenant.

---

## 📊 Verificar Datos en la Base de Datos

### Opción 1: MySQL CLI
```bash
mysql -u root -p mexico
```

```sql
-- Ver tenants
SELECT * FROM tenants;

-- Ver usuarios
SELECT id, name, email, user_type, tenant_id FROM users;

-- Ver clientes con su tenant
SELECT c.id, c.name, c.company, t.name as tenant_name
FROM clients c
JOIN tenants t ON c.tenant_id = t.id;

-- Ver roles
SELECT * FROM roles;

-- Ver permisos
SELECT * FROM permissions;
```

### Opción 2: Tinker
```bash
php artisan tinker
```

```php
// Ver tenants
\App\Models\Tenant::all();

// Ver usuarios de un tenant
\App\Models\Tenant::find(1)->users;

// Ver clientes de un tenant
\App\Models\Tenant::find(1)->clients;

// Ver permisos de un usuario
$user = \App\Models\User::find(1);
$user->roles;
$user->hasPermission('clients.create'); // true/false
```

---

## 🛠️ Crear un Nuevo Tenant

### Método 1: Via Tinker

```bash
php artisan tinker
```

```php
use App\Models\Tenant;
use App\Models\TenantLimit;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

// Crear tenant
$tenant = Tenant::create([
    'name' => 'Acme Corporation',
    'slug' => 'acme-corp',
    'status' => 'active',
    'trial_ends_at' => now()->addDays(30)
]);

// Crear límites
TenantLimit::create([
    'tenant_id' => $tenant->id,
    'max_users' => 5,
    'max_contacts' => 500,
    'max_campaigns' => 20,
    'max_waba_accounts' => 1,
    'max_messages_per_month' => 5000,
    'max_storage_mb' => 512,
]);

// Crear usuario admin para el tenant
$user = User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Admin Acme',
    'email' => 'admin@acme.com',
    'password' => Hash::make('password123'),
    'user_type' => 'tenant_admin',
    'is_active' => true,
]);

// Asignar rol
$role = Role::where('slug', 'admin')->first();
$user->assignRole($role);

echo "Tenant creado: {$tenant->name}\n";
echo "Usuario: {$user->email}\n";
echo "Password: password123\n";
```

### Método 2: Via API (Platform Admin)

```bash
curl -X POST http://localhost:8000/admin/tenants \
  -H "Content-Type: application/json" \
  -b cookies_platform.txt \
  -d '{
    "name": "Acme Corporation",
    "slug": "acme-corp",
    "status": "active",
    "trial_ends_at": "2026-01-15 23:59:59"
  }'
```

---

## 📁 Estructura de Archivos Importante

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── ClientController.php       ← CRUD de clientes
│   │   ├── ContactController.php      ← CRUD de contactos
│   │   ├── CampaignController.php     ← CRUD de campañas
│   │   ├── WabaAccountController.php  ← CRUD de WABA
│   │   └── Admin/
│   │       └── TenantController.php   ← Administración de tenants
│   └── Middleware/
│       ├── EnsureTenantIsSet.php      ← Verifica tenant asignado
│       └── CheckTenantStatus.php      ← Verifica estado activo
├── Models/
│   ├── Tenant.php
│   ├── User.php
│   ├── Client.php
│   ├── Contact.php
│   ├── Campaign.php
│   └── WabaAccount.php
└── Traits/
    ├── BelongsToTenant.php            ← Aislamiento automático
    └── HasRolesAndPermissions.php     ← Sistema de permisos

routes/
└── web.php                            ← Todas las rutas definidas
```

---

## 🔍 Comandos Útiles

### Ver Rutas
```bash
php artisan route:list
php artisan route:list --path=clients
php artisan route:list --path=contacts
```

### Limpiar Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
```

### Ver Logs
```bash
tail -f storage/logs/laravel.log
```

---

## ⚡ Tips y Trucos

### 1. Debugging
Agrega `dd()` en el controlador para ver qué datos se están enviando:
```php
public function store(Request $request)
{
    dd($request->all()); // Ver todos los datos
    // ...
}
```

### 2. Ver Queries SQL
En `app/Providers/AppServiceProvider.php`:
```php
use Illuminate\Support\Facades\DB;

public function boot()
{
    DB::listen(function($query) {
        logger($query->sql, $query->bindings);
    });
}
```

### 3. Probar Permisos
```php
$user = auth()->user();
if ($user->hasPermission('clients.create')) {
    // Permitir crear
}
```

---

## 📚 Documentación Adicional

- **Guía Completa**: [MULTITENANT_SETUP.md](MULTITENANT_SETUP.md)
- **Rutas API**: [API_ROUTES.md](API_ROUTES.md)
- **Inicio Rápido**: [QUICK_START.md](QUICK_START.md)

---

## 🆘 Problemas Comunes

### Error: "Unauthenticated"
**Solución:** Debes hacer login primero y enviar las cookies en cada request.

### Error: "This tenant account is not active"
**Solución:** El tenant está suspendido o inactivo. Verifica el status en la BD.

### Error: "Undefined method middleware()"
**Solución:** Ya lo arreglamos. Los middleware están en `bootstrap/app.php`.

### No veo datos de otros tenants
**Correcto:** Eso es el aislamiento funcionando correctamente.

---

¡Listo para usar! 🎉
