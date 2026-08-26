<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master PIN Authorization (Step 3/3) // Payate CC</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --neon-green: #00ff88;
            --green-glow: rgba(0, 255, 136, 0.4);
            --cyber-dark-bg: #040711;
            --cyber-card-bg: rgba(9, 14, 26, 0.96);
            --cyber-input-bg: rgba(5, 9, 17, 0.92);
            --text-main: #f1f5f9;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: var(--cyber-dark-bg);
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            overflow: hidden;
            position: relative;
        }

        #matrix-canvas {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            z-index: 0; pointer-events: none; opacity: 0.55;
        }

        .ambient-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: 
                radial-gradient(circle at 50% 20%, rgba(0, 255, 136, 0.12) 0%, transparent 55%),
                radial-gradient(circle at 50% 80%, rgba(5, 8, 17, 0.7) 0%, #040711 100%);
            z-index: 1; pointer-events: none;
        }
        .scanlines {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: linear-gradient(rgba(18, 16, 16, 0) 50%, rgba(0, 0, 0, 0.35) 50%);
            background-size: 100% 4px; z-index: 2; pointer-events: none; opacity: 0.45;
        }

        .admin-auth-card {
            position: relative; z-index: 10; width: 100%; max-width: 520px;
            background: var(--cyber-card-bg);
            border: 2px solid rgba(0, 255, 136, 0.45);
            border-radius: 20px; padding: 42px 38px;
            box-shadow: 0 0 60px rgba(0, 255, 136, 0.2), 0 30px 60px -12px rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(24px);
        }

        .hud-corner {
            position: absolute; width: 14px; height: 14px; border-color: var(--neon-green);
            border-style: solid; pointer-events: none; z-index: 20;
        }
        .hud-corner.top-left { top: 8px; left: 8px; border-width: 2.5px 0 0 2.5px; }
        .hud-corner.top-right { top: 8px; right: 8px; border-width: 2.5px 2.5px 0 0; }
        .hud-corner.bottom-left { bottom: 8px; left: 8px; border-width: 0 0 2.5px 2.5px; }
        .hud-corner.bottom-right { bottom: 8px; right: 8px; border-width: 0 2.5px 2.5px 0; }

        .step-progress-bar {
            display: flex; gap: 8px; margin-bottom: 24px;
        }
        .step-pill {
            flex: 1; height: 6px; border-radius: 999px; background: rgba(255, 255, 255, 0.1);
        }
        .step-pill.done {
            background: #10b981;
            box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
        }
        .step-pill.active {
            background: linear-gradient(90deg, #00ff88, #34d399);
            box-shadow: 0 0 12px var(--green-glow);
        }

        .auth-header {
            text-align: center; margin-bottom: 26px;
        }
        .auth-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(0, 255, 136, 0.12); border: 1px solid rgba(0, 255, 136, 0.4);
            padding: 4px 12px; border-radius: 999px; font-family: 'JetBrains Mono', monospace;
            font-size: 11px; font-weight: 800; color: var(--neon-green); margin-bottom: 10px;
            letter-spacing: 1.5px; text-transform: uppercase;
        }
        .auth-title {
            font-size: 24px; font-weight: 900; color: #ffffff; letter-spacing: 1px;
            text-transform: uppercase; text-shadow: 0 0 15px rgba(0, 255, 136, 0.4);
        }
        .auth-subtitle {
            font-size: 12.5px; color: #94a3b8; margin-top: 4px;
        }

        .auth-alert {
            padding: 12px 16px; border-radius: 8px; font-size: 12px; font-weight: 600;
            margin-bottom: 20px; display: flex; align-items: center; gap: 10px;
            font-family: 'JetBrains Mono', monospace;
        }
        .auth-alert-error {
            background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.5);
            color: #fca5a5;
        }

        .form-group { margin-bottom: 24px; }
        .form-label {
            display: flex; justify-content: space-between; font-size: 12px;
            font-weight: 700; color: #cbd5e1; margin-bottom: 8px;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        .form-label-tag { font-family: 'JetBrains Mono', monospace; font-size: 10px; color: var(--neon-green); }

        .input-wrap { position: relative; display: flex; align-items: center; }
        .input-icon-left { position: absolute; left: 14px; color: #64748b; font-size: 14px; pointer-events: none; }
        .form-control {
            width: 100%; background: var(--cyber-input-bg);
            border: 1.5px solid rgba(255, 255, 255, 0.14); border-radius: 8px;
            color: #f8fafc; padding: 14px 14px 14px 44px; font-size: 16px;
            font-weight: 800; font-family: 'JetBrains Mono', monospace; outline: none;
            letter-spacing: 5px; text-align: center; transition: all 0.25s ease;
        }
        .form-control:focus {
            border-color: var(--neon-green); background: rgba(7, 12, 22, 0.98);
            box-shadow: 0 0 0 3px rgba(0, 255, 136, 0.25);
        }

        .btn-proceed {
            width: 100%; background: linear-gradient(135deg, #00ff88 0%, #059669 100%);
            color: #040914; border: none; padding: 14px; border-radius: 8px;
            font-size: 14px; font-weight: 900; font-family: 'JetBrains Mono', monospace;
            letter-spacing: 1px; cursor: pointer; text-transform: uppercase;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            box-shadow: 0 0 30px rgba(0, 255, 136, 0.45); transition: all 0.2s ease;
        }
        .btn-proceed:hover {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            transform: translateY(-2px); box-shadow: 0 0 40px rgba(0, 255, 136, 0.75);
        }
    </style>
</head>
<body>
    <canvas id="matrix-canvas"></canvas>
    <div class="ambient-overlay"></div>
    <div class="scanlines"></div>

    <div class="admin-auth-card">
        <div class="hud-corner top-left"></div>
        <div class="hud-corner top-right"></div>
        <div class="hud-corner bottom-left"></div>
        <div class="hud-corner bottom-right"></div>

        <div class="step-progress-bar">
            <div class="step-pill done" title="Step 1: Primary Password (Completed)"></div>
            <div class="step-pill done" title="Step 2: Secondary Key (Completed)"></div>
            <div class="step-pill active" title="Step 3: Master PIN (Active)"></div>
        </div>

        <div class="auth-header">
            <div class="auth-badge">
                <i class="fa-solid fa-unlock-keyhole"></i> STAGE 3/3 // MASTER 6-DIGIT PIN
            </div>
            <h1 class="auth-title">PAYATE CC ADMIN</h1>
            <p class="auth-subtitle">Final Military Authorization Layer</p>
        </div>

        @if(session('error'))
            <div class="auth-alert auth-alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('admin.login.step3.post') }}" method="POST">
            @csrf

            <!-- 6-Digit Master PIN -->
            <div class="form-group">
                <label class="form-label" for="master_pin">
                    <span>Tertiary Master Security PIN</span>
                    <span class="form-label-tag">>_ MASTER_PIN_3</span>
                </label>
                <div class="input-wrap">
                    <i class="fa-solid fa-shield-halved input-icon-left"></i>
                    <input type="password" name="master_pin" id="master_pin" class="form-control" placeholder="••••••" maxlength="10" required autofocus autocomplete="off">
                </div>
            </div>

            <button type="submit" class="btn-proceed">
                <i class="fa-solid fa-unlock"></i> <span>AUTHORIZE & ENTER MASTER DESK</span>
            </button>
        </form>
    </div>

    <script>
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

        function drawMatrix() {
            ctx.fillStyle = 'rgba(4, 7, 17, 0.08)';
            ctx.fillRect(0, 0, canvas.width, canvas.height);
            for (let i = 0; i < drops.length; i++) {
                const char = characters.charAt(Math.floor(Math.random() * characters.length));
                ctx.fillStyle = Math.random() > 0.9 ? '#ffffff' : '#00ff88';
                ctx.font = fontSize + 'px "JetBrains Mono", monospace';
                ctx.fillText(char, i * fontSize, drops[i] * fontSize);
                if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
                drops[i]++;
            }
            setTimeout(() => requestAnimationFrame(drawMatrix), 33);
        }
        requestAnimationFrame(drawMatrix);
    </script>
</body>
</html>
