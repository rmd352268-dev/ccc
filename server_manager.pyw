import os
import sys
import time
import socket
import threading
import subprocess
import webbrowser
import tkinter as tk
from tkinter import messagebox

# Enable High DPI Awareness on Windows
try:
    import ctypes
    ctypes.windll.shcore.SetProcessDpiAwareness(1)
except Exception:
    try:
        ctypes.windll.user32.SetProcessDPIAware()
    except Exception:
        pass

PROJECT_DIR = r"C:\Users\hp\Desktop\ccc"
PHP_EXE = r"C:\Users\hp\php\php.exe"
PYTHONW_EXE = r"C:\Program Files\Python314\pythonw.exe"
TOR_EXE = r"C:\Users\hp\tor_service\tor\tor.exe"
TOR_RC = r"C:\Users\hp\tor_service\torrc"
TOR_DIR = r"C:\Users\hp\tor_service"

BOT_SCRIPT = os.path.join(PROJECT_DIR, "telegram_admin_bot.pyw")
SUPPORT_BOT_SCRIPT = os.path.join(PROJECT_DIR, "telegram_support_bot.pyw")
AUTO_GIT_SCRIPT = os.path.join(PROJECT_DIR, "auto_git_sync.py")

ONION_URL = "http://7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion"
LOCAL_URL = "http://127.0.0.1:8000"

STARTUP_FILE = os.path.join(
    os.environ.get("APPDATA", r"C:\Users\hp\AppData\Roaming"),
    r"Microsoft\Windows\Start Menu\Programs\Startup\StartLaravelTor.vbs"
)

class ModernServerManager:
    def __init__(self, root):
        self.root = root
        self.root.title("⚡ Server & Bot Controller Hub")
        self.root.geometry("460x780")
        self.root.minsize(440, 720)
        self.root.configure(bg="#090d16")

        # Service running states
        self.php_status = False
        self.tor_status = False
        self.website_status = False
        self.admin_bot_status = False
        self.support_bot_status = False
        self.git_sync_status = False

        self.always_on_top = tk.BooleanVar(value=True)
        self.autostart_var = tk.BooleanVar(value=os.path.exists(STARTUP_FILE))

        # Float on top by default
        self.root.attributes("-topmost", True)

        # Build complete UI
        self.setup_ui()

        # Start continuous monitoring thread
        self.monitor_active = True
        self.monitor_thread = threading.Thread(target=self.monitor_loop, daemon=True)
        self.monitor_thread.start()

        # Handle window close
        self.root.protocol("WM_DELETE_WINDOW", self.on_close)

        # Initial check & auto-sync trigger
        self.root.after(300, self.initial_refresh)

    def setup_ui(self):
        # -------------------------------------------------------------
        # TOP HEADER
        # -------------------------------------------------------------
        header_frame = tk.Frame(self.root, bg="#111827", height=65)
        header_frame.pack(fill="x", padx=0, pady=0)

        title_box = tk.Frame(header_frame, bg="#111827")
        title_box.pack(side="left", padx=14, pady=10)

        main_title = tk.Label(
            title_box,
            text="⚡ SERVER & BOT CONTROLLER",
            font=("Segoe UI", 12, "bold"),
            fg="#38bdf8",
            bg="#111827"
        )
        main_title.pack(anchor="w")

        sub_title = tk.Label(
            title_box,
            text="Individual Service & Bot Live Switcher",
            font=("Segoe UI", 8),
            fg="#94a3b8",
            bg="#111827"
        )
        sub_title.pack(anchor="w")

        self.master_badge = tk.Label(
            header_frame,
            text="CHECKING...",
            font=("Segoe UI", 9, "bold"),
            fg="#f59e0b",
            bg="#334155",
            padx=10,
            pady=4,
            relief="flat"
        )
        self.master_badge.pack(side="right", padx=14, pady=12)

        # -------------------------------------------------------------
        # OVERVIEW BAR
        # -------------------------------------------------------------
        summary_frame = tk.Frame(self.root, bg="#0f172a", padx=14, pady=6)
        summary_frame.pack(fill="x", padx=10, pady=(6, 4))

        self.summary_lbl = tk.Label(
            summary_frame,
            text="● Initializing service listeners...",
            font=("Segoe UI", 9, "bold"),
            fg="#cbd5e1",
            bg="#0f172a"
        )
        self.summary_lbl.pack(side="left")

        # -------------------------------------------------------------
        # SERVICE CARDS CONTAINER (SCROLLABLE / EXPANDABLE)
        # -------------------------------------------------------------
        cards_container = tk.Frame(self.root, bg="#090d16", padx=10, pady=0)
        cards_container.pack(fill="both", expand=True)

        # -------------------------------------------------------------
        # CARD 1: WEBSITE & TOR SERVICE
        # -------------------------------------------------------------
        self.card_web = tk.Frame(cards_container, bg="#131d2e", bd=1, relief="solid", highlightbackground="#1e293b", highlightthickness=1, padx=12, pady=10)
        self.card_web.pack(fill="x", pady=4)

        web_head = tk.Frame(self.card_web, bg="#131d2e")
        web_head.pack(fill="x")

        self.web_dot = tk.Label(web_head, text="○", font=("Segoe UI", 12, "bold"), fg="#64748b", bg="#131d2e")
        self.web_dot.pack(side="left")

        web_title_box = tk.Frame(web_head, bg="#131d2e")
        web_title_box.pack(side="left", padx=6)
        tk.Label(web_title_box, text="🌐 Website & Tor Onion", font=("Segoe UI", 10, "bold"), fg="#f8fafc", bg="#131d2e").pack(anchor="w")
        self.web_sub_lbl = tk.Label(web_title_box, text="Laravel Server (Port 8000) & Tor Daemon", font=("Segoe UI", 8), fg="#94a3b8", bg="#131d2e")
        self.web_sub_lbl.pack(anchor="w")

        self.web_toggle_btn = tk.Button(
            web_head,
            text="▶ TURN ON",
            font=("Segoe UI", 9, "bold"),
            bg="#1e293b",
            fg="#94a3b8",
            activebackground="#334155",
            activeforeground="white",
            relief="flat",
            cursor="hand2",
            padx=10,
            pady=4,
            command=self.toggle_website
        )
        self.web_toggle_btn.pack(side="right")

        # Quick action links for website
        web_actions = tk.Frame(self.card_web, bg="#131d2e")
        web_actions.pack(fill="x", pady=(8, 0))

        self.btn_open_local = tk.Button(
            web_actions,
            text="🌐 Open Localhost",
            font=("Segoe UI", 8, "bold"),
            bg="#0284c7",
            fg="white",
            activebackground="#0369a1",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=3,
            command=self.open_localhost
        )
        self.btn_open_local.pack(side="left", fill="x", expand=True, padx=(0, 3))

        self.btn_copy_onion = tk.Button(
            web_actions,
            text="📋 Copy Onion Link",
            font=("Segoe UI", 8, "bold"),
            bg="#2563eb",
            fg="white",
            activebackground="#1d4ed8",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=3,
            command=self.copy_onion_link
        )
        self.btn_copy_onion.pack(side="right", fill="x", expand=True, padx=(3, 0))

        # -------------------------------------------------------------
        # CARD 2: TELEGRAM ADMIN BOT
        # -------------------------------------------------------------
        self.card_admin = tk.Frame(cards_container, bg="#131d2e", bd=1, relief="solid", highlightbackground="#1e293b", highlightthickness=1, padx=12, pady=10)
        self.card_admin.pack(fill="x", pady=4)

        admin_head = tk.Frame(self.card_admin, bg="#131d2e")
        admin_head.pack(fill="x")

        self.admin_dot = tk.Label(admin_head, text="○", font=("Segoe UI", 12, "bold"), fg="#64748b", bg="#131d2e")
        self.admin_dot.pack(side="left")

        admin_title_box = tk.Frame(admin_head, bg="#131d2e")
        admin_title_box.pack(side="left", padx=6)
        tk.Label(admin_title_box, text="🛡️ Telegram Admin Bot", font=("Segoe UI", 10, "bold"), fg="#f8fafc", bg="#131d2e").pack(anchor="w")
        self.admin_sub_lbl = tk.Label(admin_title_box, text="Orders, Payments, Cards & Admin Control", font=("Segoe UI", 8), fg="#94a3b8", bg="#131d2e")
        self.admin_sub_lbl.pack(anchor="w")

        self.admin_toggle_btn = tk.Button(
            admin_head,
            text="▶ TURN ON",
            font=("Segoe UI", 9, "bold"),
            bg="#1e293b",
            fg="#94a3b8",
            activebackground="#334155",
            activeforeground="white",
            relief="flat",
            cursor="hand2",
            padx=10,
            pady=4,
            command=self.toggle_admin_bot
        )
        self.admin_toggle_btn.pack(side="right")

        admin_actions = tk.Frame(self.card_admin, bg="#131d2e")
        admin_actions.pack(fill="x", pady=(6, 0))

        btn_open_admin_tg = tk.Button(
            admin_actions,
            text="↗ Open Telegram Bot (@MypayteAdmin_Bot)",
            font=("Segoe UI", 8, "bold"),
            bg="#0284c7",
            fg="white",
            activebackground="#0369a1",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=3,
            command=lambda: webbrowser.open("https://t.me/MypayteAdmin_Bot")
        )
        btn_open_admin_tg.pack(fill="x")

        # -------------------------------------------------------------
        # CARD 3: TELEGRAM SUPPORT BOT
        # -------------------------------------------------------------
        self.card_support = tk.Frame(cards_container, bg="#131d2e", bd=1, relief="solid", highlightbackground="#1e293b", highlightthickness=1, padx=12, pady=10)
        self.card_support.pack(fill="x", pady=4)

        support_head = tk.Frame(self.card_support, bg="#131d2e")
        support_head.pack(fill="x")

        self.support_dot = tk.Label(support_head, text="○", font=("Segoe UI", 12, "bold"), fg="#64748b", bg="#131d2e")
        self.support_dot.pack(side="left")

        support_title_box = tk.Frame(support_head, bg="#131d2e")
        support_title_box.pack(side="left", padx=6)
        tk.Label(support_title_box, text="💬 Live Support Desk Bot", font=("Segoe UI", 10, "bold"), fg="#f8fafc", bg="#131d2e").pack(anchor="w")
        self.support_sub_lbl = tk.Label(support_title_box, text="Public Live Customer Support Relay", font=("Segoe UI", 8), fg="#94a3b8", bg="#131d2e")
        self.support_sub_lbl.pack(anchor="w")

        self.support_toggle_btn = tk.Button(
            support_head,
            text="▶ TURN ON",
            font=("Segoe UI", 9, "bold"),
            bg="#1e293b",
            fg="#94a3b8",
            activebackground="#334155",
            activeforeground="white",
            relief="flat",
            cursor="hand2",
            padx=10,
            pady=4,
            command=self.toggle_support_bot
        )
        self.support_toggle_btn.pack(side="right")

        support_actions = tk.Frame(self.card_support, bg="#131d2e")
        support_actions.pack(fill="x", pady=(6, 0))

        btn_open_support_tg = tk.Button(
            support_actions,
            text="↗ Open Telegram Bot (@payate_desk_bot)",
            font=("Segoe UI", 8, "bold"),
            bg="#0d9488",
            fg="white",
            activebackground="#0f766e",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=3,
            command=lambda: webbrowser.open("https://t.me/payate_desk_bot")
        )
        btn_open_support_tg.pack(fill="x")

        # -------------------------------------------------------------
        # CARD 4: AUTO GIT SYNC ENGINE
        # -------------------------------------------------------------
        self.card_git = tk.Frame(cards_container, bg="#131d2e", bd=1, relief="solid", highlightbackground="#1e293b", highlightthickness=1, padx=12, pady=10)
        self.card_git.pack(fill="x", pady=4)

        git_head = tk.Frame(self.card_git, bg="#131d2e")
        git_head.pack(fill="x")

        self.git_dot = tk.Label(git_head, text="○", font=("Segoe UI", 12, "bold"), fg="#64748b", bg="#131d2e")
        self.git_dot.pack(side="left")

        git_title_box = tk.Frame(git_head, bg="#131d2e")
        git_title_box.pack(side="left", padx=6)
        tk.Label(git_title_box, text="🔄 GitHub Auto-Sync Engine", font=("Segoe UI", 10, "bold"), fg="#f8fafc", bg="#131d2e").pack(anchor="w")
        self.git_sub_lbl = tk.Label(git_title_box, text="Automated Cloud Backup & Push", font=("Segoe UI", 8), fg="#94a3b8", bg="#131d2e")
        self.git_sub_lbl.pack(anchor="w")

        self.git_toggle_btn = tk.Button(
            git_head,
            text="▶ TURN ON",
            font=("Segoe UI", 9, "bold"),
            bg="#1e293b",
            fg="#94a3b8",
            activebackground="#334155",
            activeforeground="white",
            relief="flat",
            cursor="hand2",
            padx=10,
            pady=4,
            command=self.toggle_git_sync
        )
        self.git_toggle_btn.pack(side="right")

        git_actions = tk.Frame(self.card_git, bg="#131d2e")
        git_actions.pack(fill="x", pady=(6, 0))

        btn_push_now = tk.Button(
            git_actions,
            text="🚀 Sync & Push to GitHub Now",
            font=("Segoe UI", 8, "bold"),
            bg="#16a34a",
            fg="white",
            activebackground="#15803d",
            relief="flat",
            cursor="hand2",
            padx=6,
            pady=3,
            command=self.push_to_git_now
        )
        btn_push_now.pack(fill="x")

        # -------------------------------------------------------------
        # MASTER CONTROL BUTTONS (BOTTOM - PROMINENT AS REQUESTED)
        # -------------------------------------------------------------
        master_box = tk.Frame(self.root, bg="#0f172a", padx=12, pady=10)
        master_box.pack(fill="x", padx=10, pady=(6, 4))

        tk.Label(
            master_box,
            text="⚡ MASTER CONTROLS (ALL IN ONE)",
            font=("Segoe UI", 9, "bold"),
            fg="#38bdf8",
            bg="#0f172a"
        ).pack(anchor="w", pady=(0, 6))

        master_btn_row = tk.Frame(master_box, bg="#0f172a")
        master_btn_row.pack(fill="x")

        self.all_on_btn = tk.Button(
            master_btn_row,
            text="⚡ ALL SERVERS ON\n(Start All Services & Bots)",
            font=("Segoe UI", 10, "bold"),
            bg="#16a34a",
            fg="white",
            activebackground="#15803d",
            activeforeground="white",
            relief="flat",
            cursor="hand2",
            padx=10,
            pady=8,
            command=self.start_all_services
        )
        self.all_on_btn.pack(side="left", fill="x", expand=True, padx=(0, 4))

        self.all_off_btn = tk.Button(
            master_btn_row,
            text="🛑 STOP ALL\n(Turn All Offline)",
            font=("Segoe UI", 10, "bold"),
            bg="#dc2626",
            fg="white",
            activebackground="#b91c1c",
            activeforeground="white",
            relief="flat",
            cursor="hand2",
            padx=10,
            pady=8,
            command=self.stop_all_services
        )
        self.all_off_btn.pack(side="right", fill="x", expand=True, padx=(4, 0))

        # Alert / Toast notification banner
        self.alert_lbl = tk.Label(self.root, text="", font=("Segoe UI", 8, "bold"), fg="#4ade80", bg="#090d16")
        self.alert_lbl.pack(pady=(2, 2))

        # -------------------------------------------------------------
        # BOTTOM UTILITY OPTIONS (FLOATING & WINDOWS AUTOSTART)
        # -------------------------------------------------------------
        opts_frame = tk.Frame(self.root, bg="#090d16", padx=12, pady=0)
        opts_frame.pack(fill="x", padx=4, pady=(0, 8))

        top_chk = tk.Checkbutton(
            opts_frame,
            text="📌 Keep on top (Floating Window)",
            variable=self.always_on_top,
            font=("Segoe UI", 8),
            fg="#94a3b8",
            bg="#090d16",
            selectcolor="#1e293b",
            activebackground="#090d16",
            activeforeground="#38bdf8",
            command=self.toggle_topmost
        )
        top_chk.pack(anchor="w")

        start_chk = tk.Checkbutton(
            opts_frame,
            text="🚀 Auto-start all services when PC boots",
            variable=self.autostart_var,
            font=("Segoe UI", 8),
            fg="#94a3b8",
            bg="#090d16",
            selectcolor="#1e293b",
            activebackground="#090d16",
            activeforeground="#38bdf8",
            command=self.toggle_autostart
        )
        start_chk.pack(anchor="w")

    # -------------------------------------------------------------
    # STATUS DETECTION HELPERS
    # -------------------------------------------------------------
    def is_port_open(self, host="127.0.0.1", port=8000):
        try:
            with socket.socket(socket.AF_INET, socket.SOCK_STREAM) as s:
                s.settimeout(0.4)
                return s.connect_ex((host, port)) == 0
        except Exception:
            return False

    def is_process_running(self, proc_name):
        try:
            output = subprocess.check_output(
                f'tasklist /FI "IMAGENAME eq {proc_name}"',
                shell=True,
                creationflags=0x08000000
            ).decode(errors="ignore")
            return proc_name.lower() in output.lower()
        except Exception:
            return False

    def is_mutex_active(self, mutex_name):
        try:
            import ctypes
            SYNCHRONIZE = 0x00100000
            test_mutex = ctypes.windll.kernel32.OpenMutexW(SYNCHRONIZE, False, mutex_name)
            if test_mutex:
                ctypes.windll.kernel32.CloseHandle(test_mutex)
                return True
            return False
        except Exception:
            return False

    def is_script_running(self, script_name):
        try:
            ps_script = f"Get-CimInstance Win32_Process | Where-Object {{ $_.CommandLine -like '*{script_name}*' }} | Select-Object -ExpandProperty ProcessId"
            res = subprocess.run(
                ["powershell", "-NoProfile", "-NonInteractive", "-Command", ps_script],
                capture_output=True,
                text=True,
                creationflags=0x08000000
            )
            pids = [p.strip() for p in res.stdout.splitlines() if p.strip().isdigit()]
            return len(pids) > 0
        except Exception:
            return False

    # -------------------------------------------------------------
    # PROCESS TERMINATION HELPERS
    # -------------------------------------------------------------
    def stop_script_by_pattern(self, pattern):
        try:
            ps_script = f"Get-CimInstance Win32_Process | Where-Object {{ $_.CommandLine -like '*{pattern}*' }} | ForEach-Object {{ Stop-Process -Id $_.ProcessId -Force }}"
            subprocess.run(
                ["powershell", "-NoProfile", "-NonInteractive", "-Command", ps_script],
                creationflags=0x08000000,
                capture_output=True
            )
        except Exception:
            pass

    # -------------------------------------------------------------
    # MONITORING LOOP
    # -------------------------------------------------------------
    def monitor_loop(self):
        while self.monitor_active:
            php_ok = self.is_port_open("127.0.0.1", 8000)
            tor_ok = self.is_process_running("tor.exe")
            web_ok = php_ok or tor_ok

            admin_ok = self.is_mutex_active("PayateAdminTelegramBotMutex") or self.is_mutex_active("Global\\PayateAdminTelegramBotMutex")
            if not admin_ok:
                admin_ok = self.is_script_running("telegram_admin_bot.pyw")

            support_ok = self.is_mutex_active("PayateSupportTelegramBotMutex") or self.is_mutex_active("Global\\PayateSupportTelegramBotMutex")
            if not support_ok:
                support_ok = self.is_script_running("telegram_support_bot.pyw")

            git_ok = self.is_mutex_active("Global\\PayateAutoGitSyncMutex")
            if not git_ok:
                git_ok = self.is_script_running("auto_git_sync.py")

            self.php_status = php_ok
            self.tor_status = tor_ok
            self.website_status = web_ok
            self.admin_bot_status = admin_ok
            self.support_bot_status = support_ok
            self.git_sync_status = git_ok

            # Update UI on main thread
            try:
                self.root.after(0, self.update_ui_state, php_ok, tor_ok, admin_ok, support_ok, git_ok)
            except Exception:
                pass

            time.sleep(1.0)

    # -------------------------------------------------------------
    # UI STATE UPDATE
    # -------------------------------------------------------------
    def update_ui_state(self, php_ok, tor_ok, admin_ok, support_ok, git_ok):
        web_ok = php_ok and tor_ok
        web_partial = (php_ok or tor_ok) and not web_ok

        # 1. Update Card 1 (Website)
        if web_ok:
            self.web_dot.config(text="●", fg="#22c55e")
            self.web_sub_lbl.config(text="● LIVE (Port 8000 & Onion Live)", fg="#4ade80")
            self.web_toggle_btn.config(
                text="🟢 ON | STOP",
                bg="#065f46",
                fg="#6ee7b7",
                activebackground="#047857"
            )
        elif web_partial:
            self.web_dot.config(text="●", fg="#f59e0b")
            status_desc = "Port 8000 Active" if php_ok else "Tor Active"
            self.web_sub_lbl.config(text=f"● STARTING ({status_desc})", fg="#fde047")
            self.web_toggle_btn.config(
                text="🟡 ON | STOP",
                bg="#854d0e",
                fg="#fef08a",
                activebackground="#a16207"
            )
        else:
            self.web_dot.config(text="○", fg="#64748b")
            self.web_sub_lbl.config(text="○ OFFLINE (Server Stopped)", fg="#64748b")
            self.web_toggle_btn.config(
                text="▶ START",
                bg="#1e293b",
                fg="#94a3b8",
                activebackground="#334155"
            )

        # 2. Update Card 2 (Admin Bot)
        if admin_ok:
            self.admin_dot.config(text="●", fg="#22c55e")
            self.admin_sub_lbl.config(text="● LIVE (@MypayteAdmin_Bot Active)", fg="#4ade80")
            self.admin_toggle_btn.config(
                text="🟢 ON | STOP",
                bg="#065f46",
                fg="#6ee7b7",
                activebackground="#047857"
            )
        else:
            self.admin_dot.config(text="○", fg="#64748b")
            self.admin_sub_lbl.config(text="○ OFFLINE (Bot Stopped)", fg="#64748b")
            self.admin_toggle_btn.config(
                text="▶ START",
                bg="#1e293b",
                fg="#94a3b8",
                activebackground="#334155"
            )

        # 3. Update Card 3 (Support Bot)
        if support_ok:
            self.support_dot.config(text="●", fg="#22c55e")
            self.support_sub_lbl.config(text="● LIVE (@payate_desk_bot Active)", fg="#4ade80")
            self.support_toggle_btn.config(
                text="🟢 ON | STOP",
                bg="#065f46",
                fg="#6ee7b7",
                activebackground="#047857"
            )
        else:
            self.support_dot.config(text="○", fg="#64748b")
            self.support_sub_lbl.config(text="○ OFFLINE (Bot Stopped)", fg="#64748b")
            self.support_toggle_btn.config(
                text="▶ START",
                bg="#1e293b",
                fg="#94a3b8",
                activebackground="#334155"
            )

        # 4. Update Card 4 (Git Sync)
        if git_ok:
            self.git_dot.config(text="●", fg="#22c55e")
            self.git_sub_lbl.config(text="● LIVE (Watching & Auto-Pushing)", fg="#4ade80")
            self.git_toggle_btn.config(
                text="🟢 ON | STOP",
                bg="#065f46",
                fg="#6ee7b7",
                activebackground="#047857"
            )
        else:
            self.git_dot.config(text="○", fg="#64748b")
            self.git_sub_lbl.config(text="○ OFFLINE (Watcher Stopped)", fg="#64748b")
            self.git_toggle_btn.config(
                text="▶ START",
                bg="#1e293b",
                fg="#94a3b8",
                activebackground="#334155"
            )

        # Master Overview Calculation
        active_count = sum([1 for ok in [php_ok or tor_ok, admin_ok, support_ok, git_ok] if ok])
        total_services = 4

        if active_count == total_services:
            self.master_badge.config(text="● ALL LIVE (4/4)", fg="#22c55e", bg="#064e3b")
            self.summary_lbl.config(text="● All Services & Bots are Running and Live!", fg="#4ade80")
        elif active_count > 0:
            self.master_badge.config(text=f"● PARTIAL ({active_count}/{total_services})", fg="#f59e0b", bg="#78350f")
            self.summary_lbl.config(text=f"● {active_count} of {total_services} services active", fg="#fde047")
        else:
            self.master_badge.config(text="○ ALL OFFLINE", fg="#ef4444", bg="#450a0a")
            self.summary_lbl.config(text="○ All Services & Bots are Offline", fg="#94a3b8")

    # -------------------------------------------------------------
    # INDIVIDUAL SERVICE TOGGLES
    # -------------------------------------------------------------
    def toggle_website(self):
        if self.php_status or self.tor_status:
            threading.Thread(target=self._stop_website, daemon=True).start()
        else:
            threading.Thread(target=self._start_website, daemon=True).start()

    def _start_website(self):
        self.show_alert("Starting Tor & Laravel Server...", "#38bdf8")
        if not self.is_process_running("tor.exe"):
            if os.path.exists(TOR_EXE) and os.path.exists(TOR_RC):
                subprocess.Popen([TOR_EXE, "-f", TOR_RC], cwd=TOR_DIR, creationflags=0x08000000)
        if not self.is_port_open("127.0.0.1", 8000):
            php_cmd = PHP_EXE if os.path.exists(PHP_EXE) else "php"
            subprocess.Popen([php_cmd, "artisan", "serve", "--host=127.0.0.1", "--port=8000"], cwd=PROJECT_DIR, creationflags=0x08000000)
        self.show_alert("✔ Website & Tor launched!", "#4ade80")


    def _stop_website(self):
        self.show_alert("Stopping Website & Tor...", "#f87171")
        try:
            subprocess.run('taskkill /F /IM tor.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
            subprocess.run('taskkill /F /IM php.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
        except Exception:
            pass
        self.show_alert("Website & Tor turned OFFLINE.", "#f87171")

    def toggle_admin_bot(self):
        if self.admin_bot_status:
            threading.Thread(target=self._stop_admin_bot, daemon=True).start()
        else:
            threading.Thread(target=self._start_admin_bot, daemon=True).start()

    def _start_admin_bot(self):
        self.show_alert("Starting Telegram Admin Bot...", "#38bdf8")
        if not self.admin_bot_status and os.path.exists(BOT_SCRIPT):
            pyw_cmd = PYTHONW_EXE if os.path.exists(PYTHONW_EXE) else "pythonw.exe"
            subprocess.Popen([pyw_cmd, BOT_SCRIPT], cwd=PROJECT_DIR, creationflags=0x08000000)
        self.show_alert("✔ Admin Bot started!", "#4ade80")

    def _stop_admin_bot(self):
        self.show_alert("Stopping Telegram Admin Bot...", "#f87171")
        self.stop_script_by_pattern("telegram_admin_bot.pyw")
        self.show_alert("Admin Bot stopped.", "#f87171")

    def toggle_support_bot(self):
        if self.support_bot_status:
            threading.Thread(target=self._stop_support_bot, daemon=True).start()
        else:
            threading.Thread(target=self._start_support_bot, daemon=True).start()

    def _start_support_bot(self):
        self.show_alert("Starting Telegram Support Bot...", "#38bdf8")
        if not self.support_bot_status and os.path.exists(SUPPORT_BOT_SCRIPT):
            pyw_cmd = PYTHONW_EXE if os.path.exists(PYTHONW_EXE) else "pythonw.exe"
            subprocess.Popen([pyw_cmd, SUPPORT_BOT_SCRIPT], cwd=PROJECT_DIR, creationflags=0x08000000)
        self.show_alert("✔ Support Bot started!", "#4ade80")

    def _stop_support_bot(self):
        self.show_alert("Stopping Telegram Support Bot...", "#f87171")
        self.stop_script_by_pattern("telegram_support_bot.pyw")
        self.show_alert("Support Bot stopped.", "#f87171")

    def toggle_git_sync(self):
        if self.git_sync_status:
            threading.Thread(target=self._stop_git_sync, daemon=True).start()
        else:
            threading.Thread(target=self._start_git_sync, daemon=True).start()

    def _start_git_sync(self):
        self.show_alert("Starting Auto Git Sync Engine...", "#38bdf8")
        if not self.git_sync_status and os.path.exists(AUTO_GIT_SCRIPT):
            pyw_cmd = PYTHONW_EXE if os.path.exists(PYTHONW_EXE) else "pythonw.exe"
            subprocess.Popen([pyw_cmd, AUTO_GIT_SCRIPT], cwd=PROJECT_DIR, creationflags=0x08000000)
        self.show_alert("✔ Auto Git Sync started!", "#4ade80")

    def _stop_git_sync(self):
        self.show_alert("Stopping Auto Git Sync Engine...", "#f87171")
        self.stop_script_by_pattern("auto_git_sync.py")
        self.show_alert("Auto Git Sync stopped.", "#f87171")

    # -------------------------------------------------------------
    # MASTER ALL START / STOP ACTIONS
    # -------------------------------------------------------------
    def start_all_services(self):
        def _run():
            self.root.after(0, lambda: self.show_alert("⚡ Starting ALL Services & Telegram Bots...", "#38bdf8"))
            self._start_website()
            self._start_admin_bot()
            self._start_support_bot()
            self._start_git_sync()
            self.root.after(0, lambda: self.show_alert("✔ ALL SERVERS & BOTS ARE NOW LIVE!", "#4ade80"))

        threading.Thread(target=_run, daemon=True).start()

    def stop_all_services(self):
        def _run():
            self.root.after(0, lambda: self.show_alert("🛑 Turning ALL Services OFFLINE...", "#f87171"))
            self._stop_website()
            self._stop_admin_bot()
            self._stop_support_bot()
            self._stop_git_sync()
            self.root.after(0, lambda: self.show_alert("✔ All services and bots are now OFFLINE.", "#f87171"))

        threading.Thread(target=_run, daemon=True).start()

    # -------------------------------------------------------------
    # ACTION UTILITIES
    # -------------------------------------------------------------
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

    def toggle_topmost(self):
        self.root.attributes("-topmost", self.always_on_top.get())

    def toggle_autostart(self):
        enabled = self.autostart_var.get()
        if enabled:
            php_path = PHP_EXE if os.path.exists(PHP_EXE) else "php.exe"
            vbs_content = f'''Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c tasklist /FI ""IMAGENAME eq tor.exe"" | find /I ""tor.exe"" || {TOR_EXE} -f {TOR_RC}", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && tasklist /FI ""IMAGENAME eq php.exe"" | find /I ""php.exe"" || ""{php_path}"" -S 127.0.0.1:8000 -t public", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && ""{PYTHONW_EXE}"" telegram_admin_bot.pyw", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && ""{PYTHONW_EXE}"" telegram_support_bot.pyw", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && ""{PYTHONW_EXE}"" auto_git_sync.py", 0, False
'''
            try:
                os.makedirs(os.path.dirname(STARTUP_FILE), exist_ok=True)
                with open(STARTUP_FILE, "w", encoding="utf-8") as f:
                    f.write(vbs_content)
                self.show_alert("Auto-start enabled! Server & Bots will run on PC boot.", "#4ade80")
            except Exception as e:
                self.show_alert(f"Failed to enable: {e}", "#f87171")
        else:
            try:
                if os.path.exists(STARTUP_FILE):
                    os.remove(STARTUP_FILE)
                self.show_alert("Auto-start disabled. Server will NOT run on PC boot.", "#f87171")
            except Exception as e:
                self.show_alert(f"Failed to disable: {e}", "#f87171")

    def initial_refresh(self):
        pass

    def on_close(self):
        self.monitor_active = False
        self.root.destroy()

if __name__ == "__main__":
    root = tk.Tk()
    app = ModernServerManager(root)
    root.mainloop()
