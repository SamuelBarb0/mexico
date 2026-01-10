Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c cd /d ""c:\xampp\htdocs\mexico"" && php artisan queue:work --sleep=3 --tries=3 --timeout=60", 0, False
Set WshShell = Nothing
