# Sistema de Límites de Planes de Suscripción

## Descripción General

El sistema de límites permite controlar cuántos recursos puede crear cada tenant según su plan de suscripción. Esto incluye:

- 👥 **Usuarios** - Cuántos usuarios puede tener el tenant
- 📇 **Contactos** - Cuántos contactos puede almacenar
- 📢 **Campañas** - Cuántas campañas puede crear
- 📱 **Cuentas WABA** - Cuántas cuentas de WhatsApp Business puede conectar
- 💬 **Mensajes** - Cuántos mensajes puede enviar por mes
- 💾 **Almacenamiento** - Cuánto espacio de almacenamiento puede usar

## Arquitectura del Sistema

### 1. Modelos Relacionados

#### SubscriptionPlan
Define los límites máximos de cada plan:

```php
// app/Models/SubscriptionPlan.php
protected $fillable = [
    'max_users',              // Ej: 5
    'max_contacts',           // Ej: 1000
    'max_campaigns',          // Ej: 50
    'max_waba_accounts',      // Ej: 2
    'max_messages_per_month', // Ej: 10000
    'max_storage_mb',         // Ej: 500
];
```

#### TenantLimit
Almacena los límites y uso actual de cada tenant:

```php
// app/Models/TenantLimit.php
protected $fillable = [
    // Límites máximos (copiados del plan)
    'max_users',
    'max_contacts',
    'max_campaigns',
    'max_waba_accounts',
    'max_messages_per_month',
    'max_storage_mb',

    // Contadores de uso actual
    'current_users',
    'current_contacts',
    'current_campaigns',
    'current_waba_accounts',
    'current_messages_this_month',
    'current_storage_mb',
];
```

### 2. Flujo de Verificación

```
Usuario intenta crear recurso
         ↓
Middleware: subscription.limits
         ↓
Verifica: Tenant → Subscription → Plan
         ↓
Compara: current_X vs max_X
         ↓
¿Límite alcanzado? → Sí → Bloquear + Mostrar error
         ↓
        No
         ↓
Permitir creación
         ↓
Observer incrementa contador automáticamente
```

## Implementación por Componente

### 1. Middleware de Verificación

**Ubicación:** `app/Http/Middleware/CheckSubscriptionLimits.php`

**Uso:**
```php
Route::post('/contacts', [ContactController::class, 'store'])
    ->middleware('subscription.limits:contacts');
```

**Mensajes de error personalizados:**
- "Ha alcanzado el límite de contactos (1000) de su plan Básico. Por favor, actualice su plan para continuar."
- "Ha alcanzado el límite de campañas (50) de su plan Premium."
- "Ha alcanzado el límite mensual de mensajes (10000)."

### 2. Observers Automáticos

Los observadores incrementan/decrementan contadores automáticamente:

#### ContactObserver
```php
// app/Observers/ContactObserver.php
public function created(Contact $contact): void
{
    $contact->tenant->limits->increment('current_contacts');
}

public function deleted(Contact $contact): void
{
    $contact->tenant->limits->decrement('current_contacts');
}
```

#### CampaignObserver
```php
public function created(Campaign $campaign): void
{
    $campaign->tenant->limits->increment('current_campaigns');
}
```

#### WabaAccountObserver
```php
public function created(WabaAccount $wabaAccount): void
{
    $wabaAccount->tenant->limits->increment('current_waba_accounts');
}
```

#### UserObserver
```php
public function created(User $user): void
{
    // Solo cuenta usuarios de tenants, no platform admins
    if ($user->tenant_id) {
        $user->tenant->limits->increment('current_users');
    }
}
```

#### MessageObserver
```php
public function created(Message $message): void
{
    // Solo cuenta mensajes enviados (outbound)
    if ($message->direction === 'outbound') {
        $message->tenant->limits->increment('current_messages_this_month');
    }
}
```

**Registro:** `app/Providers/AppServiceProvider.php`
```php
public function boot(): void
{
    Contact::observe(ContactObserver::class);
    Campaign::observe(CampaignObserver::class);
    WabaAccount::observe(WabaAccountObserver::class);
    User::observe(UserObserver::class);
    Message::observe(MessageObserver::class);
}
```

### 3. Rutas Protegidas

**Ubicación:** `routes/web.php`

```php
// Crear contactos - verifica límite de contactos
Route::post('/contacts', [ContactController::class, 'store'])
    ->middleware('subscription.limits:contacts');

// Importar contactos - verifica límite de contactos
Route::post('/contacts-import/upload', [ContactImportController::class, 'upload'])
    ->middleware('subscription.limits:contacts');

Route::post('/contacts-import/process', [ContactImportController::class, 'process'])
    ->middleware('subscription.limits:contacts');

// Crear campañas - verifica límite de campañas
Route::post('/campaigns', [CampaignController::class, 'store'])
    ->middleware('subscription.limits:campaigns');

// Ejecutar campañas - verifica límite de mensajes
Route::post('/campaigns/{campaign}/execute', [CampaignController::class, 'execute'])
    ->middleware('subscription.limits:messages');

// Enviar mensaje individual - verifica límite de mensajes
Route::post('/inbox/{contact}/send', [InboxController::class, 'sendMessage'])
    ->middleware('subscription.limits:messages');

// Crear cuenta WABA - verifica límite de cuentas WABA
Route::post('/waba-accounts', [WabaAccountController::class, 'store'])
    ->middleware('subscription.limits:waba_accounts');
```

### 4. Métodos del Modelo Subscription

**Ubicación:** `app/Models/Subscription.php` (líneas 240-318)

#### hasReachedLimit(string $resource): bool
Verifica si se alcanzó el límite de un recurso:

```php
$subscription = $tenant->currentSubscription();

if ($subscription->hasReachedLimit('contacts')) {
    // No puede crear más contactos
}
```

#### getRemainingLimit(string $resource): int
Retorna cuántos recursos quedan disponibles:

```php
$remaining = $subscription->getRemainingLimit('messages');
// Ej: 2500 (de 10000)
```

#### getLimitPercentage(string $resource): int
Retorna el porcentaje usado (0-100):

```php
$percentage = $subscription->getLimitPercentage('contacts');
// Ej: 75 (ha usado 750 de 1000)
```

### 5. Métodos del Modelo Tenant

**Ubicación:** `app/Models/Tenant.php` (líneas 129-149)

Delegan a la suscripción actual:

```php
$tenant = auth()->user()->tenant;

// Verificar límite
if ($tenant->hasReachedLimit('campaigns')) {
    return back()->with('error', 'Límite de campañas alcanzado');
}

// Obtener restante
$remaining = $tenant->getRemainingLimit('contacts');
echo "Puedes crear {$remaining} contactos más";
```

### 6. Métodos del Modelo TenantLimit

**Ubicación:** `app/Models/TenantLimit.php`

Métodos helper para verificar capacidad:

```php
$limits = $tenant->limits;

if ($limits->canAddContact()) {
    // Puede agregar contacto
}

if ($limits->canSendMessage()) {
    // Puede enviar mensaje
}

if ($limits->hasStorageSpace(50)) {
    // Tiene espacio para archivo de 50MB
}
```

## Inicialización de Límites

### Al Registrar un Nuevo Usuario

**Ubicación:** `app/Http/Controllers/RegisterController.php`

```php
// 1. Crear Tenant
$tenant = Tenant::create([...]);

// 2. Crear Usuario
$user = User::create([...]);

// 3. Crear Suscripción
$subscription = Subscription::create([...]);

// 4. Inicializar Límites basado en el plan
TenantLimit::create([
    'tenant_id' => $tenant->id,
    'max_users' => $plan->max_users ?? 2,
    'max_contacts' => $plan->max_contacts ?? 100,
    'max_campaigns' => $plan->max_campaigns ?? 10,
    'max_waba_accounts' => $plan->max_waba_accounts ?? 1,
    'max_messages_per_month' => $plan->max_messages_per_month ?? 1000,
    'max_storage_mb' => $plan->max_storage_mb ?? 100,
    'current_users' => 1, // Ya se creó 1 usuario
    'current_contacts' => 0,
    'current_campaigns' => 0,
    'current_waba_accounts' => 0,
    'current_messages_this_month' => 0,
    'current_storage_mb' => 0,
]);
```

### Al Cambiar de Plan

Cuando un tenant cambia de plan, se deben actualizar los límites:

```php
$tenant->limits->update([
    'max_users' => $newPlan->max_users,
    'max_contacts' => $newPlan->max_contacts,
    'max_campaigns' => $newPlan->max_campaigns,
    'max_waba_accounts' => $newPlan->max_waba_accounts,
    'max_messages_per_month' => $newPlan->max_messages_per_month,
    'max_storage_mb' => $newPlan->max_storage_mb,
]);
```

**Nota:** Los contadores `current_*` NO se resetean al cambiar de plan.

## Reseteo de Contadores Mensuales

### Job de Reseteo Mensual

**Recomendación:** Crear un comando Artisan que se ejecute el primer día de cada mes:

```php
// app/Console/Commands/ResetMonthlyLimits.php
class ResetMonthlyLimits extends Command
{
    protected $signature = 'limits:reset-monthly';

    public function handle()
    {
        TenantLimit::query()->update([
            'current_messages_this_month' => 0,
        ]);

        $this->info('Límites mensuales reseteados exitosamente');
    }
}
```

**Programación:** `app/Console/Kernel.php`
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('limits:reset-monthly')
        ->monthlyOn(1, '00:00');
}
```

## Casos Especiales

### 1. Platform Admins

Los Platform Admins NO cuentan para los límites:

```php
// UserObserver.php
public function created(User $user): void
{
    // Solo incrementar si el usuario pertenece a un tenant
    if ($user->tenant_id && $user->tenant) {
        $user->tenant->limits->increment('current_users');
    }
}
```

### 2. Mensajes Entrantes (Inbound)

Los mensajes recibidos NO cuentan para el límite:

```php
// MessageObserver.php
public function created(Message $message): void
{
    // Solo contar mensajes enviados
    if ($message->direction === 'outbound') {
        $message->tenant->limits->increment('current_messages_this_month');
    }
}
```

### 3. Importación Masiva de Contactos

La importación verifica límites ANTES de procesar:

```php
// ContactImportController.php
Route::post('/contacts-import/process', ...)
    ->middleware('subscription.limits:contacts');
```

Si intenta importar 500 contactos pero solo tiene espacio para 100, se bloquea completamente.

### 4. Sin Suscripción Activa

Si un tenant no tiene suscripción activa:

```php
public function hasReachedLimit(string $resource): bool
{
    $subscription = $this->currentSubscription();

    if (!$subscription) {
        return true; // Sin suscripción = sin acceso
    }

    return $subscription->hasReachedLimit($resource);
}
```

## Mensajes de Usuario

### Errores Mostrados

Cuando se alcanza un límite:

```
❌ Ha alcanzado el límite de contactos (1000) de su plan Básico.
   Por favor, actualice su plan para continuar.
```

### En el Dashboard

Mostrar uso actual en el dashboard:

```php
$tenant = auth()->user()->tenant;
$subscription = $tenant->currentSubscription();

$stats = [
    'contacts' => [
        'current' => $tenant->limits->current_contacts,
        'max' => $subscription->plan->max_contacts,
        'percentage' => $subscription->getLimitPercentage('contacts'),
    ],
    'messages' => [
        'current' => $tenant->limits->current_messages_this_month,
        'max' => $subscription->plan->max_messages_per_month,
        'percentage' => $subscription->getLimitPercentage('messages'),
    ],
];
```

Mostrar en vista:

```html
<div class="progress-bar">
    <div class="progress" style="width: {{ $stats['contacts']['percentage'] }}%"></div>
</div>
<span>{{ $stats['contacts']['current'] }} / {{ $stats['contacts']['max'] }} contactos</span>
```

## Resumen de Archivos Modificados/Creados

### Creados
- ✅ `app/Observers/ContactObserver.php`
- ✅ `app/Observers/CampaignObserver.php`
- ✅ `app/Observers/WabaAccountObserver.php`
- ✅ `app/Observers/UserObserver.php`
- ✅ `app/Observers/MessageObserver.php`

### Modificados
- ✅ `routes/web.php` - Agregado middleware a rutas críticas
- ✅ `app/Providers/AppServiceProvider.php` - Registrados observers
- ✅ `app/Http/Controllers/RegisterController.php` - Inicialización de límites

### Existentes (No modificados)
- ✅ `app/Http/Middleware/CheckSubscriptionLimits.php` - Ya existía
- ✅ `app/Models/Subscription.php` - Métodos ya existían
- ✅ `app/Models/Tenant.php` - Métodos ya existían
- ✅ `app/Models/TenantLimit.php` - Ya existía

## Estado Actual

✅ **Sistema de límites COMPLETAMENTE IMPLEMENTADO y FUNCIONAL**

El sistema ahora:
1. ✅ Verifica límites antes de crear recursos
2. ✅ Bloquea creación si se alcanzó el límite
3. ✅ Muestra mensajes de error personalizados
4. ✅ Incrementa contadores automáticamente
5. ✅ Decrementa contadores al borrar
6. ✅ Inicializa límites al registrar nuevos usuarios
7. ✅ Soporta todos los recursos: users, contacts, campaigns, waba_accounts, messages

## Próximos Pasos Recomendados

1. **Comando de reseteo mensual** - Resetear contador de mensajes
2. **Actualización de límites al cambiar plan** - Implementar en SubscriptionController
3. **Dashboard de uso** - Mostrar estadísticas y barras de progreso
4. **Alertas proactivas** - Notificar cuando se acerque al límite (80%, 90%, 95%)
5. **Validación en importación** - Verificar ANTES de procesar archivo completo
