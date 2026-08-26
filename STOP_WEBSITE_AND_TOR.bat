@echo off
title Stop Laravel and Tor
echo Stopping PHP and Tor processes...
taskkill /F /IM tor.exe /T 2>NUL
taskkill /F /IM php.exe /T 2>NUL
echo Successfully stopped!
pause
