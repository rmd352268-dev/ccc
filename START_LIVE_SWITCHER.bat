@echo off
title Launch Server & Bot Controller Switcher
cd /d "C:\Users\hp\Desktop\ccc"

if exist "C:\Program Files\Python314\pythonw.exe" (
    start "" "C:\Program Files\Python314\pythonw.exe" server_manager.pyw
) else (
    start "" pythonw.exe server_manager.pyw
)
exit
