# 🚀 Guía de Acceso a los Módulos del Sistema

## 📋 Rutas Disponibles

Todas las rutas están protegidas con los middleware `auth`, `tenant.set` y `tenant.status`.

### 🏠 Dashboard
```
GET /dashboard
```
Vista principal del sistema

---

## 👥 Módulo de Clientes

### Listar Clientes
```
GET /clients
```
**Respuesta:**
```json
{
  "success": true,
  "data": {
    "data": [...],
    "current_page": 1,
    "total": 10
  },
  "tenant": "Demo Company"
}
```

### Ver Cliente
```
GET /clients/{id}
```

### Crear Cliente
```
POST /clients
Content-Type: application/json

{
  "name": "Juan Pérez",
  "company": "Acme Corp",
  "email": "juan@acme.com",
  "phone": "+52 123 456 7890",
  "country": "México",
  "address": "Calle 123",
  "notes": "Cliente VIP",
  "status": "active"
}
```

### Actualizar Cliente
```
PUT /clients/{id}
Content-Type: application/json

{
  "name": "Juan Pérez Actualizado",
  "status": "inactive"
}
```

### Eliminar Cliente
```
DELETE /clients/{id}
```

---

## 📞 Módulo de Contactos

### Listar Contactos
```
GET /contacts
```

### Ver Contacto
```
GET /contacts/{id}
```

### Crear Contacto
```
POST /contacts
Content-Type: application/json

{
  "client_id": 1,
  "name": "María González",
  "phone": "+52 987 654 3210",
  "email": "maria@example.com",
  "whatsapp_id": "52987654321",
  "tags": ["vip", "premium"],
  "custom_fields": {
    "birthday": "1990-05-15",
    "preferences": "nocturno"
  },
  "status": "active"
}
```

### Actualizar Contacto
```
PUT /contacts/{id}
PATCH /contacts/{id}
```

### Eliminar Contacto
```
DELETE /contacts/{id}
```

---

## 📢 Módulo de Campañas

### Listar Campañas
```
GET /campaigns
```

### Ver Campaña
```
GET /campaigns/{id}
```

### Crear Campaña
```
POST /campaigns
Content-Type: application/json

{
  "waba_account_id": 1,
  "name": "Promoción Black Friday",
  "description": "Campaña de descuentos",
  "type": "broadcast",
  "status": "draft",
  "message_template": {
    "text": "¡Hola {{name}}! Tenemos ofertas especiales para ti",
    "buttons": ["Ver Ofertas", "Contactar"]
  },
  "target_audience": {
    "tags": ["vip"],
    "status": "active"
  },
  "scheduled_at": "2025-12-15 10:00:00"
}
```

### Actualizar Campaña
```
PUT /campaigns/{id}
PATCH /campaigns/{id}
```

### Ejecutar Campaña
```
POST /campaigns/{id}/execute
```

### Eliminar Campaña
```
DELETE /campaigns/{id}
```

---

## 📱 Módulo de WABA Accounts

### Listar Cuentas WABA
```
GET /waba-accounts
```

### Ver Cuenta WABA
```
GET /waba-accounts/{id}
```

### Crear Cuenta WABA
```
POST /waba-accounts
Content-Type: application/json

{
  "name": "WABA Principal",
  "phone_number": "+52 123 456 7890",
  "phone_number_id": "123456789",
  "business_account_id": "987654321",
  "waba_id": "111222333",
  "access_token": "EAAxxxxxxxxxxxxx",
  "status": "pending",
  "quality_rating": "unknown",
  "settings": {
    "webhook_url": "https://example.com/webhook",
    "verify_token": "mi_token_secreto"
  }
}
```

### Actualizar Cuenta WABA
```
PUT /waba-accounts/{id}
PATCH /waba-accounts/{id}
```

### Eliminar Cuenta WABA
```
DELETE /waba-accounts/{id}
```

---

## 🏢 Panel de Administración (Solo Platform Admin)

### Listar Tenants
```
GET /admin/tenants
```

### Ver Tenant
```
GET /admin/tenants/{id}
```

### Crear Tenant
```
POST /admin/tenants
Content-Type: application/json

{
  "name": "Nueva Empresa S.A.",
  "slug": "nueva-empresa",
  "domain": "nuevaempresa.miapp.com",
  "status": "active",
  "settings": {
    "timezone": "America/Mexico_City",
    "language": "es"
  },
  "trial_ends_at": "2026-01-15 23:59:59"
}
```

### Actualizar Tenant
```
PUT /admin/tenants/{id}
PATCH /admin/tenants/{id}
```

### Eliminar Tenant
```
DELETE /admin/tenants/{id}
```

---

## 🔐 Autenticación

### Login
```
POST /login
Content-Type: application/x-www-form-urlencoded

email=demo@admin.com&password=password123
```

### Logout
```
POST /logout
```

---

## 🧪 Pruebas con cURL

### Ejemplo: Listar Clientes
```bash
curl -X GET http://localhost:8000/clients \
  -H "Accept: application/json" \
  -b cookies.txt
```

### Ejemplo: Crear Cliente
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
    "status": "active"
  }'
```

---

## 🧪 Pruebas con Postman

1. **Importar Colección**
   - Crea una nueva colección llamada "Sistema Multitenant"

2. **Configurar Variables de Entorno**
   ```
   base_url: http://localhost:8000
   ```

3. **Login (Primero)**
   ```
   POST {{base_url}}/login
   Body (form-data):
     email: demo@admin.com
     password: password123
   ```

4. **Hacer Requests**
   Postman guardará automáticamente las cookies de sesión.

---

## 📊 Ver Rutas Disponibles

Para ver todas las rutas registradas:

```bash
php artisan route:list
```

Filtrar solo las rutas de API:
```bash
php artisan route:list --path=clients
php artisan route:list --path=contacts
php artisan route:list --path=campaigns
```

---

## 🔍 Verificar Aislamiento de Datos

### Probar con Usuario del Tenant "Demo Company"

1. **Login como demo@admin.com**
2. **Crear un cliente:**
   ```bash
   POST /clients
   {
     "name": "Cliente Demo",
     "company": "Demo Corp"
   }
   ```
3. **Listar clientes:**
   ```bash
   GET /clients
   ```
   Solo verás los clientes del tenant "Demo Company"

### Probar con Platform Admin

1. **Login como platform@admin.com**
2. **Acceder a admin:**
   ```bash
   GET /admin/tenants
   ```
   Verás todos los tenants de la plataforma

---

## 🛡️ Seguridad y Permisos

### Middleware Activos

1. **auth**: Verifica que el usuario esté autenticado
2. **tenant.set**: Asegura que el usuario tenga un tenant asignado (excepto platform admin)
3. **tenant.status**: Verifica que el tenant esté activo y el usuario esté habilitado

### Aislamiento Automático

El trait `BelongsToTenant` en los modelos asegura que:
- Al crear: `tenant_id` se asigna automáticamente
- Al consultar: Solo se muestran datos del tenant actual
- No es posible acceder a datos de otros tenants

### Ejemplo de Aislamiento

Usuario del Tenant 1 crea un cliente:
```bash
POST /clients
{
  "name": "Cliente A"
}
```

El cliente se crea con `tenant_id = 1` automáticamente.

Si otro usuario del Tenant 2 hace:
```bash
GET /clients
```

NO verá el "Cliente A", solo sus propios clientes.

---

## 📝 Notas Importantes

1. **Todas las rutas requieren autenticación** (excepto login)
2. **Los datos se filtran automáticamente por tenant**
3. **Platform Admins** pueden acceder a `/admin/tenants`
4. **Soft Deletes**: Los registros eliminados no se borran, se marcan como eliminados
5. **Validación**: Todos los endpoints validan los datos de entrada
6. **JSON**: Las respuestas son en formato JSON

---

## 🚀 Próximos Pasos

1. Crear vistas web (Blade templates) para cada módulo
2. Implementar verificación de permisos en controladores
3. Agregar endpoints para reportes y estadísticas
4. Implementar búsqueda y filtros avanzados
5. Agregar exportación de datos (CSV, Excel)

---

**¿Necesitas ayuda?** Revisa la documentación completa en [MULTITENANT_SETUP.md](MULTITENANT_SETUP.md)
