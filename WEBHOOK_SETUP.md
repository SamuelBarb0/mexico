# Configuración del Webhook de Meta WhatsApp

## Problema Identificado

Los mensajes entrantes no llegan al inbox porque el webhook de Meta WhatsApp no está configurado correctamente.

## Solución Implementada

✅ Se habilitaron las rutas API en `bootstrap/app.php`
✅ Se agregó la excepción CSRF para webhooks
✅ La ruta del webhook está ahora disponible en: `/api/webhooks/meta`

## Pasos para Configurar el Webhook

### Opción 1: Desarrollo Local con ngrok (Recomendado para desarrollo)

1. **Instalar ngrok**
   - Descargar de: https://ngrok.com/download
   - Crear cuenta gratuita en ngrok.com
   - Autenticar: `ngrok authtoken TU_TOKEN`

2. **Iniciar el túnel**
   ```bash
   ngrok http 80 --host-header="localhost"
   ```

   O si usas un puerto específico para XAMPP:
   ```bash
   ngrok http 80 --host-header="localhost:80"
   ```

3. **Copiar la URL HTTPS**
   - ngrok te dará una URL como: `https://abc123.ngrok.io`
   - Tu webhook será: `https://abc123.ngrok.io/mexico/public/api/webhooks/meta`

4. **Configurar en Meta**
   - Ve a: https://developers.facebook.com/apps
   - Selecciona tu aplicación → WhatsApp → Configuration
   - En "Webhook", haz clic en "Edit"
   - **Callback URL**: `https://abc123.ngrok.io/mexico/public/api/webhooks/meta`
   - **Verify Token**: `mexico_whatsapp_token` (el que está en tu `.env`)
   - Haz clic en "Verify and Save"

5. **Suscribirse a eventos**
   - En la misma sección de Webhook
   - Haz clic en "Manage" en webhook fields
   - Suscríbete a: `messages` (esto te notificará de mensajes entrantes)
   - Guarda los cambios

### Opción 2: Producción/Hostinger

1. **URL del Webhook**
   - Si tu dominio es: `https://tudominio.com`
   - Tu webhook será: `https://tudominio.com/api/webhooks/meta`

2. **Configurar en Meta**
   - Callback URL: `https://tudominio.com/api/webhooks/meta`
   - Verify Token: `mexico_whatsapp_token`

3. **Verificar permisos**
   - Asegúrate de que el archivo `.env` en producción tiene:
     ```
     META_WEBHOOK_VERIFY_TOKEN=mexico_whatsapp_token
     ```

## Verificación

### 1. Probar el webhook localmente

```bash
# GET request (verificación inicial de Meta)
curl "http://localhost/mexico/public/api/webhooks/meta?hub.mode=subscribe&hub.verify_token=mexico_whatsapp_token&hub.challenge=test123"
# Debe devolver: test123

# POST request (simular mensaje entrante)
curl -X POST http://localhost/mexico/public/api/webhooks/meta \
  -H "Content-Type: application/json" \
  -d '{
    "object": "whatsapp_business_account",
    "entry": [{
      "changes": [{
        "field": "messages",
        "value": {
          "metadata": {
            "phone_number_id": "TU_PHONE_NUMBER_ID"
          },
          "messages": [{
            "from": "521234567890",
            "id": "wamid.test123",
            "timestamp": "1234567890",
            "type": "text",
            "text": {
              "body": "Hola, esto es una prueba"
            }
          }]
        }
      }]
    }]
  }'
```

### 2. Verificar logs

Después de enviar un mensaje desde WhatsApp:

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Buscar logs de webhook
php artisan tinker --execute="
\$logs = \Illuminate\Support\Facades\Log::getLogger();
echo 'Verificando logs de webhook...';
"
```

## Troubleshooting

### Webhook no verifica

- ✅ Verifica que el verify token coincida exactamente
- ✅ Verifica que la URL sea accesible desde internet (usa ngrok para desarrollo)
- ✅ Verifica que no haya errores en `storage/logs/laravel.log`

### Mensajes no llegan al inbox

1. Verifica que el webhook esté suscrito al evento `messages`
2. Verifica que el `phone_number_id` en Meta coincida con tu WABA Account
3. Revisa los logs: `tail -f storage/logs/laravel.log`

### Error CSRF

- ✅ Ya configurado: los webhooks están excluidos de CSRF en `bootstrap/app.php`

## Testing

### Ver contactos en el inbox

```bash
php artisan tinker
# Ver todos los contactos con mensajes
\App\Models\Contact::whereHas('messages')->with('messages')->get();

# Ver últimos mensajes
\App\Models\Message::latest()->take(10)->get(['id', 'direction', 'content', 'status', 'created_at']);
```

## URL del Webhook Actual

```
Desarrollo: http://localhost/mexico/public/api/webhooks/meta
Con ngrok: https://[TU-SUBDOMINIO].ngrok.io/mexico/public/api/webhooks/meta
Producción: https://tudominio.com/api/webhooks/meta
```

## Verify Token

```
mexico_whatsapp_token
```

## Eventos Soportados

El webhook procesa automáticamente:

- ✅ Mensajes entrantes (inbound messages)
- ✅ Actualizaciones de estado (sent, delivered, read, failed)
- ✅ Múltiples tipos de mensajes (text, image, video, document, audio, location)

## Próximos Pasos

1. Instalar ngrok (o usar dominio en producción)
2. Configurar la URL del webhook en Meta
3. Suscribirse al evento `messages`
4. Enviar un mensaje de prueba desde tu número personal al número de WhatsApp Business
5. Verificar que aparece en el Inbox de la aplicación
