# Prueba de Validación de Tarjetas Duplicadas

## Descripción

Se implementó un sistema de validación que previene agregar la misma tarjeta física múltiples veces a una cuenta, incluso si Stripe genera diferentes IDs.

## Cómo Funciona

### 1. Validación por Stripe Payment Method ID

Si intentas agregar un `payment_method_id` que ya existe en la base de datos para ese tenant:
- No se crea un duplicado
- Se establece como método de pago predeterminado
- Se actualiza el estado a activo

### 2. Validación por Datos de la Tarjeta

Si intentas agregar una tarjeta con los mismos datos físicos:
- **Marca** (Visa, Mastercard, Amex, etc.)
- **Últimos 4 dígitos** (last4)
- **Mes de expiración** (exp_month)
- **Año de expiración** (exp_year)

El sistema detectará que es la misma tarjeta física y mostrará el error:

```
Esta tarjeta Visa ****4242 ya está registrada en su cuenta.
```

## Implementación

### Archivo Modificado
- `app/Services/StripeService.php` (líneas 297-315)

### Código de Validación

```php
// Check if the same card already exists (by card details, not just Stripe ID)
if ($stripePaymentMethod->type === 'card' && isset($stripePaymentMethod->card)) {
    $duplicateCard = PaymentMethod::where('tenant_id', $tenant->id)
        ->where('type', 'card')
        ->where('brand', $stripePaymentMethod->card->brand)
        ->where('last4', $stripePaymentMethod->card->last4)
        ->where('exp_month', $stripePaymentMethod->card->exp_month)
        ->where('exp_year', $stripePaymentMethod->card->exp_year)
        ->where('is_active', true)
        ->first();

    if ($duplicateCard) {
        throw new Exception(
            "Esta tarjeta {$stripePaymentMethod->card->brand} ****{$stripePaymentMethod->card->last4} " .
            "ya está registrada en su cuenta."
        );
    }
}
```

## Casos de Uso

### ✅ Caso 1: Primera vez agregando una tarjeta
**Acción:** Agregar tarjeta Visa ****4242
**Resultado:** Se agrega exitosamente
**Mensaje:** "¡Método de pago agregado exitosamente!"

### ❌ Caso 2: Intentar agregar la misma tarjeta de nuevo
**Acción:** Agregar tarjeta Visa ****4242 (misma que ya existe)
**Resultado:** Se rechaza
**Mensaje:** "Esta tarjeta Visa ****4242 ya está registrada en su cuenta."

### ✅ Caso 3: Agregar una tarjeta diferente
**Acción:** Agregar tarjeta Mastercard ****5555
**Resultado:** Se agrega exitosamente
**Mensaje:** "¡Método de pago agregado exitosamente!"

### ❌ Caso 4: Tarjeta con mismo número pero expiración diferente
**Acción:** Agregar Visa ****4242 con exp 12/2026 (original tiene 12/2025)
**Resultado:** Se agrega como nueva tarjeta
**Razón:** Técnicamente es una tarjeta renovada con nueva fecha de expiración

### ✅ Caso 5: Tarjeta eliminada y vuelta a agregar
**Acción:**
1. Eliminar tarjeta Visa ****4242
2. Agregar la misma tarjeta nuevamente

**Resultado:** Se agrega exitosamente
**Razón:** La validación solo verifica tarjetas activas (`is_active = true`)

## Beneficios

1. **Previene confusión del usuario** - No permite duplicados de la misma tarjeta
2. **Mejor UX** - Mensaje de error claro y específico
3. **Datos limpios** - Evita tarjetas repetidas en la base de datos
4. **Seguridad** - Previene intentos accidentales de agregar la misma tarjeta múltiples veces

## Limitaciones Conocidas

1. **Tarjetas renovadas:** Si una tarjeta tiene nueva fecha de expiración, se considera diferente
   - **Solución:** Esto es correcto, ya que técnicamente es una nueva tarjeta

2. **Tarjetas eliminadas:** Se pueden volver a agregar después de eliminarlas
   - **Solución:** Esto es intencional y correcto

3. **Solo valida tarjetas activas:** No verifica contra tarjetas marcadas como inactivas
   - **Solución:** Esto es correcto, permite reactivar tarjetas previamente inactivas

## Prueba Manual

Para probar la funcionalidad:

1. **Acceder al sistema:**
   ```
   Login → Dashboard → Métodos de Pago
   ```

2. **Agregar primera tarjeta:**
   - Usar tarjeta de prueba de Stripe: `4242 4242 4242 4242`
   - Cualquier CVC de 3 dígitos
   - Cualquier fecha futura
   - Debe agregarse exitosamente

3. **Intentar agregar la misma tarjeta:**
   - Usar los mismos datos: `4242 4242 4242 4242`
   - Mismo CVC y fecha
   - Debe mostrar error: "Esta tarjeta Visa ****4242 ya está registrada en su cuenta."

4. **Agregar tarjeta diferente:**
   - Usar otra tarjeta de prueba: `5555 5555 5555 4444` (Mastercard)
   - Debe agregarse exitosamente

## Tarjetas de Prueba de Stripe

Para testing:

| Tarjeta | Número | Resultado Esperado |
|---------|--------|-------------------|
| Visa | 4242 4242 4242 4242 | ✓ Pago exitoso |
| Visa (debit) | 4000 0566 5566 5556 | ✓ Pago exitoso |
| Mastercard | 5555 5555 5555 4444 | ✓ Pago exitoso |
| American Express | 3782 822463 10005 | ✓ Pago exitoso |
| Discover | 6011 1111 1111 1117 | ✓ Pago exitoso |

**Notas:**
- Usar cualquier CVC de 3 dígitos (4 para Amex)
- Usar cualquier fecha de expiración futura
- Usar cualquier código postal

## Verificación en Base de Datos

Para verificar que no hay duplicados:

```sql
-- Ver todas las tarjetas de un tenant
SELECT id, brand, last4, exp_month, exp_year, is_default, is_active, created_at
FROM payment_methods
WHERE tenant_id = 1
ORDER BY created_at DESC;

-- Buscar posibles duplicados
SELECT brand, last4, exp_month, exp_year, COUNT(*) as count
FROM payment_methods
WHERE tenant_id = 1 AND is_active = 1
GROUP BY brand, last4, exp_month, exp_year
HAVING COUNT(*) > 1;
```

Si la segunda consulta retorna resultados, significa que hay duplicados (no debería pasar con la nueva validación).

## Estado

✅ **Implementado y Funcional**

La validación está activa y previene tarjetas duplicadas basándose en los datos físicos de la tarjeta, no solo en el ID de Stripe.
