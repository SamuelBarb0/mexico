@echo off
echo ============================================
echo   Configuracion de hosts para WhatsApp SaaS
echo ============================================
echo.

:: Verificar si se ejecuta como administrador
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo [ERROR] Este script debe ejecutarse como Administrador.
    echo.
    echo Por favor, haz clic derecho en este archivo y selecciona
    echo "Ejecutar como administrador"
    echo.
    pause
    exit /b 1
)

echo Agregando entradas al archivo hosts...
echo.

:: Agregar las entradas al archivo hosts
echo. >> C:\Windows\System32\drivers\etc\hosts
echo # WhatsApp SaaS - Agregado automaticamente >> C:\Windows\System32\drivers\etc\hosts
echo 127.0.0.1       whatsapp.local >> C:\Windows\System32\drivers\etc\hosts
echo 127.0.0.1       api.whatsapp.local >> C:\Windows\System32\drivers\etc\hosts

echo [OK] Entradas agregadas exitosamente!
echo.
echo Ahora puedes acceder a:
echo   - http://whatsapp.local          (Aplicacion principal)
echo   - http://whatsapp.local/api-docs (Documentacion API)
echo   - http://api.whatsapp.local      (API directamente)
echo.
echo IMPORTANTE: Reinicia Apache desde XAMPP Control Panel
echo.
pause
