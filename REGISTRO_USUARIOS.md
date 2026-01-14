# Sistema de Registro de Usuarios

## Flujo de Registro Completo

### 1. Usuario Accede al Formulario de Registro

**URL:** `/register`

El usuario ve un formulario con los siguientes campos:
- **Nombre de la Empresa** (requerido)
- **Nombre Completo** (requerido)
- **Correo Electrónico** (requerido)
- **Teléfono** (opcional)
- **Contraseña** (requerido, mínimo 8 caracteres)
- **Confirmar Contraseña** (requerido)

El formulario destaca:
- 🎉 **14 días gratis - Sin tarjeta requerida**

### 2. Proceso de Registro (RegisterController)

Cuando el usuario envía el formulario, el sistema realiza las siguientes acciones **de forma atómica** (usando transacción de base de datos):

#### Paso 1: Crear el Tenant (Empresa)
```php
Tenant::create([
    'name' => 'Nombre de la Empresa',
    'slug' => 'nombre-empresa-abc123',  // Auto-generado único
    'billing_email' => 'usuario@email.com',
    'billing_name' => 'Nombre Usuario',
    'status' => 'active',
    'trial_ends_at' => now()->addDays(14),  // 14 días de prueba
]);
```

#### Paso 2: Crear el Usuario como Administrador del Tenant
```php
User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Nombre Usuario',
    'email' => 'usuario@email.com',
    'password' => Hash::make($password),
    'user_type' => 'tenant_admin',  // Admin de su empresa
    'is_active' => true,
]);
```

#### Paso 3: Asignar Plan de Suscripción
El sistema busca un plan en este orden:
1. Plan con precio $0.00
2. Plan con nombre que contenga "trial" o "free"
3. Plan activo más barato

```php
Subscription::create([
    'tenant_id' => $tenant->id,
    'subscription_plan_id' => $plan->id,
    'status' => 'trial',
    'trial_ends_at' => now()->addDays(14),
    'current_period_start' => now(),
    'current_period_end' => now()->addDays(14),
]);
```

#### Paso 4: Iniciar Sesión Automática
```php
auth()->login($user);
```

#### Paso 5: Redirección
Redirige al dashboard con mensaje de bienvenida.

### 3. Tipos de Usuarios en el Sistema

#### Platform Admin (`platform_admin`)
- **tenant_id:** `NULL`
- **Permisos:** Acceso completo al panel de administración
- **Funciones:**
  - Gestionar todos los tenants
  - Gestionar todos los usuarios
  - Gestionar planes de suscripción
  - Ver estadísticas globales

#### Tenant Admin (`tenant_admin`)
- **tenant_id:** ID de su empresa
- **Permisos:** Administración completa de su tenant
- **Funciones:**
  - Gestionar usuarios de su tenant
  - Gestionar contactos y campañas
  - Configurar cuentas WABA
  - Ver reportes de su tenant

#### Tenant User (`tenant_user`)
- **tenant_id:** ID de su empresa
- **Permisos:** Usuario regular del tenant
- **Funciones:**
  - Ver y gestionar contactos
  - Crear y enviar campañas
  - Ver inbox y enviar mensajes
  - Limitado según permisos asignados

### 4. Acceso al Panel de Administración

Para convertir un usuario en **Platform Admin**:

```sql
UPDATE users
SET user_type = 'platform_admin',
    tenant_id = NULL
WHERE email = 'admin@ejemplo.com';
```

O usar el script helper:
```bash
php list_users_admin.php
```

### 5. Configuración Inicial Requerida

#### Crear Plan de Prueba Gratuito
Antes de permitir registros, ejecutar:

```bash
php create_trial_plan.php
```

Este script crea automáticamente:
- **Nombre:** Plan de Prueba
- **Precio:** $0.00
- **Características:**
  - 100 contactos
  - 10 campañas por mes
  - 1 cuenta WABA
  - 2 usuarios
  - Soporte por email

### 6. Validaciones del Sistema

#### Validaciones de Formulario
- Email debe ser único
- Contraseña mínimo 8 caracteres
- Todos los campos requeridos deben estar presentes
- Email debe tener formato válido

#### Validaciones de Negocio
- El slug del tenant debe ser único
- No se puede crear tenant sin usuario administrador
- Si falla cualquier paso, se revierte toda la transacción

### 7. Mensajes y Retroalimentación

#### Registro Exitoso
```
¡Bienvenido! Tu cuenta ha sido creada exitosamente.
Tienes 14 días de prueba gratis.
```

#### Errores Comunes
- **"El email ya está registrado"** - Usuario intentó registrarse con email existente
- **"Error al crear la cuenta"** - Error de base de datos o validación

### 8. Flujo Post-Registro

Después del registro exitoso, el usuario:

1. **Es redirigido al dashboard**
2. **Ve mensaje de bienvenida**
3. **Puede comenzar a:**
   - Configurar su cuenta WABA
   - Importar contactos
   - Crear campañas
   - Enviar mensajes

4. **Tiene 14 días para:**
   - Probar todas las funcionalidades
   - Decidir si continuar con el servicio
   - Actualizar a un plan de pago

### 9. Archivos Relacionados

#### Controlador
- `app/Http/Controllers/RegisterController.php`

#### Vistas
- `resources/views/auth/register.blade.php` - Formulario de registro
- `resources/views/auth/login.blade.php` - Incluye enlace a registro

#### Rutas
```php
Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);
```

#### Modelos
- `app/Models/User.php`
- `app/Models/Tenant.php`
- `app/Models/Subscription.php`
- `app/Models/SubscriptionPlan.php`

### 10. Métodos Helper en User Model

```php
$user->isPlatformAdmin()  // true si user_type = 'platform_admin'
$user->isTenantAdmin()    // true si user_type = 'tenant_admin'
$user->isTenantUser()     // true si user_type = 'tenant_user'
```

Estos métodos se usan en el layout para mostrar/ocultar secciones:

```php
@if(auth()->user()->isPlatformAdmin())
    <!-- Mostrar menú de administración -->
@endif
```

## Próximos Pasos Recomendados

1. **Email de Verificación** - Agregar confirmación de email
2. **Recuperación de Contraseña** - Implementar "Olvidé mi contraseña"
3. **Onboarding** - Tour guiado para nuevos usuarios
4. **Límites del Trial** - Aplicar restricciones según el plan
5. **Notificaciones** - Avisar cuando el trial esté por vencer
6. **Conversión a Pago** - Flujo para actualizar a plan pagado
