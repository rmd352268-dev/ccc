Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c tasklist /FI ""IMAGENAME eq tor.exe"" | find /I ""tor.exe"" || C:\Users\hp\tor_service\tor\tor.exe -f C:\Users\hp\tor_service\torrc", 0, False
WshShell.Run "cmd /c cd /d C:\Users\hp\Desktop\ccc && tasklist /FI ""IMAGENAME eq php.exe"" | find /I ""php.exe"" || C:\Users\hp\php\php.exe -S 127.0.0.1:8000 -t public", 0, False
WshShell.Run "cmd /c cd /d C:\Users\hp\Desktop\ccc && ""C:\Program Files\Python314\pythonw.exe"" telegram_admin_bot.pyw", 0, False
WshShell.Run "cmd /c cd /d C:\Users\hp\Desktop\ccc && ""C:\Program Files\Python314\pythonw.exe"" auto_git_sync.py", 0, False
