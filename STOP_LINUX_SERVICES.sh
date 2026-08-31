#!/bin/bash
# ====================================================================
# Payate CC - Stop Linux Services
# ====================================================================

echo "Stopping all Linux services for CCC Project..."

pkill -f "artisan serve" 2>/dev/null
pkill -f "telegram_admin_bot.pyw" 2>/dev/null
pkill -f "telegram_support_bot.pyw" 2>/dev/null
pkill -f "auto_git_sync.py" 2>/dev/null

echo "All Laravel and Python bot processes stopped."
