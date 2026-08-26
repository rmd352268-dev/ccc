<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ACCESS DENIED // YOU ARE BANNED - Payate CC</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background-color: #050811;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #E2E8F0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
            position: relative;
        }

        .ambient-glow {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(circle at 50% 30%, rgba(239, 68, 68, 0.22) 0%, transparent 65%),
                radial-gradient(circle at 50% 80%, rgba(15, 23, 42, 0.9) 0%, #050811 100%);
            z-index: 1;
            pointer-events: none;
        }

        .scanlines {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.4) 50%);
            background-size: 100% 4px;
            z-index: 2;
            pointer-events: none;
            opacity: 0.55;
        }

        .banned-container {
            position: relative;
            z-index: 10;
            background: rgba(13, 18, 32, 0.96);
            border: 2px solid #EF4444;
            border-radius: 20px;
            padding: 46px 38px;
            max-width: 540px;
            width: 100%;
            text-align: center;
            box-shadow: 
                0 0 70px rgba(239, 68, 68, 0.35),
                0 30px 60px -12px rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(24px);
            animation: shakeGlitch 0.6s ease;
        }

        @keyframes shakeGlitch {
            0%, 100% { transform: translateY(0); }
            20% { transform: translateY(-5px) translateX(-3px); }
            40% { transform: translateY(4px) translateX(3px); }
            60% { transform: translateY(-3px) translateX(2px); }
            80% { transform: translateY(2px) translateX(-1px); }
        }

        .banned-icon-wrap {
            width: 95px;
            height: 95px;
            background: rgba(239, 68, 68, 0.14);
            border: 3px solid #EF4444;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 46px;
            color: #EF4444;
            margin-bottom: 22px;
            box-shadow: 0 0 40px rgba(239, 68, 68, 0.55);
            animation: pulse-red 2s infinite ease-in-out;
        }

        @keyframes pulse-red {
            0%, 100% { transform: scale(1); box-shadow: 0 0 30px rgba(239, 68, 68, 0.5); }
            50% { transform: scale(1.08); box-shadow: 0 0 50px rgba(239, 68, 68, 0.9); filter: drop-shadow(0 0 12px #EF4444); }
        }

        .banned-alert-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            font-weight: 800;
            color: #EF4444;
            letter-spacing: 3px;
            text-transform: uppercase;
            margin-bottom: 8px;
            display: inline-block;
        }

        .banned-title {
            font-size: 32px;
            font-weight: 900;
            color: #FFFFFF;
            letter-spacing: 2px;
            margin-bottom: 6px;
            text-transform: uppercase;
            text-shadow: 0 0 25px rgba(239, 68, 68, 0.75);
            font-family: 'JetBrains Mono', monospace;
        }

        .banned-ru-subtitle {
            font-size: 15px;
            font-weight: 800;
            color: #F87171;
            margin-bottom: 16px;
            letter-spacing: 0.5px;
        }

        .banned-desc {
            font-size: 13.5px;
            color: #94A3B8;
            line-height: 1.65;
            margin-bottom: 24px;
        }

        .security-telemetry-box {
            background: rgba(5, 8, 17, 0.95);
            border: 1.5px dashed rgba(239, 68, 68, 0.45);
            border-radius: 10px;
            padding: 16px 18px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            color: #F87171;
            text-align: left;
            margin-bottom: 26px;
        }

        .telemetry-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .telemetry-line:last-child {
            margin-bottom: 0;
        }
        .telemetry-line span:first-child {
            color: #64748B;
        }
        .telemetry-line span:last-child {
            color: #EF4444;
            font-weight: 800;
        }

        .btn-exit-portal {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            background: linear-gradient(135deg, #EF4444 0%, #B91C1C 100%);
            color: #FFFFFF;
            font-weight: 900;
            font-size: 14px;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 1.5px;
            padding: 14px;
            border-radius: 8px;
            text-decoration: none;
            text-transform: uppercase;
            box-shadow: 0 0 30px rgba(239, 68, 68, 0.45);
            transition: all 0.2s ease;
        }
        .btn-exit-portal:hover {
            background: linear-gradient(135deg, #F87171 0%, #DC2626 100%);
            transform: translateY(-2px);
            box-shadow: 0 0 45px rgba(239, 68, 68, 0.8);
        }
    </style>
</head>
<body>
    <div class="ambient-glow"></div>
    <div class="scanlines"></div>

    <div class="banned-container">
        <div class="banned-icon-wrap">
            <i class="fa-solid fa-ban"></i>
        </div>

        <div class="banned-alert-tag">
            <i class="fa-solid fa-triangle-exclamation"></i> ACCESS RESTRICTED // SECURITY LOCKOUT
        </div>

        <h1 class="banned-title">YOU ARE BANNED</h1>
        <div class="banned-ru-subtitle" style="font-family: 'JetBrains Mono', monospace; font-size: 13.5px; font-weight: 800; color: #EF4444; margin-bottom: 16px; letter-spacing: 0.5px;">
            ВАШ АККАУНТ ЗАБЛОКИРОВАН // ДОСТУП ОГРАНИЧЕН
        </div>

        <p class="banned-desc">
            Your account has been permanently banned by the administration. You are not allowed to log in, open, or access any services, marketplace features, or funds on Payate CC.
        </p>

        <div class="security-telemetry-box">
            <div class="telemetry-line">
                <span>ACCOUNT STATUS:</span>
                <span>PERMANENTLY BANNED</span>
            </div>
            <div class="telemetry-line">
                <span>PORTAL PERMISSION:</span>
                <span>REVOKED (403 FORBIDDEN)</span>
            </div>
            <div class="telemetry-line">
                <span>SECURITY ENFORCEMENT:</span>
                <span>ACTIVE_LOCKOUT</span>
            </div>
        </div>

        <a href="{{ route('logout') }}" class="btn-exit-portal">
            <i class="fa-solid fa-arrow-right-from-bracket"></i> EXIT PORTAL / LOGOUT
        </a>
    </div>
</body>
</html>
