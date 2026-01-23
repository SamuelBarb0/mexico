# Comandos para probar la API

## 1. Autenticación - Obtener Token

```bash
# Login (reemplaza con credenciales válidas)
curl -X POST https://tu-dominio.com/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "usuario@email.com", "password": "tu_password"}'
```

**Respuesta esperada:**
```json
{
  "success": true,
  "data": {
    "token": "1|abc123...",
    "user": {...}
  }
}
```

Guarda el token para usarlo en las siguientes peticiones.

---

## 2. Verificar Usuario Autenticado

```bash
curl -X GET https://tu-dominio.com/api/v1/auth/me \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

---

## 3. Listar Contactos

```bash
curl -X GET https://tu-dominio.com/api/v1/contacts \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

---

## 4. Crear Contacto

```bash
curl -X POST https://tu-dominio.com/api/v1/contacts \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Juan Pérez",
    "phone": "+521234567890",
    "email": "juan@example.com"
  }'
```

---

## 5. Listar Plantillas

```bash
curl -X GET https://tu-dominio.com/api/v1/templates \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

---

## 6. Listar Campañas

```bash
curl -X GET https://tu-dominio.com/api/v1/campaigns \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

---

## 7. Ver Campaña Específica

```bash
curl -X GET https://tu-dominio.com/api/v1/campaigns/1 \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

---

## 8. Estadísticas de Campaña

```bash
curl -X GET https://tu-dominio.com/api/v1/campaigns/1/stats \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

---

## 9. Listar Mensajes

```bash
curl -X GET https://tu-dominio.com/api/v1/messages \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

---

## 10. Listar Cuentas WABA

```bash
curl -X GET https://tu-dominio.com/api/v1/waba-accounts \
  -H "Authorization: Bearer TU_TOKEN" \
  -H "Accept: application/json"
```

---

## Script de Prueba Rápida (Bash)

```bash
#!/bin/bash

BASE_URL="https://tu-dominio.com/api/v1"
EMAIL="usuario@email.com"
PASSWORD="tu_password"

echo "=== Obteniendo token ==="
RESPONSE=$(curl -s -X POST "$BASE_URL/auth/login" \
  -H "Content-Type: application/json" \
  -d "{\"email\": \"$EMAIL\", \"password\": \"$PASSWORD\"}")

TOKEN=$(echo $RESPONSE | grep -o '"token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$TOKEN" ]; then
  echo "❌ Error en login: $RESPONSE"
  exit 1
fi

echo "✅ Token obtenido"

echo ""
echo "=== Probando endpoints ==="

# Auth/me
echo -n "GET /auth/me: "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/auth/me" \
  -H "Authorization: Bearer $TOKEN")
[ "$STATUS" == "200" ] && echo "✅ $STATUS" || echo "❌ $STATUS"

# Contacts
echo -n "GET /contacts: "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/contacts" \
  -H "Authorization: Bearer $TOKEN")
[ "$STATUS" == "200" ] && echo "✅ $STATUS" || echo "❌ $STATUS"

# Templates
echo -n "GET /templates: "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/templates" \
  -H "Authorization: Bearer $TOKEN")
[ "$STATUS" == "200" ] && echo "✅ $STATUS" || echo "❌ $STATUS"

# Campaigns
echo -n "GET /campaigns: "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/campaigns" \
  -H "Authorization: Bearer $TOKEN")
[ "$STATUS" == "200" ] && echo "✅ $STATUS" || echo "❌ $STATUS"

# Messages
echo -n "GET /messages: "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/messages" \
  -H "Authorization: Bearer $TOKEN")
[ "$STATUS" == "200" ] && echo "✅ $STATUS" || echo "❌ $STATUS"

# WABA Accounts
echo -n "GET /waba-accounts: "
STATUS=$(curl -s -o /dev/null -w "%{http_code}" -X GET "$BASE_URL/waba-accounts" \
  -H "Authorization: Bearer $TOKEN")
[ "$STATUS" == "200" ] && echo "✅ $STATUS" || echo "❌ $STATUS"

echo ""
echo "=== Pruebas completadas ==="
```

---

## Errores Comunes

| Código | Significado | Solución |
|--------|-------------|----------|
| 401 | No autorizado | Verifica el token o haz login nuevamente |
| 403 | Prohibido | El usuario no tiene permisos para este recurso |
| 404 | No encontrado | El recurso no existe o no pertenece al tenant |
| 422 | Error de validación | Revisa los datos enviados |
| 500 | Error interno | Revisa los logs de Laravel |