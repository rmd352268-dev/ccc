@echo off
title Cloudflare Free Quick Tunnel
color 0A
echo ========================================================
echo   Starting Cloudflare Free Tunnel (No Domain Required)
echo   Local Port: http://127.0.0.1:8000
echo ========================================================
echo.
echo Please wait, generating your free live HTTPS link...
echo.
"C:\Users\hp\cloudflared.exe" tunnel --url http://127.0.0.1:8000
pause
