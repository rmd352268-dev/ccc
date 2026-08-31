@echo off
:: Batch script to install Cloudflare Tunnel as a permanent Windows Service (Run as Administrator)
echo Installing Cloudflare Tunnel as Windows Service...
"C:\Users\hp\cloudflared.exe" service install eyJhIjoiN2M1YjJmZGZmZmNjZWEzYmFhOTg3YjUzZGFlYmE4MDQiLCJ0IjoiYjc0MTE3ODYtN2MzNy00Njk5LWI0ZjEtYjcyMTExYWY1ZDBlIiwicyI6Ik1qZzNNV1prWXpZdE1HWmpPQzAwWmpnd0xXRXpNalV0TVdSalltSmtNalkzWW1JNSJ9
echo.
echo Service Installed successfully! Starting service...
net start cloudflared
pause
