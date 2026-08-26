<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payate CC // Client Registration</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&family=Share+Tech+Mono&display=swap');

        :root {
            --neon-green: #00ff88;
            --neon-green-glow: rgba(0, 255, 136, 0.4);
            --neon-cyan: #00e5ff;
            --neon-cyan-glow: rgba(0, 229, 255, 0.4);
            --cyber-dark-bg: #050811;
            --cyber-card-bg: rgba(9, 14, 26, 0.94);
            --cyber-input-bg: rgba(5, 9, 17, 0.9);
            --cyber-border: rgba(0, 255, 136, 0.25);
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
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0; pointer-events: none; opacity: 0.65;
            transition: opacity 0.5s ease;
        }
        body.solid-bg #matrix-canvas { opacity: 0; }

        .cyber-ambient-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(circle at 15% 15%, rgba(0, 255, 136, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(0, 229, 255, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(5, 8, 17, 0.65) 0%, rgba(5, 8, 17, 0.98) 100%);
            z-index: 1; pointer-events: none;
        }

        .scanlines {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%);
            background-size: 100% 4px; z-index: 1; pointer-events: none; opacity: 0.35;
        }

        .top-hud-bar {
            position: fixed; top: 16px; left: 24px; right: 24px;
            display: flex; justify-content: space-between; align-items: center; z-index: 10;
        }

        .hud-status-chip {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(10, 15, 26, 0.85);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 999px; padding: 6px 16px;
            font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 700;
            color: var(--neon-green); backdrop-filter: blur(10px);
            letter-spacing: 1px; text-transform: uppercase;
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.15);
        }

        .status-dot-pulse {
            width: 8px; height: 8px; background-color: var(--neon-green);
            border-radius: 50%; box-shadow: 0 0 10px var(--neon-green);
            animation: pulseGlow 1.8s infinite ease-in-out;
        }
        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.4; }
        }

        .hud-actions-right {
            display: flex; align-items: center; gap: 10px;
        }

        .lang-dropdown-cyber {
            position: relative; display: inline-block;
        }
        .lang-cyber-btn {
            background: rgba(10, 15, 26, 0.85);
            border: 1px solid rgba(0, 229, 255, 0.4);
            color: var(--text-main); padding: 6px 12px; border-radius: 6px;
            font-size: 11.5px; font-family: 'JetBrains Mono', monospace; font-weight: 700;
            cursor: pointer; backdrop-filter: blur(10px);
            display: inline-flex; align-items: center; gap: 6px;
            transition: all 0.2s ease;
        }
        .lang-cyber-btn:hover {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 12px var(--neon-cyan-glow);
        }
        .lang-cyber-menu {
            display: none; position: absolute; top: calc(100% + 6px); right: 0;
            background: rgba(8, 13, 24, 0.96);
            border: 1px solid rgba(0, 229, 255, 0.4); border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            min-width: 140px; z-index: 100; overflow: hidden;
        }
        .lang-dropdown-cyber.active .lang-cyber-menu { display: block; }
        .lang-cyber-item {
            padding: 9px 14px; font-size: 12px; font-weight: 700; color: #e2e8f0;
            display: flex; align-items: center; gap: 8px; cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .lang-cyber-item:hover {
            background: rgba(0, 255, 136, 0.12); color: var(--neon-green);
        }

        .bg-mode-toggle {
            background: rgba(10, 15, 26, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--text-muted); padding: 6px 12px; border-radius: 6px;
            font-size: 11px; font-family: 'JetBrains Mono', monospace; font-weight: 600;
            cursor: pointer; backdrop-filter: blur(10px); transition: all 0.2s ease;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .bg-mode-toggle:hover {
            color: var(--neon-green); border-color: var(--neon-green);
            box-shadow: 0 0 12px var(--neon-green-glow);
        }

        /* Container */
        .auth-container {
            position: relative; z-index: 5; width: 100%; max-width: 960px;
            background: var(--cyber-card-bg);
            border: 1.5px solid var(--cyber-border);
            border-radius: 18px;
            box-shadow: 0 0 50px rgba(0, 255, 136, 0.15), 0 30px 60px -12px rgba(0, 0, 0, 0.9);
            backdrop-filter: blur(20px); overflow: hidden;
            display: grid; grid-template-columns: 340px 1fr;
            transition: border-color 0.4s ease, box-shadow 0.4s ease;
        }
        .auth-container:hover {
            border-color: rgba(0, 255, 136, 0.5);
            box-shadow: 0 0 60px rgba(0, 255, 136, 0.25), 0 35px 70px -12px rgba(0, 0, 0, 0.95);
        }

        .hud-corner {
            position: absolute; width: 14px; height: 14px;
            border-color: var(--neon-green); border-style: solid; pointer-events: none; z-index: 20;
        }
        .hud-corner.top-left { top: 8px; left: 8px; border-width: 2.5px 0 0 2.5px; }
        .hud-corner.top-right { top: 8px; right: 8px; border-width: 2.5px 2.5px 0 0; }
        .hud-corner.bottom-left { bottom: 8px; left: 8px; border-width: 0 0 2.5px 2.5px; }
        .hud-corner.bottom-right { bottom: 8px; right: 8px; border-width: 0 2.5px 2.5px 0; }

        .auth-banner-side {
            background: linear-gradient(180deg, rgba(8, 14, 28, 0.98) 0%, rgba(4, 8, 16, 0.99) 100%);
            padding: 40px 28px; display: flex; flex-direction: column;
            justify-content: space-between; align-items: center;
            border-right: 1px solid rgba(0, 255, 136, 0.18); text-align: center;
        }
        .banner-terminal-tag {
            font-family: 'JetBrains Mono', monospace; font-size: 11px; font-weight: 800;
            color: var(--neon-cyan); letter-spacing: 2px; text-transform: uppercase;
        }
        .banner-title {
            font-size: 26px; font-weight: 900; letter-spacing: 2px; color: #ffffff;
            text-shadow: 0 0 15px rgba(0, 255, 136, 0.5);
        }

        .hologram-avatar-wrap {
            position: relative; width: 130px; height: 130px;
            display: flex; align-items: center; justify-content: center; margin: 18px 0;
        }
        .hologram-outer-ring {
            position: absolute; width: 100%; height: 100%; border-radius: 50%;
            border: 2px dashed rgba(0, 255, 136, 0.45);
            animation: rotateClockwise 12s linear infinite;
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
            width: 60px; height: 60px; object-fit: contain;
            filter: drop-shadow(0 0 12px rgba(0, 255, 136, 0.85));
        }

        .auth-form-side {
            background: rgba(9, 14, 25, 0.96);
            padding: 38px 40px; display: flex; flex-direction: column; justify-content: center;
        }
        .form-header-bar {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 20px; padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }
        .form-header-title {
            font-size: 18px; font-weight: 800; color: #ffffff; letter-spacing: 1px;
            display: flex; align-items: center; gap: 10px;
        }
        .form-header-title i {
            color: var(--neon-green); text-shadow: 0 0 10px var(--neon-green-glow);
        }

        .auth-alert {
            padding: 10px 14px; border-radius: 8px; font-size: 12px; font-weight: 600;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
            font-family: 'JetBrains Mono', monospace;
        }
        .auth-alert-error {
            background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5; box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }

        .form-grid-2 {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }
        .form-group { margin-bottom: 14px; }
        .form-label {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 11.5px; font-weight: 700; color: #cbd5e1; margin-bottom: 6px;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        .form-label-tag {
            font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--neon-green);
        }
        .input-wrap {
            position: relative; display: flex; align-items: center;
        }
        .input-icon-left {
            position: absolute; left: 12px; color: #64748b; font-size: 13px; pointer-events: none;
        }
        .form-control {
            width: 100%; background: var(--cyber-input-bg);
            border: 1.5px solid rgba(255, 255, 255, 0.12); border-radius: 8px;
            color: #f8fafc; padding: 11px 12px 11px 36px;
            font-size: 13px; font-weight: 600; font-family: 'JetBrains Mono', monospace;
            outline: none; transition: all 0.25s ease;
        }
        .form-control:focus {
            border-color: var(--neon-green); background: rgba(7, 12, 22, 0.98);
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.2);
        }
        .toggle-password-btn {
            position: absolute; right: 10px; background: transparent; border: none;
            color: #64748b; cursor: pointer; font-size: 13px; padding: 4px;
        }
        .toggle-password-btn:hover { color: var(--neon-green); }

        .secondary-pass-box {
            background: rgba(0, 255, 136, 0.05); border: 1.5px dashed rgba(0, 255, 136, 0.4);
            border-radius: 8px; padding: 12px 14px; margin-bottom: 16px;
        }

        .captcha-row {
            display: grid; grid-template-columns: 110px 1fr; gap: 12px;
            margin-bottom: 18px; align-items: center;
        }
        .captcha-badge {
            background: rgba(0, 255, 136, 0.12); border: 1.5px dashed var(--neon-green);
            border-radius: 8px; padding: 10px 0; text-align: center;
            font-family: 'Share Tech Mono', monospace; font-size: 16px; font-weight: 800;
            color: var(--neon-green);
        }

        .btn-auth-grid {
            display: grid; grid-template-columns: 1fr 1fr; gap: 14px;
        }
        .btn-login {
            background: linear-gradient(135deg, #00ff88 0%, #059669 100%);
            color: #040914; border: none; padding: 12px 18px; border-radius: 8px;
            font-size: 13px; font-weight: 900; font-family: 'JetBrains Mono', monospace;
            cursor: pointer; text-transform: uppercase; text-align: center;
            box-shadow: 0 0 25px rgba(0, 255, 136, 0.4);
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 0 35px rgba(0, 255, 136, 0.7); }

        .btn-register {
            background: rgba(15, 23, 42, 0.85); color: #e2e8f0;
            border: 1.5px solid rgba(0, 229, 255, 0.4); padding: 12px 18px;
            border-radius: 8px; font-size: 13px; font-weight: 800;
            font-family: 'JetBrains Mono', monospace; cursor: pointer;
            text-transform: uppercase; text-align: center; text-decoration: none;
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        }
        .btn-register:hover { background: rgba(0, 229, 255, 0.15); border-color: var(--neon-cyan); }

        /* Credentials Generated Modal */
        .credentials-modal-backdrop {
            display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(2, 6, 15, 0.85); backdrop-filter: blur(10px);
            z-index: 10000; align-items: center; justify-content: center; padding: 20px;
        }
        .credentials-modal-backdrop.active { display: flex; }
        .credentials-card {
            background: rgba(9, 14, 26, 0.98); border: 2px solid var(--neon-green);
            border-radius: 16px; max-width: 520px; width: 100%; padding: 28px;
            box-shadow: 0 0 50px rgba(0, 255, 136, 0.35); position: relative;
        }
        .credentials-data-box {
            background: rgba(5, 9, 17, 0.95); border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 8px; padding: 16px; font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px; margin: 16px 0;
        }
        .cred-item {
            display: flex; justify-content: space-between; padding: 6px 0;
            border-bottom: 1px dashed rgba(255, 255, 255, 0.08);
        }
        .cred-item:last-child { border-bottom: none; }
        .cred-label { color: #94a3b8; }
        .cred-val { color: var(--neon-green); font-weight: 800; }
        .copy-toast {
            display: none; background: rgba(0, 255, 136, 0.2); border: 1px solid var(--neon-green);
            color: var(--neon-green); font-family: 'JetBrains Mono', monospace; font-size: 11px;
            font-weight: 800; padding: 8px; border-radius: 6px; text-align: center; margin-bottom: 14px;
        }
        .copy-toast.show { display: block; }
        .modal-btn-grid { display: grid; grid-template-columns: 1fr 1.2fr; gap: 12px; }
        .btn-copy-all {
            background: rgba(0, 229, 255, 0.15); border: 1.5px solid var(--neon-cyan);
            color: var(--neon-cyan); font-weight: 800; font-family: 'JetBrains Mono', monospace;
            padding: 12px; border-radius: 8px; cursor: pointer;
        }
        .btn-proceed-login {
            background: linear-gradient(135deg, #00ff88 0%, #059669 100%);
            color: #040914; font-weight: 900; font-family: 'JetBrains Mono', monospace;
            border: none; padding: 12px; border-radius: 8px; cursor: pointer;
        }

        @media (max-width: 820px) {
            .auth-container { grid-template-columns: 1fr; max-width: 480px; }
            .auth-banner-side { padding: 24px; border-right: none; border-bottom: 1px solid rgba(0, 255, 136, 0.2); }
            .form-grid-2 { grid-template-columns: 1fr; }
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

    <!-- Main Register Container -->
    <div class="auth-container">
        <div class="hud-corner top-left"></div>
        <div class="hud-corner top-right"></div>
        <div class="hud-corner bottom-left"></div>
        <div class="hud-corner bottom-right"></div>

        <div class="auth-banner-side">
            <div>
                <span class="banner-terminal-tag" data-i18n="reg_banner_tag"><i class="fa-solid fa-user-plus"></i> РЕГИСТРАЦИЯ КЛИЕНТА</span>
                <h1 class="banner-title" data-i18n="login_banner_title">PAYATE CC</h1>
            </div>

            <div class="hologram-avatar-wrap">
                <div class="hologram-outer-ring"></div>
                <div class="hologram-inner-glow">
                    <img src="{{ asset('images/logo.png') }}" alt="Payate CC Logo" class="panther-emblem-img">
                </div>
            </div>

            <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #64748b;">
                ACCESS TIER: <span style="color: var(--neon-cyan);">MEMBER // VERIFIED</span>
            </div>
        </div>

        <div class="auth-form-side">
            <div class="form-header-bar">
                <div class="form-header-title">
                    <i class="fa-solid fa-shield-halved"></i>
                    <span data-i18n="reg_title">СОЗДАНИЕ АККАУНТА</span>
                </div>
            </div>

            @if(session('error'))
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <!-- 🎲 Quick Suggest / Auto-Fill Credentials Bar -->
            <div style="background: rgba(0, 229, 255, 0.06); border: 1px dashed rgba(0, 229, 255, 0.35); border-radius: 8px; padding: 10px 14px; margin-bottom: 18px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div style="font-size: 11.5px; color: var(--neon-cyan); font-family: 'JetBrains Mono', monospace; font-weight: 700; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-dice"></i> <span>Сгенерировать случайные данные (Suggest Login & Password)</span>
                </div>
                <button type="button" onclick="generateSuggestedCredentials()" style="background: linear-gradient(135deg, rgba(0, 229, 255, 0.2) 0%, rgba(0, 255, 136, 0.2) 100%); border: 1px solid var(--neon-cyan); color: #FFFFFF; font-size: 11px; font-weight: 800; font-family: 'JetBrains Mono', monospace; padding: 5px 12px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; transition: all 0.2s ease;">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Сгенерировать
                </button>
            </div>

            <form id="registerForm" action="{{ route('register.post') }}" method="POST" onsubmit="handleRegistrationSubmit(event)">
                @csrf
                <input type="hidden" name="ref" value="{{ request('ref', session('referral_invite')) }}">

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="username">
                            <span data-i18n="reg_username_label">Имя пользователя</span>
                            <span class="form-label-tag">>_ ID</span>
                        </label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-user-astronaut input-icon-left"></i>
                            <input type="text" name="username" id="reg_username" class="form-control" placeholder="Придумайте логин" data-i18n-ph="reg_username_ph" required autocomplete="off">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">
                            <span data-i18n="reg_email_label">Email адрес</span>
                            <span class="form-label-tag">@ MAIL</span>
                        </label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-envelope input-icon-left"></i>
                            <input type="email" name="email" id="reg_email" class="form-control" placeholder="Ваш действующий email" data-i18n-ph="reg_email_ph" required autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="password">
                            <span data-i18n="reg_pass_label">Основной пароль</span>
                            <span class="form-label-tag">***</span>
                        </label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-lock input-icon-left"></i>
                            <input type="password" name="password" id="reg_password" class="form-control" placeholder="Мин. 4-6 символов" data-i18n-ph="reg_pass_ph" required autocomplete="new-password">
                            <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('reg_password')">
                                <i class="fa-regular fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">
                            <span data-i18n="reg_pass_confirm_label">Повторите пароль</span>
                            <span class="form-label-tag">***</span>
                        </label>
                        <div class="input-wrap">
                            <i class="fa-solid fa-shield-check input-icon-left"></i>
                            <input type="password" name="password_confirmation" id="reg_password_confirmation" class="form-control" placeholder="Подтвердите пароль" data-i18n-ph="reg_pass_confirm_ph" required autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="secondary-pass-box">
                    <label class="form-label" for="secondary_password" style="color: var(--neon-green);">
                        <span><i class="fa-solid fa-key"></i> <span data-i18n="reg_sec_pass_label">Второй PIN безопасности (2FA)</span></span>
                        <span class="form-label-tag">>_ PIN</span>
                    </label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-fingerprint input-icon-left"></i>
                        <input type="password" name="secondary_password" id="reg_secondary_password" class="form-control" placeholder="4-значный защитный PIN" data-i18n-ph="reg_sec_pass_ph" required autocomplete="off" style="border-color: rgba(0, 255, 136, 0.4);">
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('reg_secondary_password')">
                            <i class="fa-regular fa-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- 🛡️ 5 Auto-Generated Personal Security Emergency Codes -->
                <div style="background: rgba(5, 9, 17, 0.85); border: 1.5px solid rgba(0, 255, 136, 0.3); border-radius: 10px; padding: 14px; margin-bottom: 18px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; flex-wrap: wrap; gap: 6px;">
                        <span style="font-size: 11.5px; font-weight: 800; color: var(--neon-green); font-family: 'JetBrains Mono', monospace; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-shield-halved"></i> 5 Персональных кодов безопасности (2FA Backup Codes)
                        </span>
                        <div style="display: flex; gap: 6px;">
                            <button type="button" onclick="regenerateFiveSecurityCodes()" style="background: rgba(0, 229, 255, 0.15); border: 1px solid rgba(0, 229, 255, 0.4); color: var(--neon-cyan); font-size: 10.5px; font-weight: 800; padding: 2px 8px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-arrows-rotate"></i> Новые 5 кодов
                            </button>
                            <button type="button" onclick="copySecurityCodesList()" style="background: rgba(0, 255, 136, 0.15); border: 1px solid rgba(0, 255, 136, 0.4); color: var(--neon-green); font-size: 10.5px; font-weight: 800; padding: 2px 8px; border-radius: 4px; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-regular fa-copy"></i> Копировать
                            </button>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(80px, 1fr)); gap: 6px; margin-bottom: 8px;" id="sec-codes-grid">
                        @foreach($defaultSecurityCodes as $secCode)
                            <div style="background: rgba(0, 255, 136, 0.08); border: 1px solid rgba(0, 255, 136, 0.25); border-radius: 6px; padding: 6px 4px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; font-weight: 800; color: #FFFFFF;">
                                {{ $secCode }}
                                <input type="hidden" name="security_codes[]" value="{{ $secCode }}" class="sec-code-input">
                            </div>
                        @endforeach
                    </div>

                    <span style="font-size: 10.5px; color: var(--text-muted); display: block; line-height: 1.4;">
                        * Для каждой новой регистрации создаются 5 уникальных кодов. Вы можете использовать любой из них для входа в аккаунт вместо 2FA PIN.
                    </span>
                </div>

                <div class="captcha-row">
                    <div class="captcha-badge">{{ $captcha }}</div>
                    <div class="input-wrap">
                        <i class="fa-solid fa-hashtag input-icon-left"></i>
                        <input type="number" name="captcha" id="reg_captcha" class="form-control" placeholder="Решите пример" data-i18n-ph="login_captcha_ph" required autocomplete="off">
                    </div>
                </div>

                <div class="btn-auth-grid">
                    <button type="submit" class="btn-login" id="submitEnrollBtn">
                        <i class="fa-solid fa-user-check"></i> <span data-i18n="reg_submit">ЗАРЕГИСТРИРОВАТЬСЯ</span>
                    </button>
                    <a href="{{ route('login') }}" class="btn-register">
                        <i class="fa-solid fa-arrow-left"></i> <span data-i18n="login_btn">ВХОД</span>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Credentials Generated / Copy Modal -->
    <div class="credentials-modal-backdrop" id="credentialsModal">
        <div class="credentials-card">
            <div class="hud-corner top-left"></div>
            <div class="hud-corner top-right"></div>
            <div class="hud-corner bottom-left"></div>
            <div class="hud-corner bottom-right"></div>

            <div style="text-align: center; margin-bottom: 18px;">
                <h3 style="font-size: 18px; font-weight: 900; color: #ffffff; letter-spacing: 1px;">CREDENTIALS GENERATED</h3>
                <p style="font-size: 12px; color: var(--neon-cyan); margin-top: 4px;">Copy and save your access keys securely before proceeding</p>
            </div>

            <div class="credentials-data-box">
                <div class="cred-item">
                    <span class="cred-label">USERNAME:</span>
                    <span class="cred-val" id="modal_username">-</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">EMAIL:</span>
                    <span class="cred-val" id="modal_email">-</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">PRIMARY PASS:</span>
                    <span class="cred-val" id="modal_password">-</span>
                </div>
                <div class="cred-item">
                    <span class="cred-label">SECONDARY PIN:</span>
                    <span class="cred-val" id="modal_secondary">-</span>
                </div>
            </div>

            <div class="copy-toast" id="copyToast">
                <i class="fa-solid fa-check-double"></i> ALL CREDENTIALS COPIED TO CLIPBOARD!
            </div>

            <div class="modal-btn-grid">
                <button type="button" class="btn-copy-all" onclick="copyAllCredentials()">
                    <i class="fa-regular fa-clone"></i> COPY ALL
                </button>
                <button type="button" class="btn-proceed-login" onclick="proceedWithRegistration()">
                    <span>LOGIN NOW</span> <i class="fa-solid fa-arrow-right"></i>
                </button>
            </div>
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

        function generateSuggestedCredentials() {
            const prefixes = ['Shadow', 'Cyber', 'Alpha', 'Vortex', 'Matrix', 'Nexus', 'Ghost', 'Titan', 'Specter', 'Crypto', 'Phantom', 'Zero', 'Omega'];
            const suffixes = ['Ops', 'Node', 'Runner', 'Net', 'Agent', 'Prime', 'Vault', 'Core', 'Link', 'Hex'];
            const randNum = Math.floor(100 + Math.random() * 900);
            const prefix = prefixes[Math.floor(Math.random() * prefixes.length)];
            const suffix = suffixes[Math.floor(Math.random() * suffixes.length)];
            const suggestedUser = `${prefix}_${suffix}${randNum}`;

            const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789#$!@';
            let suggestedPass = '';
            for (let i = 0; i < 9; i++) {
                suggestedPass += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            const suggestedPin = Math.floor(1000 + Math.random() * 9000).toString();

            document.getElementById('reg_username').value = suggestedUser;
            if (!document.getElementById('reg_email').value) {
                document.getElementById('reg_email').value = `${suggestedUser.toLowerCase()}@client.net`;
            }
            document.getElementById('reg_password').value = suggestedPass;
            document.getElementById('reg_password_confirmation').value = suggestedPass;
            document.getElementById('reg_secondary_password').value = suggestedPin;

            // Also regenerate 5 dynamic security codes
            regenerateFiveSecurityCodes();

            // Highlight inputs briefly
            ['reg_username', 'reg_password', 'reg_password_confirmation', 'reg_secondary_password'].forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.style.borderColor = 'var(--neon-green)';
                    el.style.boxShadow = '0 0 15px rgba(0,255,136,0.5)';
                    setTimeout(() => {
                        el.style.borderColor = '';
                        el.style.boxShadow = '';
                    }, 1200);
                }
            });
        }

        function regenerateFiveSecurityCodes() {
            const grid = document.getElementById('sec-codes-grid');
            if (!grid) return;
            const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            let newHtml = '';
            for (let i = 0; i < 5; i++) {
                const randNum = Math.floor(1000 + Math.random() * 9000);
                const randChar = chars.charAt(Math.floor(Math.random() * chars.length));
                const code = `SEC-${randNum}-${randChar}`;
                newHtml += `<div style="background: rgba(0, 255, 136, 0.12); border: 1px solid rgba(0, 255, 136, 0.4); border-radius: 6px; padding: 6px 4px; text-align: center; font-family: 'JetBrains Mono', monospace; font-size: 11.5px; font-weight: 800; color: #FFFFFF;">
                    ${code}
                    <input type="hidden" name="security_codes[]" value="${code}" class="sec-code-input">
                </div>`;
            }
            grid.innerHTML = newHtml;
        }

        function copySecurityCodesList() {
            const inputs = document.querySelectorAll('.sec-code-input');
            const codes = Array.from(inputs).map(inp => inp.value);
            if (codes.length === 0) return;
            const text = codes.join(' | ');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('5 кодов безопасности скопированы в буфер обмена:\n' + codes.join('\n'));
                }).catch(() => {});
            } else {
                prompt('Скопируйте 5 кодов безопасности:', text);
            }
        }

        let isReadyToSubmit = false;

        function handleRegistrationSubmit(e) {
            if (isReadyToSubmit) return true;
            e.preventDefault();

            const username = document.getElementById('reg_username').value.trim();
            const email = document.getElementById('reg_email').value.trim();
            const pass = document.getElementById('reg_password').value;
            const passConfirm = document.getElementById('reg_password_confirmation').value;
            const secPass = document.getElementById('reg_secondary_password').value;
            const captcha = document.getElementById('reg_captcha').value;

            if (!username || !email || !pass || !secPass || !captcha) {
                alert('Please fill in all required fields and solve the captcha.');
                return;
            }

            if (pass !== passConfirm) {
                alert('Primary passwords do not match!');
                return;
            }

            document.getElementById('modal_username').textContent = username;
            document.getElementById('modal_email').textContent = email;
            document.getElementById('modal_password').textContent = pass;
            document.getElementById('modal_secondary').textContent = secPass;

            copyFormattedCredentials(username, email, pass, secPass);
            document.getElementById('credentialsModal').classList.add('active');
        }

        function copyFormattedCredentials(username, email, pass, secPass) {
            const inputs = document.querySelectorAll('.sec-code-input');
            const secCodes = Array.from(inputs).map(inp => inp.value).join(', ');
            const text = `========================================\n[+] PAYATE CC - REGISTRATION CREDENTIALS\n========================================\nUsername          : ${username}\nEmail             : ${email}\nPrimary Password  : ${pass}\nSecondary 2FA PIN : ${secPass}\n5 Security Codes  : ${secCodes}\nCreated At        : ${new Date().toISOString()}\n========================================`;
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    const toast = document.getElementById('copyToast');
                    toast.classList.add('show');
                    setTimeout(() => toast.classList.remove('show'), 3500);
                }).catch(() => {});
            }
        }

        function copyAllCredentials() {
            const u = document.getElementById('modal_username').textContent;
            const e = document.getElementById('modal_email').textContent;
            const p = document.getElementById('modal_password').textContent;
            const s = document.getElementById('modal_secondary').textContent;
            copyFormattedCredentials(u, e, p, s);
        }

        function proceedWithRegistration() {
            isReadyToSubmit = true;
            document.getElementById('registerForm').submit();
        }

        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            if (!input) return;
            const eye = input.parentElement.querySelector('i:last-child');
            if (input.type === 'password') {
                input.type = 'text';
                if (eye) eye.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                if (eye) eye.className = 'fa-regular fa-eye';
            }
        }

        // Matrix background
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
