<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $success ? 'Action Completed' : 'Action Failed' }} // Payate CC Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@500;700;800&family=Plus+Jakarta+Sans:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #050811;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .action-card {
            background: rgba(15, 23, 42, 0.95);
            border: 1.5px solid {{ $success ? 'rgba(16, 185, 129, 0.5)' : 'rgba(239, 68, 68, 0.5)' }};
            border-radius: 16px;
            padding: 36px 30px;
            max-width: 460px;
            width: 100%;
            text-align: center;
            box-shadow: 0 0 40px {{ $success ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)' }};
        }
        .status-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: {{ $success ? 'rgba(16, 185, 129, 0.15)' : 'rgba(239, 68, 68, 0.15)' }};
            border: 2px solid {{ $success ? '#10B981' : '#EF4444' }};
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: {{ $success ? '#10B981' : '#EF4444' }};
            margin-bottom: 20px;
        }
        .action-title {
            font-size: 20px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 8px;
        }
        .action-desc {
            font-size: 13.5px;
            color: #94a3b8;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        .meta-box {
            background: rgba(0, 0, 0, 0.5);
            border: 1px dashed rgba(255, 255, 255, 0.15);
            border-radius: 8px;
            padding: 14px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            text-align: left;
            margin-bottom: 24px;
        }
        .meta-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 6px;
        }
        .meta-row:last-child { margin-bottom: 0; }
        .meta-key { color: #64748b; }
        .meta-val { color: #10B981; font-weight: 700; }
        .btn-admin-panel {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            background: #10B981;
            color: #050811;
            font-weight: 800;
            font-size: 13px;
            padding: 12px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s ease;
        }
        .btn-admin-panel:hover {
            background: #34D399;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <div class="action-card">
        <div class="status-icon">
            <i class="fa-solid {{ $success ? 'fa-check' : 'fa-triangle-exclamation' }}"></i>
        </div>

        <h1 class="action-title">{{ $success ? 'Telegram Action Executed' : 'Action Failed' }}</h1>
        <p class="action-desc">{{ $message }}</p>

        @if(isset($deposit))
            <div class="meta-box">
                <div class="meta-row">
                    <span class="meta-key">Reference ID:</span>
                    <span class="meta-val" style="color: #3B82F6;">{{ $deposit->trx_id }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">User Account:</span>
                    <span class="meta-val" style="color: #F59E0B;">@{{ $deposit->username }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Deposit Amount:</span>
                    <span class="meta-val">${{ number_format($deposit->amount, 2) }}</span>
                </div>
                <div class="meta-row">
                    <span class="meta-key">Status:</span>
                    <span class="meta-val" style="text-transform: uppercase;">{{ $deposit->status }}</span>
                </div>
            </div>
        @endif

        <a href="{{ route('admin.recharges.index') }}" class="btn-admin-panel">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Admin Panel
        </a>
    </div>
</body>
</html>
