<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payate CC // Terminal Access Portal</title>
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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
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

        /* Matrix Canvas Background */
        #matrix-canvas {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            z-index: 0;
            pointer-events: none;
            opacity: 0.65;
            transition: opacity 0.5s ease;
        }

        body.solid-bg #matrix-canvas {
            opacity: 0;
        }

        /* Dark Cyber Ambient Overlay */
        .cyber-ambient-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(circle at 15% 15%, rgba(0, 255, 136, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 85% 85%, rgba(0, 229, 255, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 50% 50%, rgba(5, 8, 17, 0.65) 0%, rgba(5, 8, 17, 0.98) 100%);
            z-index: 1;
            pointer-events: none;
        }

        .scanlines {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.25) 50%);
            background-size: 100% 4px;
            z-index: 1;
            pointer-events: none;
            opacity: 0.35;
        }

        /* ==================================================== */
        /* 🚀 FUTURISTIC CYBER SPLASH LOADING SCREEN            */
        /* ==================================================== */
        #cyber-splash-loader {
            position: fixed;
            top: 0; left: 0; width: 100vw; height: 100vh;
            background: #050811;
            z-index: 99999;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            transition: opacity 0.65s cubic-bezier(0.4, 0, 0.2, 1), transform 0.65s cubic-bezier(0.4, 0, 0.2, 1), visibility 0.65s;
        }

        #cyber-splash-loader.loaded {
            opacity: 0;
            visibility: hidden;
            transform: scale(1.06);
            pointer-events: none;
        }

        .loader-center-box {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            max-width: 520px;
            width: 90%;
            padding: 30px;
        }

        .loader-hologram {
            position: relative;
            width: 120px;
            height: 120px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .loader-outer-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px dashed rgba(0, 255, 136, 0.6);
            animation: rotateClockwise 6s linear infinite;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.35);
        }

        .loader-inner-ring {
            position: absolute;
            width: 82%;
            height: 82%;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top: 2.5px solid #00e5ff;
            border-bottom: 2.5px solid #00e5ff;
            animation: rotateAntiClockwise 3s linear infinite;
        }

        .loader-logo-icon {
            width: 55px;
            height: 55px;
            object-fit: contain;
            filter: drop-shadow(0 0 14px #00ff88);
            animation: pulseIcon 1.6s infinite ease-in-out;
        }

        @keyframes rotateClockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes rotateAntiClockwise {
            from { transform: rotate(0deg); }
            to { transform: rotate(-360deg); }
        }
        @keyframes pulseIcon {
            0%, 100% { transform: scale(1); opacity: 0.9; }
            50% { transform: scale(1.1); opacity: 1; filter: drop-shadow(0 0 24px #00e5ff); }
        }

        .loader-title {
            font-size: 16px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 2px;
            font-family: 'JetBrains Mono', monospace;
            margin-bottom: 10px;
            text-transform: uppercase;
        }

        .loader-terminal-stream {
            background: rgba(10, 15, 26, 0.9);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 8px;
            padding: 12px 18px;
            width: 100%;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: var(--neon-green);
            text-align: left;
            margin-bottom: 20px;
            min-height: 48px;
            display: flex;
            align-items: center;
            box-shadow: inset 0 0 20px rgba(0, 0, 0, 0.8);
        }

        .loader-progress-track {
            width: 100%;
            height: 7px;
            background: rgba(255, 255, 255, 0.06);
            border-radius: 999px;
            overflow: hidden;
            position: relative;
            margin-bottom: 12px;
            border: 1px solid rgba(0, 255, 136, 0.25);
        }

        .loader-progress-bar {
            height: 100%;
            width: 0%;
            background: linear-gradient(90deg, #00e5ff 0%, #00ff88 100%);
            box-shadow: 0 0 16px #00ff88;
            transition: width 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .loader-meta-row {
            width: 100%;
            display: flex;
            justify-content: space-between;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            color: #94a3b8;
        }

        /* Top HUD Bar */
        .top-hud-bar {
            position: fixed;
            top: 16px; left: 24px; right: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .hud-status-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(10, 15, 26, 0.85);
            border: 1px solid rgba(0, 255, 136, 0.3);
            border-radius: 999px;
            padding: 6px 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 700;
            color: var(--neon-green);
            backdrop-filter: blur(10px);
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.15);
        }

        .status-dot-pulse {
            width: 8px; height: 8px;
            background-color: var(--neon-green);
            border-radius: 50%;
            box-shadow: 0 0 10px var(--neon-green);
            animation: pulseGlow 1.8s infinite ease-in-out;
        }

        @keyframes pulseGlow {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.4; }
        }

        .hud-actions-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Language Switcher Dropdown in Cyber Style */
        .lang-dropdown-cyber {
            position: relative;
            display: inline-block;
        }
        .lang-cyber-btn {
            background: rgba(10, 15, 26, 0.85);
            border: 1px solid rgba(0, 229, 255, 0.4);
            color: var(--text-main);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11.5px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 700;
            cursor: pointer;
            backdrop-filter: blur(10px);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .lang-cyber-btn:hover {
            border-color: var(--neon-cyan);
            box-shadow: 0 0 12px var(--neon-cyan-glow);
        }
        .lang-cyber-menu {
            display: none;
            position: absolute;
            top: calc(100% + 6px);
            right: 0;
            background: rgba(8, 13, 24, 0.96);
            border: 1px solid rgba(0, 229, 255, 0.4);
            border-radius: 8px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.8);
            min-width: 140px;
            z-index: 100;
            overflow: hidden;
        }
        .lang-dropdown-cyber.active .lang-cyber-menu {
            display: block;
        }
        .lang-cyber-item {
            padding: 9px 14px;
            font-size: 12px;
            font-weight: 700;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.15s, color 0.15s;
        }
        .lang-cyber-item:hover {
            background: rgba(0, 255, 136, 0.12);
            color: var(--neon-green);
        }

        .bg-mode-toggle {
            background: rgba(10, 15, 26, 0.85);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--text-muted);
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(10px);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .bg-mode-toggle:hover {
            color: var(--neon-green);
            border-color: var(--neon-green);
            box-shadow: 0 0 12px var(--neon-green-glow);
        }

        /* Main Container */
        .auth-container {
            position: relative;
            z-index: 5;
            width: 100%;
            max-width: 900px;
            background: var(--cyber-card-bg);
            border: 1.5px solid var(--cyber-border);
            border-radius: 18px;
            box-shadow: 
                0 0 50px rgba(0, 255, 136, 0.15),
                0 30px 60px -12px rgba(0, 0, 0, 0.9),
                inset 0 1px 0 rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            overflow: hidden;
            display: grid;
            grid-template-columns: 370px 1fr;
            opacity: 0;
            transform: translateY(20px) scale(0.96);
            animation: revealContainer 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            animation-delay: 1.4s;
            transition: border-color 0.4s ease, box-shadow 0.4s ease;
        }

        @keyframes revealContainer {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .auth-container:hover {
            border-color: rgba(0, 255, 136, 0.5);
            box-shadow: 
                0 0 60px rgba(0, 255, 136, 0.25),
                0 0 30px rgba(0, 229, 255, 0.2),
                0 35px 70px -12px rgba(0, 0, 0, 0.95);
        }

        /* HUD Clamp Corners */
        .hud-corner {
            position: absolute;
            width: 14px;
            height: 14px;
            border-color: var(--neon-green);
            border-style: solid;
            pointer-events: none;
            z-index: 20;
            transition: all 0.3s ease;
        }
        .hud-corner.top-left { top: 8px; left: 8px; border-width: 2.5px 0 0 2.5px; }
        .hud-corner.top-right { top: 8px; right: 8px; border-width: 2.5px 2.5px 0 0; }
        .hud-corner.bottom-left { bottom: 8px; left: 8px; border-width: 0 0 2.5px 2.5px; }
        .hud-corner.bottom-right { bottom: 8px; right: 8px; border-width: 0 2.5px 2.5px 0; }

        .auth-container:hover .hud-corner {
            border-color: var(--neon-cyan);
            filter: drop-shadow(0 0 6px var(--neon-cyan));
        }

        /* Left Banner Side */
        .auth-banner-side {
            background: linear-gradient(180deg, rgba(8, 14, 28, 0.98) 0%, rgba(4, 8, 16, 0.99) 100%);
            padding: 42px 32px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            border-right: 1px solid rgba(0, 255, 136, 0.18);
            position: relative;
            text-align: center;
        }

        .banner-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
        }

        .banner-terminal-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            font-weight: 800;
            color: var(--neon-cyan);
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .banner-title {
            font-size: 28px;
            font-weight: 900;
            letter-spacing: 2px;
            color: #ffffff;
            text-shadow: 0 0 18px rgba(0, 255, 136, 0.5);
        }

        .hologram-avatar-wrap {
            position: relative;
            width: 150px;
            height: 150px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 22px 0;
        }

        .hologram-outer-ring {
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px dashed rgba(0, 255, 136, 0.45);
            animation: rotateClockwise 12s linear infinite;
        }

        .hologram-orbit-dot {
            position: absolute;
            top: -4px;
            left: 50%;
            transform: translateX(-50%);
            width: 9px;
            height: 9px;
            background: var(--neon-cyan);
            border-radius: 50%;
            box-shadow: 0 0 12px var(--neon-cyan);
        }

        .hologram-inner-glow {
            width: 115px;
            height: 115px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(0, 255, 136, 0.18) 0%, rgba(5, 8, 17, 0.85) 75%);
            border: 1.5px solid rgba(0, 229, 255, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.35);
        }

        .panther-emblem-img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            filter: drop-shadow(0 0 12px rgba(0, 255, 136, 0.85));
            transition: transform 0.3s ease;
        }
        .auth-container:hover .panther-emblem-img {
            transform: scale(1.06);
            filter: drop-shadow(0 0 18px rgba(0, 229, 255, 0.95));
        }

        .terminal-telemetry-box {
            width: 100%;
            background: rgba(0, 0, 0, 0.5);
            border: 1px solid rgba(255, 255, 255, 0.09);
            border-radius: 8px;
            padding: 12px 16px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11px;
            text-align: left;
        }

        .telemetry-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }
        .telemetry-row:last-child {
            margin-bottom: 0;
        }
        .telemetry-key {
            color: #64748b;
        }
        .telemetry-val {
            color: var(--neon-green);
            font-weight: 700;
        }

        /* Right Form Side */
        .auth-form-side {
            background: rgba(9, 14, 25, 0.96);
            padding: 42px 44px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
        }

        .form-header-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .form-header-title {
            font-size: 19px;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .form-header-title i {
            color: var(--neon-green);
            text-shadow: 0 0 10px var(--neon-green-glow);
        }

        .auth-alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-family: 'JetBrains Mono', monospace;
            animation: fadeInAlert 0.3s ease;
        }
        @keyframes fadeInAlert {
            from { opacity: 0; transform: translateY(-4px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .auth-alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
            box-shadow: 0 0 20px rgba(239, 68, 68, 0.2);
        }
        .auth-alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid rgba(16, 185, 129, 0.5);
            color: #6ee7b7;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label {
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 12px;
            font-weight: 700;
            color: #cbd5e1;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .form-label-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10px;
            color: var(--neon-green);
        }

        .input-wrap {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            color: #64748b;
            font-size: 14px;
            pointer-events: none;
            transition: all 0.25s ease;
        }

        .form-control {
            width: 100%;
            background: var(--cyber-input-bg);
            border: 1.5px solid rgba(255, 255, 255, 0.12);
            border-radius: 8px;
            color: #f8fafc;
            padding: 13px 14px 13px 42px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: 'JetBrains Mono', monospace;
            outline: none;
            transition: all 0.25s ease;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.6);
        }

        .form-control:focus {
            border-color: var(--neon-green);
            background: rgba(7, 12, 22, 0.98);
            box-shadow: 
                0 0 0 3px rgba(0, 255, 136, 0.2),
                inset 0 0 12px rgba(0, 255, 136, 0.1);
        }

        .form-control:focus + .input-icon-left,
        .input-wrap:focus-within .input-icon-left {
            color: var(--neon-green);
            text-shadow: 0 0 10px var(--neon-green-glow);
            transform: scale(1.1);
        }

        .toggle-password-btn {
            position: absolute;
            right: 12px;
            background: transparent;
            border: none;
            color: #64748b;
            cursor: pointer;
            font-size: 14px;
            padding: 6px;
            transition: color 0.2s ease;
        }
        .toggle-password-btn:hover {
            color: var(--neon-green);
        }

        .form-options-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
            margin-bottom: 20px;
        }

        .checkbox-label {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #94a3b8;
            font-weight: 600;
            cursor: pointer;
            user-select: none;
        }

        .checkbox-label input[type="checkbox"] {
            appearance: none;
            width: 16px;
            height: 16px;
            background: rgba(15, 23, 42, 0.9);
            border: 1.5px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            cursor: pointer;
            display: grid;
            place-content: center;
            transition: all 0.2s ease;
        }

        .checkbox-label input[type="checkbox"]:checked {
            background: var(--neon-green);
            border-color: var(--neon-green);
        }

        .checkbox-label input[type="checkbox"]:checked::before {
            content: "\f00c";
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 10px;
            color: #050811;
        }

        .forgot-link {
            color: var(--neon-cyan);
            text-decoration: none;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
            font-size: 11.5px;
            transition: all 0.2s ease;
        }
        .forgot-link:hover {
            color: #ffffff;
            text-shadow: 0 0 10px var(--neon-cyan-glow);
        }

        .captcha-row {
            display: grid;
            grid-template-columns: 110px 1fr;
            gap: 12px;
            margin-bottom: 22px;
            align-items: center;
        }

        .captcha-badge {
            background: rgba(0, 255, 136, 0.12);
            border: 1.5px dashed var(--neon-green);
            border-radius: 8px;
            padding: 12px 0;
            text-align: center;
            font-family: 'Share Tech Mono', 'JetBrains Mono', monospace;
            font-size: 16px;
            font-weight: 800;
            color: var(--neon-green);
            user-select: none;
            box-shadow: 0 0 15px rgba(0, 255, 136, 0.15);
            text-shadow: 0 0 8px var(--neon-green-glow);
        }

        /* Action Buttons */
        .btn-auth-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }

        .btn-login {
            background: linear-gradient(135deg, #00ff88 0%, #059669 100%);
            color: #040914;
            border: none;
            padding: 13px 20px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 0 25px rgba(0, 255, 136, 0.45);
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            transform: translateY(-2px);
            box-shadow: 0 0 35px rgba(0, 255, 136, 0.75);
        }

        .btn-register {
            background: rgba(15, 23, 42, 0.85);
            color: #e2e8f0;
            border: 1.5px solid rgba(0, 229, 255, 0.4);
            padding: 13px 20px;
            border-radius: 8px;
            font-size: 13.5px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 1px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-transform: uppercase;
            text-align: center;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-register:hover {
            background: rgba(0, 229, 255, 0.15);
            border-color: var(--neon-cyan);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 0 25px rgba(0, 229, 255, 0.35);
        }

        .quick-links-footer {
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px dashed rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 11px;
            color: #64748b;
            font-family: 'JetBrains Mono', monospace;
        }

        @media (max-width: 820px) {
            .auth-container {
                grid-template-columns: 1fr;
                max-width: 480px;
            }
            .auth-banner-side {
                padding: 30px 20px 20px 20px;
                border-right: none;
                border-bottom: 1px solid rgba(0, 255, 136, 0.2);
            }
            .hologram-avatar-wrap {
                width: 120px;
                height: 120px;
                margin: 14px 0;
            }
            .panther-emblem-img {
                width: 60px;
            }
            .terminal-telemetry-box {
                display: none;
            }
            .auth-form-side {
                padding: 28px 24px;
            }
            .top-hud-bar {
                left: 14px;
                right: 14px;
                top: 10px;
            }
        }
    </style>
</head>
<body>

    <!-- ⚡ Dynamic Animated Cyber Splash / Loading Overlay Screen -->
    <div id="cyber-splash-loader">
        <div class="loader-center-box">
            <div class="loader-hologram">
                <div class="loader-outer-ring"></div>
                <div class="loader-inner-ring"></div>
                <img src="{{ asset('images/logo.png') }}" alt="Payate CC Logo" class="loader-logo-icon">
            </div>

            <div class="loader-title">PAYATE CC // INITIALIZING</div>

            <div class="loader-terminal-stream" id="loaderTerminalLine">
                > [СИСТЕМА]: Инициализация защищенного шлюза...
            </div>

            <div class="loader-progress-track">
                <div class="loader-progress-bar" id="loaderBar"></div>
            </div>

            <div class="loader-meta-row">
                <span id="loaderStatusText">СТАТУС: ПОДКЛЮЧЕНИЕ</span>
                <span id="loaderPercentText" style="color: var(--neon-green); font-weight: 800;">0%</span>
            </div>
        </div>
    </div>

    <!-- Dynamic Matrix Digital Rain Canvas -->
    <canvas id="matrix-canvas"></canvas>

    <!-- Cyber Ambient Backdrop -->
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

    <!-- Main Cyber Container -->
    <div class="auth-container">
        <!-- Corner HUD Accents -->
        <div class="hud-corner top-left"></div>
        <div class="hud-corner top-right"></div>
        <div class="hud-corner bottom-left"></div>
        <div class="hud-corner bottom-right"></div>

        <!-- Left Side: Terminal Hologram / Security Node -->
        <div class="auth-banner-side">
            <div class="banner-header">
                <span class="banner-terminal-tag" data-i18n="login_banner_tag"><i class="fa-solid fa-code"></i> ТЕРМИНАЛЬНЫЙ ПРОТОКОЛ</span>
                <h1 class="banner-title" data-i18n="login_banner_title">PAYATE CC</h1>
            </div>
            
            <div class="hologram-avatar-wrap">
                <div class="hologram-outer-ring">
                    <div class="hologram-orbit-dot"></div>
                </div>
                <div class="hologram-inner-glow">
                    <img src="{{ asset('images/logo.png') }}" alt="Payate CC Emblem" class="panther-emblem-img">
                </div>
            </div>

            <!-- Real-time HUD Telemetry Info -->
            <div class="terminal-telemetry-box">
                <div class="telemetry-row">
                    <span class="telemetry-key"><i class="fa-solid fa-shield-halved"></i> <span data-i18n="login_encryption">ШИФРОВАНИЕ:</span></span>
                    <span class="telemetry-val">AES-256-GCM</span>
                </div>
                <div class="telemetry-row">
                    <span class="telemetry-key"><i class="fa-solid fa-server"></i> <span data-i18n="login_node_cluster">КЛАСТЕР НОДЫ:</span></span>
                    <span class="telemetry-val">PROD-09-ROOT</span>
                </div>
                <div class="telemetry-row">
                    <span class="telemetry-key"><i class="fa-solid fa-fingerprint"></i> <span data-i18n="login_auth_proto">ПРОТОКОЛ АВТОРИЗАЦИИ:</span></span>
                    <span class="telemetry-val">2-FACTOR 2FA</span>
                </div>
            </div>
        </div>

        <!-- Right Side: Cyber Dark Login Form -->
        <div class="auth-form-side">
            <div class="form-header-bar">
                <div class="form-header-title">
                    <i class="fa-solid fa-right-to-bracket"></i>
                    <span data-i18n="login_title">ВХОД В СИСТЕМУ</span>
                </div>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--neon-cyan);">
                    <i class="fa-solid fa-lock"></i> <span data-i18n="login_secure">БЕЗОПАСНЫЙ УЗЕЛ</span>
                </div>
            </div>

            @if(session('error'))
                <div class="auth-alert auth-alert-error">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if(session('success'))
                <div class="auth-alert auth-alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('info'))
                <div class="auth-alert auth-alert-success" style="background: rgba(0, 229, 255, 0.12); border-color: rgba(0, 229, 255, 0.4); color: #67e8f9;">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                
                <!-- Username Input (Completely Blank - No Auto Demo Text) -->
                <div class="form-group">
                    <label class="form-label" for="username">
                        <span data-i18n="login_username_label">Имя пользователя / Email</span>
                        <span class="form-label-tag">>_ ID</span>
                    </label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-user-astronaut input-icon-left"></i>
                        <input type="text" name="username" id="username" class="form-control" value="{{ old('username', session('registered_username', '')) }}" placeholder="Введите ваш логин или email" data-i18n-ph="login_username_ph" required autofocus autocomplete="off">
                    </div>
                </div>

                <!-- Password Input (Completely Blank) -->
                <div class="form-group">
                    <label class="form-label" for="password">
                        <span data-i18n="login_pass_label">Ключ доступа (Пароль)</span>
                        <span class="form-label-tag">*** PASS</span>
                    </label>
                    <div class="input-wrap">
                        <i class="fa-solid fa-key input-icon-left"></i>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Введите ваш основной пароль" data-i18n-ph="login_pass_ph" required autocomplete="off">
                        <button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility('password')" title="Toggle Password View">
                            <i class="fa-regular fa-eye" id="password-eye"></i>
                        </button>
                    </div>
                </div>

                <!-- Options Row: Remember Me -->
                <div class="form-options-row">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" value="1">
                        <span data-i18n="login_remember">Запомнить устройство</span>
                    </label>
                </div>

                <!-- Random Dynamic Math Captcha Row -->
                <div class="captcha-row">
                    <div class="captcha-badge" title="Dynamic Security Math Challenge">
                        <i class="fa-solid fa-calculator" style="font-size: 11px; margin-right: 4px; opacity: 0.7;"></i>{{ $captcha }}
                    </div>
                    <div class="input-wrap">
                        <i class="fa-solid fa-hashtag input-icon-left"></i>
                        <input type="number" name="captcha" class="form-control" placeholder="Решите пример" data-i18n-ph="login_captcha_ph" required autocomplete="off">
                    </div>
                </div>

                <!-- Action Buttons: Login & Register -->
                <div class="btn-auth-grid">
                    <button type="submit" class="btn-login">
                        <i class="fa-solid fa-terminal"></i> <span data-i18n="login_btn">ВОЙТИ В СИСТЕМУ</span>
                    </button>
                    <a href="{{ route('register') }}" class="btn-register">
                        <i class="fa-solid fa-user-plus"></i> <span data-i18n="register_btn">РЕГИСТРАЦИЯ</span>
                    </a>
                </div>
            </form>

            <div class="quick-links-footer">
                <span>СТАТУС: <strong style="color: var(--neon-green);" data-i18n="status_online">СИСТЕМА ОНЛАЙН</strong></span>
                <span><i class="fa-solid fa-shield-halved" style="color: var(--neon-green); margin-right: 4px;"></i><span data-i18n="encrypted_256">256-БИТНОЕ ШИФРОВАНИЕ</span></span>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/i18n.js') }}"></script>
    <script>
        // Language Switcher Dropdown Toggle
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

        // 🚀 Smooth Cyber Splash Loading Screen Animation
        window.addEventListener('DOMContentLoaded', () => {
            const splash = document.getElementById('cyber-splash-loader');
            const bar = document.getElementById('loaderBar');
            const percentText = document.getElementById('loaderPercentText');
            const statusText = document.getElementById('loaderStatusText');
            const terminalLine = document.getElementById('loaderTerminalLine');

            const steps = [
                { percent: 25, line: '> [СИСТЕМА]: Инициализация криптографического узла...', status: 'ЗАГРУЗКА УЗЛОВ' },
                { percent: 55, line: '> [КРИПТО]: Загрузка 256-битного шифра AES...', status: 'ПРОВЕРКА ШИФРОВ' },
                { percent: 85, line: '> [СЕТЬ]: Проверка SSL/TLS сертификатов безопасности...', status: 'КЛЮЧИ OK' },
                { percent: 100, line: '> [ШЛЮЗ]: Протоколы подтверждены. Доступ открыт.', status: 'ДОСТУП РАЗРЕШЕН' }
            ];

            let stepIndex = 0;
            function runNextStep() {
                if (stepIndex < steps.length) {
                    const step = steps[stepIndex];
                    bar.style.width = step.percent + '%';
                    percentText.textContent = step.percent + '%';
                    statusText.textContent = step.status;
                    terminalLine.textContent = step.line;
                    stepIndex++;
                    setTimeout(runNextStep, 350);
                } else {
                    setTimeout(() => {
                        splash.classList.add('loaded');
                    }, 400);
                }
            }

            setTimeout(runNextStep, 200);
        });

        // Password Visibility Toggle
        function togglePasswordVisibility(fieldId) {
            const input = document.getElementById(fieldId);
            if (!input) return;
            const eye = input.parentElement.querySelector('#password-eye') || input.parentElement.querySelector('i:last-child');
            if (input.type === 'password') {
                input.type = 'text';
                if (eye) eye.className = 'fa-regular fa-eye-slash';
            } else {
                input.type = 'password';
                if (eye) eye.className = 'fa-regular fa-eye';
            }
        }

        // Matrix Rain Canvas Animation
        const canvas = document.getElementById('matrix-canvas');
        const ctx = canvas.getContext('2d');

        function resizeCanvas() {
            canvas.width = window.innerWidth;
            canvas.height = window.innerHeight;
        }
        resizeCanvas();
        window.addEventListener('resize', resizeCanvas);

        const characters = '0123456789ABCDEF01010101XYZ';
        const fontSize = 14;
        let columns = Math.floor(window.innerWidth / fontSize);
        let drops = [];

        function initDrops() {
            columns = Math.floor(window.innerWidth / fontSize);
            drops = [];
            for (let i = 0; i < columns; i++) {
                drops[i] = Math.floor(Math.random() * -100);
            }
        }
        initDrops();

        let isSolidBg = false;

        function drawMatrix() {
            if (isSolidBg) {
                requestAnimationFrame(drawMatrix);
                return;
            }

            ctx.fillStyle = 'rgba(5, 8, 17, 0.08)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            for (let i = 0; i < drops.length; i++) {
                const char = characters.charAt(Math.floor(Math.random() * characters.length));
                ctx.fillStyle = Math.random() > 0.88 ? '#ffffff' : '#00ff88';
                ctx.font = fontSize + 'px "JetBrains Mono", monospace';
                ctx.fillText(char, i * fontSize, drops[i] * fontSize);

                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) {
                    drops[i] = 0;
                }
                drops[i]++;
            }
            setTimeout(() => {
                requestAnimationFrame(drawMatrix);
            }, 33);
        }
        requestAnimationFrame(drawMatrix);

        function toggleMatrixBackground() {
            isSolidBg = !isSolidBg;
            document.body.classList.toggle('solid-bg', isSolidBg);
            document.getElementById('bgModeText').textContent = isSolidBg ? 'FX: SOLID DARK' : 'FX: MATRIX ACTIVE';
            if (isSolidBg) {
                ctx.fillStyle = '#050811';
                ctx.fillRect(0, 0, canvas.width, canvas.height);
            }
        }
    </script>
</body>
</html>
