import os
import sys
import time
import json
import sqlite3
import datetime
import threading
import requests
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

# ----------------------------------------------------------------------
# WINDOWS OUTPUT ENCODING COMPATIBILITY
# ----------------------------------------------------------------------
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
# PATHS & CONFIGURATION
# ----------------------------------------------------------------------
PROJECT_DIR = r"C:\Users\hp\Desktop\ccc"
CONFIG_PATH = os.path.join(PROJECT_DIR, "support_bot_config.json")
DB_PATH = os.path.join(PROJECT_DIR, "database", "support_bot.sqlite")
MAIN_DB_PATH = os.path.join(PROJECT_DIR, "database", "database.sqlite")

DEFAULT_CONFIG = {
    "bot_token": "8615399993:AAEwJGBH7EMQK88sNQzmF1ExNp_tQU1sMVs",
    "admin_chat_id": "8814743492",
    "support_bot_username": "PayateSupport_Bot",
    "tor_socks_proxy": "socks5h://127.0.0.1:9050",
    "use_tor": True,
    "welcome_message": "👋 <b>Welcome to Payate CC 24/7 Live Customer Support!</b>\n\n💬 How can we assist you today? Please send your questions, issues, order number, or screenshots right here.\n\n🛡️ A support specialist will reply directly in this chat shortly.",
    "ack_message": "✅ <b>Message Received!</b>\n\nYour message has been delivered to our support desk. A support agent will respond to you directly in this chat shortly.",
    "ticket_closed_message": "🔒 <b>Support Ticket Resolved</b>\n\nYour support inquiry has been marked as resolved by our team. If you need any further assistance, feel free to send a new message anytime!"
}

def load_config():
    config = dict(DEFAULT_CONFIG)
    if os.path.exists(CONFIG_PATH):
        try:
            with open(CONFIG_PATH, "r", encoding="utf-8") as f:
                user_conf = json.load(f)
                config.update(user_conf)
        except Exception:
            pass
    return config

CONFIG = load_config()
BOT_TOKEN = str(CONFIG.get("bot_token", DEFAULT_CONFIG["bot_token"])).strip()
ADMIN_CHAT_ID = str(CONFIG.get("admin_chat_id", DEFAULT_CONFIG["admin_chat_id"])).strip()
TOR_SOCKS_PROXY = CONFIG.get("tor_socks_proxy", "socks5h://127.0.0.1:9050")

TELEGRAM_API_URL = f"https://api.telegram.org/bot{BOT_TOKEN}"
TELEGRAM_FILE_URL = f"https://api.telegram.org/file/bot{BOT_TOKEN}"

# In-memory admin state: e.g. {'replying_to_user': 12345678}
ADMIN_STATE = {}
USER_LAST_ACK = {}

# ----------------------------------------------------------------------
# SINGLETON INSTANCE LOCK (WINDOWS NAMED MUTEX)
# ----------------------------------------------------------------------
_bot_mutex = None

def enforce_single_instance():
    global _bot_mutex
    try:
        import ctypes
        _bot_mutex = ctypes.windll.kernel32.CreateMutexW(None, False, "Global\\PayateSupportTelegramBotMutex")
        if ctypes.windll.kernel32.GetLastError() == 183:  # ERROR_ALREADY_EXISTS
            print("Another instance of Payate Support Bot is already running. Exiting.")
            sys.exit(0)
    except Exception:
        pass

# ----------------------------------------------------------------------
# HTTP SESSIONS (TOR SOCKS5 WITH DIRECT FALLBACK)
# ----------------------------------------------------------------------
http_session = requests.Session()
adapter = HTTPAdapter(
    pool_connections=20,
    pool_maxsize=20,
    max_retries=Retry(total=2, backoff_factor=0.3, status_forcelist=[500, 502, 503, 504])
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

def tg_request(method, payload=None, timeout=20):
    url = f"{TELEGRAM_API_URL}/{method}"
    # Try SOCKS5 first if enabled
    if CONFIG.get("use_tor", True):
        try:
            res = http_session.post(url, json=payload or {}, timeout=timeout)
            return res.json()
        except Exception:
            pass
    # Fallback to direct connection
    try:
        res = direct_session.post(url, json=payload or {}, timeout=timeout)
        return res.json()
    except Exception as e:
        return {"ok": False, "error": str(e)}

# ----------------------------------------------------------------------
# DATABASE ENGINE (SQLITE)
# ----------------------------------------------------------------------
def get_db():
    conn = sqlite3.connect(DB_PATH, timeout=15)
    conn.row_factory = sqlite3.Row
    conn.execute("PRAGMA journal_mode = WAL")
    conn.execute("PRAGMA busy_timeout = 10000")
    return conn

def init_db():
    os.makedirs(os.path.dirname(DB_PATH), exist_ok=True)
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
            admin_msg_id INTEGER UNIQUE,
            user_id INTEGER,
            user_msg_id INTEGER,
            created_at TEXT
        )
    """)
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

def save_message_mapping(admin_msg_id, user_id, user_msg_id):
    if not admin_msg_id or not user_id:
        return
    conn = get_db()
    c = conn.cursor()
    c.execute("""
        INSERT OR REPLACE INTO message_mappings (admin_msg_id, user_id, user_msg_id, created_at)
        VALUES (?, ?, ?, ?)
    """, (admin_msg_id, user_id, user_msg_id, get_now_utc()))
    conn.commit()
    conn.close()

def get_user_id_by_admin_msg(admin_msg_id):
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT user_id, user_msg_id FROM message_mappings WHERE admin_msg_id = ?", (admin_msg_id,))
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

# ----------------------------------------------------------------------
# TELEGRAM HELPER FUNCTIONS
# ----------------------------------------------------------------------
def send_message(chat_id, text, reply_markup=None, reply_to_message_id=None):
    payload = {
        "chat_id": chat_id,
        "text": text,
        "parse_mode": "HTML",
        "disable_web_page_preview": True
    }
    if reply_markup:
        payload["reply_markup"] = reply_markup
    if reply_to_message_id:
        payload["reply_to_message_id"] = reply_to_message_id
    return tg_request("sendMessage", payload)

def answer_callback(callback_query_id, text="", show_alert=False):
    return tg_request("answerCallbackQuery", {
        "callback_query_id": callback_query_id,
        "text": text,
        "show_alert": show_alert
    })

def copy_message_to(target_chat_id, from_chat_id, message_id, caption=None, reply_markup=None):
    payload = {
        "chat_id": target_chat_id,
        "from_chat_id": from_chat_id,
        "message_id": message_id
    }
    if caption is not None:
        payload["caption"] = caption
        payload["parse_mode"] = "HTML"
    if reply_markup:
        payload["reply_markup"] = reply_markup
    return tg_request("copyMessage", payload)

def forward_message_to(target_chat_id, from_chat_id, message_id):
    return tg_request("forwardMessage", {
        "chat_id": target_chat_id,
        "from_chat_id": from_chat_id,
        "message_id": message_id
    })

# ----------------------------------------------------------------------
# ADMIN CONTROL & BROADCAST FUNCTIONS
# ----------------------------------------------------------------------
def send_admin_help(chat_id):
    help_text = (
        "🛡️ <b>PAYATE LIVE SUPPORT BOT — ADMIN DESK</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        "This bot relays customer inquiries in real-time. Your personal Telegram account is <b>100% hidden and anonymous</b>.\n\n"
        "<b>⚡ HOW TO REPLY TO USERS:</b>\n"
        "1. <b>Swipe & Reply:</b> Simply swipe/reply directly to any forwarded user message in Telegram and type your response.\n"
        "2. <b>Command Reply:</b> <code>/reply &lt;user_id&gt; &lt;your message&gt;</code>\n"
        "   <i>Shortcut:</i> <code>/r 12345678 Hello, how can I help?</code>\n"
        "3. <b>Interactive:</b> Click the <code>[ ↩️ Reply ]</code> button under any message.\n\n"
        "<b>📋 ADMIN MANAGEMENT COMMANDS:</b>\n"
        "• <code>/users</code> — List recent active support users\n"
        "• <code>/info &lt;user_id&gt;</code> — View user profile and message history\n"
        "• <code>/ban &lt;user_id&gt;</code> — Ban an abusive user or spammer\n"
        "• <code>/unban &lt;user_id&gt;</code> — Unban a user\n"
        "• <code>/banned</code> — List all banned user IDs\n"
        "• <code>/close &lt;user_id&gt;</code> — Send ticket resolved notice to user\n"
        "• <code>/broadcast &lt;message&gt;</code> — Send announcement to all users\n"
        "• <code>/stats</code> — View live support statistics\n"
        "• <code>/help</code> — Show this guide\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    )
    send_message(chat_id, help_text)

def show_support_stats(chat_id):
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

    text = (
        "📊 <b>PAYATE SUPPORT BOT LIVE STATS</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"👥 <b>Total Registered Users:</b> <code>{total_users}</code>\n"
        f"🚫 <b>Banned Users:</b> <code>{banned_users}</code>\n"
        f"📩 <b>User Messages Received:</b> <code>{user_msgs}</code>\n"
        f"🛡️ <b>Admin Replies Delivered:</b> <code>{admin_replies}</code>\n"
        f"🔒 <b>Tor Proxy Protection:</b> <code>{'ACTIVE (SOCKS5)' if CONFIG.get('use_tor') else 'DIRECT'}</code>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    )
    send_message(chat_id, text)

def show_recent_users(chat_id, limit=15):
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

    if not rows:
        send_message(chat_id, "ℹ️ No support users registered yet.")
        return

    text = "👥 <b>RECENT SUPPORT USERS</b>\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
    for r in rows:
        uname = f"@{r['username']}" if r['username'] else "No username"
        name = f"{r['first_name'] or ''} {r['last_name'] or ''}".strip() or "Anonymous"
        status_icon = "🚫 BANNED" if r['is_banned'] else "🟢 ACTIVE"
        text += (
            f"• <b>{name}</b> ({uname})\n"
            f"  🆔 <code>{r['telegram_id']}</code> | 💬 {r['total_messages']} msgs | {status_icon}\n"
            f"  🕒 Last active: <i>{r['last_seen']}</i>\n\n"
        )
    text += "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n<i>Use <code>/info &lt;id&gt;</code> or <code>/reply &lt;id&gt; &lt;text&gt;</code> to interact.</i>"
    send_message(chat_id, text)

def show_user_info(chat_id, user_id):
    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT * FROM support_users WHERE telegram_id = ?", (user_id,))
    user = c.fetchone()
    if not user:
        conn.close()
        send_message(chat_id, f"❌ User with Telegram ID <code>{user_id}</code> not found in support database.")
        return

    c.execute("""
        SELECT sender_type, message_text, media_type, created_at
        FROM support_logs
        WHERE user_id = ?
        ORDER BY id DESC
        LIMIT 6
    """, (user_id,))
    logs = c.fetchall()
    conn.close()

    uname = f"@{user['username']}" if user['username'] else "None"
    name = f"{user['first_name'] or ''} {user['last_name'] or ''}".strip() or "Anonymous"
    banned_status = "🚫 BANNED" if user['is_banned'] else "🟢 ACTIVE"

    text = (
        f"👤 <b>USER PROFILE: {name}</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"🆔 <b>Telegram ID:</b> <code>{user['telegram_id']}</code>\n"
        f"🏷️ <b>Username:</b> {uname}\n"
        f"📊 <b>Status:</b> {banned_status}\n"
        f"💬 <b>Total Messages:</b> {user['total_messages']}\n"
        f"📅 <b>First Seen:</b> {user['first_seen']}\n"
        f"🕒 <b>Last Seen:</b> {user['last_seen']}\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        "<b>📜 RECENT CONVERSATION HISTORY:</b>\n"
    )

    if logs:
        for log in reversed(logs):
            prefix = "👤 [User]" if log["sender_type"] == "user" else "🛡️ [Admin]"
            msg_snippet = (log["message_text"] or f"<{log['media_type']}>")[:100]
            text += f"• <i>{log['created_at']}</i> {prefix}: {msg_snippet}\n"
    else:
        text += "<i>No logged messages yet.</i>\n"

    text += "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"

    buttons = {
        "inline_keyboard": [
            [
                {"text": "↩️ Reply to User", "callback_data": f"reply_user:{user_id}"},
                {"text": "🚫 Unban" if user['is_banned'] else "🚫 Ban User", "callback_data": f"toggle_ban:{user_id}"}
            ],
            [
                {"text": "🔒 Mark Ticket Resolved", "callback_data": f"close_ticket:{user_id}"}
            ]
        ]
    }
    send_message(chat_id, text, reply_markup=buttons)

def broadcast_announcement(admin_chat_id, message_text):
    if not message_text:
        send_message(admin_chat_id, "❌ Broadcast message cannot be empty. Usage: <code>/broadcast &lt;message&gt;</code>")
        return

    conn = get_db()
    c = conn.cursor()
    c.execute("SELECT telegram_id FROM support_users WHERE is_banned = 0")
    users = [row[0] for row in c.fetchall()]
    conn.close()

    if not users:
        send_message(admin_chat_id, "ℹ️ No eligible users found for broadcast.")
        return

    send_message(admin_chat_id, f"🚀 <b>Starting broadcast to {len(users)} users...</b>")

    sent = 0
    failed = 0
    formatted_msg = (
        "📢 <b>ANNOUNCEMENT FROM PAYATE SUPPORT</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"{message_text}\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    )

    for uid in users:
        res = send_message(uid, formatted_msg)
        if res.get("ok"):
            sent += 1
        else:
            failed += 1
        time.sleep(0.05)  # Telegram rate limit compliance

    send_message(admin_chat_id, f"✅ <b>Broadcast Completed!</b>\n\n• Successfully Sent: <code>{sent}</code>\n• Failed: <code>{failed}</code>")

# ----------------------------------------------------------------------
# MESSAGE RELAY & FORWARDING ENGINE (USER -> ADMIN)
# ----------------------------------------------------------------------
def forward_user_message_to_admin(msg):
    from_user = msg.get("from", {})
    user_id = from_user.get("id")
    user_msg_id = msg.get("message_id")
    first_name = from_user.get("first_name", "")
    last_name = from_user.get("last_name", "")
    username = from_user.get("username", "")
    full_name = f"{first_name} {last_name}".strip() or "Anonymous"
    uname_str = f"@{username}" if username else "<i>None</i>"

    # Identify Media Type
    media_type = "text"
    text_content = msg.get("text", "")
    if "photo" in msg:
        media_type = "photo"
        text_content = msg.get("caption", "[Photo / Screenshot]")
    elif "document" in msg:
        media_type = "document"
        doc_name = msg.get("document", {}).get("file_name", "file")
        text_content = f"{msg.get('caption', '')}\n[Document: {doc_name}]".strip()
    elif "voice" in msg:
        media_type = "voice"
        text_content = "[Voice Note]"
    elif "audio" in msg:
        media_type = "audio"
        text_content = "[Audio File]"
    elif "video" in msg:
        media_type = "video"
        text_content = msg.get("caption", "[Video]")
    elif "sticker" in msg:
        media_type = "sticker"
        emoji = msg.get("sticker", {}).get("emoji", "")
        text_content = f"[Sticker {emoji}]"

    # Log to SQLite
    log_message(user_id, "user", text_content, media_type)

    # Action Buttons for Admin
    reply_markup = {
        "inline_keyboard": [
            [
                {"text": "↩️ Reply", "callback_data": f"reply_user:{user_id}"},
                {"text": "🚫 Ban", "callback_data": f"ban_user:{user_id}"},
                {"text": "ℹ️ Info", "callback_data": f"info_user:{user_id}"}
            ]
        ]
    }

    header_badge = (
        "📩 <b>NEW SUPPORT MESSAGE</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"👤 <b>User:</b> <code>{full_name}</code>\n"
        f"🏷️ <b>Username:</b> {uname_str}\n"
        f"🆔 <b>Telegram ID:</b> <code>{user_id}</code>\n"
        f"🕒 <b>Time:</b> {get_now_utc()} UTC\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
    )

    admin_msg_id = None

    if media_type == "text":
        full_text = f"{header_badge}💬 <b>Message:</b>\n{text_content}"
        res = send_message(ADMIN_CHAT_ID, full_text, reply_markup=reply_markup)
        if res.get("ok"):
            admin_msg_id = res.get("result", {}).get("message_id")
    else:
        # For Media: Send Header card first, then copy the media
        full_caption = f"{header_badge}💬 <b>Media Attached:</b>\n{text_content}"
        copy_res = copy_message_to(ADMIN_CHAT_ID, user_id, user_msg_id, caption=full_caption, reply_markup=reply_markup)
        if copy_res.get("ok"):
            admin_msg_id = copy_res.get("result", {}).get("message_id")
        else:
            # Fallback: Send header and forward directly
            res = send_message(ADMIN_CHAT_ID, full_caption, reply_markup=reply_markup)
            if res.get("ok"):
                admin_msg_id = res.get("result", {}).get("message_id")
            forward_message_to(ADMIN_CHAT_ID, user_id, user_msg_id)

    if admin_msg_id:
        save_message_mapping(admin_msg_id, user_id, user_msg_id)

    # Send Cooldown Acknowledgment to Customer
    now_ts = time.time()
    last_ack = USER_LAST_ACK.get(user_id, 0)
    if now_ts - last_ack > 180:  # Send ack at most once every 3 minutes per active session
        ack_text = CONFIG.get("ack_message", DEFAULT_CONFIG["ack_message"])
        send_message(user_id, ack_text)
        USER_LAST_ACK[user_id] = now_ts

# ----------------------------------------------------------------------
# ADMIN REPLY ENGINE (ADMIN -> USER) - 100% ANONYMOUS
# ----------------------------------------------------------------------
def deliver_admin_reply_to_user(user_id, msg, custom_text=None):
    if not user_id:
        send_message(ADMIN_CHAT_ID, "❌ Failed to identify recipient user ID.")
        return

    # Check if user exists or is banned
    if is_user_banned(user_id):
        send_message(ADMIN_CHAT_ID, f"⚠️ Notice: User <code>{user_id}</code> is currently BANNED. Unban with <code>/unban {user_id}</code> if you want to deliver messages.")
        return

    text_to_send = custom_text if custom_text is not None else msg.get("text", "")
    media_type = "text"

    # Identify if admin sent media
    if "photo" in msg and custom_text is None:
        media_type = "photo"
        caption = msg.get("caption", "")
        formatted_caption = f"🛡️ <b>Payate Support:</b>\n\n{caption}".strip() if caption else "🛡️ <b>Payate Support:</b>"
        res = copy_message_to(user_id, ADMIN_CHAT_ID, msg.get("message_id"), caption=formatted_caption)
        log_message(user_id, "admin", caption or "[Photo]", media_type)
    elif "document" in msg and custom_text is None:
        media_type = "document"
        caption = msg.get("caption", "")
        formatted_caption = f"🛡️ <b>Payate Support:</b>\n\n{caption}".strip() if caption else "🛡️ <b>Payate Support:</b>"
        res = copy_message_to(user_id, ADMIN_CHAT_ID, msg.get("message_id"), caption=formatted_caption)
        log_message(user_id, "admin", caption or "[Document]", media_type)
    elif "voice" in msg and custom_text is None:
        media_type = "voice"
        res = copy_message_to(user_id, ADMIN_CHAT_ID, msg.get("message_id"))
        log_message(user_id, "admin", "[Voice Note]", media_type)
    elif "audio" in msg and custom_text is None:
        media_type = "audio"
        res = copy_message_to(user_id, ADMIN_CHAT_ID, msg.get("message_id"))
        log_message(user_id, "admin", "[Audio File]", media_type)
    elif "video" in msg and custom_text is None:
        media_type = "video"
        caption = msg.get("caption", "")
        formatted_caption = f"🛡️ <b>Payate Support:</b>\n\n{caption}".strip() if caption else "🛡️ <b>Payate Support:</b>"
        res = copy_message_to(user_id, ADMIN_CHAT_ID, msg.get("message_id"), caption=formatted_caption)
        log_message(user_id, "admin", caption or "[Video]", media_type)
    elif "sticker" in msg and custom_text is None:
        media_type = "sticker"
        res = copy_message_to(user_id, ADMIN_CHAT_ID, msg.get("message_id"))
        log_message(user_id, "admin", "[Sticker]", media_type)
    else:
        # Standard text reply
        formatted_reply = (
            "🛡️ <b>Payate Support Team:</b>\n"
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            f"{text_to_send}\n"
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
        )
        res = send_message(user_id, formatted_reply)
        log_message(user_id, "admin", text_to_send, "text")

    if res.get("ok"):
        send_message(
            ADMIN_CHAT_ID,
            f"✅ <b>Reply successfully delivered!</b>\n\n👤 Recipient User ID: <code>{user_id}</code>\n💬 Sent as: <i>Payate Support Bot (Anonymous)</i>",
            reply_to_message_id=msg.get("message_id")
        )
    else:
        error_desc = res.get("description", "Unknown error")
        send_message(
            ADMIN_CHAT_ID,
            f"❌ <b>Failed to deliver reply to User <code>{user_id}</code>:</b>\n<code>{error_desc}</code>\n\n<i>User may have blocked or stopped the bot.</i>",
            reply_to_message_id=msg.get("message_id")
        )

# ----------------------------------------------------------------------
# MAIN MESSAGE & UPDATE DISPATCHER
# ----------------------------------------------------------------------
def process_message(msg):
    chat = msg.get("chat", {})
    chat_id = str(chat.get("id"))
    from_user = msg.get("from", {})
    user_id = from_user.get("id")
    text = msg.get("text", "").strip()

    # ==================================================================
    # 1. MESSAGE FROM ADMIN (ADMIN CONTROL DESK)
    # ==================================================================
    if chat_id == ADMIN_CHAT_ID or str(user_id) == ADMIN_CHAT_ID:
        # Check for Swipe-To-Reply (reply_to_message)
        reply_to = msg.get("reply_to_message")
        if reply_to:
            orig_admin_msg_id = reply_to.get("message_id")
            target_user_id, target_user_msg_id = get_user_id_by_admin_msg(orig_admin_msg_id)
            if target_user_id:
                deliver_admin_reply_to_user(target_user_id, msg)
                return

        # Check for pending reply state (from inline button click)
        if ADMIN_STATE.get("awaiting_reply_for"):
            target_user_id = ADMIN_STATE.pop("awaiting_reply_for")
            deliver_admin_reply_to_user(target_user_id, msg)
            return

        # Admin Slash Commands
        if text.startswith("/"):
            parts = text.split(maxsplit=2)
            cmd = parts[0].lower()

            if cmd in ["/start", "/help"]:
                send_admin_help(chat_id)
                return

            if cmd == "/stats":
                show_support_stats(chat_id)
                return

            if cmd == "/users":
                show_recent_users(chat_id)
                return

            if cmd == "/banned":
                conn = get_db()
                c = conn.cursor()
                c.execute("SELECT telegram_id, first_name, last_name, username, last_seen FROM support_users WHERE is_banned = 1")
                banned_list = c.fetchall()
                conn.close()
                if not banned_list:
                    send_message(chat_id, "ℹ️ No banned users currently.")
                else:
                    b_text = "🚫 <b>BANNED SUPPORT USERS:</b>\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
                    for b in banned_list:
                        uname = f"@{b['username']}" if b['username'] else "No handle"
                        b_text += f"• <code>{b['telegram_id']}</code> — {b['first_name'] or 'User'} ({uname})\n"
                    b_text += "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n<i>Use <code>/unban &lt;id&gt;</code> to unban.</i>"
                    send_message(chat_id, b_text)
                return

            if cmd in ["/reply", "/r"]:
                if len(parts) < 3:
                    send_message(chat_id, "❌ Usage: <code>/reply &lt;user_id&gt; &lt;your message&gt;</code>\nExample: <code>/r 123456789 Hello! Your deposit has been credited.</code>")
                    return
                try:
                    target_id = int(parts[1])
                    reply_content = parts[2]
                    deliver_admin_reply_to_user(target_id, msg, custom_text=reply_content)
                except ValueError:
                    send_message(chat_id, "❌ Invalid User ID. Must be a numeric Telegram ID.")
                return

            if cmd == "/info":
                if len(parts) < 2:
                    send_message(chat_id, "❌ Usage: <code>/info &lt;user_id&gt;</code>")
                    return
                try:
                    target_id = int(parts[1])
                    show_user_info(chat_id, target_id)
                except ValueError:
                    send_message(chat_id, "❌ Invalid User ID. Must be a numeric Telegram ID.")
                return

            if cmd == "/ban":
                if len(parts) < 2:
                    send_message(chat_id, "❌ Usage: <code>/ban &lt;user_id&gt;</code>")
                    return
                try:
                    target_id = int(parts[1])
                    set_user_ban_status(target_id, banned=True)
                    send_message(chat_id, f"🚫 <b>User <code>{target_id}</code> has been BANNED from support.</b>")
                except ValueError:
                    send_message(chat_id, "❌ Invalid User ID.")
                return

            if cmd == "/unban":
                if len(parts) < 2:
                    send_message(chat_id, "❌ Usage: <code>/unban &lt;user_id&gt;</code>")
                    return
                try:
                    target_id = int(parts[1])
                    set_user_ban_status(target_id, banned=False)
                    send_message(chat_id, f"🟢 <b>User <code>{target_id}</code> has been UNBANNED.</b>")
                except ValueError:
                    send_message(chat_id, "❌ Invalid User ID.")
                return

            if cmd == "/close":
                if len(parts) < 2:
                    send_message(chat_id, "❌ Usage: <code>/close &lt;user_id&gt;</code>")
                    return
                try:
                    target_id = int(parts[1])
                    close_msg = CONFIG.get("ticket_closed_message", DEFAULT_CONFIG["ticket_closed_message"])
                    send_message(target_id, close_msg)
                    send_message(chat_id, f"🔒 <b>Ticket for User <code>{target_id}</code> marked as resolved and closed.</b>")
                except ValueError:
                    send_message(chat_id, "❌ Invalid User ID.")
                return

            if cmd == "/broadcast":
                if len(parts) < 2:
                    send_message(chat_id, "❌ Usage: <code>/broadcast &lt;message&gt;</code>")
                    return
                broadcast_announcement(chat_id, parts[1])
                return

        # If not a command and not a reply, give friendly prompt
        send_message(chat_id, "💡 <i>To reply to a user, swipe & reply to their forwarded message, or use <code>/reply &lt;user_id&gt; &lt;text&gt;</code>. Type <code>/help</code> for commands.</i>")
        return

    # ==================================================================
    # 2. MESSAGE FROM CUSTOMER / WEBSITE USER
    # ==================================================================
    is_banned = record_user_activity(from_user)

    if is_banned:
        # Silently drop or notify
        return

    if text == "/start":
        welcome = CONFIG.get("welcome_message", DEFAULT_CONFIG["welcome_message"])
        send_message(chat_id, welcome)
        return

    # Forward user's message/media to admin
    forward_user_message_to_admin(msg)

def process_callback_query(cb):
    cb_id = cb.get("id")
    data = cb.get("data", "")
    from_user = cb.get("from", {})
    user_id = str(from_user.get("id"))

    if user_id != ADMIN_CHAT_ID:
        answer_callback(cb_id, "Unauthorized.", show_alert=True)
        return

    if data.startswith("reply_user:"):
        target_uid = int(data.split(":")[1])
        ADMIN_STATE["awaiting_reply_for"] = target_uid
        answer_callback(cb_id, f"Type your reply message for user {target_uid} now.")
        send_message(
            ADMIN_CHAT_ID,
            f"✍️ <b>Interactive Reply Mode Active:</b>\n\nRecipient User ID: <code>{target_uid}</code>\n\n<i>Send your text, photo, voice, or document now, and it will be anonymously delivered to this user.</i>"
        )
        return

    if data.startswith("ban_user:"):
        target_uid = int(data.split(":")[1])
        set_user_ban_status(target_uid, banned=True)
        answer_callback(cb_id, f"User {target_uid} banned.", show_alert=True)
        send_message(ADMIN_CHAT_ID, f"🚫 User <code>{target_uid}</code> has been banned.")
        return

    if data.startswith("toggle_ban:"):
        target_uid = int(data.split(":")[1])
        banned = is_user_banned(target_uid)
        set_user_ban_status(target_uid, banned=not banned)
        status_str = "unbanned" if banned else "banned"
        answer_callback(cb_id, f"User {target_uid} is now {status_str}.", show_alert=True)
        show_user_info(ADMIN_CHAT_ID, target_uid)
        return

    if data.startswith("info_user:"):
        target_uid = int(data.split(":")[1])
        answer_callback(cb_id)
        show_user_info(ADMIN_CHAT_ID, target_uid)
        return

    if data.startswith("close_ticket:"):
        target_uid = int(data.split(":")[1])
        close_msg = CONFIG.get("ticket_closed_message", DEFAULT_CONFIG["ticket_closed_message"])
        send_message(target_uid, close_msg)
        answer_callback(cb_id, "Ticket resolved and user notified.", show_alert=True)
        send_message(ADMIN_CHAT_ID, f"🔒 Ticket for User <code>{target_uid}</code> has been closed.")
        return

    answer_callback(cb_id)

# ----------------------------------------------------------------------
# MAIN LONG POLLING LOOP
# ----------------------------------------------------------------------
def main():
    enforce_single_instance()
    init_db()

    print(f"🚀 Payate Support Bot started successfully.")
    print(f"👑 Admin Chat ID: {ADMIN_CHAT_ID}")
    print(f"🌐 Tor SOCKS Proxy: {TOR_SOCKS_PROXY if CONFIG.get('use_tor') else 'Disabled (Direct)'}")

    # Send startup notification to admin
    try:
        startup_msg = (
            "🚀 <b>PAYATE 24/7 CUSTOMER SUPPORT BOT ACTIVATED</b>\n"
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            f"🤖 <b>Bot Username:</b> @{CONFIG.get('support_bot_username', 'PayateSupport_Bot')}\n"
            f"🛡️ <b>Admin Chat ID:</b> <code>{ADMIN_CHAT_ID}</code>\n"
            f"🔒 <b>Security & Privacy:</b> 100% Anonymous Admin Relay\n"
            f"🕒 <b>Started at:</b> {get_now_utc()} UTC\n"
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            "<i>Customers can now message the support bot. Use <code>/help</code> for admin commands.</i>"
        )
        send_message(ADMIN_CHAT_ID, startup_msg)
    except Exception:
        pass

    offset = 0
    error_count = 0

    while True:
        try:
            payload = {
                "offset": offset,
                "timeout": 25,
                "allowed_updates": ["message", "callback_query"]
            }
            res = tg_request("getUpdates", payload, timeout=35)

            if not res or not res.get("ok"):
                error_count += 1
                sleep_time = min(15, 2 * error_count)
                time.sleep(sleep_time)
                continue

            error_count = 0
            updates = res.get("result", [])

            for u in updates:
                offset = u.get("update_id", 0) + 1

                if "message" in u:
                    process_message(u["message"])
                elif "callback_query" in u:
                    process_callback_query(u["callback_query"])

        except Exception as e:
            error_count += 1
            sleep_time = min(15, 2 * error_count)
            time.sleep(sleep_time)

if __name__ == "__main__":
    main()
