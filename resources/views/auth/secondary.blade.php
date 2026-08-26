<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payate CC // 2FA Security Access Node</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap');

        :root {
            --neon-green: #00ff88;
            --neon-green-glow: rgba(0, 255, 136, 0.35);
            --neon-cyan: #00e5ff;
            --neon-emerald: #10b981;
            --cyber-dark-bg: #050811;
            --cyber-card-bg: rgba(10, 15, 26, 0.88);
            --cyber-input-bg: rgba(6, 10, 18, 0.85);
            --cyber-border: rgba(0, 255, 136, 0.28);
            --text-main: #f1f5f9;
            --text-muted: #94a3b8;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
            background-color: var(--cyber-dark-bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        #matrix-canvas {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0; pointer-events: none; opacity: 0.65;
            transition: opacity 0.5s ease;
        }
        body.solid-bg #matrix-canvas { opacity: 0; }

        .cyber-ambient-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(circle at 20% 20%, rgba(0, 255, 136, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 80% 80%, rgba(0, 229, 255, 0.08) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(5, 8, 17, 0.6) 0%, rgba(5, 8, 17, 0.95) 100%);
            z-index: 1; pointer-events: none;
        }

        .scanlines {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%);
            background-size: 100% 4px; z-index: 1; pointer-events: none; opacity: 0.4;
        }

        .top-hud-bar {
            position: fixed; top: 16px; left: 24px; right: 24px;
            display: flex; justify-content: space-between; align-items: center; z-index: 10;
        }

        .hud-status-chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(10, 15, 26, 0.8); border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 999px; padding: 6px 14px;
            font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 700;
            color: var(--neon-green); backdrop-filter: blur(10px);
            letter-spacing: 1px; text-transform: uppercase;
        }

        .status-dot-pulse {
            width: 8px; height: 8px; background-color: var(--neon-green);
            border-radius: 50%; box-shadow: 0 0 10px var(--neon-green);
            animation: pulseGlow 1.8s infinite ease-in-out;
        }
        @keyframes pulseGlow { 0%, 100% { transform: scale(1); opacity: 1; } 50% { transform: scale(1.3); opacity: 0.4; } }

        .hud-actions-right {
            display: flex; align-items: center; gap: 10px;
        }

        .lang-dropdown-cyber { position: relative; display: inline-block; }
        .lang-cyber-btn {
            background: rgba(10, 15, 26, 0.85); border: 1px solid rgba(0, 229, 255, 0.4);
            color: var(--text-main); padding: 6px 12px; border-radius: 6px;
            font-size: 11.5px; font-family: 'JetBrains Mono', monospace; font-weight: 700;
            cursor: pointer; backdrop-filter: blur(10px);
            display: inline-flex; align-items: center; gap: 6px;
        }
        .lang-cyber-menu {
            display: none; position: absolute; top: calc(100% + 6px); right: 0;
            background: rgba(8, 13, 24, 0.96); border: 1px solid rgba(0, 229, 255, 0.4);
            border-radius: 8px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            min-width: 140px; z-index: 100; overflow: hidden;
        }
        .lang-dropdown-cyber.active .lang-cyber-menu { display: block; }
        .lang-cyber-item {
            padding: 9px 14px; font-size: 12px; font-weight: 700; color: #e2e8f0;
            display: flex; align-items: center; gap: 8px; cursor: pointer;
        }
        .lang-cyber-item:hover { background: rgba(0, 255, 136, 0.12); color: var(--neon-green); }

        .bg-mode-toggle {
            background: rgba(10, 15, 26, 0.85); border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--text-muted); padding: 6px 12px; border-radius: 6px;
            font-size: 11px; font-family: 'JetBrains Mono', monospace; font-weight: 600;
            cursor: pointer; backdrop-filter: blur(10px);
            display: inline-flex; align-items: center; gap: 6px;
        }

        .auth-container {
            position: relative; z-index: 5; width: 100%; max-width: 820px;
            background: var(--cyber-card-bg); border: 1.5px solid var(--cyber-border);
            border-radius: 16px; box-shadow: 0 0 50px rgba(0, 255, 136, 0.15), 0 30px 60px -12px rgba(0, 0, 0, 0.85);
            backdrop-filter: blur(20px); overflow: hidden;
            display: grid; grid-template-columns: 320px 1fr;
        }

        .hud-corner {
            position: absolute; width: 14px; height: 14px; border-color: var(--neon-green);
            border-style: solid; pointer-events: none; z-index: 20;
        }
        .hud-corner.top-left { top: 8px; left: 8px; border-width: 2px 0 0 2px; }
        .hud-corner.top-right { top: 8px; right: 8px; border-width: 2px 2px 0 0; }
        .hud-corner.bottom-left { bottom: 8px; left: 8px; border-width: 0 0 2px 2px; }
        .hud-corner.bottom-right { bottom: 8px; right: 8px; border-width: 0 2px 2px 0; }

        .auth-banner-side {
            background: linear-gradient(180deg, rgba(8, 14, 28, 0.95) 0%, rgba(4, 8, 16, 0.98) 100%);
            padding: 36px 28px; display: flex; flex-direction: column;
            justify-content: space-between; align-items: center;
            border-right: 1px solid rgba(0, 255, 136, 0.15); text-align: center;
        }
        .banner-terminal-tag {
            font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 800;
            color: var(--neon-cyan); letter-spacing: 2px;
        }
        .banner-title {
            font-size: 24px; font-weight: 900; letter-spacing: 2px; color: #ffffff;
            text-shadow: 0 0 15px rgba(0, 255, 136, 0.4);
        }

        .hologram-avatar-wrap {
            position: relative; width: 130px; height: 130px;
            display: flex; align-items: center; justify-content: center; margin: 18px 0;
        }
        .hologram-outer-ring {
            position: absolute; width: 100%; height: 100%; border-radius: 50%;
            border: 2px dashed rgba(0, 255, 136, 0.4); animation: rotateClockwise 12s linear infinite;
        }
        @keyframes rotateClockwise { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        .hologram-inner-glow {
            width: 100px; height: 100px; border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 255, 136, 0.18) 0%, rgba(5, 8, 17, 0.85) 75%);
            border: 1.5px solid rgba(0, 229, 255, 0.6);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 0 25px rgba(0, 255, 136, 0.35);
        }
        .panther-emblem-img {
            width: 58px; height: 58px; object-fit: contain;
            filter: drop-shadow(0 0 12px rgba(0, 255, 136, 0.85));
        }

        .auth-form-side {
            background: rgba(9, 14, 25, 0.95);
            padding: 38px 40px; display: flex; flex-direction: column; justify-content: center;
        }

        .user-verified-chip {
            background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 8px; padding: 10px 14px; font-family: 'JetBrains Mono', monospace;
            font-size: 12px; color: #10b981; margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }

        .auth-alert {
            padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
            font-family: 'JetBrains Mono', monospace;
        }
        .auth-alert-error {
            background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
        }

        .form-group { margin-bottom: 20px; }
        .form-label {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 12px; font-weight: 700; color: #cbd5e1; margin-bottom: 8px;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        .form-label-tag { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--neon-green); }

        .input-wrap { position: relative; display: flex; align-items: center; }
        .input-icon-left { position: absolute; left: 14px; color: #64748b; font-size: 15px; pointer-events: none; }
        .form-control {
            width: 100%; background: var(--cyber-input-bg);
            border: 1.5px solid rgba(255, 255, 255, 0.12); border-radius: 8px;
            color: #f8fafc; padding: 13px 14px 13px 44px; font-size: 15px; font-weight: 700;
            font-family: 'JetBrains Mono', monospace; outline: none; letter-spacing: 4px;
        }
        .form-control:focus {
            border-color: var(--neon-green); background: rgba(7, 12, 22, 0.98);
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.2);
        }

        .btn-verify {
            background: linear-gradient(135deg, #00ff88 0%, #059669 100%);
            color: #040914; border: none; padding: 14px; border-radius: 8px;
            font-size: 13.5px; font-weight: 900; font-family: 'JetBrains Mono', monospace;
            cursor: pointer; text-transform: uppercase; text-align: center; width: 100%;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 0 25px rgba(0, 255, 136, 0.4); margin-bottom: 12px;
        }
        .btn-verify:hover { transform: translateY(-2px); box-shadow: 0 0 35px rgba(0, 255, 136, 0.7); }

        .btn-cancel {
            display: block; text-align: center; color: #94a3b8; font-size: 12px;
            font-weight: 700; text-decoration: none; font-family: 'JetBrains Mono', monospace;
            padding: 8px; border-radius: 6px; border: 1px dashed rgba(255, 255, 255, 0.1);
        }
        .btn-cancel:hover { color: #ffffff; border-color: rgba(255, 255, 255, 0.3); }

        @media (max-width: 768px) {
            .auth-container { grid-template-columns: 1fr; max-width: 440px; }
            .auth-banner-side { padding: 24px; border-right: none; border-bottom: 1px solid rgba(0, 255, 136, 0.2); }
            .auth-form-side { padding: 24px; }
        }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="cyber-ambient-overlay"></div>
    <div class="scanlines"></div>

    <!-- Top HUD Bar with 3-Language Switcher (RU Default, EN, ZH) -->
    <div class="top-hud-bar">
        <div class="hud-status-chip">
            <div class="status-dot-pulse"></div>
            <span data-i18n="login_gateway">ШЛЮЗ СЕТИ // TLS 1.3</span>
        </div>

        <div class="hud-actions-right">
            <!-- 🌐 3-Language Selector Dropdown (RU default) -->
            <div class="lang-dropdown-cyber" id="lang-dropdown-cyber">
                <button type="button" class="lang-cyber-btn" onclick="toggleAuthLangDropdown(event)">
                    <span id="current-lang-flag">🇷🇺</span> <span id="current-lang-label">Русский</span> <i class="fa-solid fa-chevron-down" style="font-size: 10px;"></i>
                </button>
                <div class="lang-cyber-menu">
                    <div class="lang-cyber-item" onclick="switchAuthLang('ru')">
                        <span>🇷🇺</span> Русский
                    </div>
                    <div class="lang-cyber-item" onclick="switchAuthLang('en')">
                        <span>🇺🇸</span> English
                    </div>
                    <div class="lang-cyber-item" onclick="switchAuthLang('zh')">
                        <span>🇨🇳</span> 中文
                    </div>
                </div>
            </div>

            <button type="button" class="bg-mode-toggle" id="toggleBgBtn" onclick="toggleMatrixBackground()">
                <i class="fa-solid fa-terminal"></i>
                <span id="bgModeText" data-i18n="login_fx_active">FX: МАТРИЦА ВКЛ</span>
            </button>
        </div>
    </div>

    <div class="auth-container">
        <div class="hud-corner top-left"></div>
        <div class="hud-corner top-right"></div>
        <div class="hud-corner bottom-left"></div>
        <div class="hud-corner bottom-right"></div>

        <div class="auth-banner-side">
            <div>
                <span class="banner-terminal-tag" data-i18n="sec_banner_tag"><i class="fa-solid fa-shield-halved"></i> БЕЗОПАСНЫЙ АНКЛАВ</span>
                <h1 class="banner-title" data-i18n="sec_banner_title">2FA PIN</h1>
            </div>

            <div class="hologram-avatar-wrap">
                <div class="hologram-outer-ring"></div>
                <div class="hologram-inner-glow">
                    <img src="{{ asset('images/logo.png') }}" alt="Payate CC Logo" class="panther-emblem-img">
                </div>
            </div>

            <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #64748b;">
                SECURITY ID: <span style="color: var(--neon-green);">#2FA-PROD</span>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="user-verified-chip">
                <i class="fa-solid fa-circle-check" style="font-size: 14px;"></i>
                <span><span data-i18n="sec_verified_user">ПОДТВЕРЖДЕННЫЙ КЛИЕНТ:</span> <strong>{{ $username ?? session('pending_login_username') }}</strong></span>
            </div>

            @if(session('error'))
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <form action="{{ route('login.secondary.post') }}" method="POST">
                @csrf

                <div class="form-group" style="margin-bottom: 20px;">
                    <label class="form-label" for="secondary_password">
                        <span data-i18n="sec_pin_label">PIN безопасности или Код восстановления</span>
                        <span class="form-label-tag">>_ 2FA / CODE</span>
                    </label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-fingerprint input-icon-left"></i>
                        <input type="text" name="secondary_password" id="secondary_password" class="form-control" placeholder="PIN или SEC-XXXX-X" required autofocus autocomplete="off" style="letter-spacing: 2px;">
                    </div>
                    <span style="font-size: 11px; color: var(--text-muted); margin-top: 6px; display: block; font-family: 'JetBrains Mono', monospace;">
                        * Введите ваш 4-значный защитный PIN или один из 5 кодов безопасности (SEC-XXXX-X).
                    </span>
                </div>

                <button type="submit" class="btn-verify">
                    <i class="fa-solid fa-unlock-keyhole"></i> <span data-i18n="sec_btn">ПОДТВЕРДИТЬ И ВОЙТИ</span>
                </button>

                <a href="{{ route('login') }}" class="btn-cancel" data-i18n="sec_back">
                    &larr; Назад к форме входа
                </a>
            </form>
        </div>
    </div>

    <script src="{{ asset('js/i18n.js') }}"></script>
    <script>
        function toggleAuthLangDropdown(e) {
            e.stopPropagation();
            document.getElementById('lang-dropdown-cyber').classList.toggle('active');
        }
        function switchAuthLang(lang) {
            setLanguage(lang);
            document.getElementById('lang-dropdown-cyber').classList.remove('active');
        }
        document.addEventListener('click', () => {
            const dropdown = document.getElementById('lang-dropdown-cyber');
            if (dropdown) dropdown.classList.remove('active');
        });

        const canvas = document.getElementById('matrix-canvas');
        const ctx = canvas.getContext('2d');
        function resizeCanvas() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);
        const characters = '0123456789ABCDEF01010101XYZ';
        const fontSize = 14;
        let columns = Math.floor(window.innerWidth / fontSize);
        let drops = [];
        for (let i = 0; i < columns; i++) drops[i] = Math.floor(Math.random() * -100);
        let isSolidBg = false;

        function drawMatrix() {
            if (isSolidBg) { requestAnimationFrame(drawMatrix); return; }
            ctx.fillStyle = 'rgba(5, 8, 17, 0.08)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < drops.length; i++) {
                const char = characters.charAt(Math.floor(Math.random() * characters.length));
                ctx.fillStyle = Math.random() > 0.88 ? '#ffffff' : '#00ff88';
                ctx.font = fontSize + 'px "JetBrains Mono", monospace';
                ctx.fillText(char, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i]++;
            }
            setTimeout(() => requestAnimationFrame(drawMatrix), 33);
        }
        requestAnimationFrame(drawMatrix);

        function toggleMatrixBackground() {
            isSolidBg = !isSolidBg;
            document.body.classList.toggle('solid-bg', isSolidBg);
            document.getElementById('bgModeText').textContent = isSolidBg ? 'FX: SOLID DARK' : 'FX: MATRIX ACTIVE';
        }
    </script>
</body>
</html>
