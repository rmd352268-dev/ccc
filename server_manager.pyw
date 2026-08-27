import os
import sys
import time
import socket
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

class ServerControllerApp:
    def __init__(self, root):
        self.root = root
        self.root.title("⚡ Website & Bot Live Switcher")
        self.root.geometry("400x680")
        self.root.resizable(False, False)
        self.root.configure(bg="#0f172a")  # Dark Slate

        self.is_running = False
        self.tor_status = False
        self.php_status = False
        self.bot_status = False
        self.support_bot_status = False
        self.always_on_top = tk.BooleanVar(value=True)
        self.autostart_var = tk.BooleanVar(value=os.path.exists(STARTUP_FILE))

        # Set always on top default
        self.root.attributes("-topmost", True)

        self.setup_ui()

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

        # Auto-start services on app open
        self.root.after(200, self.start_services)

    def setup_ui(self):
        # Header / Title Bar
        header_frame = tk.Frame(self.root, bg="#1e293b", height=60)
        header_frame.pack(fill="x", padx=0, pady=0)

        title_label = tk.Label(
            header_frame,
            text="⚡ SERVER & BOT CONTROLLER",
            font=("Segoe UI", 12, "bold"),
            fg="#38bdf8",
            bg="#1e293b"
        )
        title_label.pack(side="left", padx=14, pady=12)

        self.badge_lbl = tk.Label(
            header_frame,
            text="STARTING...",
            font=("Segoe UI", 9, "bold"),
            fg="#f59e0b",
            bg="#334155",
            padx=8,
            pady=3
        )
        self.badge_lbl.pack(side="right", padx=14, pady=12)

        # Main Card Frame
        card = tk.Frame(self.root, bg="#1e293b", padx=16, pady=14)
        card.pack(fill="both", expand=True, padx=12, pady=10)

        # Big Status Indicator
        self.status_big_dot = tk.Label(
            card,
            text="●",
            font=("Segoe UI", 30),
            fg="#eab308",
            bg="#1e293b"
        )
        self.status_big_dot.pack(pady=(0, 0))

        self.status_text = tk.Label(
            card,
            text="INITIALIZING SERVICES...",
            font=("Segoe UI", 11, "bold"),
            fg="#cbd5e1",
            bg="#1e293b"
        )
        self.status_text.pack(pady=(0, 8))

        # Big Power Button
        self.power_btn = tk.Button(
            card,
            text="CONNECTING...",
            font=("Segoe UI", 11, "bold"),
            bg="#334155",
            fg="white",
            activebackground="#475569",
            activeforeground="white",
            relief="flat",
            cursor="hand2",
            padx=16,
            pady=8,
            command=self.toggle_server
        )
        self.power_btn.pack(fill="x", pady=4)

        # Status Grid for Tor, PHP & Bot
        status_box = tk.Frame(card, bg="#0f172a", padx=12, pady=8)
        status_box.pack(fill="x", pady=8)

        # Row 1: Laravel Web Server
        f1 = tk.Frame(status_box, bg="#0f172a")
        f1.pack(fill="x", pady=2)
        self.php_indicator = tk.Label(f1, text="○", font=("Segoe UI", 10, "bold"), fg="#64748b", bg="#0f172a")
        self.php_indicator.pack(side="left")
        tk.Label(f1, text=" Laravel Server (Port 8000)", font=("Segoe UI", 9), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.php_val = tk.Label(f1, text="Checking...", font=("Segoe UI", 9, "bold"), fg="#94a3b8", bg="#0f172a")
        self.php_val.pack(side="right")

        # Row 2: Tor Onion Daemon
        f2 = tk.Frame(status_box, bg="#0f172a")
        f2.pack(fill="x", pady=2)
        self.tor_indicator = tk.Label(f2, text="○", font=("Segoe UI", 10, "bold"), fg="#64748b", bg="#0f172a")
        self.tor_indicator.pack(side="left")
        tk.Label(f2, text=" Tor Onion Service", font=("Segoe UI", 9), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.tor_val = tk.Label(f2, text="Checking...", font=("Segoe UI", 9, "bold"), fg="#94a3b8", bg="#0f172a")
        self.tor_val.pack(side="right")

        # Row 3: Telegram Admin Bot Daemon
        f3 = tk.Frame(status_box, bg="#0f172a")
        f3.pack(fill="x", pady=2)
        self.bot_indicator = tk.Label(f3, text="○", font=("Segoe UI", 10, "bold"), fg="#64748b", bg="#0f172a")
        self.bot_indicator.pack(side="left")
        tk.Label(f3, text=" 🛡️ Telegram Admin Bot", font=("Segoe UI", 9), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.bot_val = tk.Label(f3, text="Checking...", font=("Segoe UI", 9, "bold"), fg="#94a3b8", bg="#0f172a")
        self.bot_val.pack(side="right")

        # Row 4: Telegram Customer Support Bot Daemon
        f4_bot = tk.Frame(status_box, bg="#0f172a")
        f4_bot.pack(fill="x", pady=2)
        self.support_bot_indicator = tk.Label(f4_bot, text="○", font=("Segoe UI", 10, "bold"), fg="#64748b", bg="#0f172a")
        self.support_bot_indicator.pack(side="left")
        tk.Label(f4_bot, text=" 💬 Live Support Bot", font=("Segoe UI", 9), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.support_bot_val = tk.Label(f4_bot, text="Checking...", font=("Segoe UI", 9, "bold"), fg="#94a3b8", bg="#0f172a")
        self.support_bot_val.pack(side="right")

        # Row 5: Auto Git Sync Engine
        f4 = tk.Frame(status_box, bg="#0f172a")
        f4.pack(fill="x", pady=2)
        self.git_indicator = tk.Label(f4, text="●", font=("Segoe UI", 10, "bold"), fg="#22c55e", bg="#0f172a")
        self.git_indicator.pack(side="left")
        tk.Label(f4, text=" 🚀 Auto Git Sync (GitHub)", font=("Segoe UI", 9), fg="#94a3b8", bg="#0f172a").pack(side="left")
        self.git_val = tk.Label(f4, text="Active & Live", font=("Segoe UI", 9, "bold"), fg="#4ade80", bg="#0f172a")
        self.git_val.pack(side="right")

        # Action Buttons
        btn_frame = tk.Frame(card, bg="#1e293b")
        btn_frame.pack(fill="x", pady=4)

        self.copy_btn = tk.Button(
            btn_frame,
            text="📋 Copy Onion Link",
            font=("Segoe UI", 9, "bold"),
            bg="#2563eb",
            fg="white",
            activebackground="#1d4ed8",
            relief="flat",
            cursor="hand2",
            padx=8,
            pady=6,
            command=self.copy_onion_link
        )
        self.copy_btn.pack(side="left", fill="x", expand=True, padx=(0, 3))

        self.open_local_btn = tk.Button(
            btn_frame,
            text="🌐 Open Localhost",
            font=("Segoe UI", 9, "bold"),
            bg="#0284c7",
            fg="white",
            activebackground="#0369a1",
            relief="flat",
            cursor="hand2",
            padx=8,
            pady=6,
            command=self.open_localhost
        )
        self.open_local_btn.pack(side="right", fill="x", expand=True, padx=(3, 0))

        # Telegram Open Buttons Frame
        tg_frame = tk.Frame(card, bg="#1e293b")
        tg_frame.pack(fill="x", pady=2)

        tg_admin_btn = tk.Button(
            tg_frame,
            text="🛡️ Admin Bot",
            font=("Segoe UI", 8, "bold"),
            bg="#0284c7",
            fg="white",
            activebackground="#0369a1",
            relief="flat",
            cursor="hand2",
            padx=4,
            pady=4,
            command=lambda: webbrowser.open("https://t.me/MypayteAdmin_Bot")
        )
        tg_admin_btn.pack(side="left", fill="x", expand=True, padx=(0, 2))

        tg_support_btn = tk.Button(
            tg_frame,
            text="💬 Support Bot",
            font=("Segoe UI", 8, "bold"),
            bg="#0d9488",
            fg="white",
            activebackground="#0f766e",
            relief="flat",
            cursor="hand2",
            padx=4,
            pady=4,
            command=lambda: webbrowser.open("https://t.me/PayateSupport_Bot")
        )
        tg_support_btn.pack(side="right", fill="x", expand=True, padx=(2, 0))

        # Git Push Button
        git_btn = tk.Button(
            card,
            text="🔄 Sync & Push to GitHub Now",
            font=("Segoe UI", 9, "bold"),
            bg="#16a34a",
            fg="white",
            activebackground="#15803d",
            relief="flat",
            cursor="hand2",
            padx=8,
            pady=5,
            command=self.push_to_git_now
        )
        git_btn.pack(fill="x", pady=2)

        # Copied alert label (fadeable)
        self.alert_lbl = tk.Label(card, text="", font=("Segoe UI", 8), fg="#4ade80", bg="#1e293b")
        self.alert_lbl.pack(pady=(2, 0))

        # Bottom Options
        opts_frame = tk.Frame(self.root, bg="#0f172a")
        opts_frame.pack(fill="x", padx=16, pady=(0, 10))

        # Always on top check
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

        # Autostart with windows check
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
            php_path = PHP_EXE if os.path.exists(PHP_EXE) else "php.exe"
            vbs_content = f'''Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c tasklist /FI ""IMAGENAME eq tor.exe"" | find /I ""tor.exe"" || {TOR_EXE} -f {TOR_RC}", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && tasklist /FI ""IMAGENAME eq php.exe"" | find /I ""php.exe"" || ""{php_path}"" -S 127.0.0.1:8000 -t public", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && ""{PYTHONW_EXE}"" telegram_admin_bot.pyw", 0, False
WshShell.Run "cmd /c cd /d {PROJECT_DIR} && ""{PYTHONW_EXE}"" auto_git_sync.py", 0, False
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

            self.root.after(0, self.update_ui_state, php_ok, tor_ok, bot_ok)
            time.sleep(1.0)

    def update_ui_state(self, php_ok, tor_ok, bot_ok):
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
            # 1. Start Tor Daemon
            if not self.is_process_running("tor.exe"):
                if os.path.exists(TOR_EXE) and os.path.exists(TOR_RC):
                    self.tor_proc = subprocess.Popen(
                        [TOR_EXE, "-f", TOR_RC],
                        cwd=TOR_DIR,
                        creationflags=0x08000000  # CREATE_NO_WINDOW
                    )
            
            # 2. Start PHP Laravel Server
            if not self.is_port_open("127.0.0.1", 8000):
                php_cmd = PHP_EXE if os.path.exists(PHP_EXE) else "php"
                self.php_proc = subprocess.Popen(
                    [php_cmd, "-S", "127.0.0.1:8000", "-t", "public"],
                    cwd=PROJECT_DIR,
                    creationflags=0x08000000  # CREATE_NO_WINDOW
                )

            # 3. Start Telegram Admin Bot
            if not self.is_bot_running():
                if os.path.exists(BOT_SCRIPT):
                    pyw_cmd = PYTHONW_EXE if os.path.exists(PYTHONW_EXE) else "pythonw.exe"
                    self.bot_proc = subprocess.Popen(
                        [pyw_cmd, BOT_SCRIPT],
                        cwd=PROJECT_DIR,
                        creationflags=0x08000000
                    )
            
            self.root.after(0, lambda: self.show_alert("Starting Tor, Laravel, and Telegram Bot...", "#38bdf8"))
        
        threading.Thread(target=_start, daemon=True).start()

    def stop_services(self):
        def _stop():
            try:
                subprocess.run('taskkill /F /IM tor.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
                subprocess.run('taskkill /F /IM php.exe /T', shell=True, creationflags=0x08000000, capture_output=True)
            except Exception:
                pass
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
        self.root.destroy()

if __name__ == "__main__":
    root = tk.Tk()
    app = ServerControllerApp(root)
    root.mainloop()
