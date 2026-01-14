# Configuración de ngrok para Webhooks de WhatsApp

## 1. Descargar e Instalar ngrok

### Opción A: Descarga directa
1. Ve a: https://ngrok.com/download
2. Descarga la versión para Windows
3. Extrae el archivo `ngrok.exe` en una carpeta (ej: `C:\ngrok\`)

### Opción B: Con Chocolatey (si lo tienes instalado)
```bash
choco install ngrok
```

## 2. Crear Cuenta en ngrok

1. Ve a: https://dashboard.ngrok.com/signup
2. Crea una cuenta gratuita (puedes usar Google/GitHub)
3. Una vez dentro, copia tu **Authtoken** desde: https://dashboard.ngrok.com/get-started/your-authtoken

## 3. Autenticar ngrok

Abre una terminal y ejecuta (reemplaza TU_AUTHTOKEN con tu token):

```bash
ngrok config add-authtoken TU_AUTHTOKEN
```

## 4. Iniciar el Túnel

Desde la raíz del proyecto ejecuta:

```bash
ngrok http 80 --host-header="localhost"
```

O si XAMPP usa otro puerto:

```bash
ngrok http 8080 --host-header="localhost"
```

Verás algo como:
```
Session Status                online
Account                       tu@email.com
Version                       3.x.x
Region                        United States (us)
Latency                       45ms
Web Interface                 http://127.0.0.1:4040
Forwarding                    https://abc123def456.ngrok-free.app -> http://localhost:80
```

**IMPORTANTE**: Copia la URL HTTPS (ej: `https://abc123def456.ngrok-free.app`)

## 5. Configurar el Webhook en Meta

### 5.1. Acceder a tu App de Meta

1. Ve a: https://developers.facebook.com/apps
2. Selecciona tu aplicación de WhatsApp
3. En el menú lateral, ve a: **WhatsApp > Configuration**

### 5.2. Configurar la URL del Webhook

En la sección **Webhook**:

1. Haz clic en **Edit** (o **Configure Webhook**)
2. Ingresa los siguientes datos:

   **Callback URL**:
   ```
   https://TU-NGROK-URL.ngrok-free.app/mexico/public/api/webhooks/meta
   ```

   **Verify Token**:
   ```
   mexico_whatsapp_token
   ```

3. Haz clic en **Verify and Save**

Si todo está bien, verás un mensaje de éxito ✅

### 5.3. Suscribirse a Eventos

1. En la misma página, busca la sección **Webhook fields**
2. Haz clic en **Manage**
3. Suscríbete a estos eventos:
   - ✅ **messages** (IMPORTANTE - para recibir mensajes)
   - ✅ **message_status** (opcional - para actualizaciones de estado)
4. Haz clic en **Save**

## 6. Verificar que Funciona

### 6.1. Ver los logs de ngrok

Abre en tu navegador:
```
http://127.0.0.1:4040
```

Aquí verás en tiempo real todas las peticiones que llegan al webhook.

### 6.2. Enviar un mensaje de prueba

1. Abre WhatsApp en tu teléfono
2. Envía un mensaje al número de tu WhatsApp Business
3. Verifica en:
   - **ngrok dashboard** (http://127.0.0.1:4040) - Verás la petición POST
   - **Laravel logs** - Verás los logs de procesamiento
   - **Inbox de la app** - El mensaje debería aparecer

### 6.3. Verificar en la base de datos

```bash
php check_messages.php
```

Deberías ver el nuevo mensaje inbound.

## 7. Verificación de la URL del Webhook

Tu webhook completo será:

```
https://[TU-NGROK-SUBDOMAIN].ngrok-free.app/mexico/public/api/webhooks/meta
```

Ejemplo:
```
https://abc123def456.ngrok-free.app/mexico/public/api/webhooks/meta
```

## 8. Script de Inicio Rápido

Crea un archivo `start_ngrok.bat` en la raíz del proyecto:

```batch
@echo off
echo ========================================
echo   Iniciando ngrok para WhatsApp
echo ========================================
echo.
echo El tunel se creara en:
echo http://localhost:80
echo.
echo Una vez iniciado, copia la URL HTTPS
echo y configurala en Meta Developers
echo.
echo Dashboard: http://127.0.0.1:4040
echo ========================================
echo.

ngrok http 80 --host-header="localhost"
```

Luego solo ejecuta:
```bash
start_ngrok.bat
```

## 9. Notas Importantes

### ⚠️ Cuenta Gratuita de ngrok
- La URL cambia cada vez que reinicias ngrok
- Tendrás que actualizar la URL en Meta cada vez
- Límite: 40 conexiones/minuto

### 💡 Cuenta de Pago de ngrok (Opcional)
- $8/mes - URL fija que no cambia
- Sin límites de conexiones
- Más regiones disponibles

### 🔄 Reiniciar ngrok

Si reinicias ngrok:
1. Obtendrás una nueva URL
2. Debes actualizar la URL en Meta Developers
3. No olvides incluir la ruta completa: `/mexico/public/api/webhooks/meta`

### 📝 Verificar que el webhook responde

Prueba manualmente con curl:

```bash
curl "https://TU-NGROK-URL.ngrok-free.app/mexico/public/api/webhooks/meta?hub.mode=subscribe&hub.verify_token=mexico_whatsapp_token&hub.challenge=test123"
```

Debe devolver: `test123`

## 10. Troubleshooting

### Error: "Webhook verification failed"
- Verifica que ngrok esté corriendo
- Verifica que la URL incluya `/mexico/public/api/webhooks/meta`
- Verifica que el verify token sea exactamente: `mexico_whatsapp_token`

### Error: "Unable to connect"
- Verifica que XAMPP/Apache esté corriendo
- Verifica que ngrok esté apuntando al puerto correcto (80 u 8080)
- Revisa los logs de ngrok en http://127.0.0.1:4040

### Los mensajes no llegan al inbox
- Verifica que estés suscrito al evento `messages` en Meta
- Revisa los logs de Laravel: `storage/logs/laravel.log`
- Revisa el dashboard de ngrok para ver si llegan las peticiones
- Verifica que el `phone_number_id` en Meta coincida con tu WABA Account

## 11. Flujo Completo

```
Usuario en WhatsApp
       ↓
   [Envía mensaje]
       ↓
  Meta WhatsApp API
       ↓
   [Webhook POST]
       ↓
      ngrok
       ↓
   Laravel App
       ↓
  WebhookService
       ↓
   Guarda en DB
       ↓
  Aparece en Inbox
```

## 12. Para Producción (Hostinger)

En producción NO necesitas ngrok, usarás tu dominio:

```
https://tudominio.com/api/webhooks/meta
```

Y configurarás un Cron Job para procesar los mensajes.

---

¡Listo para recibir mensajes! 🚀📱
