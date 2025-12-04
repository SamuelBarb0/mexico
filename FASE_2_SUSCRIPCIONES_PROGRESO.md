# Fase 2 - Sistema de Suscripciones (SaaS) con Stripe

## ✅ Progreso Completado

### 1. Instalación de Dependencias
- ✅ Stripe PHP SDK v19.0.0 instalado correctamente

### 2. Migraciones Creadas y Ejecutadas
- ✅ `subscription_plans` - Planes de suscripción con límites y características
- ✅ `subscriptions` - Suscripciones activas por tenant
- ✅ `payment_methods` - Métodos de pago (tarjetas) vinculados a Stripe
- ✅ `invoices` - Facturas generadas por Stripe
- ✅ Campos de suscripción agregados a la tabla `tenants`

### 3. Modelos Creados
- ✅ **SubscriptionPlan** - Con helpers para cálculos de precios y ahorros
- ⏳ **Subscription** - Pendiente implementación completa
- ⏳ **PaymentMethod** - Pendiente implementación completa
- ⏳ **Invoice** - Pendiente implementación completa

### 4. Configuración de Base de Datos
Todas las tablas incluyen:
- Integración completa con Stripe (IDs de productos, precios, clientes)
- Soft deletes para mantener historial
- Índices optimizados para consultas frecuentes
- Campos JSON para flexibilidad (features, metadata, etc.)

## 📋 Estructura de Planes

Cada plan incluye:

**Precios:**
- `price_monthly` - Precio mensual
- `price_yearly` - Precio anual (con descuento)
- Soporte para múltiples monedas (USD, MXN, EUR)

**Períodos de Prueba:**
- `has_trial` - Si incluye período de prueba
- `trial_days` - Días de prueba (0 = sin prueba, -1 = prueba indefinida)

**Límites del Tenant:**
- `max_users` - Usuarios máximos
- `max_contacts` - Contactos máximos
- `max_campaigns` - Campañas activas simultáneas
- `max_waba_accounts` - Cuentas WABA máximas
- `max_messages_per_month` - Mensajes mensuales
- `max_storage_mb` - Almacenamiento en MB

**Características:**
- `features` (JSON) - Lista de características incluidas
- `restrictions` (JSON) - Restricciones personalizadas

## 🔄 Estados de Suscripción

Las suscripciones pueden estar en los siguientes estados:
1. **trial** - En período de prueba
2. **active** - Activa y pagando
3. **canceled** - Cancelada (continúa hasta fin de período)
4. **past_due** - Pago vencido
5. **unpaid** - Sin pagar
6. **incomplete** - Pago incompleto
7. **incomplete_expired** - Pago expirado
8. **paused** - Pausada temporalmente

## 📝 Próximos Pasos Críticos

### 1. Completar Modelos Restantes (PRIORITARIO)
- [ ] **Subscription Model** - Con métodos para:
  - Verificar si está activa/en trial
  - Calcular días restantes
  - Verificar límites de uso
  - Cancelar/reanudar
- [ ] **PaymentMethod Model** - Gestión de tarjetas
- [ ] **Invoice Model** - Gestión de facturas

### 2. Configuración de Stripe
- [ ] Agregar credenciales a `.env`:
  ```env
  STRIPE_KEY=pk_test_...
  STRIPE_SECRET=sk_test_...
  STRIPE_WEBHOOK_SECRET=whsec_...
  ```
- [ ] Crear servicio `StripeService` para:
  - Crear clientes en Stripe
  - Crear/actualizar suscripciones
  - Manejar métodos de pago
  - Procesar webhooks

### 3. Seeder de Planes
- [ ] Crear `SubscriptionPlanSeeder` con planes predefinidos:
  - **Free** - $0/mes (prueba indefinida)
  - **Starter** - $29/mes, $290/año (15 días de prueba)
  - **Professional** - $79/mes, $790/año (15 días de prueba)
  - **Enterprise** - $199/mes, $1990/año (30 días de prueba)

### 4. Actualizar Modelo Tenant
- [ ] Agregar relación con suscripciones
- [ ] Método `currentSubscription()`
- [ ] Método `hasActiveSubscription()`
- [ ] Método `isOnTrial()`
- [ ] Método `daysRemainingOnTrial()`
- [ ] Método `canUseFeature($feature)`

### 5. Middleware de Límites
- [ ] `CheckSubscriptionLimits` - Verificar antes de crear recursos:
  - Verificar límite de usuarios antes de crear
  - Verificar límite de contactos
  - Verificar límite de campañas
  - Verificar límite de mensajes del mes
  - Retornar error 403 con mensaje específico

### 6. Controllers y Vistas

#### SubscriptionController
- [ ] `index()` - Ver suscripción actual
- [ ] `plans()` - Página de planes disponibles
- [ ] `checkout(plan, cycle)` - Proceso de pago con Stripe
- [ ] `update(plan, cycle)` - Cambiar plan
- [ ] `cancel()` - Cancelar suscripción
- [ ] `resume()` - Reanudar suscripción cancelada

#### PaymentMethodController
- [ ] `index()` - Ver métodos de pago
- [ ] `store()` - Agregar tarjeta
- [ ] `setDefault(id)` - Establecer como predeterminada
- [ ] `destroy(id)` - Eliminar tarjeta

#### InvoiceController
- [ ] `index()` - Historial de facturas
- [ ] `show(id)` - Ver factura
- [ ] `download(id)` - Descargar PDF

### 7. Vistas Necesarias

**Públicas:**
- [ ] `subscriptions/plans.blade.php` - Página de precios (público)
- [ ] `subscriptions/checkout.blade.php` - Formulario de pago con Stripe Elements

**Autenticadas:**
- [ ] `subscriptions/index.blade.php` - Suscripción actual, límites de uso
- [ ] `subscriptions/payment-methods.blade.php` - Tarjetas guardadas
- [ ] `subscriptions/invoices.blade.php` - Historial de facturas

### 8. Panel de Admin (Platform Admin)
- [ ] Vista `admin/subscriptions.blade.php`:
  - Lista de todos los tenants
  - Plan actual de cada tenant
  - Estado de pago (activo, vencido, cancelado)
  - Uso actual vs límites
  - Alertas (límites cerca de alcanzarse, pagos vencidos)
  - Acciones: Cambiar plan, extender trial, suspender

### 9. Webhooks de Stripe
- [ ] Ruta `/stripe/webhook` (excluida de CSRF)
- [ ] `StripeWebhookController`:
  - `customer.subscription.created`
  - `customer.subscription.updated`
  - `customer.subscription.deleted`
  - `invoice.payment_succeeded`
  - `invoice.payment_failed`
  - `payment_method.attached`
  - `payment_method.detached`

### 10. Registro de Tenants con Selección de Plan
- [ ] Modificar proceso de registro:
  1. Elegir plan (Free o con tarjeta)
  2. Si no es Free: Capturar método de pago con Stripe
  3. Crear tenant
  4. Crear suscripción en Stripe
  5. Guardar suscripción en BD
  6. Redirigir a dashboard

### 11. Tracking de Uso
- [ ] Observer o Listener para incrementar contadores cuando se crea:
  - Usuario → `tenant_limits->current_users++`
  - Contacto → `tenant_limits->current_contacts++`
  - Campaña → `tenant_limits->current_campaigns++`
  - Mensaje enviado → `tenant_limits->current_messages_this_month++`
- [ ] Command para resetear `current_messages_this_month` cada mes

### 12. Notificaciones
- [ ] Email cuando el trial está por expirar (7, 3, 1 día antes)
- [ ] Email cuando el pago falla
- [ ] Email cuando se alcanza el 80% de un límite
- [ ] Email cuando se alcanza el 100% de un límite

## 🎯 Flujo Completo del Usuario

1. **Nuevo Usuario:**
   - Visita `/pricing`
   - Elige un plan
   - Ingresa datos de tarjeta (Stripe Checkout o Elements)
   - Se crea tenant + suscripción
   - Comienza trial si aplica

2. **Durante Uso:**
   - Sistema verifica límites antes de crear recursos
   - Dashboard muestra uso actual vs límites
   - Puede cambiar plan desde `/subscriptions`
   - Puede actualizar tarjeta

3. **Renovación:**
   - Stripe cobra automáticamente
   - Webhook actualiza estado
   - Si falla: Estado → `past_due`, enviar email

4. **Cancelación:**
   - Usuario cancela desde dashboard
   - Suscripción continúa hasta fin de período
   - Al finalizar: Estado → `canceled`, tenant → `inactive`

## 🔐 Seguridad

- Todas las operaciones de Stripe se hacen server-side
- Las claves de API nunca se exponen al frontend
- Webhooks verificados con firma de Stripe
- Idempotency keys para evitar cargos duplicados
- Logs de todas las transacciones importantes

## 📊 Métricas para Admin

Dashboard de admin debe mostrar:
- **MRR** (Monthly Recurring Revenue)
- **Churn Rate**
- **Tenants activos** por plan
- **Tenants en trial**
- **Conversión de trial a pago**
- **Pagos fallidos** del mes
- **Límites más alcanzados** (para ajustar planes)

## 💡 Consideraciones Importantes

1. **Pruebas con Stripe Test Mode:**
   - Usar tarjetas de prueba de Stripe
   - Verificar webhooks en modo test

2. **Manejo de Errores:**
   - Stripe puede lanzar excepciones
   - Siempre usar try-catch en operaciones de pago
   - Logging detallado de errores

3. **Sincronización:**
   - Stripe es la fuente de verdad
   - Webhooks mantienen BD sincronizada
   - Reconciliación periódica recomendada

4. **Localización:**
   - Precios en USD por defecto
   - Opción de mostrar en MXN
   - Textos en español

## 📝 Archivos Clave a Crear

```
app/
├── Services/
│   └── StripeService.php          # Servicio principal de Stripe
├── Http/
│   ├── Controllers/
│   │   ├── SubscriptionController.php
│   │   ├── PaymentMethodController.php
│   │   ├── InvoiceController.php
│   │   └── StripeWebhookController.php
│   └── Middleware/
│       └── CheckSubscriptionLimits.php
├── Observers/
│   └── UsageTrackingObserver.php  # Para contadores automáticos
└── Console/
    └── Commands/
        └── ResetMonthlyLimits.php

resources/views/
├── subscriptions/
│   ├── plans.blade.php            # Página pública de precios
│   ├── index.blade.php            # Suscripción actual
│   ├── checkout.blade.php         # Proceso de pago
│   ├── payment-methods.blade.php  # Tarjetas
│   └── invoices.blade.php         # Facturas
└── admin/
    └── subscriptions/
        └── index.blade.php        # Panel de admin

database/seeders/
└── SubscriptionPlanSeeder.php
```

## 🚀 Orden de Implementación Sugerido

1. ✅ Migraciones y modelos básicos
2. ⏳ Completar modelos con relaciones y métodos
3. ⏳ Configurar Stripe y crear StripeService
4. ⏳ Crear seeder de planes y ejecutar
5. ⏳ Implementar SubscriptionController + vistas
6. ⏳ Implementar webhooks de Stripe
7. ⏳ Middleware de límites
8. ⏳ Tracking de uso automático
9. ⏳ Panel de admin
10. ⏳ Sistema de notificaciones
11. ⏳ Pruebas end-to-end

## 📌 Notas Finales

- El sistema está diseñado para escalar
- Soporta cambios de plan (upgrade/downgrade)
- Prorratea automáticamente con Stripe
- Historial completo de facturas
- Compatible con SCA (Strong Customer Authentication) de Europa

---

**Estado Actual:** Infraestructura de base de datos lista ✅
**Siguiente Paso:** Implementar modelos completos y StripeService

