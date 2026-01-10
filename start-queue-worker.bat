@echo off
echo Iniciando Queue Worker para WhatsApp Campaigns...
echo.
echo Este proceso debe mantenerse abierto para procesar mensajes.
echo Presiona Ctrl+C para detener.
echo.

php artisan queue:work --sleep=3 --tries=3 --timeout=60

pause
