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

import support_bridge
from support_bridge import (
    CONFIG,
    SUPPORT_BOT_TOKEN,
    ADMIN_CHAT_ID,
    TOR_SOCKS_PROXY,
    record_user_activity,
    is_user_banned,
    set_user_ban_status,
    get_user_info,
    get_recent_users,
    get_support_stats,
    forward_user_message_to_admin,
    deliver_admin_reply_to_user,
    broadcast_to_support_users,
    get_user_id_by_admin_msg,
    get_now_utc
)

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

TELEGRAM_API_URL = f"https://api.telegram.org/bot{SUPPORT_BOT_TOKEN}"

# Interactive In-Memory Conversation & Reply States
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

def tg_request(method, payload=None, timeout=20):
    url = f"{TELEGRAM_API_URL}/{method}"
    if CONFIG.get("use_tor", True):
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
# TELEGRAM SEND & KEYBOARD HELPERS
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

def edit_message_text(chat_id, message_id, text, reply_markup=None):
    payload = {
        "chat_id": chat_id,
        "message_id": message_id,
        "text": text,
        "parse_mode": "HTML",
        "disable_web_page_preview": True
    }
    if reply_markup:
        payload["reply_markup"] = reply_markup
    return tg_request("editMessageText", payload)

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

# ----------------------------------------------------------------------
# KEYBOARDS (ADMIN vs USER)
# ----------------------------------------------------------------------
def get_admin_reply_keyboard():
    return {
        "keyboard": [
            [{"text": "👑 Admin Dashboard"}, {"text": "👥 Recent Users"}],
            [{"text": "📊 Live Stats"}, {"text": "📢 Broadcast"}],
            [{"text": "🚫 Banned Users"}, {"text": "❓ Admin Help"}]
        ],
        "resize_keyboard": True,
        "persistent": True
    }

def get_user_reply_keyboard():
    return {
        "keyboard": [
            [{"text": "💬 Live Support / Help"}, {"text": "📋 My Ticket Status"}],
            [{"text": "❓ FAQ & Guides"}, {"text": "🌐 Visit Website"}]
        ],
        "resize_keyboard": True,
        "persistent": True
    }

# ----------------------------------------------------------------------
# ADMIN CONTROL DASHBOARD & ACTIONS (IF ADMIN USES SUPPORT BOT)
# ----------------------------------------------------------------------
def send_admin_dashboard(chat_id, admin_user=None):
    stats = get_support_stats()
    admin_name = f"{admin_user.get('first_name', '')} {admin_user.get('last_name', '')}".strip() if admin_user else "Admin"
    admin_uname = f"@{admin_user.get('username')}" if admin_user and admin_user.get('username') else "None"

    text = (
        "👑 <b>PAYATE LIVE SUPPORT — DESK CONTROLLER</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"👤 <b>Operator:</b> <b>{admin_name}</b> ({admin_uname})\n"
        f"🆔 <b>Admin ID:</b> <code>{ADMIN_CHAT_ID}</code>\n"
        f"🟢 <b>Bot Status:</b> <code>ONLINE & READY</code>\n"
        f"🔒 <b>Tor Proxy:</b> <code>{'ACTIVE (SOCKS5)' if CONFIG.get('use_tor') else 'DIRECT'}</code>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        "📊 <b>LIVE SUPPORT METRICS:</b>\n"
        f"• 👥 <b>Registered Customers:</b> <code>{stats['total_users']}</code>\n"
        f"• 📩 <b>Customer Inquiries:</b> <code>{stats['user_msgs']}</code>\n"
        f"• 🛡️ <b>Admin Replies Sent:</b> <code>{stats['admin_replies']}</code>\n"
        f"• 🚫 <b>Banned Spammers:</b> <code>{stats['banned_users']}</code>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        "⚡ <b>HOW TO REPLY TO CUSTOMERS:</b>\n"
        "1. <b>Swipe & Reply:</b> Swipe on any customer alert message in your Admin Bot and send your response.\n"
        "2. <b>Command Reply:</b> <code>/reply &lt;user_id&gt; &lt;message&gt;</code>\n"
        "3. <b>Interactive:</b> Click <code>[ ↩️ Reply to User ]</code> on any alert card.\n\n"
        "<i>Your identity is 100% hidden. Customers only see replies from @payate_desk_bot.</i>"
    )

    inline_buttons = {
        "inline_keyboard": [
            [
                {"text": "👥 Active Users", "callback_data": "admin:users"},
                {"text": "📊 Live Stats", "callback_data": "admin:stats"}
            ],
            [
                {"text": "📢 Broadcast Message", "callback_data": "admin:broadcast_prompt"},
                {"text": "🚫 Banned List", "callback_data": "admin:banned"}
            ]
        ]
    }

    send_message(chat_id, text, reply_markup=get_admin_reply_keyboard())
    send_message(chat_id, "⚙️ <b>Quick Support Desk Suite:</b>", reply_markup=inline_buttons)

def show_support_stats(chat_id):
    stats = get_support_stats()
    text = (
        "📊 <b>PAYATE SUPPORT LIVE STATISTICS</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        f"👥 <b>Total Registered Customers:</b> <code>{stats['total_users']}</code>\n"
        f"📩 <b>Total Customer Messages:</b> <code>{stats['user_msgs']}</code>\n"
        f"🛡️ <b>Total Admin Responses:</b> <code>{stats['admin_replies']}</code>\n"
        f"🚫 <b>Banned Spammers:</b> <code>{stats['banned_users']}</code>\n"
        f"🌐 <b>Active Relay Bot:</b> @{CONFIG.get('support_bot_username', 'payate_desk_bot')}\n"
        f"🕒 <b>Report Time:</b> <i>{get_now_utc()} UTC</i>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    )
    send_message(chat_id, text)

def show_recent_users(chat_id, limit=15):
    rows = get_recent_users(limit)
    if not rows:
        send_message(chat_id, "ℹ️ No support users registered yet.")
        return

    text = "👥 <b>RECENT SUPPORT USERS</b>\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
    for r in rows:
        uname = f"@{r['username']}" if r['username'] else "No username"
        name = f"{r['first_name'] or ''} {r['last_name'] or ''}".strip() or "Customer"
        status_icon = "🚫 BANNED" if r['is_banned'] else "🟢 ACTIVE"
        text += (
            f"• <b>{name}</b> ({uname})\n"
            f"  🆔 <code>{r['telegram_id']}</code> | 💬 {r['total_messages']} msgs | {status_icon}\n"
            f"  🕒 Last seen: <i>{r['last_seen']}</i>\n\n"
        )
    text += "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n<i>Use <code>/reply &lt;id&gt; &lt;text&gt;</code> to respond.</i>"
    send_message(chat_id, text)

# ----------------------------------------------------------------------
# CUSTOMER / USER PORTAL (NON-ADMIN)
# ----------------------------------------------------------------------
def send_user_welcome(chat_id, user_obj):
    first_name = user_obj.get("first_name", "Customer")
    welcome_text = (
        f"👋 <b>Hello {first_name}, Welcome to Payate 24/7 Live Support!</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        "We are here to assist you with:\n"
        "• 💳 <b>Card Orders & Replacements</b>\n"
        "• 💰 <b>Deposit & Crypto Top-up Verification</b>\n"
        "• 📦 <b>Wholesale Packs & Special Inquiries</b>\n"
        "• 🛠️ <b>Technical & Account Assistance</b>\n\n"
        "💬 <b>HOW TO GET SUPPORT:</b>\n"
        "Simply type your message, transaction ID, order number, or send a screenshot directly in this chat.\n\n"
        "🛡️ Our live support agents will reply directly to you right here!\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
    )
    send_message(chat_id, welcome_text, reply_markup=get_user_reply_keyboard())

def send_user_status(chat_id, user_id):
    user = get_user_info(user_id)
    if user:
        text = (
            "📋 <b>YOUR SUPPORT DESK STATUS</b>\n"
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            f"🆔 <b>Customer ID:</b> <code>{user_id}</code>\n"
            f"💬 <b>Total Inquiries Sent:</b> <code>{user['total_messages']}</code>\n"
            f"📅 <b>Member Since:</b> <i>{user['first_seen']}</i>\n"
            f"🟢 <b>Status:</b> <code>Active & Connected</code>\n"
            "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            "<i>Feel free to send a message anytime if you need help with orders or deposits!</i>"
        )
    else:
        text = "ℹ️ No support history found for your account yet. Feel free to type your question!"
    send_message(chat_id, text, reply_markup=get_user_reply_keyboard())

def send_user_faq(chat_id):
    faq_text = (
        "❓ <b>FREQUENTLY ASKED QUESTIONS (FAQ)</b>\n"
        "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        "<b>1. How do I deposit funds?</b>\n"
        "Go to the Add Funds section on our website, select BTC/LTC/USDT, send the exact amount, and submit your TXID.\n\n"
        "<b>2. How long do deposits take?</b>\n"
        "Crypto deposits are automatically checked or approved within 5-15 minutes after blockchain confirmation.\n\n"
        "<b>3. What if a card is invalid?</b>\n"
        "Send your Order ID and card details here immediately to request a replacement.\n\n"
        "<b>4. Need direct assistance?</b>\n"
        "Type your message below and a live operator will respond to you directly."
    )
    send_message(chat_id, faq_text, reply_markup=get_user_reply_keyboard())

# ----------------------------------------------------------------------
# DISPATCHER (ADMIN vs USER)
# ----------------------------------------------------------------------
def process_message(msg):
    chat = msg.get("chat", {})
    chat_id = str(chat.get("id"))
    from_user = msg.get("from", {})
    user_id = str(from_user.get("id"))
    text = msg.get("text", "").strip()

    is_admin = (chat_id == ADMIN_CHAT_ID or user_id == ADMIN_CHAT_ID)

    # ==================================================================
    # 👑 1. ADMIN USER LOGIC (IF ADMIN MESSAGES IN SUPPORT BOT)
    # ==================================================================
    if is_admin:
        # Check for Swipe-To-Reply
        reply_to = msg.get("reply_to_message")
        if reply_to:
            orig_admin_msg_id = reply_to.get("message_id")
            target_user_id, _ = get_user_id_by_admin_msg(orig_admin_msg_id)
            if target_user_id:
                ok, res_str = deliver_admin_reply_to_user(target_user_id, admin_msg=msg)
                if ok:
                    send_message(chat_id, f"✅ <b>Reply sent to User <code>{target_user_id}</code>!</b>", reply_to_message_id=msg.get("message_id"))
                else:
                    send_message(chat_id, f"❌ <b>Error:</b> {res_str}", reply_to_message_id=msg.get("message_id"))
                return

        # Check for awaiting reply prompt
        if ADMIN_STATE.get("awaiting_reply_for"):
            target_user_id = ADMIN_STATE.pop("awaiting_reply_for")
            ok, res_str = deliver_admin_reply_to_user(target_user_id, admin_msg=msg)
            if ok:
                send_message(chat_id, f"✅ <b>Reply sent to User <code>{target_user_id}</code>!</b>", reply_to_message_id=msg.get("message_id"))
            else:
                send_message(chat_id, f"❌ <b>Error:</b> {res_str}", reply_to_message_id=msg.get("message_id"))
            return

        # Check for awaiting broadcast prompt
        if ADMIN_STATE.get("awaiting_broadcast"):
            ADMIN_STATE.pop("awaiting_broadcast")
            sent, failed = broadcast_to_support_users(text)
            send_message(chat_id, f"✅ <b>Broadcast Completed!</b>\n\n• Successfully Sent: <code>{sent}</code>\n• Failed: <code>{failed}</code>")
            return

        # Admin Button / Text Commands
        if text in ["/start", "/admin", "👑 Admin Dashboard"]:
            send_admin_dashboard(chat_id, from_user)
            return

        if text in ["/stats", "📊 Live Stats"]:
            show_support_stats(chat_id)
            return

        if text in ["/users", "👥 Recent Users"]:
            show_recent_users(chat_id)
            return

        if text in ["/banned", "🚫 Banned Users"]:
            conn = support_bridge.get_db()
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
                    b_text += f"• <code>{b['telegram_id']}</code> — {b['first_name'] or 'Customer'} ({uname})\n"
                b_text += "━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n<i>Use <code>/unban &lt;id&gt;</code> to unban.</i>"
                send_message(chat_id, b_text)
            return

        if text in ["/broadcast", "📢 Broadcast"]:
            ADMIN_STATE["awaiting_broadcast"] = True
            send_message(chat_id, "📢 <b>Interactive Broadcast Mode:</b>\n\nPlease type the announcement message you want to broadcast to all support customers now:")
            return

        if text in ["/help", "❓ Admin Help"]:
            send_admin_dashboard(chat_id, from_user)
            return

        # Admin Slash Commands
        if text.startswith("/"):
            parts = text.split(maxsplit=2)
            cmd = parts[0].lower()

            if cmd in ["/reply", "/r"]:
                if len(parts) < 3:
                    send_message(chat_id, "❌ Usage: <code>/reply &lt;user_id&gt; &lt;message&gt;</code>\nExample: <code>/r 123456789 Hello! Your deposit is confirmed.</code>")
                    return
                try:
                    target_id = int(parts[1])
                    ok, res_str = deliver_admin_reply_to_user(target_id, custom_text=parts[2])
                    if ok:
                        send_message(chat_id, f"✅ <b>Reply delivered to User <code>{target_id}</code>!</b>")
                    else:
                        send_message(chat_id, f"❌ <b>Error:</b> {res_str}")
                except ValueError:
                    send_message(chat_id, "❌ Invalid User ID.")
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
                    send_message(target_id, "🔒 <b>Support Ticket Resolved</b>\n\nYour inquiry has been marked as resolved. If you need any more assistance, feel free to send a new message anytime!")
                    send_message(chat_id, f"🔒 <b>Ticket for User <code>{target_id}</code> marked as resolved.</b>")
                except ValueError:
                    send_message(chat_id, "❌ Invalid User ID.")
                return

        send_message(chat_id, "💡 <i>Swipe & reply to any customer alert message in your Admin Bot, or type <code>/admin</code> to open dashboard.</i>")
        return

    # ==================================================================
    # 👤 2. ORDINARY CUSTOMER / USER LOGIC (STRICT PRIVACY)
    # ==================================================================
    # Prevent normal user from accessing admin controls
    if text in ["/admin", "👑 Admin Dashboard"]:
        send_message(chat_id, "🚫 <b>Access Denied:</b> You do not have administrator permissions to access this control suite.")
        return

    # Register/update customer
    is_banned = record_user_activity(from_user)
    if is_banned:
        # Banned user: Ignore or alert
        return

    if text == "/start":
        send_user_welcome(chat_id, from_user)
        return

    if text == "📋 My Ticket Status":
        send_user_status(chat_id, int(user_id))
        return

    if text == "❓ FAQ & Guides":
        send_user_faq(chat_id)
        return

    if text == "🌐 Visit Website":
        send_message(chat_id, f"🌐 <b>Official Website Links:</b>\n\n• Local Web: {CONFIG.get('website_url', 'http://127.0.0.1:8000')}\n• Tor Onion: {CONFIG.get('onion_url', '')}", reply_markup=get_user_reply_keyboard())
        return

    if text == "💬 Live Support / Help":
        send_message(chat_id, "💬 <b>Please type your question or issue below:</b>\n\nYou can also attach screenshots, order numbers, or transaction hashes.", reply_markup=get_user_reply_keyboard())
        return

    # Forward customer's message to Admin Bot
    forward_user_message_to_admin(msg)

    # Cooldown acknowledgment to customer (every 60 seconds)
    now_ts = time.time()
    last_ack = USER_LAST_ACK.get(user_id, 0)
    if now_ts - last_ack > 60:
        ack_text = (
            "✅ <b>Message Received!</b>\n\n"
            "Your inquiry has been delivered to our support operators. A support specialist will respond to you right here shortly."
        )
        send_message(user_id, ack_text)
        USER_LAST_ACK[user_id] = now_ts

# ----------------------------------------------------------------------
# CALLBACK QUERY ROUTING (ADMIN INLINE BUTTONS)
# ----------------------------------------------------------------------
def process_callback_query(cb):
    cb_id = cb.get("id")
    data = cb.get("data", "")
    from_user = cb.get("from", {})
    user_id = str(from_user.get("id"))

    if user_id != ADMIN_CHAT_ID:
        answer_callback(cb_id, "Access Denied: Admin privileges required.", show_alert=True)
        return

    if data == "admin:stats":
        show_support_stats(ADMIN_CHAT_ID)
        answer_callback(cb_id, "Stats refreshed!")
        return

    if data == "admin:users":
        show_recent_users(ADMIN_CHAT_ID)
        answer_callback(cb_id)
        return

    if data == "admin:banned":
        answer_callback(cb_id)
        conn = support_bridge.get_db()
        c = conn.cursor()
        c.execute("SELECT telegram_id, first_name, last_name, username FROM support_users WHERE is_banned = 1")
        banned_list = c.fetchall()
        conn.close()
        if not banned_list:
            send_message(ADMIN_CHAT_ID, "ℹ️ No banned users currently.")
        else:
            b_text = "🚫 <b>BANNED SUPPORT USERS:</b>\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
            for b in banned_list:
                uname = f"@{b['username']}" if b['username'] else "No handle"
                b_text += f"• <code>{b['telegram_id']}</code> — {b['first_name'] or 'Customer'} ({uname})\n"
            send_message(ADMIN_CHAT_ID, b_text)
        return

    if data == "admin:broadcast_prompt":
        ADMIN_STATE["awaiting_broadcast"] = True
        answer_callback(cb_id)
        send_message(ADMIN_CHAT_ID, "📢 <b>Send the broadcast message text now:</b>")
        return

    if data == "admin:help":
        answer_callback(cb_id)
        send_admin_dashboard(ADMIN_CHAT_ID, from_user)
        return

    if data.startswith("support_reply:"):
        target_uid = int(data.split(":")[1])
        ADMIN_STATE["awaiting_reply_for"] = target_uid
        answer_callback(cb_id, f"Type your reply for user {target_uid} now.")
        send_message(
            ADMIN_CHAT_ID,
            f"✍️ <b>Interactive Reply Mode Active:</b>\n\nRecipient Customer ID: <code>{target_uid}</code>\n\n<i>Send your text, photo, voice, or document now, and it will be delivered anonymously to this user.</i>"
        )
        return

    if data.startswith("support_ban:"):
        target_uid = int(data.split(":")[1])
        set_user_ban_status(target_uid, banned=True)
        answer_callback(cb_id, f"User {target_uid} banned!", show_alert=True)
        send_message(ADMIN_CHAT_ID, f"🚫 <b>User <code>{target_uid}</code> has been banned from support.</b>")
        return

    if data.startswith("support_history:"):
        target_uid = int(data.split(":")[1])
        u = get_user_info(target_uid)
        history = support_bridge.get_user_history(target_uid, limit=10)
        name = f"{u['first_name'] or ''} {u['last_name'] or ''}".strip() if u else "Customer"
        uname = f"@{u['username']}" if u and u['username'] else "No username"
        h_text = f"📜 <b>CHAT HISTORY FOR {name} ({uname})</b>\n━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n"
        if not history:
            h_text += "<i>No messages recorded yet.</i>\n"
        else:
            for h in history:
                sender = "👑 <b>Admin:</b>" if h["sender_type"] == "admin" else f"👤 <b>{name}:</b>"
                h_text += f"{sender} {h['message_text']}\n<i>({h['created_at']} UTC)</i>\n\n"
        answer_callback(cb_id)
        send_message(ADMIN_CHAT_ID, h_text)
        return

    if data.startswith("support_close:"):
        target_uid = int(data.split(":")[1])
        send_message(target_uid, "🔒 <b>Support Ticket Resolved</b>\n\nYour inquiry has been marked as resolved by our team. If you need any more assistance, feel free to send a new message anytime!")
        answer_callback(cb_id, f"Ticket #{target_uid} marked resolved.", show_alert=True)
        send_message(ADMIN_CHAT_ID, f"🔒 <b>Ticket for User <code>{target_uid}</code> marked as resolved.</b>")
        return

    answer_callback(cb_id)

# ----------------------------------------------------------------------
# MAIN POLLING LOOP
# ----------------------------------------------------------------------
def main():
    enforce_single_instance()

    print("==================================================")
    print(f"[+] PAYATE CC TELEGRAM LIVE SUPPORT BOT STARTED")
    print(f"[+] Bot Username: @{CONFIG.get('support_bot_username', 'payate_desk_bot')}")
    print(f"[+] Admin ID: {ADMIN_CHAT_ID}")
    print(f"[+] Tor SOCKS5: {'Active' if CONFIG.get('use_tor') else 'Direct'}")
    print("==================================================")

    # Flush old updates
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
                "timeout": 15,
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
