# Instrucciones para el Queue Worker

El sistema de campañas de WhatsApp utiliza Laravel Queue para procesar el envío de mensajes en segundo plano.

## Opciones para ejecutar el Queue Worker

### Opción 1: Modo Visible (Recomendado para desarrollo)

Ejecuta el archivo `start-queue-worker.bat`:

```bash
# Doble clic en el archivo o ejecuta desde cmd:
start-queue-worker.bat
```

Este método abre una ventana de comandos que muestra el progreso en tiempo real. Útil para desarrollo y debugging.

**Ventajas:**
- Ves los trabajos procesándose en tiempo real
- Ves errores inmediatamente
- Fácil de detener (Ctrl+C)

### Opción 2: Modo Silencioso (Para producción local)

Ejecuta el archivo `start-queue-background.vbs`:

```bash
# Doble clic en el archivo
start-queue-background.vbs
```

Este método ejecuta el worker en segundo plano sin ventana visible.

**Ventajas:**
- No ocupa espacio en pantalla
- Se ejecuta silenciosamente

**Para detenerlo:**
- Abre el Administrador de Tareas
- Busca el proceso "php.exe" que ejecuta queue:work
- Finaliza el proceso

### Opción 3: Manual (Desde terminal)

```bash
php artisan queue:work --sleep=3 --tries=3 --timeout=60
```

### Opción 4: Comando personalizado

```bash
php artisan queue:process
```

## Parámetros del Queue Worker

- `--sleep=3`: Espera 3 segundos cuando no hay trabajos
- `--tries=3`: Intenta cada trabajo hasta 3 veces antes de marcarlo como fallido
- `--timeout=60`: Tiempo máximo de ejecución por trabajo (60 segundos)

## Verificar que está funcionando

1. Crea una campaña desde la UI
2. Prepara la campaña (botón amarillo "Preparar Campaña")
3. Ejecuta la campaña (botón verde "Ejecutar Campaña")
4. Si el queue worker está corriendo, verás los mensajes procesándose inmediatamente

## Logs

Los logs del queue worker se guardan en:
- `storage/logs/laravel.log`

## Para Producción (Servidor Linux)

En producción, se recomienda usar **Supervisor** para mantener el queue worker corriendo:

```ini
[program:mexico-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /ruta/a/mexico/artisan queue:work --sleep=3 --tries=3 --timeout=60
autostart=true
autorestart=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/ruta/a/mexico/storage/logs/worker.log
```

## Troubleshooting

### El worker no procesa trabajos

1. Verifica que el worker esté corriendo:
   ```bash
   # Windows: Busca php.exe en el Administrador de Tareas
   # Linux: ps aux | grep queue:work
   ```

2. Verifica la tabla `jobs` en la base de datos:
   ```sql
   SELECT * FROM jobs ORDER BY id DESC LIMIT 10;
   ```

3. Verifica la tabla `failed_jobs`:
   ```sql
   SELECT * FROM failed_jobs ORDER BY id DESC LIMIT 10;
   ```

### Los trabajos fallan constantemente

1. Revisa los logs: `storage/logs/laravel.log`
2. Verifica las credenciales de WhatsApp API en `.env`
3. Verifica que el WABA Account esté activo

### El worker se detiene solo

1. Asegúrate de que no haya errores fatales en el código
2. Verifica la memoria disponible
3. En producción, usa Supervisor para auto-reinicio
