import os
import sys

# Ensure UTF-8 output encoding on Windows
try:
    if sys.stdout is not None:
        sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    if sys.stderr is not None:
        sys.stderr.reconfigure(encoding='utf-8', errors='replace')
except Exception:
    pass

import time
import subprocess
import threading
import datetime
import sqlite3
import requests

PROJECT_DIR = r"C:\Users\hp\Desktop\ccc"
DB_PATH = os.path.join(PROJECT_DIR, "database", "database.sqlite")
DEFAULT_BOT_TOKEN = "8615399993:AAEwJGBH7EMQK88sNQzmF1ExNp_tQU1sMVs"
DEFAULT_ADMIN_CHAT_ID = "8814743492"

def get_tg_credentials():
    try:
        if os.path.exists(DB_PATH):
            conn = sqlite3.connect(DB_PATH, timeout=5)
            c = conn.cursor()
            c.execute("SELECT telegram_bot_token, telegram_chat_id FROM crypto_settings WHERE id = 1")
            row = c.fetchone()
            conn.close()
            if row:
                tok = row[0] or DEFAULT_BOT_TOKEN
                cid = row[1] or DEFAULT_ADMIN_CHAT_ID
                return tok.strip(), str(cid).strip()
    except Exception:
        pass
    return DEFAULT_BOT_TOKEN, DEFAULT_ADMIN_CHAT_ID

def send_tg_notification(text):
    bot_token, chat_id = get_tg_credentials()
    if not bot_token or not chat_id:
        return
    url = f"https://api.telegram.org/bot{bot_token}/sendMessage"
    payload = {
        "chat_id": chat_id,
        "text": text,
        "parse_mode": "HTML",
        "disable_web_page_preview": True
    }
    # Try SOCKS5 first, then direct
    try:
        requests.post(url, json=payload, proxies={"http": "socks5h://127.0.0.1:9050", "https": "socks5h://127.0.0.1:9050"}, timeout=10)
    except Exception:
        try:
            requests.post(url, json=payload, timeout=10)
        except Exception:
            pass

def run_git_cmd(cmd_list, cwd=PROJECT_DIR, timeout=60):
    try:
        res = subprocess.run(
            cmd_list,
            cwd=cwd,
            capture_output=True,
            text=True,
            creationflags=0x08000000,  # CREATE_NO_WINDOW
            timeout=timeout
        )
        return res.returncode == 0, res.stdout.strip(), res.stderr.strip()
    except Exception as e:
        return False, "", str(e)

def get_git_status_info():
    """Returns (has_changes, changed_files_list, current_branch, last_commit)"""
    ok, stdout, _ = run_git_cmd(["git", "status", "--porcelain"])
    changed_files = []
    if ok and stdout:
        for line in stdout.splitlines():
            line = line.strip()
            if line:
                changed_files.append(line)

    ok_b, stdout_b, _ = run_git_cmd(["git", "branch", "--show-current"])
    branch = stdout_b if ok_b and stdout_b else "main"

    ok_c, stdout_c, _ = run_git_cmd(["git", "log", "-1", "--format=%h - %s (%cr)"])
    last_commit = stdout_c if ok_c and stdout_c else "N/A"

    return len(changed_files) > 0, changed_files, branch, last_commit

def sync_and_push_now(custom_commit_msg=None, notify_telegram=True):
    """Stages all changes, creates a commit, and pushes to origin main."""
    has_changes, changed_files, branch, _ = get_git_status_info()
    
    if not has_changes:
        return True, "Repository is already clean. No changes to commit."

    # Stage all changes
    ok_add, _, err_add = run_git_cmd(["git", "add", "-A"])
    if not ok_add:
        return False, f"Git Add Failed: {err_add}"

    # Build commit message
    now_str = datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S")
    if custom_commit_msg:
        commit_msg = f"{custom_commit_msg} [{now_str}]"
    else:
        file_summary = ", ".join([f.split()[-1] for f in changed_files[:3]])
        if len(changed_files) > 3:
            file_summary += f" (+{len(changed_files)-3} more)"
        commit_msg = f"Auto sync: {file_summary} [{now_str}]"

    ok_commit, out_commit, err_commit = run_git_cmd(["git", "commit", "-m", commit_msg])
    if not ok_commit and "nothing to commit" not in (out_commit + err_commit).lower():
        return False, f"Git Commit Failed: {err_commit}"

    # Push to origin
    ok_push, out_push, err_push = run_git_cmd(["git", "push", "origin", branch], timeout=90)
    if not ok_push:
        return False, f"Git Push Failed: {err_push or out_push}"

    success_msg = (
        f"🚀 <b>[GITHUB AUTO-SYNC SUCCESS]</b>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"📦 <b>Branch:</b> <code>{branch}</code>\n"
        f"📝 <b>Commit:</b> <code>{commit_msg}</code>\n"
        f"📁 <b>Files Synced ({len(changed_files)}):</b>\n"
        + "\n".join([f"• <code>{f}</code>" for f in changed_files[:6]])
        + (f"\n<i>...and {len(changed_files)-6} more files</i>" if len(changed_files) > 6 else "")
        + f"\n━━━━━━━━━━━━━━━━━━━━\n"
        f"⏰ <b>Pushed At:</b> {now_str} UTC"
    )

    if notify_telegram:
        send_tg_notification(success_msg)

    return True, f"Successfully committed & pushed {len(changed_files)} file(s) to GitHub {branch}!"

# ----------------------------------------------------------------------
# BACKGROUND WATCHER LOOP WITH DEBOUNCE
# ----------------------------------------------------------------------
_sync_loop_running = False

def run_auto_sync_loop(interval_seconds=10, debounce_seconds=6):
    """Continuously monitors workspace for changes and pushes automatically."""
    global _sync_loop_running
    if _sync_loop_running:
        return
    _sync_loop_running = True

    print(f"[+] Auto Git Sync Engine Active (Watching {PROJECT_DIR})")
    last_change_detected = None

    while _sync_loop_running:
        try:
            has_changes, changed_files, _, _ = get_git_status_info()
            now = time.time()

            if has_changes:
                if last_change_detected is None:
                    last_change_detected = now
                elif (now - last_change_detected) >= debounce_seconds:
                    # Debounce period passed, perform commit & push
                    print(f"[*] Detected changes in {len(changed_files)} file(s). Auto-pushing to GitHub...")
                    ok, res = sync_and_push_now()
                    print(f"[*] Git Push Result: {res}")
                    last_change_detected = None
            else:
                last_change_detected = None

        except Exception as e:
            print(f"[!] Auto Git Sync Error: {e}")

        time.sleep(interval_seconds)

def start_background_git_sync_thread():
    t = threading.Thread(target=run_auto_sync_loop, daemon=True)
    t.start()
    return t

if __name__ == "__main__":
    import ctypes
    try:
        # Enforce single instance for standalone runner
        mutex = ctypes.windll.kernel32.CreateMutexW(None, False, "Global\\PayateAutoGitSyncMutex")
        if ctypes.windll.kernel32.GetLastError() == 183:
            sys.exit(0)
    except Exception:
        pass

    print("==================================================")
    print("[+] PAYATE CC - AUTOMATIC GITHUB SYNC ENGINE")
    print("==================================================")
    
    # Run immediate check & push if pending changes exist
    has_ch, files, b, _ = get_git_status_info()
    if has_ch:
        print(f"[+] Found {len(files)} unstaged/uncommitted change(s). Pushing to GitHub now...")
        ok, msg = sync_and_push_now(notify_telegram=True)
        print(f"[+] Result: {msg}")
    
    # Start continuous loop
    run_auto_sync_loop()
