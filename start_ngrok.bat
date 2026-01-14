@echo off
cls
echo ========================================
echo   NGROK - Tunel para WhatsApp Webhook
echo ========================================
echo.
echo Este script creara un tunel HTTPS para
echo que Meta WhatsApp pueda enviar webhooks
echo a tu aplicacion local.
echo.
echo ========================================
echo   INFORMACION IMPORTANTE
echo ========================================
echo.
echo 1. El tunel se creara en:
echo    http://localhost:80
echo.
echo 2. Una vez iniciado, veras una URL HTTPS:
echo    https://XXXXXXXX.ngrok-free.app
echo.
echo 3. COPIA esa URL y agregale al final:
echo    /mexico/public/api/webhooks/meta
echo.
echo 4. Configurala en Meta Developers:
echo    https://developers.facebook.com/apps
echo.
echo 5. Dashboard de ngrok (ver peticiones):
echo    http://127.0.0.1:4040
echo.
echo 6. Verify Token:
echo    mexico_whatsapp_token
echo.
echo ========================================
echo.
pause
echo.
echo Iniciando ngrok...
echo.

ngrok http 80 --host-header="localhost"
