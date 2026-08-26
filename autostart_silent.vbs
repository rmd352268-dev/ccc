Set WshShell = CreateObject("WScript.Shell")

' Start Tor if not running
WshShell.Run "cmd /c tasklist /FI ""IMAGENAME eq tor.exe"" | find /I ""tor.exe"" || C:\Users\hp\tor_service\tor\tor.exe -f C:\Users\hp\tor_service\torrc", 0, False

' Start Laravel if not running
WshShell.Run "cmd /c cd /d C:\Users\hp\Desktop\ccc && tasklist /FI ""IMAGENAME eq php.exe"" | find /I ""php.exe"" || php artisan serve --host=127.0.0.1 --port=8000", 0, False
