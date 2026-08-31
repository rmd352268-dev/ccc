import os
import sys
import time
import json
import socket
import sqlite3
import datetime
import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

# ----------------------------------------------------------------------
# PATHS & CONFIGURATION
# ----------------------------------------------------------------------
PROJECT_DIR = os.path.dirname(os.path.abspath(__file__))
CONFIG_PATH = os.path.join(PROJECT_DIR, "support_bot_config.json")
DB_PATH = os.path.join(PROJECT_DIR, "database", "support_bot.sqlite")

DEFAULT_CONFIG = {
    "bot_token": "",
    "support_bot_token": "",
    "admin_bot_token": "",
    "admin_chat_id": "",
    "support_bot_username": "payate_desk_bot",
    "tor_socks_proxy": "socks5h://127.0.0.1:9050",
    "use_tor": True,
    "website_url": "http://127.0.0.1:8000",
    "onion_url": "http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion"
}

def load_config():
    config = dict(DEFAULT_CONFIG)
    
    # 1. Load from support_bot_config.json if present
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                user_conf = json.load(f)
                config.update(user_conf)
        except Exception:
            pass

    # 2. Check .env file for environment credentials
    env_path = os.path.join(PROJECT_DIR, ".env")
    if os.path.exists(env_path):
        try:
            with open(env_path, "r", encoding="utf-8") as f:
                for line in f:
                    line = line.strip()
                    if line.startswith("TELEGRAM_SUPPORT_BOT_TOKEN="):
                        val = line.split("=", 1)[1].strip().strip('"').strip("'")
                        if val: config["support_bot_token"] = val
                    elif line.startswith("TELEGRAM_BOT_TOKEN=") and not config.get("admin_bot_token"):
                        val = line.split("=", 1)[1].strip().strip('"').strip("'")
                        if val: config["admin_bot_token"] = val
                    elif line.startswith("TELEGRAM_ADMIN_CHAT_ID=") and not config.get("admin_chat_id"):
                        val = line.split("=", 1)[1].strip().strip('"').strip("'")
                        if val: config["admin_chat_id"] = val
        except Exception:
            pass

    # 3. Fallback to SQLite crypto_settings for Admin Bot Token & Admin Chat ID
    main_db_path = os.path.join(PROJECT_DIR, "database", "database.sqlite")
    if os.path.exists(main_db_path) and (not config.get("admin_bot_token") or not config.get("admin_chat_id")):
        try:
            conn = sqlite3.connect(main_db_path, timeout=5)
            c = conn.cursor()
            c.execute("SELECT telegram_bot_token, telegram_chat_id FROM crypto_settings WHERE id = 1")
            row = c.fetchone()
            conn.close()
            if row:
                if not config.get("admin_bot_token") and row[0]:
                    config["admin_bot_token"] = str(row[0]).strip()
                if not config.get("admin_chat_id") and row[1]:
                    config["admin_chat_id"] = str(row[1]).strip()
        except Exception:
            pass

    return config

CONFIG = load_config()
SUPPORT_BOT_TOKEN = str(CONFIG.get("support_bot_token") or CONFIG.get("bot_token") or "").strip()
ADMIN_BOT_TOKEN = str(CONFIG.get("admin_bot_token") or "").strip()
ADMIN_CHAT_ID = str(CONFIG.get("admin_chat_id") or "").strip()
TOR_SOCKS_PROXY = CONFIG.get("tor_socks_proxy", "socks5h://127.0.0.1:9050")

SUPPORT_BOT_API_URL = f"https://api.telegram.org/bot{SUPPORT_BOT_TOKEN}"
ADMIN_BOT_API_URL = f"https://api.telegram.org/bot{ADMIN_BOT_TOKEN}"

# ----------------------------------------------------------------------
# HTTP SESSIONS (TOR SOCKS5 WITH DIRECT FALLBACK)
# ----------------------------------------------------------------------
_tor_proxy_status = None
_tor_proxy_last_check = 0

def is_tor_proxy_alive():
    global _tor_proxy_status, _tor_proxy_last_check
    now = time.time()
    if _tor_proxy_status is not None and (now - _tor_proxy_last_check < 30):
        return _tor_proxy_status
    try:
        s = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        s.settimeout(0.4)
        res = s.connect_ex(("127.0.0.1", 9050))
        s.close()
        _tor_proxy_status = (res == 0)
    except Exception:
        _tor_proxy_status = False
    _tor_proxy_last_check = now
    return _tor_proxy_status

http_session = requests.Session()
adapter = HTTPAdapter(
    pool_connections=25,
    pool_maxsize=25,
    max_retries=Retry(total=2, backoff_factor=0.2, status_forcelist=[500, 502, 503, 504])
)
http_session.mount("https://", adapter)
http_session.mount("http://", adapter)
if CONFIG.get("use_tor", True):
    http_session.proxies = {
        "http": TOR_SOCKS_PROXY,
        "https": TOR_SOCKS_PROXY
    }

direct_session = requests.Session()
direct_session.mount("https://", adapter)
direct_session.mount("http://", adapter)

def tg_api_call(bot_type, method, payload=None, timeout=20):
    """
    bot_type: 'support' or 'admin'
    """
    base_url = SUPPORT_BOT_API_URL if bot_type == "support" else ADMIN_BOT_API_URL
    url = f"{base_url}/{method}"
    
    if CONFIG.get("use_tor", True) and is_tor_proxy_alive():
        try:
            res = http_session.post(url, json=payload or {}, timeout=timeout)
            return res.json()
        except Exception:
            pass
    try:
        res = direct_session.post(url, json=payload or {}, timeout=timeout)
        return res.json()
    except Exception as e:
        return {"ok": False, "error": str(e)}

# ----------------------------------------------------------------------
# DATABASE ENGINE (SQLITE)
# ----------------------------------------------------------------------
def get_db():
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
    conn = sqlite3.connect(DB_PATH, timeout=15)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA journal_mode = WAL")
    conn.execute("PRAGMA busy_timeout = 10000")
    return conn

def init_support_db():
    conn = get_db()
    c = conn.cursor()
    c.execute("""
        CREATE TABLE IF NOT EXISTS support_users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            telegram_id INTEGER UNIQUE,
            first_name TEXT,
            last_name TEXT,
            username TEXT,
            is_banned INTEGER DEFAULT 0,
            total_messages INTEGER DEFAULT 0,
            first_seen TEXT,
            last_seen TEXT
        )
    """)
    c.execute("""
        CREATE TABLE IF NOT EXISTS message_mappings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            admin_bot_msg_id INTEGER UNIQUE,
            support_bot_msg_id INTEGER,
            user_id INTEGER,
            user_msg_id INTEGER,
            created_at TEXT
        )
    """)
    
    # Check columns in message_mappings and auto-upgrade if schema was created with older format
    try:
        c.execute("PRAGMA table_info(message_mappings)")
        cols = [col[1] for col in c.fetchall()]
        if "admin_bot_msg_id" not in cols:
            if "admin_msg_id" in cols:
                c.execute("ALTER TABLE message_mappings RENAME COLUMN admin_msg_id TO admin_bot_msg_id")
            else:
                c.execute("ALTER TABLE message_mappings ADD COLUMN admin_bot_msg_id INTEGER")
        if "support_bot_msg_id" not in cols:
            c.execute("ALTER TABLE message_mappings ADD COLUMN support_bot_msg_id INTEGER")
    except Exception:
        pass

    c.execute("""
        CREATE TABLE IF NOT EXISTS support_logs (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER,
            sender_type TEXT,
            message_text TEXT,
            media_type TEXT,
            created_at TEXT
        )
    """)
    conn.commit()
    conn.close()

# Auto-initialize DB on import
init_support_db()

def get_now_utc():
    return datetime.datetime.now(datetime.timezone.utc).strftime("%Y-%m-%d %H:%M:%S")

def record_user_activity(user_obj):
    telegram_id = user_obj.get("id")
    first_name = user_obj.get("first_name", "")
    last_name = user_obj.get("last_name", "")
    username = user_obj.get("username", "")
    now = get_now_utc()

    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT id, is_banned, total_messages FROM support_users WHERE telegram_id = ?", (telegram_id,))
    row = c.fetchone()
    if row:
        c.execute("""
            UPDATE support_users 
            SET first_name = ?, last_name = ?, username = ?, total_messages = total_messages + 1, last_seen = ?
            WHERE telegram_id = ?
        """, (first_name, last_name, username, now, telegram_id))
        is_banned = bool(row["is_banned"])
    else:
        c.execute("""
            INSERT INTO support_users (telegram_id, first_name, last_name, username, is_banned, total_messages, first_seen, last_seen)
            VALUES (?, ?, ?, ?, 0, 1, ?, ?)
        """, (telegram_id, first_name, last_name, username, now, now))
        is_banned = False
    conn.commit()
    conn.close()
    return is_banned

def save_message_mapping(admin_bot_msg_id, user_id, user_msg_id, support_bot_msg_id=None):
    if not user_id:
        return
    conn = get_db()
    c = conn.cursor()
    c.execute("""
        INSERT OR REPLACE INTO message_mappings (admin_bot_msg_id, support_bot_msg_id, user_id, user_msg_id, created_at)
        VALUES (?, ?, ?, ?, ?)
    """, (admin_bot_msg_id, support_bot_msg_id, user_id, user_msg_id, get_now_utc()))
    conn.commit()
    conn.close()

def get_user_id_by_admin_msg(admin_msg_id):
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT user_id, user_msg_id FROM message_mappings WHERE admin_bot_msg_id = ? OR support_bot_msg_id = ?", (admin_msg_id, admin_msg_id))
    row = c.fetchone()
    conn.close()
    if row:
        return row["user_id"], row["user_msg_id"]
    return None, None

def log_message(user_id, sender_type, message_text, media_type="text"):
    try:
        conn = get_db()
        c = conn.cursor()
        c.execute("""
            INSERT INTO support_logs (user_id, sender_type, message_text, media_type, created_at)
            VALUES (?, ?, ?, ?, ?)
        """, (user_id, sender_type, (message_text or "")[:1500], media_type, get_now_utc()))
        conn.commit()
        conn.close()
    except Exception:
        pass

def is_user_banned(telegram_id):
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT is_banned FROM support_users WHERE telegram_id = ?", (telegram_id,))
    row = c.fetchone()
    conn.close()
    return bool(row and row["is_banned"])

def set_user_ban_status(telegram_id, banned=True):
    conn = get_db()
    c = conn.cursor()
    c.execute("UPDATE support_users SET is_banned = ? WHERE telegram_id = ?", (1 if banned else 0, telegram_id))
    conn.commit()
    conn.close()

def get_user_info(telegram_id):
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT * FROM support_users WHERE telegram_id = ?", (telegram_id,))
    user = c.fetchone()
    conn.close()
    return user

def get_recent_users(limit=15):
    conn = get_db()
    c = conn.cursor()
    c.execute("""
        SELECT telegram_id, first_name, last_name, username, total_messages, is_banned, last_seen
        FROM support_users
        ORDER BY last_seen DESC
        LIMIT ?
    """, (limit,))
    rows = c.fetchall()
    conn.close()
    return rows

def get_user_history(user_id, limit=10):
    conn = get_db()
    c = conn.cursor()
    c.execute("""
        SELECT sender_type, message_text, media_type, created_at
        FROM support_logs
        WHERE user_id = ?
        ORDER BY id DESC
        LIMIT ?
    """, (user_id, limit))
    rows = c.fetchall()
    conn.close()
    return list(reversed(rows))

def get_support_stats():
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT COUNT(*) FROM support_users")
    total_users = c.fetchone()[0]
    c.execute("SELECT COUNT(*) FROM support_users WHERE is_banned = 1")
    banned_users = c.fetchone()[0]
    c.execute("SELECT COUNT(*) FROM support_logs WHERE sender_type = 'user'")
    user_msgs = c.fetchone()[0]
    c.execute("SELECT COUNT(*) FROM support_logs WHERE sender_type = 'admin'")
    admin_replies = c.fetchone()[0]
    conn.close()
    return {
        "total_users": total_users,
        "banned_users": banned_users,
        "user_msgs": user_msgs,
        "admin_replies": admin_replies
    }

# ----------------------------------------------------------------------
# USER -> ADMIN RELAY ENGINE
# ----------------------------------------------------------------------
def forward_user_message_to_admin(msg):
    """
    Called when a customer messages the Support Bot.
    Relays the customer message with user identity badge and action buttons to the Admin Bot.
    """
    from_user = msg.get("from", {})
    user_id = from_user.get("id")
    user_msg_id = msg.get("message_id")
    first_name = from_user.get("first_name", "")
    last_name = from_user.get("last_name", "")
    username = from_user.get("username", "")
    full_name = f"{first_name} {last_name}".strip() or "Customer"
    uname_str = f"@{username}" if username else "<i>None</i>"

    # Identify media & content
    media_type = "text"
    text_content = msg.get("text", "")
    photo_file_id = None
    doc_file_id = None
    voice_file_id = None
    video_file_id = None
    caption_text = msg.get("caption", "")

    if "photo" in msg:
        media_type = "photo"
        photo_file_id = msg["photo"][-1]["file_id"]
        text_content = caption_text or "[Photo / Screenshot Attached]"
    elif "document" in msg:
        media_type = "document"
        doc_file_id = msg["document"]["file_id"]
        doc_name = msg["document"].get("file_name", "document")
        text_content = f"{caption_text}\n[Document: {doc_name}]".strip()
    elif "voice" in msg:
        media_type = "voice"
        voice_file_id = msg["voice"]["file_id"]
        text_content = "[Voice Note Attached]"
    elif "video" in msg:
        media_type = "video"
        video_file_id = msg["video"]["file_id"]
        text_content = caption_text or "[Video Attached]"
    elif "sticker" in msg:
        media_type = "sticker"
        emoji = msg.get("sticker", {}).get("emoji", "")
        text_content = f"[Sticker {emoji}]"

    # Log to SQLite
    log_message(user_id, "user", text_content, media_type)

    # Action Buttons for Admin Bot
    reply_markup = {
        "inline_keyboard": [
            [
                {"text": "↩️ Reply to User", "callback_data": f"support_reply:{user_id}"},
                {"text": "🚫 Ban User", "callback_data": f"support_ban:{user_id}"}
            ],
            [
                {"text": "📜 User History", "callback_data": f"support_history:{user_id}"},
                {"text": "🔒 Resolve Ticket", "callback_data": f"support_close:{user_id}"}
            ]
        ]
    }

    header_badge = (
        "📩 <b>[LIVE CUSTOMER SUPPORT MESSAGE]</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"👤 <b>Customer:</b> <code>{full_name}</code>\n"
        f"🏷️ <b>Username:</b> {uname_str}\n"
        f"🆔 <b>Telegram ID:</b> <code>{user_id}</code>\n"
        f"🕒 <b>Time:</b> {get_now_utc()} UTC\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
    )

    admin_bot_msg_id = None

    # ------------------------------------------------------------------
    # Forward exclusively into the Live Support Bot (@payate_desk_bot)
    # ------------------------------------------------------------------
    if media_type == "text":
        full_text = f"{header_badge}💬 <b>Message:</b>\n{text_content}"
        res = tg_api_call("support", "sendMessage", {
            "chat_id": ADMIN_CHAT_ID,
            "text": full_text,
            "parse_mode": "HTML",
            "reply_markup": reply_markup,
            "disable_web_page_preview": True
        })
        if res.get("ok"):
            admin_bot_msg_id = res.get("result", {}).get("message_id")
    elif media_type == "photo" and photo_file_id:
        full_caption = f"{header_badge}💬 <b>Caption:</b>\n{caption_text}".strip()
        res = tg_api_call("support", "sendPhoto", {
            "chat_id": ADMIN_CHAT_ID,
            "photo": photo_file_id,
            "caption": full_caption,
            "parse_mode": "HTML",
            "reply_markup": reply_markup
        })
        if res.get("ok"):
            admin_bot_msg_id = res.get("result", {}).get("message_id")
    elif media_type == "document" and doc_file_id:
        full_caption = f"{header_badge}💬 <b>Caption:</b>\n{caption_text}".strip()
        res = tg_api_call("support", "sendDocument", {
            "chat_id": ADMIN_CHAT_ID,
            "document": doc_file_id,
            "caption": full_caption,
            "parse_mode": "HTML",
            "reply_markup": reply_markup
        })
        if res.get("ok"):
            admin_bot_msg_id = res.get("result", {}).get("message_id")
    elif media_type == "voice" and voice_file_id:
        res = tg_api_call("support", "sendVoice", {
            "chat_id": ADMIN_CHAT_ID,
            "voice": voice_file_id,
            "caption": header_badge,
            "parse_mode": "HTML",
            "reply_markup": reply_markup
        })
        if res.get("ok"):
            admin_bot_msg_id = res.get("result", {}).get("message_id")
    elif media_type == "video" and video_file_id:
        full_caption = f"{header_badge}💬 <b>Caption:</b>\n{caption_text}".strip()
        res = tg_api_call("support", "sendVideo", {
            "chat_id": ADMIN_CHAT_ID,
            "video": video_file_id,
            "caption": full_caption,
            "parse_mode": "HTML",
            "reply_markup": reply_markup
        })
        if res.get("ok"):
            admin_bot_msg_id = res.get("result", {}).get("message_id")
    else:
        # Fallback text relay
        full_text = f"{header_badge}💬 <b>Message:</b>\n{text_content}"
        res = tg_api_call("support", "sendMessage", {
            "chat_id": ADMIN_CHAT_ID,
            "text": full_text,
            "parse_mode": "HTML",
            "reply_markup": reply_markup,
            "disable_web_page_preview": True
        })
        if res.get("ok"):
            admin_bot_msg_id = res.get("result", {}).get("message_id")

    if admin_bot_msg_id:
        save_message_mapping(admin_bot_msg_id, user_id, user_msg_id)

    return admin_bot_msg_id

# ----------------------------------------------------------------------
# ADMIN -> USER ANONYMOUS REPLY ENGINE
# ----------------------------------------------------------------------
def deliver_admin_reply_to_user(user_id, admin_msg=None, custom_text=None):
    """
    Delivers an Admin's reply to the customer via the Support Bot API.
    Guarantees complete anonymity for the Admin.
    """
    if not user_id:
        return False, "Invalid Customer ID."

    if is_user_banned(user_id):
        return False, f"User {user_id} is currently banned."

    text_to_send = custom_text
    if text_to_send is None and admin_msg:
        text_to_send = admin_msg.get("text", "")

    # Media handling if admin sends a photo/document/voice
    if admin_msg and "photo" in admin_msg and custom_text is None:
        photo_id = admin_msg["photo"][-1]["file_id"]
        caption = admin_msg.get("caption", "")
        formatted_caption = f"🛡️ <b>Payate Support:</b>\n\n{caption}".strip() if caption else "🛡️ <b>Payate Support Desk</b>"
        res = tg_api_call("support", "sendPhoto", {
            "chat_id": user_id,
            "photo": photo_id,
            "caption": formatted_caption,
            "parse_mode": "HTML"
        })
        log_message(user_id, "admin", caption or "[Photo]", "photo")
    elif admin_msg and "document" in admin_msg and custom_text is None:
        doc_id = admin_msg["document"]["file_id"]
        caption = admin_msg.get("caption", "")
        formatted_caption = f"🛡️ <b>Payate Support:</b>\n\n{caption}".strip() if caption else "🛡️ <b>Payate Support Desk</b>"
        res = tg_api_call("support", "sendDocument", {
            "chat_id": user_id,
            "document": doc_id,
            "caption": formatted_caption,
            "parse_mode": "HTML"
        })
        log_message(user_id, "admin", caption or "[Document]", "document")
    elif admin_msg and "voice" in admin_msg and custom_text is None:
        voice_id = admin_msg["voice"]["file_id"]
        res = tg_api_call("support", "sendVoice", {
            "chat_id": user_id,
            "voice": voice_id,
            "caption": "🛡️ <b>Payate Support Audio Note</b>",
            "parse_mode": "HTML"
        })
        log_message(user_id, "admin", "[Voice Note]", "voice")
    elif admin_msg and "video" in admin_msg and custom_text is None:
        video_id = admin_msg["video"]["file_id"]
        caption = admin_msg.get("caption", "")
        formatted_caption = f"🛡️ <b>Payate Support:</b>\n\n{caption}".strip() if caption else "🛡️ <b>Payate Support Desk</b>"
        res = tg_api_call("support", "sendVideo", {
            "chat_id": user_id,
            "video": video_id,
            "caption": formatted_caption,
            "parse_mode": "HTML"
        })
        log_message(user_id, "admin", caption or "[Video]", "video")
    else:
        if not text_to_send:
            return False, "No reply message content provided."
        formatted_reply = (
            "🛡️ <b>Payate Support Desk:</b>\n"
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            f"{text_to_send}\n"
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        )
        res = tg_api_call("support", "sendMessage", {
            "chat_id": user_id,
            "text": formatted_reply,
            "parse_mode": "HTML",
            "disable_web_page_preview": True
        })
        log_message(user_id, "admin", text_to_send, "text")

    if res.get("ok"):
        return True, "Reply delivered successfully."
    else:
        err = res.get("description", "Unknown Telegram API error")
        return False, f"Failed to deliver: {err}"

def broadcast_to_support_users(message_text):
    """
    Broadcasts announcement message to all registered support users via Support Bot.
    """
    if not message_text:
        return 0, 0
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT telegram_id FROM support_users WHERE is_banned = 0")
    users = [row[0] for row in c.fetchall() if str(row[0]) != str(ADMIN_CHAT_ID)]
    conn.close()

    sent = 0
    failed = 0
    formatted_msg = (
        "📢 <b>ANNOUNCEMENT FROM PAYATE SUPPORT</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"{message_text}\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    )

    for uid in users:
        res = tg_api_call("support", "sendMessage", {
            "chat_id": uid,
            "text": formatted_msg,
            "parse_mode": "HTML",
            "disable_web_page_preview": True
        })
        if res.get("ok"):
            sent += 1
        else:
            failed += 1
        time.sleep(0.05)

    return sent, failed
