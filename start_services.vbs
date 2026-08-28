Set WshShell = CreateObject("WScript.Shell")
WshShell.CurrentDirectory = "C:\Users\hp\Desktop\ccc"

' 1. Start Tor if not already running
WshShell.Run "cmd /c tasklist /FI ""IMAGENAME eq tor.exe"" | find /I ""tor.exe"" || C:\Users\hp\tor_service\tor\tor.exe -f C:\Users\hp\tor_service\torrc", 0, False

' 2. Start PHP Built-in Server if not already running
WshShell.Run "cmd /c tasklist /FI ""IMAGENAME eq php.exe"" | find /I ""php.exe"" || C:\Users\hp\php\php.exe -S 127.0.0.1:8000 -t public", 0, False

' 3. Start Python Daemons (Admin Bot, Support Bot, Auto Git Sync)
WshShell.Run """C:\Program Files\Python314\pythonw.exe"" C:\Users\hp\Desktop\ccc\telegram_admin_bot.pyw", 0, False
WshShell.Run """C:\Program Files\Python314\pythonw.exe"" C:\Users\hp\Desktop\ccc\telegram_support_bot.pyw", 0, False
WshShell.Run """C:\Program Files\Python314\pythonw.exe"" C:\Users\hp\Desktop\ccc\auto_git_sync.py", 0, False


