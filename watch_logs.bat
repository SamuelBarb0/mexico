@echo off
echo ========================================
echo   Monitoreando logs de importacion
echo   Presiona Ctrl+C para salir
echo ========================================
echo.

powershell -Command "Get-Content -Path 'storage\logs\laravel.log' -Wait -Tail 50"
