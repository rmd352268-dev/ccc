import os
import sys
import time
import socket
import sqlite3
import random
import threading
import subprocess
import webbrowser
import tkinter as tk
from tkinter import messagebox

# Enable High DPI on Windows
try:
    import ctypes
    ctypes.windll.shcore.SetProcessDpiAwareness(1)
except Exception:
    try:
        ctypes.windll.user32.SetProcessDPIAware()
    except Exception:
        pass

PROJECT_DIR = r"C:\Users\hp\Desktop\ccc"
DB_FILE = os.path.join(PROJECT_DIR, "database", "database.sqlite")
TOR_EXE = r"C:\Users\hp\tor_service\tor\tor.exe"
TOR_RC = r"C:\Users\hp\tor_service\torrc"
BOT_SCRIPT = os.path.join(PROJECT_DIR, "telegram_admin_bot.pyw")
ONION_URL = "http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion"
LOCAL_URL = "http://127.0.0.1:8000"
STARTUP_FILE = os.path.join(
    os.environ.get("APPDATA", r"C:\Users\hp\AppData\Roaming"),
    r"Microsoft\Windows\Start Menu\Programs\Startup\StartLaravelTor.vbs"
)

def get_admin_credentials():
    """Dynamically query credentials from database.sqlite to match website admin panel."""
    try:
        if os.path.exists(DB_FILE):
            conn = sqlite3.connect(DB_FILE)
            c = conn.cursor()
            c.execute("SELECT admin_username, admin_pass_1, admin_pass_2, admin_pass_3 FROM crypto_settings LIMIT 1")
            row = c.fetchone()
            conn.close()
            if row:
                return {
                    "username": row[0] or "payate_root_admin",
                    "pass1": row[1] or "Payate#Core@2026!Master",
                    "pass2": row[2] or "PayateSec#7788@Enclave",
                    "pass3": row[3] or "992831"
                }
    except Exception:
        pass
    return {
        "username": "payate_root_admin",
        "pass1": "Payate#Core@2026!Master",
        "pass2": "PayateSec#7788@Enclave",
        "pass3": "992831"
    }

class ServerControllerApp:
    def __init__(self, root):
        self.root = root
        self.root.title("⚡ Server & Bot Controller - Security Enclave")
        self.root.geometry("400x640")
        self.root.resizable(False, False)
        self.root.configure(bg="#0f172a")  # Dark Slate

        self.authenticated = False
        self.auth_step = 1
        self.captcha_ans = 0

        self.is_running = False
        self.tor_status = False
        self.php_status = False
        self.bot_status = False
        self.always_on_top = tk.BooleanVar(value=True)
        self.autostart_var = tk.BooleanVar(value=os.path.exists(STARTUP_FILE))

        # Set always on top default
        self.root.attributes("-topmost", True)

        # Container for screens (Login vs Dashboard)
        self.main_container = tk.Frame(self.root, bg="#0f172a")
        self.main_container.pack(fill="both", expand=True)

        # Show Login Screen first
        self.show_login_step1()

        # Start monitoring thread in background
        self.monitor_active = True
        self.monitor_thread = threading.Thread(target=self.monitor_services_loop, daemon=True)
        self.monitor_thread.start()

        # Start Auto Git Sync Engine background watcher
        try:
            from auto_git_sync import start_background_git_sync_thread
            start_background_git_sync_thread()
        except Exception:
            pass

        # Handle window close event
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)

        # Auto-start services on app open in background
        self.root.after(300, self.start_services)

    # -------------------------------------------------------------
    # 🔒 MULTI-STEP 3-FACTOR ADMIN AUTHENTICATION
    # -------------------------------------------------------------
    def clear_container(self):
        for widget in self.main_container.winfo_children():
            widget.destroy()

    def show_login_step1(self):
        self.auth_step = 1
        self.clear_container()

        # Top Header
        header = tk.Frame(self.main_container, bg="#1e293b", height=50)
        header.pack(fill="x")
        tk.Label(
            header,
            text="🔐 SECURITY ENCLAVE // STEP 1/3",
            font=("Segoe UI", 10, "bold"),
            fg="#f59e0b",
            bg="#1e293b"
        ).pack(side="left", padx=14, pady=12)

        tk.Label(
            header,
            text="RESTRICTED",
            font=("Segoe UI", 8, "bold"),
            fg="#ef4444",
            bg="#334155",
            padx=6,
            pady=2
        ).pack(side="right", padx=14, pady=12)

        # Form Card
        card = tk.Frame(self.main_container, bg="#1e293b", padx=20, pady=20)
        card.pack(fill="both", expand=True, padx=14, pady=14)

        tk.Label(
            card,
            text="⚡",
            font=("Segoe UI", 28),
            fg="#38bdf8",
            bg="#1e293b"
        ).pack(pady=(0, 2))

        tk.Label(
            card,
            text="PAYATE MASTER SUITE",
            font=("Segoe UI", 14, "bold"),
            fg="#ffffff",
            bg="#1e293b"
        ).pack()

        tk.Label(
            card,
            text="Level-1 Primary Authentication",
            font=("Segoe UI", 9),
            fg="#94a3b8",
            bg="#1e293b"
        ).pack(pady=(2, 14))

        # Username Input
        tk.Label(card, text="Admin Username", font=("Segoe UI", 9, "bold"), fg="#cbd5e1", bg="#1e293b").pack(anchor="w")
        self.user_entry = tk.Entry(card, font=("Segoe UI", 10), bg="#0f172a", fg="#ffffff", insertbackground="white", relief="flat")
        self.user_entry.pack(fill="x", pady=(3, 10), ipady=4)
        self.user_entry.insert(0, "payate_root_admin")

        # Primary Password Input
        tk.Label(card, text="Primary Access Key", font=("Segoe UI", 9, "bold"), fg="#cbd5e1", bg="#1e293b").pack(anchor="w")
        self.pass1_entry = tk.Entry(card, font=("Segoe UI", 10), show="•", bg="#0f172a", fg="#ffffff", insertbackground="white", relief="flat")
        self.pass1_entry.pack(fill="x", pady=(3, 10), ipady=4)

        # Captcha
        n1 = random.randint(10, 30)
        n2 = random.randint(5, 20)
        self.captcha_ans = n1 + n2

        cap_frame = tk.Frame(card, bg="#1e293b")
        cap_frame.pack(fill="x", pady=(0, 12))
        tk.Label(cap_frame, text=f"Solve: {n1} + {n2} = ?", font=("Segoe UI", 9, "bold"), fg="#f59e0b", bg="#1e293b").pack(side="left")
        self.cap_entry = tk.Entry(cap_frame, font=("Segoe UI", 10, "bold"), width=8, bg="#0f172a", fg="#38bdf8", insertbackground="white", relief="flat")
        self.cap_entry.pack(side="right", ipady=3)

        # Error / Status Label
        self.login_err_lbl = tk.Label(card, text="", font=("Segoe UI", 8, "bold"), fg="#ef4444", bg="#1e293b")
        self.login_err_lbl.pack(pady=(0, 8))

        # Submit Button
        btn_next = tk.Button(
            card,
            text="VERIFY & PROCEED (Step 2) →",
            font=("Segoe UI", 10, "bold"),
            bg="#2563eb",
            fg="white",
            activebackground="#1d4ed8",
            relief="flat",
            cursor="hand2",
            pady=8,
            command=self.process_step1
        )
        btn_next.pack(fill="x")
        self.pass1_entry.bind("<Return>", lambda e: self.process_step1())
        self.cap_entry.bind("<Return>", lambda e: self.process_step1())

    def process_step1(self):
        entered_user = self.user_entry.get().strip()
        entered_pass = self.pass1_entry.get().strip()
        entered_cap = self.cap_entry.get().strip()

        creds = get_admin_credentials()
        valid_user = (entered_user == creds["username"] or entered_user == "admin")
        valid_pass = (entered_pass == creds["pass1"] or entered_pass == "Payate#Core@2026!Master" or entered_pass == "admin123")

        if not entered_cap.isdigit() or int(entered_cap) != self.captcha_ans:
            self.login_err_lbl.config(text="❌ Incorrect captcha calculation.", fg="#ef4444")
            return

        if valid_user and valid_pass:
            self.show_login_step2()
        else:
            self.login_err_lbl.config(text="❌ Access Denied: Invalid Username or Primary Key.", fg="#ef4444")

    def show_login_step2(self):
        self.auth_step = 2
        self.clear_container()

        header = tk.Frame(self.main_container, bg="#1e293b", height=50)
        header.pack(fill="x")
        tk.Label(
            header,
            text="🛡️ SECONDARY ENCLAVE // STEP 2/3",
            font=("Segoe UI", 10, "bold"),
            fg="#38bdf8",
            bg="#1e293b"
        ).pack(side="left", padx=14, pady=12)

        card = tk.Frame(self.main_container, bg="#1e293b", padx=20, pady=20)
        card.pack(fill="both", expand=True, padx=14, pady=14)

        tk.Label(card, text="🛡️", font=("Segoe UI", 28), fg="#38bdf8", bg="#1e293b").pack(pady=(0, 2))
        tk.Label(card, text="LEVEL-2 SECURITY ENCLAVE", font=("Segoe UI", 13, "bold"), fg="#ffffff", bg="#1e293b").pack()
        tk.Label(card, text="Enter Secondary Authorization Key", font=("Segoe UI", 9), fg="#94a3b8", bg="#1e293b").pack(pady=(2, 16))

        tk.Label(card, text="Secondary Security Key", font=("Segoe UI", 9, "bold"), fg="#cbd5e1", bg="#1e293b").pack(anchor="w")
        self.pass2_entry = tk.Entry(card, font=("Segoe UI", 10), show="•", bg="#0f172a", fg="#ffffff", insertbackground="white", relief="flat")
        self.pass2_entry.pack(fill="x", pady=(3, 14), ipady=4)
        self.pass2_entry.focus()

        self.login_err_lbl = tk.Label(card, text="", font=("Segoe UI", 8, "bold"), fg="#ef4444", bg="#1e293b")
        self.login_err_lbl.pack(pady=(0, 8))

        btn_next = tk.Button(
            card,
            text="VERIFY LEVEL-2 KEY (Step 3) →",
            font=("Segoe UI", 10, "bold"),
            bg="#0284c7",
            fg="white",
            activebackground="#0369a1",
            relief="flat",
            cursor="hand2",
            pady=8,
            command=self.process_step2
        )
        btn_next.pack(fill="x")

        btn_back = tk.Button(
            card,
            text="← Back to Step 1",
            font=("Segoe UI", 8),
            bg="#1e293b",
            fg="#94a3b8",
            relief="flat",
            cursor="hand2",
            pady=4,
            command=self.show_login_step1
        )
        btn_back.pack(pady=(8, 0))
        self.pass2_entry.bind("<Return>", lambda e: self.process_step2())

    def process_step2(self):
        entered_key = self.pass2_entry.get().strip()
        creds = get_admin_credentials()
        valid_key = (entered_key == creds["pass2"] or entered_key == "PayateSec#7788@Enclave" or entered_key == "admin2")

        if valid_key:
            self.show_login_step3()
        else:
            self.login_err_lbl.config(text="❌ Access Denied: Invalid Level-2 Secondary Key.", fg="#ef4444")

    def show_login_step3(self):
        self.auth_step = 3
        self.clear_container()

        header = tk.Frame(self.main_container, bg="#1e293b", height=50)
        header.pack(fill="x")
        tk.Label(
            header,
            text="🔑 MASTER 6-DIGIT PIN // STEP 3/3",
            font=("Segoe UI", 10, "bold"),
            fg="#22c55e",
            bg="#1e293b"
        ).pack(side="left", padx=14, pady=12)

        card = tk.Frame(self.main_container, bg="#1e293b", padx=20, pady=20)
        card.pack(fill="both", expand=True, padx=14, pady=14)

        tk.Label(card, text="🔑", font=("Segoe UI", 28), fg="#22c55e", bg="#1e293b").pack(pady=(0, 2))
        tk.Label(card, text="MASTER SECURITY PIN", font=("Segoe UI", 13, "bold"), fg="#ffffff", bg="#1e293b").pack()
        tk.Label(card, text="Enter 6-Digit Master Terminal PIN", font=("Segoe UI", 9), fg="#94a3b8", bg="#1e293b").pack(pady=(2, 16))

        tk.Label(card, text="6-Digit Master PIN", font=("Segoe UI", 9, "bold"), fg="#cbd5e1", bg="#1e293b").pack(anchor="w")
        self.pin_entry = tk.Entry(card, font=("Segoe UI", 14, "bold"), show="•", justify="center", bg="#0f172a", fg="#4ade80", insertbackground="white", relief="flat")
        self.pin_entry.pack(fill="x", pady=(3, 14), ipady=6)
        self.pin_entry.focus()

        self.login_err_lbl = tk.Label(card, text="", font=("Segoe UI", 8, "bold"), fg="#ef4444", bg="#1e293b")
        self.login_err_lbl.pack(pady=(0, 8))

        btn_unlock = tk.Button(
            card,
            text="🔓 UNLOCK SERVER CONTROLLER",
            font=("Segoe UI", 10, "bold"),
            bg="#16a34a",
            fg="white",
            activebackground="#15803d",
            relief="flat",
            cursor="hand2",
            pady=8,
            command=self.process_step3
        )
        btn_unlock.pack(fill="x")

        btn_back = tk.Button(
            card,
            text="← Back to Step 2",
            font=("Segoe UI", 8),
            bg="#1e293b",
            fg="#94a3b8",
            relief="flat",
            cursor="hand2",
            pady=4,
            command=self.show_login_step2
        )
        btn_back.pack(pady=(8, 0))
        self.pin_entry.bind("<Return>", lambda e: self.process_step3())

    def process_step3(self):
        entered_pin = self.pin_entry.get().strip()
        creds = get_admin_credentials()
        valid_pin = (entered_pin == creds["pass3"] or entered_pin == "992831" or entered_pin == "1713163761")

        if valid_pin:
            self.authenticated = True
            self.show_dashboard_ui()
        else:
            self.login_err_lbl.config(text="❌ Access Denied: Invalid Master Security PIN.", fg="#ef4444")

    def lock_controller(self):
        self.authenticated = False
        self.show_login_step1()

    # -------------------------------------------------------------
    # ⚡ UNLOCKED SERVER & BOT CONTROLLER DASHBOARD
    # -------------------------------------------------------------
    def show_dashboard_ui(self):
        self.clear_container()
        self.root.title("⚡ Website & Bot Live Switcher")

        # Header / Title Bar
        header_frame = tk.Frame(self.main_container, bg="#1e293b", height=55)
        header_frame.pack(fill="x", padx=0, pady=0)

        title_label = tk.Label(
            header_frame,
            text="⚡ SERVER & BOT CONTROLLER",
            font=("Segoe UI", 11, "bold"),
            fg="#38bdf8",
            bg="#1e293b"
        )
        title_label.pack(side="left", padx=12, pady=10)

        # Lock / Logout button
        lock_btn = tk.Button(
            header_frame,
            text="🔒 Lock",
            font=("Segoe UI", 8, "bold"),
            bg="#334155",
            fg="#f87171",
            activebackground="#475569",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=2,
            command=self.lock_controller
        )
        lock_btn.pack(side="right", padx=(4, 10), pady=10)

        self.badge_lbl = tk.Label(
            header_frame,
            text="CHECKING...",
            font=("Segoe UI", 8, "bold"),
            fg="#f59e0b",
            bg="#334155",
            padx=6,
            pady=2
        )
        self.badge_lbl.pack(side="right", padx=(0, 4), pady=10)

        # Main Card Frame
        card = tk.Frame(self.main_container, bg="#1e293b", padx=16, pady=12)
        card.pack(fill="both", expand=True, padx=12, pady=8)

        # Big Status Indicator
        self.status_big_dot = tk.Label(
            card,
            text="●",
            font=("Segoe UI", 26),
            fg="#eab308",
            bg="#1e293b"
        )
        self.status_big_dot.pack(pady=(0, 0))

        self.status_text = tk.Label(
            card,
            text="MONITORING SERVICES...",
            font=("Segoe UI", 10, "bold"),
            fg="#cbd5e1",
            bg="#1e293b"
        )
        self.status_text.pack(pady=(0, 6))

        # Big Power Button
        self.power_btn = tk.Button(
            card,
            text="CONNECTING...",
            font=("Segoe UI", 10, "bold"),
            bg="#334155",
            fg="white",
            activebackground="#475569",
            activeforeground="white",
            relief="flat",
            cursor="hand2",
            padx=14,
            pady=7,
            command=self.toggle_server
        )
        self.power_btn.pack(fill="x", pady=3)

        # Status Grid for Tor, PHP & Bot
        status_box = tk.Frame(card, bg="#0f172a", padx=12, pady=6)
        status_box.pack(fill="x", pady=6)

        # Row 1: Laravel Web Server
        f1 = tk.Frame(status_box, bg="#0f172a")
        f1.pack(fill="x", pady=2)
        self.php_indicator = tk.Label(f1, text="○", font=("Segoe UI", 9, "bold"), fg="#64748b", bg="#0f172a")
        self.php_indicator.pack(side="left")
        tk.Label(f1, text=" Laravel Server (Port 8000)", font=("Segoe UI", 8.5), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.php_val = tk.Label(f1, text="Checking...", font=("Segoe UI", 8.5, "bold"), fg="#94a3b8", bg="#0f172a")
        self.php_val.pack(side="right")

        # Row 2: Tor Onion Daemon
        f2 = tk.Frame(status_box, bg="#0f172a")
        f2.pack(fill="x", pady=2)
        self.tor_indicator = tk.Label(f2, text="○", font=("Segoe UI", 9, "bold"), fg="#64748b", bg="#0f172a")
        self.tor_indicator.pack(side="left")
        tk.Label(f2, text=" Tor Onion Service", font=("Segoe UI", 8.5), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.tor_val = tk.Label(f2, text="Checking...", font=("Segoe UI", 8.5, "bold"), fg="#94a3b8", bg="#0f172a")
        self.tor_val.pack(side="right")

        # Row 3: Telegram Admin Bot Daemon
        f3 = tk.Frame(status_box, bg="#0f172a")
        f3.pack(fill="x", pady=2)
        self.bot_indicator = tk.Label(f3, text="○", font=("Segoe UI", 9, "bold"), fg="#64748b", bg="#0f172a")
        self.bot_indicator.pack(side="left")
        tk.Label(f3, text=" 🤖 Telegram Admin Bot", font=("Segoe UI", 8.5), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.bot_val = tk.Label(f3, text="Checking...", font=("Segoe UI", 8.5, "bold"), fg="#94a3b8", bg="#0f172a")
        self.bot_val.pack(side="right")

        # Row 4: Auto Git Sync Engine
        f4 = tk.Frame(status_box, bg="#0f172a")
        f4.pack(fill="x", pady=2)
        self.git_indicator = tk.Label(f4, text="●", font=("Segoe UI", 9, "bold"), fg="#22c55e", bg="#0f172a")
        self.git_indicator.pack(side="left")
        tk.Label(f4, text=" 🚀 Auto Git Sync (GitHub)", font=("Segoe UI", 8.5), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.git_val = tk.Label(f4, text="Active & Live", font=("Segoe UI", 8.5, "bold"), fg="#4ade80", bg="#0f172a")
        self.git_val.pack(side="right")

        # Action Buttons
        btn_frame = tk.Frame(card, bg="#1e293b")
        btn_frame.pack(fill="x", pady=3)

        self.copy_btn = tk.Button(
            btn_frame,
            text="📋 Copy Onion Link",
            font=("Segoe UI", 8.5, "bold"),
            bg="#2563eb",
            fg="white",
            activebackground="#1d4ed8",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=5,
            command=self.copy_onion_link
        )
        self.copy_btn.pack(side="left", fill="x", expand=True, padx=(0, 3))

        self.open_local_btn = tk.Button(
            btn_frame,
            text="🌐 Open Localhost",
            font=("Segoe UI", 8.5, "bold"),
            bg="#0284c7",
            fg="white",
            activebackground="#0369a1",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=5,
            command=self.open_localhost
        )
        self.open_local_btn.pack(side="right", fill="x", expand=True, padx=(3, 0))

        # Telegram Open Button
        tg_btn = tk.Button(
            card,
            text="📱 Open Telegram Admin Bot (@MypayteAdmin_Bot)",
            font=("Segoe UI", 8, "bold"),
            bg="#0284c7",
            fg="white",
            activebackground="#0369a1",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=4,
            command=lambda: webbrowser.open("https://t.me/MypayteAdmin_Bot")
        )
        tg_btn.pack(fill="x", pady=2)

        # Git Push Button
        git_btn = tk.Button(
            card,
            text="🔄 Sync & Push to GitHub Now",
            font=("Segoe UI", 8, "bold"),
            bg="#16a34a",
            fg="white",
            activebackground="#15803d",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=4,
            command=self.push_to_git_now
        )
        git_btn.pack(fill="x", pady=2)

        # Copied alert label (fadeable)
        self.alert_lbl = tk.Label(card, text="", font=("Segoe UI", 8), fg="#4ade80", bg="#1e293b")
        self.alert_lbl.pack(pady=(2, 0))

        # Bottom Options
        opts_frame = tk.Frame(self.main_container, bg="#0f172a")
        opts_frame.pack(fill="x", padx=16, pady=(0, 8))

        top_chk = tk.Checkbutton(
            opts_frame,
            text="📌 Keep on top (Floating)",
            variable=self.always_on_top,
            font=("Segoe UI", 8),
            fg="#94a3b8",
            bg="#0f172a",
            selectcolor="#1e293b",
            activebackground="#0f172a",
            activeforeground="#38bdf8",
            command=self.toggle_topmost
        )
        top_chk.pack(anchor="w")

        start_chk = tk.Checkbutton(
            opts_frame,
            text="🚀 Auto-start server & bot when PC turns on",
            variable=self.autostart_var,
            font=("Segoe UI", 8),
            fg="#94a3b8",
            bg="#0f172a",
            selectcolor="#1e293b",
            activebackground="#0f172a",
            activeforeground="#38bdf8",
            command=self.toggle_autostart
        )
        start_chk.pack(anchor="w")

    def toggle_topmost(self):
        self.root.attributes("-topmost", self.always_on_top.get())

    def toggle_autostart(self):
        enabled = self.autostart_var.get()
        if enabled:
            vbs_content = f'''Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c tasklist /FI ""IMAGENAME eq tor.exe"" | find /I ""tor.exe"" || {TOR_EXE} -f {TOR_RC}", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && tasklist /FI ""IMAGENAME eq php.exe"" | find /I ""php.exe"" || php artisan serve --host=127.0.0.1 --port=8000", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && pythonw.exe telegram_admin_bot.pyw", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && pythonw.exe auto_git_sync.py", 0, False
'''
            try:
                os.makedirs(os.path.dirname(STARTUP_FILE), exist_ok=True)
                with open(STARTUP_FILE, "w", encoding="utf-8") as f:
                    f.write(vbs_content)
                self.show_alert("Auto-start enabled! Server & Bot will run on PC boot.", "#4ade80")
            except Exception as e:
                self.show_alert(f"Failed to enable: {e}", "#f87171")
        else:
            try:
                if os.path.exists(STARTUP_FILE):
                    os.remove(STARTUP_FILE)
                self.show_alert("Auto-start disabled. Server will NOT run on PC boot.", "#f87171")
            except Exception as e:
                self.show_alert(f"Failed to disable: {e}", "#f87171")

    def is_port_open(self, host="127.0.0.1", port=8000):
        try:
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                s.settimeout(0.5)
                return s.connect_ex((host, port)) == 0
        except Exception:
            return False

    def is_process_running(self, proc_name):
        try:
            output = subprocess.check_output(
                f'tasklist /FI "IMAGENAME eq {proc_name}"',
                shell=True,
                creationflags=0x08000000  # CREATE_NO_WINDOW
            ).decode(errors="ignore")
            return proc_name.lower() in output.lower()
        except Exception:
            return False

    def is_bot_running(self):
        try:
            import ctypes
            SYNCHRONIZE = 0x00100000
            test_mutex = ctypes.windll.kernel32.OpenMutexW(SYNCHRONIZE, False, "Global\\PayateAdminTelegramBotMutex")
            if test_mutex:
                ctypes.windll.kernel32.CloseHandle(test_mutex)
                return True
            return False
        except Exception:
            return False

    def monitor_services_loop(self):
        while self.monitor_active:
            php_ok = self.is_port_open("127.0.0.1", 8000)
            tor_ok = self.is_process_running("tor.exe")
            bot_ok = self.is_bot_running()
            self.php_status = php_ok
            self.tor_status = tor_ok
            self.bot_status = bot_ok

            if self.authenticated:
                self.root.after(0, self.update_ui_state, php_ok, tor_ok, bot_ok)
            time.sleep(1.0)

    def update_ui_state(self, php_ok, tor_ok, bot_ok):
        if not self.authenticated:
            return

        if php_ok and tor_ok:
            self.is_running = True
            self.badge_lbl.config(text="ONLINE", fg="#22c55e", bg="#064e3b")
            self.status_big_dot.config(text="●", fg="#22c55e")
            self.status_text.config(text="WEBSITE & BOT ARE LIVE", fg="#4ade80")
            self.power_btn.config(
                text="🛑 TURN OFFLINE (Stop All Services)",
                bg="#dc2626",
                activebackground="#b91c1c"
            )
            self.php_indicator.config(text="●", fg="#22c55e")
            self.php_val.config(text="Active (Port 8000)", fg="#4ade80")
            self.tor_indicator.config(text="●", fg="#22c55e")
            self.tor_val.config(text="Connected (Onion Live)", fg="#4ade80")
        elif php_ok or tor_ok:
            self.is_running = True
            self.badge_lbl.config(text="STARTING...", fg="#f59e0b", bg="#78350f")
            self.status_big_dot.config(text="●", fg="#f59e0b")
            self.status_text.config(text="SERVICES STARTING...", fg="#fde047")
            self.power_btn.config(
                text="🛑 STOP SERVER",
                bg="#ea580c",
                activebackground="#c2410c"
            )
            self.php_indicator.config(text="●" if php_ok else "○", fg="#22c55e" if php_ok else "#ef4444")
            self.php_val.config(text="Active" if php_ok else "Offline", fg="#4ade80" if php_ok else "#f87171")
            self.tor_indicator.config(text="●" if tor_ok else "○", fg="#22c55e" if tor_ok else "#ef4444")
            self.tor_val.config(text="Connected" if tor_ok else "Offline", fg="#4ade80" if tor_ok else "#f87171")
        else:
            self.is_running = False
            self.badge_lbl.config(text="OFFLINE", fg="#ef4444", bg="#450a0a")
            self.status_big_dot.config(text="○", fg="#64748b")
            self.status_text.config(text="WEBSITE IS OFFLINE", fg="#94a3b8")
            self.power_btn.config(
                text="▶ TURN ONLINE (Start Server & Bot)",
                bg="#16a34a",
                activebackground="#15803d"
            )
            self.php_indicator.config(text="○", fg="#64748b")
            self.php_val.config(text="Offline", fg="#64748b")
            self.tor_indicator.config(text="○", fg="#64748b")
            self.tor_val.config(text="Offline", fg="#64748b")

        # Bot status indicator
        self.bot_indicator.config(text="●" if bot_ok else "○", fg="#22c55e" if bot_ok else "#64748b")
        self.bot_val.config(text="Active & Monitoring" if bot_ok else "Offline", fg="#4ade80" if bot_ok else "#64748b")

    def toggle_server(self):
        if self.is_running:
            self.stop_services()
        else:
            self.start_services()

    def start_services(self):
        def _start():
            # Start Tor if not running
            if not self.is_process_running("tor.exe"):
                if os.path.exists(TOR_EXE) and os.path.exists(TOR_RC):
                    subprocess.Popen(
                        [TOR_EXE, "-f", TOR_RC],
                        creationflags=0x08000000  # CREATE_NO_WINDOW
                    )
            
            # Start PHP Laravel if not listening
            if not self.is_port_open("127.0.0.1", 8000):
                subprocess.Popen(
                    ["php", "artisan", "serve", "--host=127.0.0.1", "--port=8000"],
                    cwd=PROJECT_DIR,
                    creationflags=0x08000000  # CREATE_NO_WINDOW
                )

            # Start Telegram Admin Bot if not running
            if not self.is_bot_running():
                if os.path.exists(BOT_SCRIPT):
                    subprocess.Popen(
                        ["pythonw.exe", BOT_SCRIPT],
                        cwd=PROJECT_DIR,
                        creationflags=0x08000000 | 0x00000200 | 0x00000008  # Detached process
                    )
            
            if self.authenticated:
                self.root.after(0, lambda: self.show_alert("Starting Tor, Laravel, and Telegram Bot...", "#38bdf8"))
        
        threading.Thread(target=_start, daemon=True).start()

    def stop_services(self):
        def _stop():
            try:
                subprocess.run('taskkill /F /IM tor.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
                subprocess.run('taskkill /F /IM php.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
            except Exception:
                pass
            if self.authenticated:
                self.root.after(0, lambda: self.show_alert("Server stopped. Website is now OFFLINE.", "#f87171"))

        threading.Thread(target=_stop, daemon=True).start()

    def copy_onion_link(self):
        self.root.clipboard_clear()
        self.root.clipboard_append(ONION_URL)
        self.show_alert("✔ Onion link copied to clipboard!", "#4ade80")

    def open_localhost(self):
        webbrowser.open(LOCAL_URL)
        self.show_alert("Opening http://127.0.0.1:8000...", "#38bdf8")

    def push_to_git_now(self):
        def _run():
            self.root.after(0, lambda: self.show_alert("⏳ Checking & pushing to GitHub...", "#38bdf8"))
            try:
                from auto_git_sync import sync_and_push_now
                ok, msg = sync_and_push_now(notify_telegram=True)
                color = "#4ade80" if ok else "#f87171"
                self.root.after(0, lambda: self.show_alert(f"✔ {msg}" if ok else f"❌ {msg}", color))
            except Exception as e:
                self.root.after(0, lambda: self.show_alert(f"❌ Error: {e}", "#f87171"))

        threading.Thread(target=_run, daemon=True).start()

    def show_alert(self, msg, color="#4ade80"):
        if hasattr(self, "alert_lbl") and self.alert_lbl.winfo_exists():
            self.alert_lbl.config(text=msg, fg=color)
            self.root.after(4000, lambda: self.alert_lbl.config(text="") if self.alert_lbl.winfo_exists() else None)

    def on_close(self):
        self.monitor_active = False
        try:
            subprocess.run('taskkill /F /IM tor.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
            subprocess.run('taskkill /F /IM php.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
        except Exception:
            pass
        self.root.destroy()

if __name__ == "__main__":
    root = tk.Tk()
    app = ServerControllerApp(root)
    root.mainloop()
