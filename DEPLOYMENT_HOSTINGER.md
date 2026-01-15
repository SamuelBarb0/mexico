# Guía de Deployment en Hostinger

## 📋 Requisitos Previos

- Cuenta de Hostinger con hosting activado
- Acceso SSH al servidor
- Base de datos MySQL creada en Hostinger
- Git instalado en el servidor (opcional pero recomendado)

## 🚀 Pasos para Deployment

### 1. Preparar Archivos Localmente

```bash
# Asegúrate de que todo funciona localmente
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Optimizar para producción
composer install --optimize-autoloader --no-dev
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 2. Configurar Base de Datos en Hostinger

1. Accede al panel de Hostinger
2. Ve a **Bases de datos MySQL**
3. Crea una nueva base de datos:
   - Nombre: `u123456789_mexico` (Hostinger añade un prefijo automático)
   - Usuario: Anota el usuario generado
   - Password: Anota el password generado

### 3. Subir Archivos al Servidor

#### Opción A: Usando Git (Recomendado)

```bash
# Conecta por SSH a tu servidor Hostinger
ssh u123456789@forestgreen-hamster-482261.hostingersite.com

# Navega al directorio public_html
cd public_html

# Clona el repositorio (si usas Git)
git clone TU_REPOSITORIO .

# O sube los archivos por FTP/FileZilla
```

#### Opción B: Usando FTP/FileZilla

1. Conecta a tu servidor FTP de Hostinger
2. Sube TODOS los archivos del proyecto a `public_html/`
3. Asegúrate de subir también los archivos ocultos (`.env`, `.htaccess`)

### 4. Configurar el Archivo .env

```bash
# En el servidor, copia el archivo de ejemplo
cp .env.production.example .env

# Edita el archivo .env con tus datos reales
nano .env
```

**Configuración importante en .env:**

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://forestgreen-hamster-482261.hostingersite.com

# Base de datos (obtener de Hostinger)
DB_HOST=localhost
DB_DATABASE=u123456789_mexico
DB_USERNAME=u123456789_user
DB_PASSWORD=tu_password_real

# Stripe - USAR CLAVES DE PRODUCCIÓN
STRIPE_KEY=pk_live_...
STRIPE_SECRET=sk_live_...

# Meta WhatsApp - USAR TOKENS DE PRODUCCIÓN
META_APP_ID=tu_app_id
META_ACCESS_TOKEN=tu_token_produccion
```

### 5. Configurar Permisos

```bash
# Asignar permisos correctos
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

# Si tienes problemas, intenta:
chmod -R 777 storage bootstrap/cache
```

### 6. Generar Application Key

```bash
php artisan key:generate
```

### 7. Ejecutar Migraciones

```bash
# Ejecutar migraciones en producción
php artisan migrate --force

# Ejecutar seeders si es necesario
php artisan db:seed --class=SubscriptionPlanSeeder --force
```

### 8. Configurar el Document Root en Hostinger

**IMPORTANTE:** Laravel requiere que el document root apunte a la carpeta `public/`

1. Accede al panel de Hostinger
2. Ve a **Configuración Avanzada** → **Avanzado** → **Configurar**
3. Busca la opción **Document Root**
4. Cambia el valor a: `public_html/public`
5. Guarda los cambios

**O crea/edita el archivo `.htaccess` en `public_html/`:**

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### 9. Optimizar para Producción

```bash
# Limpiar cachés antiguos
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cachear configuración para mejor performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimizar autoloader de Composer
composer dump-autoload --optimize
```

### 10. Configurar Queue Worker (Opcional pero Recomendado)

Si usas colas para envío de mensajes:

```bash
# Crear un cron job en Hostinger
# Panel → Cron Jobs → Añadir nuevo

# Comando:
php /home/u123456789/public_html/artisan queue:work --stop-when-empty

# Frecuencia: Cada minuto
```

### 11. Configurar SSL (HTTPS)

1. En el panel de Hostinger, ve a **SSL**
2. Activa el certificado SSL gratuito de Let's Encrypt
3. Espera 5-10 minutos para que se active
4. Verifica que `APP_URL` en `.env` use `https://`

### 12. Configurar Webhooks de WhatsApp

1. Ve a Meta Business Suite → Configuración de WhatsApp
2. Configura el webhook:
   - URL: `https://forestgreen-hamster-482261.hostingersite.com/api/v1/webhooks/whatsapp`
   - Token de verificación: El valor de `META_WEBHOOK_VERIFY_TOKEN` en tu `.env`
3. Suscríbete a los eventos: `messages`, `messaging_postbacks`, `message_deliveries`

### 13. Configurar Webhooks de Stripe

1. Ve a Stripe Dashboard → Developers → Webhooks
2. Añade endpoint:
   - URL: `https://forestgreen-hamster-482261.hostingersite.com/webhook/stripe`
   - Eventos: Selecciona todos los eventos relacionados con `payment_intent`, `customer`, `subscription`
3. Copia el `Signing Secret` y añádelo a `.env`:
   ```env
   STRIPE_WEBHOOK_SECRET=whsec_...
   ```

## 🔒 Seguridad

### Checklist de Seguridad

- [ ] `APP_DEBUG=false` en producción
- [ ] `APP_ENV=production`
- [ ] Archivo `.env` NO debe ser accesible públicamente
- [ ] Usar claves de Stripe de producción (`pk_live_` y `sk_live_`)
- [ ] Usar tokens de WhatsApp de producción
- [ ] SSL/HTTPS activado
- [ ] Permisos correctos en archivos (755 para directorios, 644 para archivos)
- [ ] `APP_KEY` generada correctamente
- [ ] Credenciales de base de datos seguras

### Verificar que .env no sea público

Crea el archivo `public_html/public/.htaccess` (si no existe):

```apache
<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Bloquear acceso a .env
    <Files .env>
        Order allow,deny
        Deny from all
    </Files>

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]
</IfModule>
```

## 🧪 Testing en Producción

### Verificar que todo funciona:

1. **Página principal:**
   ```
   https://forestgreen-hamster-482261.hostingersite.com/
   ```

2. **Documentación API:**
   ```
   https://forestgreen-hamster-482261.hostingersite.com/api-docs
   ```

3. **Test de login API:**
   ```bash
   curl -X POST https://forestgreen-hamster-482261.hostingersite.com/api/v1/auth/login \
     -H "Content-Type: application/json" \
     -d '{"email":"tu@email.com","password":"tu_password"}'
   ```

4. **Verificar webhook de WhatsApp:**
   ```bash
   curl "https://forestgreen-hamster-482261.hostingersite.com/api/v1/webhooks/whatsapp?hub.mode=subscribe&hub.verify_token=TU_TOKEN&hub.challenge=test"
   ```

## 🐛 Troubleshooting

### Error 500 - Internal Server Error

```bash
# Ver logs de Laravel
tail -n 50 storage/logs/laravel.log

# Verificar permisos
chmod -R 775 storage bootstrap/cache

# Limpiar cachés
php artisan cache:clear
php artisan config:clear
```

### Página en blanco

- Verifica que el Document Root apunte a `public/`
- Verifica que `.htaccess` exista en `public/`
- Revisa los logs de Apache

### Base de datos no conecta

- Verifica las credenciales en `.env`
- Asegúrate de usar `DB_HOST=localhost` (no 127.0.0.1)
- Verifica que la base de datos exista en el panel de Hostinger

### Assets (CSS/JS) no cargan

- Verifica que `APP_URL` sea correcto en `.env`
- Ejecuta `php artisan storage:link`
- Verifica que los archivos en `public/` tengan permisos 644

### Webhooks no funcionan

- Verifica que SSL esté activado
- Comprueba que la URL del webhook sea accesible públicamente
- Revisa los logs: `storage/logs/laravel.log`

## 📊 Monitoreo

### Logs importantes:

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Apache error logs (en Hostinger)
tail -f ~/logs/error.log
```

### Comandos útiles de mantenimiento:

```bash
# Limpiar logs antiguos (ejecutar cada mes)
php artisan log:clear

# Optimizar base de datos
php artisan optimize:clear
php artisan optimize

# Ver estado de la cola
php artisan queue:work --once
```

## 🔄 Actualizar la Aplicación

```bash
# Conecta por SSH
ssh u123456789@forestgreen-hamster-482261.hostingersite.com

# Navega al proyecto
cd public_html

# Activar modo mantenimiento
php artisan down

# Actualizar código (si usas Git)
git pull origin main

# Actualizar dependencias
composer install --optimize-autoloader --no-dev

# Ejecutar migraciones
php artisan migrate --force

# Limpiar y cachear
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Desactivar modo mantenimiento
php artisan up
```

## 📞 Soporte

Si encuentras problemas:
1. Revisa los logs: `storage/logs/laravel.log`
2. Verifica la configuración del `.env`
3. Contacta al soporte de Hostinger si es problema del servidor
4. Revisa la documentación oficial de Laravel: https://laravel.com/docs
