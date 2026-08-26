import os
import sys
import time
import socket
import sqlite3
import datetime
import threading
import subprocess
import re
import json
import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry
import bcrypt

# Auto Git Sync Engine Integration
try:
    from auto_git_sync import get_git_status_info, sync_and_push_now, start_background_git_sync_thread
except Exception:
    pass

# Ensure output encoding is safe when running under pythonw
try:
    if sys.stdout is None:
        sys.stdout = open(os.devnull, 'w', encoding='utf-8')
    else:
        sys.stdout.reconfigure(encoding='utf-8', errors='replace')
    if sys.stderr is None:
        sys.stderr = open(os.devnull, 'w', encoding='utf-8')
    else:
        sys.stderr.reconfigure(encoding='utf-8', errors='replace')
except Exception:
    pass

# ----------------------------------------------------------------------
# CONFIGURATION & CREDENTIALS
# ----------------------------------------------------------------------
BOT_TOKEN = "8615399993:AAEwJGBH7EMQK88sNQzmF1ExNp_tQU1sMVs"
ADMIN_CHAT_ID = "8814743492"
EMERGENCY_PIN = "1713163761"

PROJECT_DIR = r"C:\Users\hp\Desktop\ccc"
DB_PATH = os.path.join(PROJECT_DIR, "database", "database.sqlite")
TOR_EXE = r"C:\Users\hp\tor_service\tor\tor.exe"
TOR_RC = r"C:\Users\hp\tor_service\torrc"
TOR_SERVICE_DIR = r"C:\Users\hp\tor_service"
TOR_SOCKS_PROXY = "socks5h://127.0.0.1:9050"

TELEGRAM_API_URL = f"https://api.telegram.org/bot{BOT_TOKEN}"
TELEGRAM_FILE_URL = f"https://api.telegram.org/file/bot{BOT_TOKEN}"

# Interactive In-Memory Conversation State Store
ADMIN_STATE = {}

# ----------------------------------------------------------------------
# SINGLETON INSTANCE LOCK (WINDOWS NATIVE NAMED MUTEX)
# ----------------------------------------------------------------------
_bot_mutex = None

def enforce_single_instance():
    global _bot_mutex
    import ctypes
    _bot_mutex = ctypes.windll.kernel32.CreateMutexW(None, False, "Global\\PayateAdminTelegramBotMutex")
    if ctypes.windll.kernel32.GetLastError() == 183:  # ERROR_ALREADY_EXISTS
        os._exit(0)
    return True

# ----------------------------------------------------------------------
# HTTP SESSIONS WITH RESILIENT SOCKS5 / DIRECT RETRIES
# ----------------------------------------------------------------------
http_session = requests.Session()
adapter = HTTPAdapter(
    pool_connections=25,
    pool_maxsize=25,
    max_retries=Retry(total=2, backoff_factor=0.2, status_forcelist=[500, 502, 503, 504])
)
http_session.mount("https://", adapter)
http_session.mount("http://", adapter)
http_session.proxies = {
    "http": TOR_SOCKS_PROXY,
    "https": TOR_SOCKS_PROXY
}

# Direct session fallback if Tor SOCKS proxy is restarting
direct_session = requests.Session()
direct_session.mount("https://", adapter)
direct_session.mount("http://", adapter)

def tg_request(method, payload=None, timeout=15):
    url = f"{TELEGRAM_API_URL}/{method}"
    try:
        res = http_session.post(url, json=payload or {}, timeout=timeout)
        return res.json()
    except Exception:
        try:
            res = direct_session.post(url, json=payload or {}, timeout=timeout)
            return res.json()
        except Exception as e:
            return {"ok": False, "error": str(e)}

def download_tg_file(file_path, timeout=30):
    url = f"{TELEGRAM_FILE_URL}/{file_path}"
    try:
        res = http_session.get(url, timeout=timeout)
        if res.status_code == 200:
            return res.content
    except Exception:
        pass
    try:
        res = direct_session.get(url, timeout=timeout)
        if res.status_code == 200:
            return res.content
    except Exception:
        pass
    return None

def send_admin_msg(text, reply_markup=None):
    if len(text) > 4000:
        chunks = [text[i:i+3800] for i in range(0, len(text), 3800)]
        for i, chunk in enumerate(chunks):
            markup = reply_markup if i == len(chunks) - 1 else None
            tg_request("sendMessage", {
                "chat_id": ADMIN_CHAT_ID,
                "text": chunk,
                "parse_mode": "HTML",
                "disable_web_page_preview": True,
                "reply_markup": markup
            })
        return {"ok": True}

    payload = {
        "chat_id": ADMIN_CHAT_ID,
        "text": text,
        "parse_mode": "HTML",
        "disable_web_page_preview": True
    }
    if reply_markup:
        payload["reply_markup"] = reply_markup
    return tg_request("sendMessage", payload)

def edit_admin_msg(chat_id, message_id, text, reply_markup=None):
    payload = {
        "chat_id": chat_id,
        "message_id": message_id,
        "text": text,
        "parse_mode": "HTML",
        "disable_web_page_preview": True
    }
    if reply_markup:
        payload["reply_markup"] = reply_markup
    res = tg_request("editMessageText", payload)
    if not res.get("ok") and "message is not modified" in str(res.get("description", "")):
        return {"ok": True, "description": "Message unmodified"}
    return res

def answer_callback(callback_query_id, text="", show_alert=False):
    return tg_request("answerCallbackQuery", {
        "callback_query_id": callback_query_id,
        "text": text,
        "show_alert": show_alert
    }, timeout=5)

# ----------------------------------------------------------------------
# DATABASE CONNECTION & SECURITY HELPERS
# ----------------------------------------------------------------------
def get_db():
    conn = sqlite3.connect(DB_PATH, timeout=15)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA busy_timeout = 10000")
    return conn

def hash_password(raw_pass):
    hashed = bcrypt.hashpw(raw_pass.encode("utf-8"), bcrypt.gensalt(rounds=12)).decode("utf-8")
    if hashed.startswith("$2b$"):
        hashed = "$2y$" + hashed[4:]
    return hashed

def get_now_utc():
    return datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%d %H:%M:%S")


# ----------------------------------------------------------------------
# PERSISTENT 4-DOT REPLY KEYBOARD ENGINE
# ----------------------------------------------------------------------
def get_persistent_reply_keyboard():
    default_keyboard = [
        [{"text": "🚀 /start"}, {"text": "📊 Live Status"}],
        [{"text": "💰 Pending Deposits"}, {"text": "👥 User Management"}],
        [{"text": "💳 Cards Vault & Import"}, {"text": "🎫 Support Tickets"}],
        [{"text": "📢 News Feed"}, {"text": "⚙️ Crypto Settings"}],
        [{"text": "📦 Wholesale Packs"}, {"text": "📋 Orders & Sales"}],
        [{"text": "🔄 Git Sync & Push"}, {"text": "⚡ Server Power"}]
    ]
    try:
        conn = get_db()
        c = conn.cursor()
        c.execute("SELECT telegram_custom_buttons FROM crypto_settings WHERE id = 1")
        row = c.fetchone()
        conn.close()
        if row and row[0] and row[0].strip():
            lines = [l.strip() for l in row[0].strip().splitlines() if l.strip()]
            custom_rows = []
            for line in lines:
                cols = [col.strip() for col in line.split("|") if col.strip()]
                if cols:
                    custom_rows.append([{"text": col} for col in cols])
            if custom_rows:
                return {
                    "keyboard": custom_rows,
                    "resize_keyboard": True,
                    "is_persistent": True
                }
    except Exception:
        pass

    return {
        "keyboard": default_keyboard,
        "resize_keyboard": True,
        "is_persistent": True
    }

# ----------------------------------------------------------------------
# SERVER & PROCESS CONTROL
# ----------------------------------------------------------------------
def is_port_listening(host="127.0.0.1", port=8000):
    try:
        with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
            s.settimeout(0.3)
            return s.connect_ex((host, port)) == 0
    except Exception:
        return False

def is_proc_running(name):
    try:
        out = subprocess.check_output(
            f'tasklist /FI "IMAGENAME eq {name}"',
            shell=True,
            creationflags=0x08000000
        ).decode(errors="ignore")
        return name.lower() in out.lower()
    except Exception:
        return False

def start_all_services():
    started = []
    if not is_proc_running("tor.exe"):
        if os.path.exists(TOR_EXE) and os.path.exists(TOR_RC):
            subprocess.Popen([TOR_EXE, "-f", TOR_RC], creationflags=0x08000000)
            started.append("Tor Daemon")
    
    if not is_port_listening("127.0.0.1", 8000):
        subprocess.Popen(
            ["php", "artisan", "serve", "--host=127.0.0.1", "--port=8000"],
            cwd=PROJECT_DIR,
            creationflags=0x08000000
        )
        started.append("Laravel PHP Web Server (Port 8000)")
    
    return started

def stop_all_services():
    try:
        subprocess.run('taskkill /F /IM tor.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
        subprocess.run('taskkill /F /IM php.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
        return True
    except Exception:
        return False

def get_current_onion_domain():
    try:
        with open(os.path.join(PROJECT_DIR, ".env"), "r", encoding="utf-8") as f:
            for line in f:
                if line.startswith("TOR_ONION_ADDRESS="):
                    return line.strip().split("=", 1)[1].strip()
    except Exception:
        pass
    return "http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion"

def generate_new_onion_domain():
    try:
        subprocess.run('taskkill /F /IM tor.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
        time.sleep(1)

        hs_dir = r"C:\Users\hp\tor_service\hidden_service"
        if os.path.exists(hs_dir):
            backup_dir = f"{hs_dir}_backup_{int(time.time())}"
            try:
                os.rename(hs_dir, backup_dir)
            except Exception:
                pass
        
        os.makedirs(hs_dir, exist_ok=True)
        subprocess.Popen([TOR_EXE, "-f", TOR_RC], creationflags=0x08000000)
        
        hostname_path = os.path.join(hs_dir, "hostname")
        new_onion = None
        for _ in range(15):
            time.sleep(1)
            if os.path.exists(hostname_path):
                with open(hostname_path, "r", encoding="utf-8") as f:
                    new_onion = f.read().strip()
                if new_onion:
                    break

        if not new_onion:
            return False, "Tor started but hostname generation timed out."

        env_path = os.path.join(PROJECT_DIR, ".env")
        if os.path.exists(env_path):
            with open(env_path, "r", encoding="utf-8") as f:
                content = f.read()
            
            content = re.sub(r"APP_URL=.*", f"APP_URL=http://{new_onion}", content)
            content = re.sub(r"TOR_ONION_ADDRESS=.*", f"TOR_ONION_ADDRESS={new_onion}", content)
            
            with open(env_path, "w", encoding="utf-8") as f:
                f.write(content)

        return True, f"http://{new_onion}"
    except Exception as e:
        return False, str(e)

# ----------------------------------------------------------------------
# SYSTEM STATS
# ----------------------------------------------------------------------
def get_system_stats():
    php_ok = is_port_listening("127.0.0.1", 8000)
    tor_ok = is_proc_running("tor.exe")
    onion_url = get_current_onion_domain()

    users_count = "0"
    pending_dep = "0"
    completed_dep_sum = "$0.00"
    orders_count = "0"
    cards_count = "0"
    open_tickets = "0"

    try:
        conn = get_db()
        c = conn.cursor()
        users_count = str(c.execute("SELECT COUNT(id) FROM users").fetchone()[0])
        pending_dep = str(c.execute("SELECT COUNT(id) FROM deposits WHERE status = 'pending'").fetchone()[0])
        sum_val = c.execute("SELECT SUM(amount) FROM deposits WHERE status = 'completed'").fetchone()[0]
        completed_dep_sum = f"${float(sum_val or 0):,.2f}"
        orders_count = str(c.execute("SELECT COUNT(id) FROM orders").fetchone()[0])
        cards_count = str(c.execute("SELECT COUNT(id) FROM cards WHERE status = 'available'").fetchone()[0])
        open_tickets = str(c.execute("SELECT COUNT(id) FROM tickets WHERE status != 'closed'").fetchone()[0])
        conn.close()
    except Exception:
        pass

    status_icon = "🟢 LIVE & ONLINE" if (php_ok and tor_ok) else ("🟡 PARTIAL" if (php_ok or tor_ok) else "🔴 OFFLINE")
    
    msg = (
        f"⚡ <b>[PAYATE CC SYSTEM MONITOR]</b>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"📊 <b>Server Status:</b> {status_icon}\n"
        f"🌐 <b>Laravel Web (8000):</b> {'🟢 Active' if php_ok else '🔴 Stopped'}\n"
        f"🧅 <b>Tor Network:</b> {'🟢 Connected' if tor_ok else '🔴 Offline'}\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"👥 <b>Total Users:</b> <code>{users_count}</code>\n"
        f"⏳ <b>Pending Deposits:</b> <code>{pending_dep}</code>\n"
        f"💰 <b>Total Recharged:</b> <code>{completed_dep_sum}</code>\n"
        f"💳 <b>Cards in Vault:</b> <code>{cards_count}</code>\n"
        f"📦 <b>Total Orders:</b> <code>{orders_count}</code>\n"
        f"🎫 <b>Open Tickets:</b> <code>{open_tickets}</code>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"🧅 <b>Onion Domain:</b>\n<code>{onion_url}</code>\n"
        f"⏰ <b>Checked:</b> {datetime.datetime.now().strftime('%Y-%m-%d %H:%M:%S')}"
    )
    return msg

# ----------------------------------------------------------------------
# 1. AUTO-UPLOAD & CARDS IMPORT ENGINE
# ----------------------------------------------------------------------
def parse_card_line(line, default_base="TELEGRAM_IMPORT", default_price=2.50):
    line = line.strip()
    if not line:
        return None

    delimiter = '|'
    if '|' in line:
        delimiter = '|'
    elif ';' in line:
        delimiter = ';'
    elif '\t' in line:
        delimiter = '\t'
    elif len(line.split(',')) >= 3:
        delimiter = ','

    raw_parts = line.split(delimiter)
    parts = [p.strip(" \t\n\r\"'|") for p in raw_parts if p.strip(" \t\n\r\"'|") != '']
    if not parts:
        return None

    card_num = re.sub(r'\D', '', parts[0])
    if len(card_num) < 12 or len(card_num) > 19:
        return None

    bin_num = card_num[:6]

    brand = "VISA"
    if card_num.startswith("4"):
        brand = "VISA"
    elif re.match(r'^(5[1-5]|2[2-7])', card_num):
        brand = "MASTERCARD"
    elif re.match(r'^3[47]', card_num):
        brand = "AMEX"
    elif re.match(r'^(6011|65|64[4-9])', card_num):
        brand = "DISCOVER"
    elif card_num.startswith("35"):
        brand = "JCB"

    exp_date = "12/28"
    cvv = "000"
    next_idx = 1

    if len(parts) > 1 and re.match(r'^(\d{1,2})[\/\-\.](\d{2,4})$', parts[1]):
        m_match = re.match(r'^(\d{1,2})[\/\-\.](\d{2,4})$', parts[1])
        m = m_match.group(1).zfill(2)
        y = m_match.group(2)[-2:]
        exp_date = f"{m}/{y}"
        cvv = re.sub(r'\D', '', parts[2]) if len(parts) > 2 else "000"
        next_idx = 3
    elif len(parts) > 2 and parts[1].isdigit() and 1 <= int(parts[1]) <= 12 and parts[2].isdigit() and len(parts[2]) in [2, 4]:
        m = parts[1].zfill(2)
        y = parts[2][-2:]
        exp_date = f"{m}/{y}"
        cvv = re.sub(r'\D', '', parts[3]) if len(parts) > 3 else "000"
        next_idx = 4
    elif len(parts) > 1:
        exp_date = parts[1]
        cvv = re.sub(r'\D', '', parts[2]) if len(parts) > 2 else "000"
        next_idx = 3

    if len(cvv) > 4:
        cvv = cvv[:4]
    if not cvv:
        cvv = "000"

    holder_name = parts[next_idx] if len(parts) > next_idx else "Customer"
    address = parts[next_idx + 1] if len(parts) > next_idx + 1 else "Main St"
    city = parts[next_idx + 2] if len(parts) > next_idx + 2 else "City"
    state = parts[next_idx + 3] if len(parts) > next_idx + 3 else "ST"
    zip_code = parts[next_idx + 4] if len(parts) > next_idx + 4 else "10001"
    
    country_code = "US"
    country_name = "United States"
    bank = "CHASE BANK, N.A."
    phone = ""
    email = ""

    for p in parts[next_idx:]:
        val = p.strip()
        if not val or val.lower() in ['n', 'n/a']:
            continue
        if "@" in val and "." in val:
            email = val
        elif len(val) == 2 and val.isalpha() and val.isupper():
            country_code = val
            country_name = "United States" if val == "US" else ("United Kingdom" if val == "GB" else val)
        elif re.match(r'^\+?\d[\d\s\-\(\)]{7,}\d$', val):
            phone = val
        elif "bank" in val.lower() or "federal" in val.lower() or "cu" in val.lower():
            bank = val.upper()

    now_str = get_now_utc()
    return {
        "bin": bin_num,
        "brand": brand,
        "type": "CREDIT",
        "country_code": country_code,
        "country_name": country_name,
        "has_name": 1 if (holder_name and holder_name.lower() not in ['customer', 'n']) else 0,
        "has_address": 1 if (address and address.lower() not in ['main st', 'n']) else 0,
        "has_zip": 1 if (zip_code and zip_code != 'n') else 0,
        "has_phone": 1 if phone else 0,
        "has_mail": 1 if email else 0,
        "has_ssn": 0,
        "has_dob": 0,
        "has_user_agent": 0,
        "has_email_password": 0,
        "bank": bank,
        "base_name": default_base,
        "refundable": 0,
        "price_c": default_price,
        "price_unc": default_price,
        "card_number": card_num,
        "exp_date": exp_date,
        "cvv": cvv,
        "holder_name": holder_name if holder_name != 'n' else 'Customer',
        "address": address if address != 'n' else 'Main St',
        "city": city if city != 'n' else 'City',
        "state": state if state != 'n' else 'ST',
        "zip": zip_code if zip_code != 'n' else '10001',
        "phone": phone,
        "email": email,
        "user_agent": None,
        "email_password": None,
        "status": "available",
        "created_at": now_str,
        "updated_at": now_str,
        "is_super_shop": 0
    }

def import_cards_from_text(raw_text, base_name="TELEGRAM_AUTO_IMPORT", default_price=2.50):
    lines = raw_text.strip().splitlines()
    if not lines:
        return 0, {}, {}, 0

    parsed_cards = []
    brand_counts = {}
    country_counts = {}

    for line in lines:
        card = parse_card_line(line, default_base=base_name, default_price=default_price)
        if card:
            parsed_cards.append(card)
            b = card["brand"]
            c = card["country_code"]
            brand_counts[b] = brand_counts.get(b, 0) + 1
            country_counts[c] = country_counts.get(c, 0) + 1

    if not parsed_cards:
        return 0, {}, {}, 0

    conn = get_db()
    cursor = conn.cursor()
    
    insert_sql = """
        INSERT INTO cards (
            bin, brand, type, country_code, country_name, has_name, has_address,
            has_zip, has_phone, has_mail, has_ssn, has_dob, has_user_agent,
            has_email_password, bank, base_name, refundable, price_c, price_unc,
            card_number, exp_date, cvv, holder_name, address, city, state, zip,
            phone, email, user_agent, email_password, status, created_at, updated_at,
            is_super_shop
        ) VALUES (
            :bin, :brand, :type, :country_code, :country_name, :has_name, :has_address,
            :has_zip, :has_phone, :has_mail, :has_ssn, :has_dob, :has_user_agent,
            :has_email_password, :bank, :base_name, :refundable, :price_c, :price_unc,
            :card_number, :exp_date, :cvv, :holder_name, :address, :city, :state, :zip,
            :phone, :email, :user_agent, :email_password, :status, :created_at, :updated_at,
            :is_super_shop
        )
    """
    
    cursor.executemany(insert_sql, parsed_cards)
    conn.commit()
    
    total_vault = cursor.execute("SELECT COUNT(id) FROM cards WHERE status = 'available'").fetchone()[0]
    conn.close()

    return len(parsed_cards), brand_counts, country_counts, total_vault

# ----------------------------------------------------------------------
# 2. USER MANAGEMENT ACTIONS
# ----------------------------------------------------------------------
def get_user_by_name_or_id(identifier):
    conn = get_db()
    c = conn.cursor()
    if str(identifier).isdigit():
        c.execute("SELECT * FROM users WHERE id = ?", (int(identifier),))
    else:
        username = str(identifier).lstrip("@").strip()
        c.execute("SELECT * FROM users WHERE username = ? COLLATE NOCASE", (username,))
    user = c.fetchone()
    conn.close()
    return user

def list_users_data(page=1, per_page=8):
    conn = get_db()
    c = conn.cursor()
    offset = (page - 1) * per_page
    total = c.execute("SELECT COUNT(id) FROM users").fetchone()[0]
    c.execute("SELECT * FROM users ORDER BY id DESC LIMIT ? OFFSET ?", (per_page, offset))
    users = c.fetchall()
    conn.close()
    return users, total

def search_users_data(query):
    conn = get_db()
    c = conn.cursor()
    q = f"%{query.strip()}%"
    c.execute("SELECT * FROM users WHERE username LIKE ? OR email LIKE ? OR phone LIKE ? LIMIT 15", (q, q, q))
    users = c.fetchall()
    conn.close()
    return users

def update_user_balance_action(username, amount, mode="add"):
    conn = get_db()
    c = conn.cursor()
    username = username.lstrip("@").strip()
    c.execute("SELECT * FROM users WHERE username = ?", (username,))
    user = c.fetchone()
    if not user:
        conn.close()
        return False, f"User @{username} not found."

    old_bal = float(user["balance"] or 0.0)
    now_str = get_now_utc()

    if mode == "add":
        new_bal = old_bal + float(amount)
        new_tot = float(user["total_recharge"] or 0.0) + float(amount)
        c.execute("UPDATE users SET balance = ?, total_recharge = ?, updated_at = ? WHERE id = ?", (new_bal, new_tot, now_str, user["id"]))
        trx = "DEP-MANUAL-" + os.urandom(4).hex().upper()
        c.execute("""
            INSERT INTO deposits (username, trx_id, currency, amount, address, status, txid, admin_notes, created_at, updated_at)
            VALUES (?, ?, 'USD', ?, 'Telegram Admin Manual Add', 'completed', 'MANUAL_CREDIT', 'Added via Telegram Bot', ?, ?)
        """, (username, trx, float(amount), now_str, now_str))

    elif mode == "deduct":
        deduct_val = float(amount)
        new_bal = max(0.0, old_bal - deduct_val)
        c.execute("UPDATE users SET balance = ?, updated_at = ? WHERE id = ?", (new_bal, now_str, user["id"]))
        trx = "ADJ-" + os.urandom(4).hex().upper()
        c.execute("""
            INSERT INTO deposits (username, trx_id, currency, amount, address, status, txid, admin_notes, created_at, updated_at)
            VALUES (?, ?, 'USD', ?, 'Admin Manual Deduction', 'deducted', 'ADMIN_DEDUCTION', 'Deducted via Telegram Bot', ?, ?)
        """, (username, trx, -deduct_val, now_str, now_str))

    elif mode == "set":
        new_bal = float(amount)
        diff = new_bal - old_bal
        c.execute("UPDATE users SET balance = ?, updated_at = ? WHERE id = ?", (new_bal, now_str, user["id"]))
        if diff != 0:
            trx = "ADJ-" + os.urandom(4).hex().upper()
            c.execute("""
                INSERT INTO deposits (username, trx_id, currency, amount, address, status, txid, admin_notes, created_at, updated_at)
                VALUES (?, ?, 'USD', ?, 'Admin Balance Set', 'completed' if ? > 0 else 'deducted', 'ADMIN_SET', 'Set via Telegram Bot', ?, ?)
            """, (username, trx, diff, diff, now_str, now_str))

    elif mode == "zero":
        new_bal = 0.0
        c.execute("UPDATE users SET balance = 0.00, updated_at = ? WHERE id = ?", (now_str, user["id"]))
        if old_bal > 0:
            trx = "ADJ-" + os.urandom(4).hex().upper()
            c.execute("""
                INSERT INTO deposits (username, trx_id, currency, amount, address, status, txid, admin_notes, created_at, updated_at)
                VALUES (?, ?, 'USD', ?, 'Admin Zero Reset', 'deducted', 'ADMIN_ZERO', 'Reset to $0 via Telegram Bot', ?, ?)
            """, (username, trx, -old_bal, now_str, now_str))

    conn.commit()
    conn.close()
    return True, f"Balance for @{username} updated: ${old_bal:.2f} ➔ <b>${new_bal:.2f}</b>"

def toggle_user_suspend_action(username):
    conn = get_db()
    c = conn.cursor()
    username = username.lstrip("@").strip()
    c.execute("SELECT * FROM users WHERE username = ?", (username,))
    user = c.fetchone()
    if not user:
        conn.close()
        return False, f"User @{username} not found."

    current_status = user["status"] or "active"
    new_status = "banned" if current_status == "active" else "active"
    now_str = get_now_utc()

    c.execute("UPDATE users SET status = ?, updated_at = ? WHERE id = ?", (new_status, now_str, user["id"]))
    conn.commit()
    conn.close()
    return True, f"User @{username} status changed to <b>{new_status.upper()}</b>"

def set_user_password_action(username, new_pass):
    conn = get_db()
    c = conn.cursor()
    username = username.lstrip("@").strip()
    c.execute("SELECT * FROM users WHERE username = ?", (username,))
    user = c.fetchone()
    if not user:
        conn.close()
        return False, f"User @{username} not found."

    hashed = hash_password(new_pass)
    now_str = get_now_utc()
    c.execute("UPDATE users SET password = ?, updated_at = ? WHERE id = ?", (hashed, now_str, user["id"]))
    conn.commit()
    conn.close()
    return True, f"Password for @{username} changed successfully!"

def set_user_pin_action(username, new_pin):
    conn = get_db()
    c = conn.cursor()
    username = username.lstrip("@").strip()
    c.execute("SELECT * FROM users WHERE username = ?", (username,))
    user = c.fetchone()
    if not user:
        conn.close()
        return False, f"User @{username} not found."

    hashed = hash_password(new_pin)
    now_str = get_now_utc()
    c.execute("UPDATE users SET secondary_password = ?, updated_at = ? WHERE id = ?", (hashed, now_str, user["id"]))
    conn.commit()
    conn.close()
    return True, f"Secondary PIN for @{username} updated successfully!"

def set_user_tier_action(username, tier_name):
    conn = get_db()
    c = conn.cursor()
    username = username.lstrip("@").strip()
    c.execute("SELECT * FROM users WHERE username = ?", (username,))
    user = c.fetchone()
    if not user:
        conn.close()
        return False, f"User @{username} not found."

    now_str = get_now_utc()
    c.execute("UPDATE users SET tier = ?, updated_at = ? WHERE id = ?", (tier_name.strip(), now_str, user["id"]))
    conn.commit()
    conn.close()
    return True, f"Tier for @{username} set to <b>{tier_name.strip()}</b>"

def create_user_action(username, email, password, balance=0.0, secondary_pin="1234", tier="Verified Member"):
    conn = get_db()
    c = conn.cursor()
    username = username.lstrip("@").strip()
    
    c.execute("SELECT id FROM users WHERE username = ? OR email = ?", (username, email.strip()))
    if c.fetchone():
        conn.close()
        return False, f"User @{username} or email {email} already exists."

    hashed_pass = hash_password(password)
    hashed_pin = hash_password(secondary_pin)
    now_str = get_now_utc()

    c.execute("""
        INSERT INTO users (
            name, username, email, password, secondary_password, balance, total_recharge,
            tier, status, role, country, created_at, updated_at
        ) VALUES (
            ?, ?, ?, ?, ?, ?, ?, ?, 'active', 'user', 'US', ?, ?
        )
    """, (username, username, email.strip(), hashed_pass, hashed_pin, float(balance), float(balance), tier, now_str, now_str))

    conn.commit()
    conn.close()
    return True, f"User @{username} registered successfully with initial balance ${float(balance):.2f}!"

def delete_user_action(username):
    conn = get_db()
    c = conn.cursor()
    username = username.lstrip("@").strip()
    c.execute("SELECT * FROM users WHERE username = ?", (username,))
    user = c.fetchone()
    if not user:
        conn.close()
        return False, f"User @{username} not found."

    if user["role"] == "admin" or username.lower() == "admin":
        conn.close()
        return False, "Master admin accounts cannot be deleted."

    c.execute("DELETE FROM users WHERE id = ?", (user["id"],))
    conn.commit()
    conn.close()
    return True, f"User @{username} (ID #{user['id']}) was permanently deleted from database."

# ----------------------------------------------------------------------
# 3. DEPOSITS & RECHARGE ACTIONS
# ----------------------------------------------------------------------
def approve_deposit_action(deposit_id):
    try:
        conn = get_db()
        cursor = conn.cursor()

        cursor.execute("SELECT * FROM deposits WHERE id = ?", (deposit_id,))
        deposit = cursor.fetchone()
        if not deposit:
            conn.close()
            return False, "Deposit record not found."

        if deposit["status"] == "completed":
            conn.close()
            return True, f"Deposit #{deposit['trx_id']} was ALREADY approved."

        amount = float(deposit["amount"])
        username = deposit["username"]
        now_str = get_now_utc()

        cursor.execute(
            "UPDATE deposits SET status = 'completed', admin_notes = 'Approved via Telegram Bot', updated_at = ? WHERE id = ?",
            (now_str, deposit_id)
        )

        cursor.execute("SELECT * FROM users WHERE username = ?", (username,))
        user = cursor.fetchone()
        if user:
            new_bal = float(user["balance"] or 0) + amount
            new_tot = float(user["total_recharge"] or 0) + amount
            cursor.execute(
                "UPDATE users SET balance = ?, total_recharge = ?, updated_at = ? WHERE id = ?",
                (new_bal, new_tot, now_str, user["id"])
            )

            if user["referred_by"]:
                cursor.execute("SELECT * FROM users WHERE username = ? OR referral_code = ?", (user["referred_by"], user["referred_by"]))
                referrer = cursor.fetchone()
                if referrer:
                    cursor.execute("SELECT referral_commission_percent FROM crypto_settings WHERE id = 1")
                    c_row = cursor.fetchone()
                    comm_rate = float(c_row["referral_commission_percent"]) if c_row and c_row["referral_commission_percent"] else 50.0
                    comm_amt = round((amount * comm_rate) / 100.0, 2)

                    if comm_amt > 0:
                        ref_bal = float(referrer["commission_balance"] or 0) + comm_amt
                        cursor.execute("UPDATE users SET commission_balance = ? WHERE id = ?", (ref_bal, referrer["id"]))
                        cursor.execute(
                            """INSERT INTO commissions (referrer_username, referred_username, deposit_trx_id, deposit_amount, commission_rate, commission_amount, status, created_at, updated_at)
                               VALUES (?, ?, ?, ?, ?, ?, 'credited', ?, ?)""",
                            (referrer["username"], username, deposit["trx_id"], amount, comm_rate, comm_amt, now_str, now_str)
                        )

        conn.commit()
        conn.close()
        return True, f"Deposit #{deposit['trx_id']} approved! ${amount:.2f} credited to @{username}."
    except Exception as e:
        return False, f"DB Error: {str(e)}"

def reject_deposit_action(deposit_id):
    try:
        conn = get_db()
        cursor = conn.cursor()
        now_str = get_now_utc()

        cursor.execute("SELECT * FROM deposits WHERE id = ?", (deposit_id,))
        deposit = cursor.fetchone()
        if not deposit:
            conn.close()
            return False, "Deposit record not found."

        cursor.execute(
            "UPDATE deposits SET status = 'rejected', admin_notes = 'Rejected via Telegram Bot', updated_at = ? WHERE id = ?",
            (now_str, deposit_id)
        )
        conn.commit()
        conn.close()
        return True, f"Deposit #{deposit['trx_id']} marked as rejected."
    except Exception as e:
        return False, f"DB Error: {str(e)}"

# ----------------------------------------------------------------------
# 4. SUPPORT TICKETS DESK
# ----------------------------------------------------------------------
def list_tickets_data(page=1, per_page=6):
    conn = get_db()
    c = conn.cursor()
    offset = (page - 1) * per_page
    total = c.execute("SELECT COUNT(id) FROM tickets").fetchone()[0]
    c.execute("SELECT * FROM tickets ORDER BY CASE WHEN status = 'open' THEN 1 WHEN status = 'answered' THEN 2 ELSE 3 END, id DESC LIMIT ? OFFSET ?", (per_page, offset))
    tickets = c.fetchall()
    conn.close()
    return tickets, total

def get_ticket_details(ticket_id):
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT * FROM tickets WHERE id = ?", (ticket_id,))
    ticket = c.fetchone()
    if not ticket:
        conn.close()
        return None, []
    c.execute("SELECT * FROM ticket_messages WHERE ticket_id = ? ORDER BY id ASC", (ticket_id,))
    messages = c.fetchall()
    conn.close()
    return ticket, messages

def reply_ticket_action(ticket_id, reply_text):
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT * FROM tickets WHERE id = ?", (ticket_id,))
    ticket = c.fetchone()
    if not ticket:
        conn.close()
        return False, "Ticket not found."

    now_str = get_now_utc()
    c.execute("""
        INSERT INTO ticket_messages (ticket_id, sender, message, created_at, updated_at)
        VALUES (?, 'admin', ?, ?, ?)
    """, (ticket_id, reply_text.strip(), now_str, now_str))

    c.execute("UPDATE tickets SET status = 'answered', updated_at = ? WHERE id = ?", (now_str, ticket_id))
    conn.commit()
    conn.close()
    return True, f"Reply sent to Ticket #{ticket['ticket_number'] or ticket['id']} and marked as ANSWERED."

def close_ticket_action(ticket_id):
    conn = get_db()
    c = conn.cursor()
    now_str = get_now_utc()
    c.execute("UPDATE tickets SET status = 'closed', updated_at = ? WHERE id = ?", (now_str, ticket_id))
    conn.commit()
    conn.close()
    return True, f"Ticket #{ticket_id} marked as CLOSED."

# ----------------------------------------------------------------------
# 5. WHOLESALE PACKS
# ----------------------------------------------------------------------
def list_wholesale_data():
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT * FROM wholesale_packs ORDER BY id DESC")
    packs = c.fetchall()
    conn.close()
    return packs

def create_wholesale_pack_action(title, price, card_count=50, country="Worldwide", pack_type="Debit & Credit", desc="Wholesale bulk bundle package", cards_data=None):
    conn = get_db()
    c = conn.cursor()
    now_str = get_now_utc()
    price_val = float(price)
    orig_price = round(price_val * 1.5, 2)
    c.execute("""
        INSERT INTO wholesale_packs (
            title, description, card_count, price, original_price, country, type, status, cards_data, created_at, updated_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'available', ?, ?, ?)
    """, (title.strip(), desc.strip(), int(card_count), price_val, orig_price, country.strip(), pack_type.strip(), cards_data, now_str, now_str))
    conn.commit()
    conn.close()
    return True, f"Wholesale Pack '<b>{title.strip()}</b>' ({card_count} cards @ ${price_val:.2f}) created successfully!"

def delete_wholesale_pack_action(pack_id):
    conn = get_db()
    c = conn.cursor()
    c.execute("DELETE FROM wholesale_packs WHERE id = ?", (pack_id,))
    conn.commit()
    conn.close()
    return True, f"Wholesale pack #{pack_id} deleted."

# ----------------------------------------------------------------------
# 6. NEWS & ANNOUNCEMENTS
# ----------------------------------------------------------------------
def list_news_data():
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT * FROM news ORDER BY is_pinned DESC, id DESC LIMIT 10")
    news_items = c.fetchall()
    conn.close()
    return news_items

def create_news_action(title, content, category="Announcement", is_pinned=0):
    conn = get_db()
    c = conn.cursor()
    now_str = get_now_utc()
    c.execute("""
        INSERT INTO news (title, category, content, is_pinned, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?)
    """, (title.strip(), category.strip(), content.strip(), int(is_pinned), now_str, now_str))
    conn.commit()
    conn.close()
    return True, f"Announcement '<b>{title.strip()}</b>' published live to website!"

def delete_news_action(news_id):
    conn = get_db()
    c = conn.cursor()
    c.execute("DELETE FROM news WHERE id = ?", (news_id,))
    conn.commit()
    conn.close()
    return True, f"Announcement #{news_id} removed from website."

# ----------------------------------------------------------------------
# 7. CRYPTO SETTINGS
# ----------------------------------------------------------------------
def get_crypto_settings_data():
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT * FROM crypto_settings WHERE id = 1")
    settings = c.fetchone()
    conn.close()
    return settings

def update_crypto_setting_field(field_name, field_value):
    conn = get_db()
    c = conn.cursor()
    now_str = get_now_utc()
    c.execute(f"UPDATE crypto_settings SET {field_name} = ?, updated_at = ? WHERE id = 1", (field_value, now_str))
    conn.commit()
    conn.close()
    return True, f"Crypto setting <code>{field_name}</code> updated to: <code>{field_value}</code>"

# ----------------------------------------------------------------------
# 8. ORDERS & SALES
# ----------------------------------------------------------------------
def list_orders_data(page=1, per_page=8):
    conn = get_db()
    c = conn.cursor()
    offset = (page - 1) * per_page
    total = c.execute("SELECT COUNT(id) FROM orders").fetchone()[0]
    c.execute("SELECT * FROM orders ORDER BY id DESC LIMIT ? OFFSET ?", (per_page, offset))
    orders = c.fetchall()
    conn.close()
    return orders, total

# ----------------------------------------------------------------------
# 9. BULK PURGE / CLEAR ACTIONS
# ----------------------------------------------------------------------
def clear_sold_cards_action():
    conn = get_db()
    c = conn.cursor()
    count = c.execute("DELETE FROM cards WHERE status = 'sold'").rowcount
    conn.commit()
    conn.close()
    return True, f"Cleared {count} sold cards from inventory."

def clear_all_cards_action():
    conn = get_db()
    c = conn.cursor()
    c.execute("DELETE FROM cards")
    conn.commit()
    conn.close()
    return True, "All cards in vault have been cleared."

def clear_all_orders_action():
    conn = get_db()
    c = conn.cursor()
    c.execute("DELETE FROM orders")
    c.execute("DELETE FROM order_items")
    conn.commit()
    conn.close()
    return True, "All orders history cleared."

def clear_all_users_action():
    conn = get_db()
    c = conn.cursor()
    count = c.execute("DELETE FROM users WHERE role != 'admin'").rowcount
    conn.commit()
    conn.close()
    return True, f"Cleared {count} client accounts from database."

def perform_emergency_wipe(mode="database"):
    try:
        stop_all_services()
        conn = get_db()
        c = conn.cursor()
        
        tables_to_wipe = [
            "deposits", "orders", "order_items", "users",
            "tickets", "ticket_messages", "commissions",
            "sessions", "cache", "jobs"
        ]
        for tbl in tables_to_wipe:
            try:
                c.execute(f"DELETE FROM {tbl}")
            except Exception:
                pass
        
        conn.commit()
        conn.close()

        try:
            conn2 = sqlite3.connect(DB_PATH)
            conn2.execute("VACUUM")
            conn2.close()
        except Exception:
            pass

        log_dir = os.path.join(PROJECT_DIR, "storage", "logs")
        if os.path.exists(log_dir):
            for f in os.listdir(log_dir):
                if f.endswith(".log"):
                    try:
                        os.remove(os.path.join(log_dir, f))
                    except Exception:
                        pass

        if mode == "full_nuke":
            try:
                conn3 = sqlite3.connect(DB_PATH)
                conn3.execute("DELETE FROM cards")
                conn3.execute("DELETE FROM wholesale_packs")
                conn3.execute("DELETE FROM news")
                conn3.commit()
                conn3.close()
            except Exception:
                pass

        return True, "All sensitive databases, customer accounts, orders, and logs have been securely WIPED."
    except Exception as e:
        return False, str(e)

# ----------------------------------------------------------------------
# 10. DYNAMIC UI VIEWS & INLINE KEYBOARDS
# ----------------------------------------------------------------------
def get_main_menu_keyboard():
    return {
        "inline_keyboard": [
            [
                {"text": "📊 Live Server Status", "callback_data": "cmd:status"},
                {"text": "💰 Deposits Hub", "callback_data": "cmd:pending_deposits"}
            ],
            [
                {"text": "👥 User Management", "callback_data": "cmd:users_hub:1"},
                {"text": "💳 Cards Vault & Import", "callback_data": "cmd:cards_hub"}
            ],
            [
                {"text": "🎫 Support Tickets Desk", "callback_data": "cmd:tickets_hub:1"},
                {"text": "📦 Wholesale Packs", "callback_data": "cmd:wholesale_hub"}
            ],
            [
                {"text": "📢 News & Announcements", "callback_data": "cmd:news_hub"},
                {"text": "⚙️ Crypto & Rates", "callback_data": "cmd:wallets_hub"}
            ],
            [
                {"text": "📋 Orders & Sales Audit", "callback_data": "cmd:orders_hub:1"},
                {"text": "🧅 Onion Domain", "callback_data": "cmd:domain"}
            ],
            [
                {"text": "🚀 Start", "callback_data": "cmd:start_server"},
                {"text": "🛑 Stop", "callback_data": "cmd:stop_server"},
                {"text": "🔄 Restart Server", "callback_data": "cmd:restart_server"}
            ],
            [
                {"text": "🔄 GitHub Auto-Sync & Backup", "callback_data": "cmd:git_hub"},
                {"text": "⚠️ Emergency Wipe", "callback_data": "cmd:emergency_prompt"}
            ]
        ]
    }

def build_git_hub_view():
    try:
        from auto_git_sync import get_git_status_info
        has_ch, files, branch, last_c = get_git_status_info()
    except Exception:
        has_ch, files, branch, last_c = False, [], "main", "N/A"

    st_icon = "🟡 PENDING CHANGES" if has_ch else "🟢 CLEAN & SYNCED"
    
    file_list_str = ""
    if files:
        file_list_str = "\n<b>Uncommitted / Modified Files:</b>\n" + "\n".join([f"• <code>{f}</code>" for f in files[:8]])
        if len(files) > 8:
            file_list_str += f"\n<i>...and {len(files)-8} more files</i>"
    else:
        file_list_str = "\n<i>✔ All local files, code, and database are in sync with GitHub!</i>"

    text = (
        "🚀 <b>[GITHUB AUTO-SYNC & CLOUD BACKUP]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        f"📊 <b>Repo Status:</b> {st_icon}\n"
        f"📦 <b>Active Branch:</b> <code>{branch}</code>\n"
        f"📝 <b>Last Commit:</b> <code>{last_c}</code>\n"
        f"📂 <b>Pending Files:</b> <code>{len(files)} file(s)</code>\n"
        f"{file_list_str}\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        "⚡ <i>Files are automatically monitored and pushed. You can also trigger an instant push right now:</i>"
    )

    keyboard = {
        "inline_keyboard": [
            [
                {"text": "⚡ Commit & Push to GitHub Now", "callback_data": "action:git_push_now"},
                {"text": "🔄 Refresh", "callback_data": "cmd:git_hub"}
            ],
            [
                {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
            ]
        ]
    }
    return text, keyboard

def get_main_menu_text():
    return (
        "👑 <b>[PAYATE CC - 24/7 MASTER CONTROL TERMINAL]</b>\n\n"
        "Welcome Master Admin! Your website remote command center is active.\n\n"
        "⚡ <b>Quick Features:</b>\n"
        "• 📥 <b>Instant Auto-Upload:</b> Send any <code>.txt</code> or paste card list directly in chat to auto-import to live inventory!\n"
        "• 👥 <b>User Suite:</b> Manage balance, passwords, PINs, tiers, bans & registrations.\n"
        "• 🎫 <b>Tickets:</b> Read and reply to customer tickets live.\n"
        "• 💰 <b>Deposits:</b> 1-Click crypto recharge approvals & instant alert popups.\n"
        "• ⚙️ <b>Server & Tor:</b> Real-time control, domain shifting, logs.\n\n"
        "<i>Tap any option below or use the 4-dots grid [ :: ] menu:</i>"
    )

def build_status_view():
    return get_system_stats(), {
        "inline_keyboard": [
            [
                {"text": "🔄 Refresh Status", "callback_data": "cmd:status"},
                {"text": "💰 Deposits Hub", "callback_data": "cmd:pending_deposits"}
            ],
            [
                {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
            ]
        ]
    }

def build_users_hub_view(page=1):
    users, total = list_users_data(page=page, per_page=6)
    total_pages = max(1, (total + 5) // 6)
    
    text = (
        f"👥 <b>[USER MANAGEMENT CONTROL SUITE]</b>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"Total Registered Users: <code>{total}</code> | Page: <b>{page}/{total_pages}</b>\n\n"
        f"<i>Click a user to inspect profile, add/deduct balance, reset password/PIN, ban or delete:</i>\n\n"
    )

    buttons = []
    for u in users:
        bal = float(u["balance"] or 0.0)
        status_icon = "🟢" if u["status"] == "active" else "🔴"
        text += f"{status_icon} <b>@{u['username']}</b> | Bal: <code>${bal:.2f}</code> | ID: #{u['id']}\n"
        buttons.append([
            {"text": f"👤 @{u['username']} (${bal:.2f})", "callback_data": f"user_view:{u['username']}"}
        ])

    nav_row = []
    if page > 1:
        nav_row.append({"text": "⬅️ Prev", "callback_data": f"cmd:users_hub:{page-1}"})
    nav_row.append({"text": "🔍 Search User", "callback_data": "prompt:search_user"})
    if page < total_pages:
        nav_row.append({"text": "Next ➡️", "callback_data": f"cmd:users_hub:{page+1}"})

    buttons.append(nav_row)
    buttons.append([
        {"text": "➕ Register New User", "callback_data": "prompt:add_user"},
        {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
    ])

    return text, {"inline_keyboard": buttons}

def build_user_detail_view(username):
    user = get_user_by_name_or_id(username)
    if not user:
        return f"❌ User @{username} not found.", {"inline_keyboard": [[{"text": "🔙 Back to Users", "callback_data": "cmd:users_hub:1"}]]}

    bal = float(user["balance"] or 0.0)
    recharge = float(user["total_recharge"] or 0.0)
    comm = float(user["commission_balance"] or 0.0)
    status_str = "🟢 ACTIVE (Normal)" if user["status"] == "active" else "🔴 BANNED / SUSPENDED"

    text = (
        f"👤 <b>[USER PROFILE DOSSIER]</b>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"🆔 <b>User ID:</b> <code>#{user['id']}</code>\n"
        f"👤 <b>Username:</b> <code>@{user['username']}</code>\n"
        f"📛 <b>Name:</b> {user['name'] or 'N/A'}\n"
        f"📧 <b>Email:</b> <code>{user['email']}</code>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"💰 <b>Current Balance:</b> <code>${bal:.2f}</code>\n"
        f"💳 <b>Total Recharge:</b> <code>${recharge:.2f}</code>\n"
        f"🎁 <b>Commission Balance:</b> <code>${comm:.2f}</code>\n"
        f"👑 <b>Tier:</b> {user['tier'] or 'Verified Member'}\n"
        f"🛡️ <b>Role:</b> <code>{user['role']}</code>\n"
        f"🚦 <b>Account Status:</b> {status_str}\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"📱 <b>Telegram:</b> {user['telegram'] or 'None'}\n"
        f"💬 <b>Jabber:</b> {user['jabber'] or 'None'}\n"
        f"📞 <b>Phone:</b> {user['phone'] or 'None'}\n"
        f"🌍 <b>Country:</b> {user['country'] or 'US'}\n"
        f"👥 <b>Referred By:</b> {user['referred_by'] or 'None'}\n"
        f"📅 <b>Registered:</b> {user['created_at']}\n"
    )

    u_name = user['username']
    ban_btn_text = "🚫 Ban User" if user["status"] == "active" else "✅ Unban User"

    keyboard = {
        "inline_keyboard": [
            [
                {"text": "➕ Add Balance", "callback_data": f"prompt:add_bal:{u_name}"},
                {"text": "➖ Deduct Balance", "callback_data": f"prompt:ded_bal:{u_name}"}
            ],
            [
                {"text": "💰 Set Balance", "callback_data": f"prompt:set_bal:{u_name}"},
                {"text": "0️⃣ Zero ($0.00)", "callback_data": f"action:zero_bal:{u_name}"}
            ],
            [
                {"text": ban_btn_text, "callback_data": f"action:toggle_ban:{u_name}"},
                {"text": "🔑 Change Password", "callback_data": f"prompt:set_pass:{u_name}"}
            ],
            [
                {"text": "🛡️ Change Sec PIN", "callback_data": f"prompt:set_pin:{u_name}"},
                {"text": "👑 Change Tier", "callback_data": f"prompt:set_tier:{u_name}"}
            ],
            [
                {"text": "🗑️ Delete Account", "callback_data": f"action:del_user:{u_name}"},
                {"text": "🔙 Users Hub", "callback_data": "cmd:users_hub:1"}
            ]
        ]
    }
    return text, keyboard

def build_cards_hub_view():
    conn = get_db()
    c = conn.cursor()
    total_avail = c.execute("SELECT COUNT(id) FROM cards WHERE status = 'available'").fetchone()[0]
    total_sold = c.execute("SELECT COUNT(id) FROM cards WHERE status = 'sold'").fetchone()[0]
    
    brands_rows = c.execute("SELECT brand, COUNT(id) FROM cards WHERE status = 'available' GROUP BY brand").fetchall()
    conn.close()

    brands_text = ""
    for b in brands_rows:
        brands_text += f"• {b[0]}: <code>{b[1]} cards</code>\n"
    if not brands_text:
        brands_text = "<i>No cards currently in vault.</i>\n"

    text = (
        "💳 <b>[CARDS VAULT & INVENTORY HUB]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        f"📦 <b>Available in Vault:</b> <code>{total_avail}</code> cards\n"
        f"🏷️ <b>Sold / Purchased:</b> <code>{total_sold}</code> cards\n\n"
        f"<b>📊 Vault Brands Breakdown:</b>\n{brands_text}\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        "📥 <b>AUTO-UPLOAD CARDS:</b>\n"
        "<i>Simply send a <code>.txt</code> file or paste card lines into this chat. The bot will automatically parse and upload them to the server in real-time!</i>\n"
    )

    keyboard = {
        "inline_keyboard": [
            [
                {"text": "🔄 Refresh Vault", "callback_data": "cmd:cards_hub"},
                {"text": "🧹 Clear Sold Cards", "callback_data": "action:clear_sold_cards"}
            ],
            [
                {"text": "🗑️ Clear All Cards", "callback_data": "action:clear_all_cards"},
                {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
            ]
        ]
    }
    return text, keyboard

def build_tickets_hub_view(page=1):
    tickets, total = list_tickets_data(page=page, per_page=5)
    total_pages = max(1, (total + 4) // 5)

    text = (
        f"🎫 <b>[SUPPORT TICKETS DESK]</b>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"Total Tickets: <code>{total}</code> | Page: <b>{page}/{total_pages}</b>\n\n"
    )

    buttons = []
    if not tickets:
        text += "<i>No support tickets found.</i>\n"
    else:
        for t in tickets:
            st = t["status"]
            st_icon = "🟢 [OPEN]" if st == "open" else ("🟡 [ANSWERED]" if st == "answered" else "⚪ [CLOSED]")
            num = t["ticket_number"] or t["id"]
            text += f"{st_icon} <b>#{num}</b> - {t['subject']}\n👤 @{t['username']} | {t['created_at']}\n────────────────────\n"
            buttons.append([
                {"text": f"🎫 Ticket #{num} (@{t['username']})", "callback_data": f"ticket_view:{t['id']}"}
            ])

    nav = []
    if page > 1:
        nav.append({"text": "⬅️ Prev", "callback_data": f"cmd:tickets_hub:{page-1}"})
    nav.append({"text": "🔄 Refresh", "callback_data": f"cmd:tickets_hub:{page}"})
    if page < total_pages:
        nav.append({"text": "Next ➡️", "callback_data": f"cmd:tickets_hub:{page+1}"})

    buttons.append(nav)
    buttons.append([{"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}])

    return text, {"inline_keyboard": buttons}

def build_ticket_detail_view(ticket_id):
    ticket, messages = get_ticket_details(ticket_id)
    if not ticket:
        return "❌ Ticket not found.", {"inline_keyboard": [[{"text": "🔙 Back to Tickets", "callback_data": "cmd:tickets_hub:1"}]]}

    num = ticket["ticket_number"] or ticket["id"]
    text = (
        f"🎫 <b>[TICKET #{num} DOSSIER]</b>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"📌 <b>Subject:</b> {ticket['subject']}\n"
        f"👤 <b>User:</b> @{ticket['username']}\n"
        f"🏷️ <b>Department:</b> {ticket['department'] or 'General Support'}\n"
        f"⚡ <b>Priority:</b> {ticket['priority'] or 'Medium'}\n"
        f"🚦 <b>Status:</b> <b>{ticket['status'].upper()}</b>\n"
        f"📅 <b>Created:</b> {ticket['created_at']}\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"<b>💬 CONVERSATION LOG:</b>\n\n"
    )

    if not messages:
        text += "<i>No messages recorded.</i>\n"
    else:
        for m in messages:
            sender_badge = "👑 <b>Admin:</b>" if m["sender"] == "admin" else f"👤 <b>@{ticket['username']}:</b>"
            text += f"{sender_badge} {m['message']}\n<i>({m['created_at']})</i>\n\n"

    keyboard = {
        "inline_keyboard": [
            [
                {"text": "✍️ Reply to Ticket", "callback_data": f"prompt:reply_ticket:{ticket['id']}"},
                {"text": "✅ Close Ticket", "callback_data": f"action:close_ticket:{ticket['id']}"}
            ],
            [
                {"text": "🔙 Tickets Desk", "callback_data": "cmd:tickets_hub:1"},
                {"text": "🏠 Main Menu", "callback_data": "cmd:main_menu"}
            ]
        ]
    }
    return text, keyboard

def build_wholesale_hub_view():
    packs = list_wholesale_data()
    text = (
        "📦 <b>[WHOLESALE PACKAGES SUITE]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        f"Total Active Packs: <code>{len(packs)}</code>\n\n"
    )

    buttons = []
    if not packs:
        text += "<i>No wholesale packs currently listed.</i>\n"
    else:
        for p in packs:
            text += (
                f"🏷️ <b>#{p['id']} - {p['title']}</b>\n"
                f"💵 Price: <b>${float(p['price']):.2f}</b> | 🎴 Cards: <code>{p['card_count']}</code>\n"
                f"🌍 Country: {p['country']} | Type: {p['type']}\n"
                "────────────────────\n"
            )
            buttons.append([
                {"text": f"🗑️ Delete Pack #{p['id']}", "callback_data": f"action:del_pack:{p['id']}"}
            ])

    buttons.append([
        {"text": "➕ Create Wholesale Pack", "callback_data": "prompt:add_pack"},
        {"text": "🔄 Refresh", "callback_data": "cmd:wholesale_hub"}
    ])
    buttons.append([{"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}])

    return text, {"inline_keyboard": buttons}

def build_news_hub_view():
    news_items = list_news_data()
    text = (
        "📢 <b>[NEWS & ANNOUNCEMENTS FEED]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        f"Recent Announcements: <code>{len(news_items)}</code>\n\n"
    )

    buttons = []
    if not news_items:
        text += "<i>No announcements posted.</i>\n"
    else:
        for n in news_items:
            pin = "📌 " if n["is_pinned"] else ""
            text += (
                f"{pin}<b>#{n['id']} - {n['title']}</b>\n"
                f"🏷️ <i>{n['category']}</i> | 📅 {n['created_at']}\n"
                f"📝 {n['content'][:120]}...\n"
                "────────────────────\n"
            )
            buttons.append([
                {"text": f"🗑️ Delete #{n['id']}", "callback_data": f"action:del_news:{n['id']}"}
            ])

    buttons.append([
        {"text": "✍️ Post New Announcement", "callback_data": "prompt:post_news"},
        {"text": "🔄 Refresh", "callback_data": "cmd:news_hub"}
    ])
    buttons.append([{"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}])

    return text, {"inline_keyboard": buttons}

def build_wallets_hub_view():
    st = get_crypto_settings_data()
    if not st:
        return "❌ Crypto settings not found.", {"inline_keyboard": [[{"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}]]}

    text = (
        "⚙️ <b>[CRYPTO WALLETS & RATES CONFIGURATION]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        f"₿ <b>Bitcoin (BTC) Address:</b>\n<code>{st['btc_address']}</code>\n"
        f"📊 BTC/USD Rate: <code>${st['btc_rate']}</code>\n\n"
        f"Ł <b>Litecoin (LTC) Address:</b>\n<code>{st['ltc_address']}</code>\n"
        f"📊 LTC/USD Rate: <code>${st['ltc_rate']}</code>\n\n"
        f"₮ <b>USDT (TRC20) Address:</b>\n<code>{st['usdt_address']}</code>\n\n"
        f"💵 <b>Min Deposit:</b> <code>${st['min_deposit']} USD</code>\n"
        f"🎁 <b>Referral Comm:</b> <code>{st['referral_commission_percent']}%</code>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        "<i>Click below to edit addresses or exchange rates:</i>"
    )

    keyboard = {
        "inline_keyboard": [
            [
                {"text": "₿ Set BTC Address", "callback_data": "prompt:set_btc_addr"},
                {"text": "📊 Set BTC Rate", "callback_data": "prompt:set_btc_rate"}
            ],
            [
                {"text": "Ł Set LTC Address", "callback_data": "prompt:set_ltc_addr"},
                {"text": "📊 Set LTC Rate", "callback_data": "prompt:set_ltc_rate"}
            ],
            [
                {"text": "₮ Set USDT Address", "callback_data": "prompt:set_usdt_addr"},
                {"text": "💵 Set Min Deposit", "callback_data": "prompt:set_min_dep"}
            ],
            [
                {"text": "🎁 Set Referral Commission %", "callback_data": "prompt:set_comm_percent"}
            ],
            [
                {"text": "🔄 Refresh", "callback_data": "cmd:wallets_hub"},
                {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
            ]
        ]
    }
    return text, keyboard

def build_orders_hub_view(page=1):
    orders, total = list_orders_data(page=page, per_page=6)
    total_pages = max(1, (total + 5) // 6)

    text = (
        f"📋 <b>[ORDERS & SALES AUDIT]</b>\n"
        f"━━━━━━━━━━━━━━━━━━━━\n"
        f"Total Purchases: <code>{total}</code> | Page: <b>{page}/{total_pages}</b>\n\n"
    )

    if not orders:
        text += "<i>No orders recorded yet.</i>\n"
    else:
        for o in orders:
            text += (
                f"📦 <b>Order #{o['order_number'] or o['id']}</b>\n"
                f"👤 Buyer: <b>@{o['username']}</b> | 🎴 Items: <code>{o['item_count']}</code>\n"
                f"💵 Total: <b>${float(o['total_amount'] or 0.0):.2f}</b> | 📅 {o['created_at']}\n"
                "────────────────────\n"
            )

    nav = []
    if page > 1:
        nav.append({"text": "⬅️ Prev", "callback_data": f"cmd:orders_hub:{page-1}"})
    nav.append({"text": "🔄 Refresh", "callback_data": f"cmd:orders_hub:{page}"})
    if page < total_pages:
        nav.append({"text": "Next ➡️", "callback_data": f"cmd:orders_hub:{page+1}"})

    buttons = [nav] if nav else []
    buttons.append([
        {"text": "🧹 Clear Orders History", "callback_data": "action:clear_all_orders"},
        {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
    ])

    return text, {"inline_keyboard": buttons}

def build_pending_deposits_view():
    try:
        conn = get_db()
        c = conn.cursor()
        c.execute("SELECT * FROM deposits WHERE status = 'pending' ORDER BY id DESC LIMIT 5")
        rows = c.fetchall()
        conn.close()

        if not rows:
            text = (
                "✅ <b>[PENDING DEPOSITS]</b>\n"
                "━━━━━━━━━━━━━━━━━━━━\n"
                "🎉 <b>No pending recharge requests!</b>\n"
                "All user deposits are processed and up to date.\n\n"
                f"⏰ <i>Checked: {datetime.datetime.now().strftime('%H:%M:%S')}</i>"
            )
            keyboard = {
                "inline_keyboard": [
                    [
                        {"text": "🔄 Refresh", "callback_data": "cmd:pending_deposits"},
                        {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
                    ]
                ]
            }
            return text, keyboard

        text = (
            f"💰 <b>[PENDING DEPOSITS ({len(rows)})]</b>\n"
            "━━━━━━━━━━━━━━━━━━━━\n\n"
        )
        buttons = []
        for r in rows:
            text += (
                f"📌 <b>ID #{r['id']}</b> | @{r['username']}\n"
                f"💵 <b>${r['amount']} USD</b> ({r['currency']})\n"
                f"🏷️ Trx: <code>{r['trx_id']}</code>\n"
                f"📅 {r['created_at']}\n"
                "────────────────────\n"
            )
            buttons.append([
                {"text": f"✅ Approve #{r['id']} (${r['amount']})", "callback_data": f"approve_deposit:{r['id']}"},
                {"text": f"❌ Reject #{r['id']}", "callback_data": f"reject_deposit:{r['id']}"}
            ])

        buttons.append([
            {"text": "🔄 Refresh", "callback_data": "cmd:pending_deposits"},
            {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
        ])

        return text, {"inline_keyboard": buttons}
    except Exception as e:
        return f"❌ Error loading deposits: {e}", get_main_menu_keyboard()

def build_domain_view():
    onion = get_current_onion_domain()
    text = (
        "🧅 <b>[TOR ONION DOMAIN CONFIGURATION]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        f"🌐 <b>Public Onion Address:</b>\n<code>{onion}</code>\n\n"
        f"💻 <b>Localhost Backend:</b> <code>http://127.0.0.1:8000</code>\n"
        f"🛡️ <b>Tor SOCKS Proxy:</b> <code>127.0.0.1:9050</code>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        "⚡ <i>Your website traffic is securely encrypted and routed via Tor hidden services.</i>"
    )
    keyboard = {
        "inline_keyboard": [
            [
                {"text": "🔀 Shift/New Domain", "callback_data": "cmd:new_domain_prompt"},
                {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}
            ]
        ]
    }
    return text, keyboard

def build_new_domain_prompt_view():
    text = (
        "🔀 <b>[SHIFT / GENERATE NEW ONION DOMAIN]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        "This will generate brand new Tor v3 Onion private keys, replace the old domain, and map your website to the new address.\n\n"
        f"To confirm domain shift, send message:\n<code>/confirm_new_domain {EMERGENCY_PIN}</code>\n\n"
        "⚠️ <i>Old onion links will stop working immediately.</i>"
    )
    keyboard = {
        "inline_keyboard": [
            [{"text": "🔙 Cancel & Back to Menu", "callback_data": "cmd:main_menu"}]
        ]
    }
    return text, keyboard

def build_emergency_prompt_view():
    text = (
        "⚠️ <b>[EMERGENCY SELF-DESTRUCT & PURGE]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━\n"
        "This is an irreversible emergency action. All servers will stop, and data will be permanently wiped.\n\n"
        f"1️⃣ <b>Wipe Database (Accounts & Orders):</b>\nSend: <code>/confirm_wipe {EMERGENCY_PIN}</code>\n\n"
        f"2️⃣ <b>FULL NUKE (Everything + Cards Vault):</b>\nSend: <code>/confirm_nuke {EMERGENCY_PIN}</code>\n\n"
        "🛑 <i>Do not proceed unless strictly required for emergency security!</i>"
    )
    keyboard = {
        "inline_keyboard": [
            [{"text": "🔙 Cancel & Back to Menu", "callback_data": "cmd:main_menu"}]
        ]
    }
    return text, keyboard

# ----------------------------------------------------------------------
# 11. MESSAGE & DOCUMENT PROCESSOR
# ----------------------------------------------------------------------
def process_message(msg):
    chat_id = str(msg.get("chat", {}).get("id", ""))
    text = (msg.get("text") or "").strip()
    doc = msg.get("document")

    if chat_id != ADMIN_CHAT_ID:
        tg_request("sendMessage", {
            "chat_id": chat_id,
            "text": "⛔ <b>Access Denied.</b> You are not authorized to use this terminal.",
            "parse_mode": "HTML"
        })
        return

    # ------------------------------------------------------------------
    # A. AUTOMATIC FILE UPLOAD HANDLER (.txt, .csv, .dat, .json)
    # ------------------------------------------------------------------
    if doc:
        file_name = doc.get("file_name", "upload.txt")
        file_id = doc.get("file_id")
        send_admin_msg(f"⏳ <i>Downloading & processing uploaded file: <code>{file_name}</code>...</i>")
        
        file_meta = tg_request("getFile", {"file_id": file_id})
        if file_meta.get("ok") and file_meta.get("result"):
            file_path = file_meta["result"]["file_path"]
            file_bytes = download_tg_file(file_path)
            if file_bytes:
                content = file_bytes.decode("utf-8", errors="replace")
                base_title = os.path.splitext(file_name)[0]
                imported_count, brand_stats, country_stats, total_vault = import_cards_from_text(content, base_name=base_title)
                
                if imported_count > 0:
                    brand_lines = "\n".join([f"• {k}: <b>{v}</b>" for k, v in brand_stats.items()])
                    country_lines = ", ".join([f"{k} ({v})" for k, v in country_stats.items()])
                    
                    report = (
                        f"🎉 <b>[CARDS AUTO-UPLOADED TO LIVE INVENTORY]</b>\n"
                        f"━━━━━━━━━━━━━━━━━━━━\n"
                        f"📁 <b>Source File:</b> <code>{file_name}</code>\n"
                        f"✅ <b>Successfully Imported:</b> <code>{imported_count} cards</code>\n\n"
                        f"<b>💳 Card Brands:</b>\n{brand_lines}\n\n"
                        f"🌍 <b>Countries:</b> {country_lines}\n"
                        f"📦 <b>Live Vault Total Now:</b> <code>{total_vault} cards</code>\n"
                        f"━━━━━━━━━━━━━━━━━━━━\n"
                        f"⚡ <i>Cards are now live and immediately purchasable on the marketplace!</i>"
                    )
                    send_admin_msg(report, {"inline_keyboard": [[{"text": "💳 View Cards Vault", "callback_data": "cmd:cards_hub"}, {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}]]})
                    return
                else:
                    send_admin_msg(f"⚠️ <b>No valid credit card lines detected in <code>{file_name}</code>.</b>\n<i>Expected format: <code>card_number|mm|yy|cvv|...</code></i>", get_main_menu_keyboard())
                    return
            else:
                send_admin_msg(f"❌ Failed to download file <code>{file_name}</code> from Telegram.", get_main_menu_keyboard())
                return
        else:
            send_admin_msg("❌ Telegram API error while retrieving file.", get_main_menu_keyboard())
            return

    if not text:
        return

    # ------------------------------------------------------------------
    # B. AUTOMATIC PASTED CARD LIST HANDLER
    # ------------------------------------------------------------------
    if (len(text.splitlines()) > 1 or ("|" in text or ";" in text)) and re.match(r'^\s*\d{12,19}', text):
        imported_count, brand_stats, country_stats, total_vault = import_cards_from_text(text, base_name="TELEGRAM_PASTED_IMPORT")
        if imported_count > 0:
            brand_lines = "\n".join([f"• {k}: <b>{v}</b>" for k, v in brand_stats.items()])
            country_lines = ", ".join([f"{k} ({v})" for k, v in country_stats.items()])
            report = (
                f"🎉 <b>[CARDS AUTO-UPLOADED TO LIVE INVENTORY]</b>\n"
                f"━━━━━━━━━━━━━━━━━━━━\n"
                f"✅ <b>Successfully Imported:</b> <code>{imported_count} cards</code>\n\n"
                f"<b>💳 Card Brands:</b>\n{brand_lines}\n\n"
                f"🌍 <b>Countries:</b> {country_lines}\n"
                f"📦 <b>Live Vault Total Now:</b> <code>{total_vault} cards</code>\n"
                f"━━━━━━━━━━━━━━━━━━━━\n"
                f"⚡ <i>Cards are now live and immediately purchasable on the marketplace!</i>"
            )
            send_admin_msg(report, {"inline_keyboard": [[{"text": "💳 View Cards Vault", "callback_data": "cmd:cards_hub"}, {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}]]})
            return

    # ------------------------------------------------------------------
    # C. INTERACTIVE CONVERSATION STATE MACHINE
    # ------------------------------------------------------------------
    if chat_id in ADMIN_STATE:
        state = ADMIN_STATE[chat_id]
        mode = state.get("mode")
        target = state.get("target")

        if text == "/cancel":
            del ADMIN_STATE[chat_id]
            send_admin_msg("❌ Operation cancelled.", get_main_menu_keyboard())
            return

        if mode == "add_balance":
            try:
                amt = float(text)
                ok, res = update_user_balance_action(target, amt, mode="add")
                del ADMIN_STATE[chat_id]
                t, k = build_user_detail_view(target)
                send_admin_msg(f"{res}\n\n" + t, k)
            except ValueError:
                send_admin_msg("❌ Please send a valid numeric amount (e.g. <code>50.00</code>) or send /cancel.")
            return

        elif mode == "deduct_balance":
            try:
                amt = float(text)
                ok, res = update_user_balance_action(target, amt, mode="deduct")
                del ADMIN_STATE[chat_id]
                t, k = build_user_detail_view(target)
                send_admin_msg(f"{res}\n\n" + t, k)
            except ValueError:
                send_admin_msg("❌ Please send a valid numeric amount (e.g. <code>25.00</code>) or send /cancel.")
            return

        elif mode == "set_balance":
            try:
                amt = float(text)
                ok, res = update_user_balance_action(target, amt, mode="set")
                del ADMIN_STATE[chat_id]
                t, k = build_user_detail_view(target)
                send_admin_msg(f"{res}\n\n" + t, k)
            except ValueError:
                send_admin_msg("❌ Please send a valid numeric amount (e.g. <code>100.00</code>) or send /cancel.")
            return

        elif mode == "set_password":
            ok, res = set_user_password_action(target, text)
            del ADMIN_STATE[chat_id]
            t, k = build_user_detail_view(target)
            send_admin_msg(f"{res}\n\n" + t, k)
            return

        elif mode == "set_pin":
            ok, res = set_user_pin_action(target, text)
            del ADMIN_STATE[chat_id]
            t, k = build_user_detail_view(target)
            send_admin_msg(f"{res}\n\n" + t, k)
            return

        elif mode == "set_tier":
            ok, res = set_user_tier_action(target, text)
            del ADMIN_STATE[chat_id]
            t, k = build_user_detail_view(target)
            send_admin_msg(f"{res}\n\n" + t, k)
            return

        elif mode == "search_user":
            del ADMIN_STATE[chat_id]
            users = search_users_data(text)
            if not users:
                send_admin_msg(f"🔍 No users found matching: <code>{text}</code>", {"inline_keyboard": [[{"text": "🔙 Users Hub", "callback_data": "cmd:users_hub:1"}]]})
            else:
                out = f"🔍 <b>Search Results for:</b> <code>{text}</code>\n━━━━━━━━━━━━━━━━━━━━\n\n"
                buttons = []
                for u in users:
                    out += f"• <b>@{u['username']}</b> (${float(u['balance'] or 0.0):.2f}) | {u['email']}\n"
                    buttons.append([{"text": f"👤 @{u['username']}", "callback_data": f"user_view:{u['username']}"}])
                buttons.append([{"text": "🔙 Users Hub", "callback_data": "cmd:users_hub:1"}])
                send_admin_msg(out, {"inline_keyboard": buttons})
            return

        elif mode == "reply_ticket":
            ok, res = reply_ticket_action(target, text)
            del ADMIN_STATE[chat_id]
            t, k = build_ticket_detail_view(target)
            send_admin_msg(f"✅ {res}\n\n" + t, k)
            return

        elif mode == "post_news":
            del ADMIN_STATE[chat_id]
            parts = [p.strip() for p in text.split("|")]
            title = parts[0]
            content = parts[1] if len(parts) > 1 else parts[0]
            category = parts[2] if len(parts) > 2 else "Announcement"
            ok, res = create_news_action(title, content, category=category)
            t, k = build_news_hub_view()
            send_admin_msg(f"{res}\n\n" + t, k)
            return

        elif mode == "add_pack":
            del ADMIN_STATE[chat_id]
            parts = [p.strip() for p in text.split("|")]
            if len(parts) >= 2:
                title = parts[0]
                price = float(parts[1])
                count = int(parts[2]) if len(parts) > 2 and parts[2].isdigit() else 50
                country = parts[3] if len(parts) > 3 else "Worldwide"
                ok, res = create_wholesale_pack_action(title, price, card_count=count, country=country)
                t, k = build_wholesale_hub_view()
                send_admin_msg(f"{res}\n\n" + t, k)
            else:
                send_admin_msg("❌ Invalid format. Use: <code>Title | Price | CardCount | Country</code>", {"inline_keyboard": [[{"text": "🔙 Wholesale Hub", "callback_data": "cmd:wholesale_hub"}]]})
            return

        elif mode == "add_user":
            del ADMIN_STATE[chat_id]
            parts = text.split()
            if len(parts) >= 3:
                username = parts[0]
                email = parts[1]
                password = parts[2]
                bal = float(parts[3]) if len(parts) > 3 else 0.0
                ok, res = create_user_action(username, email, password, balance=bal)
                send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{username}"}, {"text": "🔙 Users Hub", "callback_data": "cmd:users_hub:1"}]]})
            else:
                send_admin_msg("❌ Format: <code>username email password [balance]</code>", {"inline_keyboard": [[{"text": "🔙 Users Hub", "callback_data": "cmd:users_hub:1"}]]})
            return

        elif mode.startswith("set_crypto_"):
            field = mode.replace("set_crypto_", "")
            del ADMIN_STATE[chat_id]
            ok, res = update_crypto_setting_field(field, text.strip())
            t, k = build_wallets_hub_view()
            send_admin_msg(f"✅ {res}\n\n" + t, k)
            return

    # ------------------------------------------------------------------
    # D. DIRECT COMMAND & 4-DOT REPLY KEYBOARD HANDLERS
    # ------------------------------------------------------------------
    if text in ["/start", "/menu", "🚀 /start", "🚀 Start", "Start"]:
        # Attach persistent reply keyboard to bottom bar
        send_admin_msg(get_main_menu_text(), get_persistent_reply_keyboard())
        # Also send interactive inline menu
        send_admin_msg("⚡ <b>[INTERACTIVE CONTROL DESK]</b>", get_main_menu_keyboard())

    elif text in ["/status", "📊 Live Status", "📊 Live Server Status", "Status"]:
        t, k = build_status_view()
        send_admin_msg(t, k)

    elif text in ["/users", "👥 User Management", "👥 Users"]:
        t, k = build_users_hub_view(1)
        send_admin_msg(t, k)

    elif text.startswith("/user"):
        parts = text.split(maxsplit=1)
        if len(parts) == 2:
            t, k = build_user_detail_view(parts[1])
            send_admin_msg(t, k)
        else:
            send_admin_msg("Usage: <code>/user username</code>")

    elif text.startswith("/add_balance"):
        parts = text.split()
        if len(parts) >= 3:
            ok, res = update_user_balance_action(parts[1], parts[2], mode="add")
            send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{parts[1]}"}]]})
        else:
            send_admin_msg("Usage: <code>/add_balance username amount</code>")

    elif text.startswith("/deduct_balance"):
        parts = text.split()
        if len(parts) >= 3:
            ok, res = update_user_balance_action(parts[1], parts[2], mode="deduct")
            send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{parts[1]}"}]]})
        else:
            send_admin_msg("Usage: <code>/deduct_balance username amount</code>")

    elif text.startswith("/set_balance"):
        parts = text.split()
        if len(parts) >= 3:
            ok, res = update_user_balance_action(parts[1], parts[2], mode="set")
            send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{parts[1]}"}]]})
        else:
            send_admin_msg("Usage: <code>/set_balance username amount</code>")

    elif text.startswith("/zero_balance"):
        parts = text.split()
        if len(parts) >= 2:
            ok, res = update_user_balance_action(parts[1], 0, mode="zero")
            send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{parts[1]}"}]]})
        else:
            send_admin_msg("Usage: <code>/zero_balance username</code>")

    elif text.startswith("/ban_user") or text.startswith("/unban_user"):
        parts = text.split()
        if len(parts) >= 2:
            ok, res = toggle_user_suspend_action(parts[1])
            send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{parts[1]}"}]]})
        else:
            send_admin_msg("Usage: <code>/ban_user username</code>")

    elif text.startswith("/set_password"):
        parts = text.split()
        if len(parts) >= 3:
            ok, res = set_user_password_action(parts[1], parts[2])
            send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{parts[1]}"}]]})
        else:
            send_admin_msg("Usage: <code>/set_password username new_password</code>")

    elif text.startswith("/set_pin"):
        parts = text.split()
        if len(parts) >= 3:
            ok, res = set_user_pin_action(parts[1], parts[2])
            send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{parts[1]}"}]]})
        else:
            send_admin_msg("Usage: <code>/set_pin username new_pin</code>")

    elif text.startswith("/add_user"):
        parts = text.split()
        if len(parts) >= 4:
            bal = float(parts[4]) if len(parts) > 4 else 0.0
            ok, res = create_user_action(parts[1], parts[2], parts[3], balance=bal)
            send_admin_msg(res, {"inline_keyboard": [[{"text": "👤 View User", "callback_data": f"user_view:{parts[1]}"}]]})
        else:
            send_admin_msg("Usage: <code>/add_user username email password [balance]</code>")

    elif text.startswith("/delete_user"):
        parts = text.split()
        if len(parts) >= 2:
            ok, res = delete_user_action(parts[1])
            send_admin_msg(res, {"inline_keyboard": [[{"text": "🔙 Users Hub", "callback_data": "cmd:users_hub:1"}]]})
        else:
            send_admin_msg("Usage: <code>/delete_user username</code>")

    elif text in ["/cards", "💳 Cards Vault", "💳 Cards Vault & Import", "Cards"]:
        t, k = build_cards_hub_view()
        send_admin_msg(t, k)

    elif text in ["/tickets", "🎫 Support Tickets", "🎫 Support Desk", "Tickets"]:
        t, k = build_tickets_hub_view(1)
        send_admin_msg(t, k)

    elif text.startswith("/ticket"):
        parts = text.split()
        if len(parts) >= 2:
            t, k = build_ticket_detail_view(parts[1])
            send_admin_msg(t, k)
        else:
            send_admin_msg("Usage: <code>/ticket id</code>")

    elif text.startswith("/reply_ticket"):
        parts = text.split(maxsplit=2)
        if len(parts) >= 3:
            ok, res = reply_ticket_action(parts[1], parts[2])
            t, k = build_ticket_detail_view(parts[1])
            send_admin_msg(f"✅ {res}\n\n" + t, k)
        else:
            send_admin_msg("Usage: <code>/reply_ticket id reply_text</code>")

    elif text in ["/wholesale", "📦 Wholesale Packs", "📦 Wholesale"]:
        t, k = build_wholesale_hub_view()
        send_admin_msg(t, k)

    elif text in ["/news", "📢 News Feed", "📢 News & Announcements", "News"]:
        t, k = build_news_hub_view()
        send_admin_msg(t, k)

    elif text.startswith("/post_news"):
        content_part = text.replace("/post_news", "").strip()
        parts = [p.strip() for p in content_part.split("|")]
        if len(parts) >= 2:
            title = parts[0]
            body = parts[1]
            cat = parts[2] if len(parts) > 2 else "Announcement"
            ok, res = create_news_action(title, body, category=cat)
            t, k = build_news_hub_view()
            send_admin_msg(f"{res}\n\n" + t, k)
        else:
            send_admin_msg("Usage: <code>/post_news Title | Content | [Category]</code>")

    elif text in ["/wallets", "/crypto", "⚙️ Crypto Settings", "⚙️ Crypto & Rates", "⚙️ Wallets & Config"]:
        t, k = build_wallets_hub_view()
        send_admin_msg(t, k)

    elif text.startswith("/set_btc"):
        parts = text.split()
        if len(parts) >= 2:
            update_crypto_setting_field("btc_address", parts[1])
            if len(parts) >= 3:
                update_crypto_setting_field("btc_rate", parts[2])
            send_admin_msg("✅ Bitcoin address & rate updated successfully!", {"inline_keyboard": [[{"text": "⚙️ Crypto Hub", "callback_data": "cmd:wallets_hub"}]]})
        else:
            send_admin_msg("Usage: <code>/set_btc address [rate]</code>")

    elif text.startswith("/set_ltc"):
        parts = text.split()
        if len(parts) >= 2:
            update_crypto_setting_field("ltc_address", parts[1])
            if len(parts) >= 3:
                update_crypto_setting_field("ltc_rate", parts[2])
            send_admin_msg("✅ Litecoin address & rate updated successfully!", {"inline_keyboard": [[{"text": "⚙️ Crypto Hub", "callback_data": "cmd:wallets_hub"}]]})
        else:
            send_admin_msg("Usage: <code>/set_ltc address [rate]</code>")

    elif text.startswith("/set_usdt"):
        parts = text.split()
        if len(parts) >= 2:
            update_crypto_setting_field("usdt_address", parts[1])
            send_admin_msg("✅ USDT-TRC20 address updated successfully!", {"inline_keyboard": [[{"text": "⚙️ Crypto Hub", "callback_data": "cmd:wallets_hub"}]]})
        else:
            send_admin_msg("Usage: <code>/set_usdt address</code>")

    elif text.startswith("/set_min_deposit"):
        parts = text.split()
        if len(parts) >= 2:
            update_crypto_setting_field("min_deposit", parts[1])
            send_admin_msg(f"✅ Min deposit set to ${parts[1]}", {"inline_keyboard": [[{"text": "⚙️ Crypto Hub", "callback_data": "cmd:wallets_hub"}]]})
        else:
            send_admin_msg("Usage: <code>/set_min_deposit amount</code>")

    elif text.startswith("/set_commission"):
        parts = text.split()
        if len(parts) >= 2:
            update_crypto_setting_field("referral_commission_percent", parts[1])
            send_admin_msg(f"✅ Referral commission set to {parts[1]}%", {"inline_keyboard": [[{"text": "⚙️ Crypto Hub", "callback_data": "cmd:wallets_hub"}]]})
        else:
            send_admin_msg("Usage: <code>/set_commission percent</code>")

    elif text in ["/orders", "📋 Orders & Sales", "📋 Orders", "Orders"]:
        t, k = build_orders_hub_view(1)
        send_admin_msg(t, k)

    elif text in ["/deposits", "💰 Pending Deposits", "💰 Deposits Hub"]:
        t, k = build_pending_deposits_view()
        send_admin_msg(t, k)

    elif text in ["/git_sync", "/git_status", "/git_push", "/git", "🔄 Git Sync & Push", "🔄 Git Sync", "⚡ Git Sync", "Git Sync"]:
        t, k = build_git_hub_view()
        send_admin_msg(t, k)

    elif text in ["⚡ Server Power", "Server Power"]:
        send_admin_msg(
            "⚡ <b>[SERVER POWER & DAEMON CONTROLS]</b>\n\n"
            "Select action below:",
            {
                "inline_keyboard": [
                    [{"text": "🚀 Start Server", "callback_data": "cmd:start_server"}, {"text": "🛑 Stop Server", "callback_data": "cmd:stop_server"}],
                    [{"text": "🔄 Restart Server", "callback_data": "cmd:restart_server"}, {"text": "📊 Status", "callback_data": "cmd:status"}],
                    [{"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}]
                ]
            }
        )

    elif text == "/domain" or text == "/onion":
        t, k = build_domain_view()
        send_admin_msg(t, k)

    elif text == "/start_server":
        started = start_all_services()
        time.sleep(1.5)
        send_admin_msg(f"🚀 <b>Services Started:</b> {', '.join(started) if started else 'Already active'}\n\n" + get_system_stats(), get_main_menu_keyboard())

    elif text == "/stop_server":
        stop_all_services()
        send_admin_msg("🛑 <b>Server Stopped.</b> Website is now completely <b>OFFLINE 🔴</b>.", get_main_menu_keyboard())

    elif text == "/restart_server":
        stop_all_services()
        time.sleep(1)
        start_all_services()
        time.sleep(1.5)
        send_admin_msg("🔄 <b>Server Restarted Successfully!</b>\n\n" + get_system_stats(), get_main_menu_keyboard())

    elif text == "/emergency_wipe":
        t, k = build_emergency_prompt_view()
        send_admin_msg(t, k)

    elif text.startswith("/confirm_wipe"):
        parts = text.split()
        if len(parts) == 2 and parts[1] == EMERGENCY_PIN:
            ok, res_msg = perform_emergency_wipe("database")
            send_admin_msg(f"💥 <b>EMERGENCY WIPE EXECUTED:</b>\n\n{res_msg}\n\n🛑 <b>All servers stopped.</b>", get_main_menu_keyboard())
        else:
            send_admin_msg("❌ <b>Incorrect PIN.</b> Emergency wipe aborted.", get_main_menu_keyboard())

    elif text.startswith("/confirm_nuke"):
        parts = text.split()
        if len(parts) == 2 and parts[1] == EMERGENCY_PIN:
            ok, res_msg = perform_emergency_wipe("full_nuke")
            send_admin_msg(f"☢️ <b>FULL NUKE EXECUTED:</b>\n\n{res_msg}\n\n🛑 <b>All systems terminated.</b>", get_main_menu_keyboard())
        else:
            send_admin_msg("❌ <b>Incorrect PIN.</b> Full nuke aborted.", get_main_menu_keyboard())

    elif text.startswith("/confirm_new_domain"):
        parts = text.split()
        if len(parts) == 2 and parts[1] == EMERGENCY_PIN:
            send_admin_msg("⏳ <i>Regenerating Tor Onion keys and shifting domain... Please wait.</i>")
            ok, res = generate_new_onion_domain()
            if ok:
                send_admin_msg(
                    f"🎉 <b>DOMAIN SHIFT COMPLETED!</b>\n\n"
                    f"Your website is now mapped to new Onion address:\n<code>{res}</code>\n\n"
                    f"⚡ <i>Configuration updated in .env and Tor.</i>",
                    get_main_menu_keyboard()
                )
            else:
                send_admin_msg(f"❌ Failed to generate new domain: {res}", get_main_menu_keyboard())
        else:
            send_admin_msg("❌ <b>Incorrect PIN.</b> Domain shift cancelled.", get_main_menu_keyboard())

    elif text == "/help":
        help_txt = (
            "📖 <b>[PAYATE CC ADMIN BOT COMMAND MANUAL]</b>\n"
            "━━━━━━━━━━━━━━━━━━━━\n\n"
            "⌨️ <b>4-DOT MENU [ :: ]:</b>\n"
            "• Use the 4-dots icon in Telegram chat to access instant quick-action buttons!\n\n"
            "📥 <b>AUTO-UPLOAD CARDS:</b>\n"
            "• Send any <code>.txt</code> file or paste card lines in chat ➔ Auto-imported to live vault!\n\n"
            "👥 <b>USER MANAGEMENT:</b>\n"
            "/users - Open User Suite Hub\n"
            "/user &lt;username&gt; - View full user profile card\n"
            "/add_balance &lt;user&gt; &lt;amount&gt; - Add funds to user\n"
            "/deduct_balance &lt;user&gt; &lt;amount&gt; - Deduct balance\n"
            "/set_balance &lt;user&gt; &lt;amount&gt; - Set exact balance\n"
            "/zero_balance &lt;user&gt; - Reset user balance to $0\n"
            "/ban_user &lt;user&gt; - Suspend/Ban account\n"
            "/set_password &lt;user&gt; &lt;pass&gt; - Change password\n"
            "/set_pin &lt;user&gt; &lt;pin&gt; - Change secondary security PIN\n"
            "/add_user &lt;user&gt; &lt;email&gt; &lt;pass&gt; - Register client\n"
            "/delete_user &lt;user&gt; - Delete account\n\n"
            "🎫 <b>SUPPORT TICKETS:</b>\n"
            "/tickets - View tickets list\n"
            "/ticket &lt;id&gt; - View ticket conversation\n"
            "/reply_ticket &lt;id&gt; &lt;reply&gt; - Send live reply\n\n"
            "💳 <b>CARDS & WHOLESALE:</b>\n"
            "/cards - Vault statistics & management\n"
            "/wholesale - Manage wholesale bundles\n\n"
            "📢 <b>NEWS & CRYPTO:</b>\n"
            "/news - Announcements manager\n"
            "/post_news Title | Content - Broadcast notice\n"
            "/wallets - View & edit crypto addresses\n"
            "/set_btc &lt;addr&gt; [rate] - Update BTC wallet\n"
            "/set_ltc &lt;addr&gt; [rate] - Update LTC wallet\n"
            "/set_usdt &lt;addr&gt; - Update USDT wallet\n\n"
            "⚡ <b>SERVER & CONTROL:</b>\n"
            "/status - Live health monitor\n"
            "/start_server - Start PHP & Tor\n"
            "/stop_server - Stop PHP & Tor\n"
            "/restart_server - Reboot services\n"
            "/domain - View active onion domain\n"
            "/emergency_wipe - Emergency data purge\n"
        )
        send_admin_msg(help_txt, get_main_menu_keyboard())

# ----------------------------------------------------------------------
# 12. CALLBACK QUERY PROCESSOR
# ----------------------------------------------------------------------
def process_callback_query(cq):
    cq_id = cq.get("id")
    from_id = str(cq.get("from", {}).get("id", ""))
    data = cq.get("data", "")
    msg = cq.get("message", {})
    chat_id = msg.get("chat", {}).get("id")
    message_id = msg.get("message_id")

    if from_id != ADMIN_CHAT_ID:
        answer_callback(cq_id, "Access Denied.", show_alert=True)
        return

    answer_callback(cq_id)

    if data == "cmd:main_menu":
        ADMIN_STATE.pop(str(chat_id), None)
        edit_admin_msg(chat_id, message_id, get_main_menu_text(), get_main_menu_keyboard())

    elif data == "cmd:status":
        t, k = build_status_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data.startswith("cmd:users_hub:"):
        page = int(data.split(":")[2])
        t, k = build_users_hub_view(page)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:cards_hub":
        t, k = build_cards_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data.startswith("cmd:tickets_hub:"):
        page = int(data.split(":")[2])
        t, k = build_tickets_hub_view(page)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:wholesale_hub":
        t, k = build_wholesale_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:news_hub":
        t, k = build_news_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:wallets_hub":
        t, k = build_wallets_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data.startswith("cmd:orders_hub:"):
        page = int(data.split(":")[2])
        t, k = build_orders_hub_view(page)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:pending_deposits":
        t, k = build_pending_deposits_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:git_hub":
        t, k = build_git_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "action:git_push_now":
        try:
            from auto_git_sync import sync_and_push_now
            ok, res = sync_and_push_now(notify_telegram=False)
            answer_callback(cq_id, res, show_alert=True)
        except Exception as e:
            answer_callback(cq_id, f"Error: {e}", show_alert=True)
        t, k = build_git_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:domain":
        t, k = build_domain_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:new_domain_prompt":
        t, k = build_new_domain_prompt_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:emergency_prompt":
        t, k = build_emergency_prompt_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "cmd:start_server":
        started = start_all_services()
        time.sleep(1.5)
        edit_admin_msg(
            chat_id, message_id,
            f"🚀 <b>Services Started:</b> {', '.join(started) if started else 'Already Running'}\n\n" + get_system_stats(),
            {
                "inline_keyboard": [
                    [{"text": "🔄 Refresh Status", "callback_data": "cmd:status"}, {"text": "🛑 Stop Server", "callback_data": "cmd:stop_server"}],
                    [{"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}]
                ]
            }
        )

    elif data == "cmd:stop_server":
        stop_all_services()
        edit_admin_msg(
            chat_id, message_id,
            "🛑 <b>Website is now completely OFFLINE 🔴</b>\n\nPHP & Tor processes terminated.",
            {
                "inline_keyboard": [
                    [{"text": "▶ Start Server", "callback_data": "cmd:start_server"}, {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}]
                ]
            }
        )

    elif data == "cmd:restart_server":
        stop_all_services()
        time.sleep(1)
        start_all_services()
        time.sleep(1.5)
        edit_admin_msg(
            chat_id, message_id,
            "🔄 <b>Server Rebooted Successfully!</b>\n\n" + get_system_stats(),
            {
                "inline_keyboard": [
                    [{"text": "🔄 Refresh Status", "callback_data": "cmd:status"}, {"text": "🔙 Main Menu", "callback_data": "cmd:main_menu"}]
                ]
            }
        )

    elif data.startswith("user_view:"):
        username = data.split(":", 1)[1]
        t, k = build_user_detail_view(username)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data.startswith("action:zero_bal:"):
        username = data.split(":", 2)[2]
        ok, res = update_user_balance_action(username, 0, mode="zero")
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_user_detail_view(username)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data.startswith("action:toggle_ban:"):
        username = data.split(":", 2)[2]
        ok, res = toggle_user_suspend_action(username)
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_user_detail_view(username)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data.startswith("action:del_user:"):
        username = data.split(":", 2)[2]
        ok, res = delete_user_action(username)
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_users_hub_view(1)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data.startswith("prompt:add_bal:"):
        username = data.split(":", 2)[2]
        ADMIN_STATE[str(chat_id)] = {"mode": "add_balance", "target": username}
        send_admin_msg(f"💬 <b>Please reply with the amount to ADD to @{username}:</b>\n<i>Example: <code>50.00</code> or <code>100</code> (Send /cancel to abort)</i>")

    elif data.startswith("prompt:ded_bal:"):
        username = data.split(":", 2)[2]
        ADMIN_STATE[str(chat_id)] = {"mode": "deduct_balance", "target": username}
        send_admin_msg(f"💬 <b>Please reply with the amount to DEDUCT from @{username}:</b>\n<i>Example: <code>25.00</code> (Send /cancel to abort)</i>")

    elif data.startswith("prompt:set_bal:"):
        username = data.split(":", 2)[2]
        ADMIN_STATE[str(chat_id)] = {"mode": "set_balance", "target": username}
        send_admin_msg(f"💬 <b>Please reply with the EXACT balance to set for @{username}:</b>\n<i>Example: <code>150.00</code> (Send /cancel to abort)</i>")

    elif data.startswith("prompt:set_pass:"):
        username = data.split(":", 2)[2]
        ADMIN_STATE[str(chat_id)] = {"mode": "set_password", "target": username}
        send_admin_msg(f"💬 <b>Please reply with the NEW PASSWORD for @{username}:</b>\n<i>(Send /cancel to abort)</i>")

    elif data.startswith("prompt:set_pin:"):
        username = data.split(":", 2)[2]
        ADMIN_STATE[str(chat_id)] = {"mode": "set_pin", "target": username}
        send_admin_msg(f"💬 <b>Please reply with the NEW SECONDARY PIN for @{username}:</b>\n<i>(Send /cancel to abort)</i>")

    elif data.startswith("prompt:set_tier:"):
        username = data.split(":", 2)[2]
        ADMIN_STATE[str(chat_id)] = {"mode": "set_tier", "target": username}
        send_admin_msg(f"💬 <b>Please reply with the NEW TIER for @{username}:</b>\n<i>Example: <code>Verified VIP Member</code> (Send /cancel to abort)</i>")

    elif data == "prompt:search_user":
        ADMIN_STATE[str(chat_id)] = {"mode": "search_user", "target": None}
        send_admin_msg("🔍 <b>Please send the username or email to search:</b>\n<i>(Send /cancel to abort)</i>")

    elif data == "prompt:add_user":
        ADMIN_STATE[str(chat_id)] = {"mode": "add_user", "target": None}
        send_admin_msg("➕ <b>Send new user credentials in format:</b>\n<code>username email password [balance]</code>\n<i>Example: <code>buyer99 buyer@gmail.com pass1234 25.00</code></i>")

    elif data.startswith("ticket_view:"):
        t_id = data.split(":", 1)[1]
        t, k = build_ticket_detail_view(t_id)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data.startswith("prompt:reply_ticket:"):
        t_id = data.split(":", 2)[2]
        ADMIN_STATE[str(chat_id)] = {"mode": "reply_ticket", "target": t_id}
        send_admin_msg(f"✍️ <b>Please send your reply text for Ticket #{t_id}:</b>\n<i>(Send /cancel to abort)</i>")

    elif data.startswith("action:close_ticket:"):
        t_id = data.split(":", 2)[2]
        ok, res = close_ticket_action(t_id)
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_ticket_detail_view(t_id)
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "prompt:add_pack":
        ADMIN_STATE[str(chat_id)] = {"mode": "add_pack", "target": None}
        send_admin_msg("📦 <b>Send Wholesale Pack details in format:</b>\n<code>Title | Price | CardCount | Country</code>\n<i>Example: <code>USA Visa Gold Bundle | 50.00 | 50 | United States</code></i>")

    elif data.startswith("action:del_pack:"):
        pack_id = data.split(":", 2)[2]
        ok, res = delete_wholesale_pack_action(pack_id)
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_wholesale_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "prompt:post_news":
        ADMIN_STATE[str(chat_id)] = {"mode": "post_news", "target": None}
        send_admin_msg("📢 <b>Send Announcement in format:</b>\n<code>Title | Content | [Category]</code>\n<i>Example: <code>Fresh CC Base Added | High validity USA/UK bases uploaded | Updates</code></i>")

    elif data.startswith("action:del_news:"):
        news_id = data.split(":", 2)[2]
        ok, res = delete_news_action(news_id)
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_news_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "prompt:set_btc_addr":
        ADMIN_STATE[str(chat_id)] = {"mode": "set_crypto_btc_address", "target": None}
        send_admin_msg("₿ <b>Please send the new Bitcoin (BTC) address:</b>")

    elif data == "prompt:set_btc_rate":
        ADMIN_STATE[str(chat_id)] = {"mode": "set_crypto_btc_rate", "target": None}
        send_admin_msg("📊 <b>Please send the new BTC/USD rate:</b>\n<i>Example: <code>69,500.00</code></i>")

    elif data == "prompt:set_ltc_addr":
        ADMIN_STATE[str(chat_id)] = {"mode": "set_crypto_ltc_address", "target": None}
        send_admin_msg("Ł <b>Please send the new Litecoin (LTC) address:</b>")

    elif data == "prompt:set_ltc_rate":
        ADMIN_STATE[str(chat_id)] = {"mode": "set_crypto_ltc_rate", "target": None}
        send_admin_msg("📊 <b>Please send the new LTC/USD rate:</b>\n<i>Example: <code>75.50</code></i>")

    elif data == "prompt:set_usdt_addr":
        ADMIN_STATE[str(chat_id)] = {"mode": "set_crypto_usdt_address", "target": None}
        send_admin_msg("₮ <b>Please send the new USDT (TRC20) address:</b>")

    elif data == "prompt:set_min_dep":
        ADMIN_STATE[str(chat_id)] = {"mode": "set_crypto_min_deposit", "target": None}
        send_admin_msg("💵 <b>Please send the minimum deposit in USD:</b>\n<i>Example: <code>10.00</code></i>")

    elif data == "prompt:set_comm_percent":
        ADMIN_STATE[str(chat_id)] = {"mode": "set_crypto_referral_commission_percent", "target": None}
        send_admin_msg("🎁 <b>Please send the referral commission percent:</b>\n<i>Example: <code>50.00</code></i>")

    elif data.startswith("approve_deposit:"):
        dep_id = data.split(":", 1)[1]
        ok, res_msg = approve_deposit_action(dep_id)
        if ok:
            answer_callback(cq_id, f"✅ Deposit #{dep_id} Approved!", show_alert=True)
            orig_text = msg.get("text") or ""
            if "NEW DEPOSIT" in orig_text or "PENDING DEPOSIT" in orig_text:
                new_text = orig_text + f"\n\n✅ <b>[APPROVED - {res_msg}]</b>"
                edit_admin_msg(chat_id, message_id, new_text, {
                    "inline_keyboard": [[{"text": "🔙 Open Admin Menu", "callback_data": "cmd:main_menu"}]]
                })
            else:
                t, k = build_pending_deposits_view()
                edit_admin_msg(chat_id, message_id, t, k)
        else:
            answer_callback(cq_id, f"❌ Error: {res_msg}", show_alert=True)

    elif data.startswith("reject_deposit:"):
        dep_id = data.split(":", 1)[1]
        ok, res_msg = reject_deposit_action(dep_id)
        if ok:
            answer_callback(cq_id, f"❌ Deposit #{dep_id} Rejected", show_alert=False)
            orig_text = msg.get("text") or ""
            if "NEW DEPOSIT" in orig_text or "PENDING DEPOSIT" in orig_text:
                new_text = orig_text + f"\n\n❌ <b>[REJECTED BY ADMIN]</b>"
                edit_admin_msg(chat_id, message_id, new_text, {
                    "inline_keyboard": [[{"text": "🔙 Open Admin Menu", "callback_data": "cmd:main_menu"}]]
                })
            else:
                t, k = build_pending_deposits_view()
                edit_admin_msg(chat_id, message_id, t, k)
        else:
            answer_callback(cq_id, f"❌ Error: {res_msg}", show_alert=True)

    elif data == "action:clear_sold_cards":
        ok, res = clear_sold_cards_action()
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_cards_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "action:clear_all_cards":
        ok, res = clear_all_cards_action()
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_cards_hub_view()
        edit_admin_msg(chat_id, message_id, t, k)

    elif data == "action:clear_all_orders":
        ok, res = clear_all_orders_action()
        answer_callback(cq_id, res, show_alert=True)
        t, k = build_orders_hub_view(1)
        edit_admin_msg(chat_id, message_id, t, k)

# ----------------------------------------------------------------------
# 13. REAL-TIME DEPOSIT ALERT MONITOR BACKGROUND THREAD
# ----------------------------------------------------------------------
def deposit_alert_monitor_loop():
    """Continuously checks for un-alerted pending deposits and pushes instant notification to Admin Telegram."""
    while True:
        try:
            conn = get_db()
            c = conn.cursor()
            c.execute("SELECT * FROM deposits WHERE status = 'pending' AND (admin_notes IS NULL OR admin_notes NOT LIKE '%[ALERTED]%')")
            new_deposits = c.fetchall()

            for dep in new_deposits:
                user = get_user_by_name_or_id(dep["username"])
                user_bal = float(user["balance"] or 0.0) if user else 0.0
                
                tg_user_info = dep["telegram_username"] if ("telegram_username" in dep.keys() and dep["telegram_username"]) else "Not Provided"
                txid_info = dep["txid"] if ("txid" in dep.keys() and dep["txid"]) else "DIRECT_DEPOSIT"

                alert_text = (
                    "🚨 <b>[PAYATE CC] NEW DEPOSIT SUBMITTED!</b>\n"
                    "━━━━━━━━━━━━━━━━━━━━\n"
                    f"👤 <b>Account Name:</b> @{dep['username']}\n"
                    f"📱 <b>Telegram:</b> <b>{tg_user_info}</b>\n"
                    f"💵 <b>Amount:</b> <b>${float(dep['amount']):.2f} USD</b>\n"
                    f"💎 <b>Gateway:</b> {dep['currency']}\n"
                    f"🏷️ <b>Ref / Trx:</b> <code>{dep['trx_id']}</code>\n"
                    f"📝 <b>Sender / TxID:</b> <code>{txid_info}</code>\n"
                    f"🏦 <b>Address:</b> <code>{dep['address']}</code>\n"
                    f"💳 <b>Current Balance:</b> ${user_bal:.2f}\n"
                    f"📅 <b>Time:</b> {dep['created_at']} UTC\n"
                    "━━━━━━━━━━━━━━━━━━━━\n"
                    "⚡ <i>Click below to instantly approve or reject:</i>"
                )

                keyboard = {
                    "inline_keyboard": [
                        [
                            {"text": f"✅ Approve #${dep['id']} (${float(dep['amount']):.2f})", "callback_data": f"approve_deposit:{dep['id']}"},
                            {"text": f"❌ Reject", "callback_data": f"reject_deposit:{dep['id']}"}
                        ]
                    ]
                }

                send_admin_msg(alert_text, keyboard)

                # Mark as alerted in SQLite
                now_str = get_now_utc()
                curr_notes = dep["admin_notes"] or ""
                new_notes = (curr_notes + " [ALERTED]").strip()
                c.execute("UPDATE deposits SET admin_notes = ?, updated_at = ? WHERE id = ?", (new_notes, now_str, dep["id"]))
                conn.commit()

            conn.close()
        except Exception:
            pass
        time.sleep(3)

# ----------------------------------------------------------------------
# 14. SERVER HEALTH MONITOR & MAIN LOOP
# ----------------------------------------------------------------------
def health_monitor_loop():
    last_state = None
    while True:
        try:
            php = is_port_listening("127.0.0.1", 8000)
            tor = is_proc_running("tor.exe")
            current_state = (php, tor)

            if last_state is not None:
                if last_state == (True, True) and current_state != (True, True):
                    send_admin_msg(
                        f"🚨 <b>[CRITICAL ALERT] SERVER DOWNTIME DETECTED!</b>\n\n"
                        f"Laravel Web: {'🟢 Running' if php else '🔴 CRASHED/STOPPED'}\n"
                        f"Tor Network: {'🟢 Running' if tor else '🔴 CRASHED/STOPPED'}\n\n"
                        f"⚡ <i>Use /start_server to recover immediately.</i>",
                        get_main_menu_keyboard()
                    )
            last_state = current_state
        except Exception:
            pass
        time.sleep(30)

def main():
    enforce_single_instance()

    print("==================================================")
    print(f"[+] PAYATE CC ADMIN TELEGRAM BOT DAEMON STARTED")
    print(f"[+] Admin ID: {ADMIN_CHAT_ID}")
    print(f"[+] Bot Token: {BOT_TOKEN[:10]}...")
    print("==================================================")

    # Start health monitor in background
    monitor_th = threading.Thread(target=health_monitor_loop, daemon=True)
    monitor_th.start()

    # Start real-time deposit push monitor in background
    dep_monitor_th = threading.Thread(target=deposit_alert_monitor_loop, daemon=True)
    dep_monitor_th.start()

    # Start Auto Git Sync Engine background watcher
    try:
        start_background_git_sync_thread()
    except Exception as e:
        print(f"[!] Git Sync thread start error: {e}")

    # Flush old pending updates before polling
    try:
        flush_res = tg_request("getUpdates", {"offset": -1, "timeout": 0}, timeout=10)
        offset = None
        if flush_res.get("ok") and flush_res.get("result"):
            offset = flush_res["result"][-1]["update_id"] + 1
    except Exception:
        offset = None

    while True:
        try:
            payload = {
                "timeout": 10,
                "allowed_updates": ["message", "callback_query"]
            }
            if offset:
                payload["offset"] = offset
            
            res = tg_request("getUpdates", payload, timeout=25)
            if res.get("ok"):
                updates = res.get("result", [])
                for u in updates:
                    offset = u["update_id"] + 1

                    if "message" in u:
                        process_message(u["message"])
                    elif "callback_query" in u:
                        process_callback_query(u["callback_query"])
            else:
                time.sleep(0.5)
        except Exception:
            time.sleep(0.5)

if __name__ == "__main__":
    main()
