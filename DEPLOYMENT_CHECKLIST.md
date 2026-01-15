# ✅ Checklist Rápido de Deployment

## 📦 Antes de Subir al Servidor

- [ ] Probar todo localmente: `php artisan serve`
- [ ] Verificar que todas las migraciones funcionan
- [ ] Verificar que `.env.example` está actualizado
- [ ] Commit y push de todos los cambios

## 🚀 En el Servidor Hostinger

### 1. Subir Archivos
- [ ] Subir TODOS los archivos vía FTP a `public_html/`
- [ ] Verificar que `.htaccess` esté en la raíz
- [ ] Verificar que `public/.htaccess` también esté presente

### 2. Configurar .env
- [ ] Copiar `.env.production.example` a `.env`
- [ ] Actualizar `APP_URL=https://forestgreen-hamster-482261.hostingersite.com`
- [ ] Cambiar `APP_ENV=production`
- [ ] Cambiar `APP_DEBUG=false`
- [ ] Configurar credenciales de base de datos de Hostinger
- [ ] Actualizar claves de Stripe a producción (`pk_live_`, `sk_live_`)
- [ ] Actualizar tokens de Meta WhatsApp a producción
- [ ] Ejecutar: `php artisan key:generate`

### 3. Base de Datos
- [ ] Crear base de datos MySQL en panel de Hostinger
- [ ] Anotar: nombre DB, usuario, password
- [ ] Ejecutar: `php artisan migrate --force`
- [ ] Ejecutar seeders: `php artisan db:seed --class=SubscriptionPlanSeeder --force`

### 4. Permisos y Optimización
```bash
chmod -R 755 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### 5. Configurar Document Root
- [ ] Panel Hostinger → Configuración → Document Root → `public_html/public`
- [ ] O verificar que `.htaccess` en raíz redirija a `public/`

### 6. SSL y Seguridad
- [ ] Activar SSL en panel de Hostinger (Let's Encrypt)
- [ ] Verificar que `APP_URL` use `https://`
- [ ] Verificar que `.env` no sea accesible públicamente

### 7. Webhooks

#### WhatsApp (Meta):
- [ ] URL: `https://forestgreen-hamster-482261.hostingersite.com/api/v1/webhooks/whatsapp`
- [ ] Token: Valor de `META_WEBHOOK_VERIFY_TOKEN` del `.env`
- [ ] Eventos: `messages`, `messaging_postbacks`, `message_deliveries`

#### Stripe:
- [ ] URL: `https://forestgreen-hamster-482261.hostingersite.com/webhook/stripe`
- [ ] Copiar Signing Secret a `STRIPE_WEBHOOK_SECRET` en `.env`
- [ ] Eventos: Todos de `payment_intent.*`, `customer.*`, `subscription.*`

## 🧪 Verificación Final

- [ ] ✅ Página principal carga: `https://forestgreen-hamster-482261.hostingersite.com/`
- [ ] ✅ API Docs funciona: `https://forestgreen-hamster-482261.hostingersite.com/api-docs`
- [ ] ✅ Login funciona correctamente
- [ ] ✅ Registro de usuarios funciona
- [ ] ✅ Creación de contactos funciona
- [ ] ✅ Envío de mensajes funciona
- [ ] ✅ Webhook de WhatsApp responde
- [ ] ✅ Pagos con Stripe funcionan
- [ ] ✅ No hay errores en logs: `tail storage/logs/laravel.log`

## 🔧 Comandos Útiles

```bash
# Ver logs en tiempo real
tail -f storage/logs/laravel.log

# Limpiar cachés si algo falla
php artisan optimize:clear

# Verificar rutas
php artisan route:list

# Verificar configuración
php artisan config:show

# Test de conexión a DB
php artisan migrate:status
```

## 🆘 Si Algo Falla

1. **Error 500**: Revisar `storage/logs/laravel.log`
2. **Página en blanco**: Verificar Document Root y `.htaccess`
3. **Assets no cargan**: Verificar `APP_URL` en `.env`
4. **DB no conecta**: Verificar credenciales y que host sea `localhost`
5. **Webhooks fallan**: Verificar SSL activo y URLs accesibles

## 📞 URLs Importantes

- **App**: https://forestgreen-hamster-482261.hostingersite.com/
- **API Docs**: https://forestgreen-hamster-482261.hostingersite.com/api-docs
- **Webhook WhatsApp**: https://forestgreen-hamster-482261.hostingersite.com/api/v1/webhooks/whatsapp
- **Webhook Stripe**: https://forestgreen-hamster-482261.hostingersite.com/webhook/stripe
- **Panel Hostinger**: https://hpanel.hostinger.com
