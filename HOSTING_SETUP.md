# Configuración para Hostinger (Hosting Compartido)

## Queue Worker en Hostinger

En hosting compartido como Hostinger, **NO puedes ejecutar procesos perpetuos**. La solución es usar **Cron Jobs**.

### Paso 1: Configurar Cron Job en Hostinger

1. Entra al panel de Hostinger
2. Ve a **Advanced → Cron Jobs**
3. Crea un nuevo Cron Job con esta configuración:

**Comando:**
```bash
cd /home/tu_usuario/public_html && /usr/bin/php artisan queue:work --stop-when-empty --max-time=50
```

**Frecuencia:** Cada minuto
```
* * * * *
```

### ¿Qué hace este comando?

- `--stop-when-empty`: El worker se detiene cuando no hay más trabajos (importante para hosting compartido)
- `--max-time=50`: Se detiene después de 50 segundos (antes del límite de 60 segundos del cron)
- Se ejecuta cada minuto automáticamente

### Paso 2: Alternativa más segura (Recomendado)

Si Hostinger no permite `queue:work`, usa este comando alternativo:

```bash
cd /home/tu_usuario/public_html && /usr/bin/php artisan queue:process-batch
```

Para esto, vamos a crear un comando personalizado que procesa un lote de trabajos.

### Ventajas de esta configuración:

✅ Compatible con hosting compartido
✅ No consume recursos cuando no hay trabajos
✅ Reinicio automático cada minuto
✅ No supera límites de tiempo de ejecución del servidor

### Desventajas:

❌ Puede haber hasta 1 minuto de retraso entre que se crea el trabajo y se procesa
❌ Si hay muchos trabajos, pueden tomar varios ciclos

## Para Desarrollo Local (XAMPP)

Mientras desarrollas en local, puedes ejecutar:

```bash
php artisan queue:work
```

Mantenlo corriendo en una terminal mientras pruebas.

## Para Producción en VPS/Servidor Dedicado

Si en el futuro usas un VPS o servidor dedicado, usa **Supervisor**:

```ini
[program:mexico-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/html/storage/logs/worker.log
```

## Verificación

Para verificar que los trabajos se están procesando en Hostinger:

1. Revisa la tabla `jobs` en phpMyAdmin:
   ```sql
   SELECT COUNT(*) as pendientes FROM jobs;
   ```

2. Revisa la tabla `failed_jobs` para ver errores:
   ```sql
   SELECT * FROM failed_jobs ORDER BY failed_at DESC LIMIT 10;
   ```

3. Revisa los logs en `storage/logs/laravel.log`
