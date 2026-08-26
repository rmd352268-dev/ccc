<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Control Panel') - Payate CC Master Suite</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
    @php
        $pendingRechargesCount = \App\Models\Deposit::where('status', 'pending')->count();
    @endphp

    <!-- Top Admin Header -->
    <header style="background: var(--bg-surface); border-bottom: 1px solid var(--border-color); padding: 10px 24px; display: flex; justify-content: space-between; align-items: center; box-shadow: var(--card-shadow);">
        <div style="display: flex; align-items: center; gap: 14px;">
            <a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 10px; text-decoration: none;">
                <img src="{{ asset('images/logo.png') }}" alt="Payate CC" style="height: 30px; width: 30px; object-fit: contain; filter: drop-shadow(0 0 10px rgba(245, 158, 11, 0.6));">
                <span style="font-size: 15px; font-weight: 900; color: var(--text-primary); letter-spacing: 0.5px; font-family: 'JetBrains Mono', monospace;">
                    PAYATE <span style="color: var(--gold-primary);">CC</span> <span style="color: #94A3B8; font-size: 12px; font-weight: 700;">// ADMIN DESK</span>
                </span>
            </a>
            <span style="background: rgba(16,185,129,0.15); color: #10B981; border: 1px solid rgba(16,185,129,0.3); font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 6px; font-family: 'JetBrains Mono', monospace;">
                RESTRICTED
            </span>
        </div>

        <div style="display: flex; align-items: center; gap: 12px;">
            <!-- Day / Night Mode Switcher for Admin (Independent) -->
            <button type="button" class="btn-theme-toggle" onclick="toggleTheme()" title="Toggle Admin Day/Night Mode">
                <i class="fa-solid fa-sun theme-toggle-icon"></i><span> Day</span>
            </button>

            <a href="{{ route('marketplace.index') }}" target="_blank" class="btn-reset" style="padding: 5px 12px; font-size: 12px; text-decoration: none;">
                <i class="fa-solid fa-arrow-up-right-from-square"></i> Open Public Site
            </a>
            <span style="color: var(--text-secondary); font-size: 12px;">Admin: <strong style="color: var(--text-primary);">{{ session('admin_user', 'SuperAdmin') }}</strong></span>
            <a href="{{ route('admin.logout') }}" class="btn-reset" style="padding: 5px 10px; font-size: 12px; text-decoration: none; color: #EF4444; border-color: rgba(239,68,68,0.3);" title="Logout of Admin">
                <i class="fa-solid fa-power-off"></i> Logout
            </a>
        </div>
    </header>

    <div style="display: flex;">
        <!-- Left Sidebar -->
        <aside class="admin-sidebar">
            <div style="padding: 14px 16px; margin: 0 10px 14px 10px; background: rgba(245, 158, 11, 0.06); border: 1px solid rgba(245, 158, 11, 0.2); border-radius: 10px; display: flex; align-items: center; gap: 10px;">
                <img src="{{ asset('images/logo.png') }}" style="height: 24px; width: 24px; object-fit: contain; filter: drop-shadow(0 0 6px rgba(245, 158, 11, 0.5));">
                <div>
                    <div style="font-size: 11px; font-weight: 800; color: var(--gold-primary); font-family: 'JetBrains Mono', monospace;">MASTER CONTROL</div>
                    <div style="font-size: 9.5px; color: var(--text-muted);">Root Security Active</div>
                </div>
            </div>

            <div style="padding: 0 20px 10px 20px; font-size: 11px; font-weight: 700; color: var(--gold-dark); text-transform: uppercase; letter-spacing: 0.5px;">
                Financial & System Control
            </div>

            <a href="{{ route('admin.dashboard') }}" class="admin-nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa-solid fa-gauge-high"></i> Dashboard
            </a>

            <!-- Priority 1: File Upload / Bulk Card Import -->
            <a href="{{ route('admin.cards.bulk') }}" class="admin-nav-link {{ request()->routeIs('admin.cards.bulk') ? 'active' : '' }}" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3); color: var(--gold-primary); font-weight: 800; margin: 4px 10px; border-radius: 8px;">
                <i class="fa-solid fa-file-arrow-up" style="color: var(--gold-primary); font-size: 14px;"></i> Upload Card File
            </a>

            <a href="{{ route('admin.recharges.index') }}" class="admin-nav-link {{ request()->routeIs('admin.recharges.*') ? 'active' : '' }}" style="position: relative;">
                <i class="fa-solid fa-hand-holding-dollar"></i> Deposit Approvals
                @if($pendingRechargesCount > 0)
                    <span style="background: #F59E0B; color: #000; font-size: 10px; font-weight: 800; padding: 1px 6px; border-radius: 10px; margin-left: auto;">
                        {{ $pendingRechargesCount }}
                    </span>
                @endif
            </a>

            <a href="{{ route('admin.users.index') }}" class="admin-nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="fa-solid fa-users-gear"></i> User Profiles & Balances
            </a>

            <a href="{{ route('admin.cards.index') }}" class="admin-nav-link {{ request()->routeIs('admin.cards.index', 'admin.cards.create', 'admin.cards.edit') ? 'active' : '' }}">
                <i class="fa-solid fa-credit-card"></i> Cards Inventory & Edit
            </a>

            <a href="{{ route('admin.wholesale.index') }}" class="admin-nav-link {{ request()->routeIs('admin.wholesale.*') ? 'active' : '' }}">
                <i class="fa-solid fa-box-archive"></i> Wholesale Bundles
            </a>

            <a href="{{ route('admin.orders.index') }}" class="admin-nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">
                <i class="fa-solid fa-receipt"></i> Orders Audit Log
            </a>

            <a href="{{ route('admin.news.index') }}" class="admin-nav-link {{ request()->routeIs('admin.news.*') ? 'active' : '' }}">
                <i class="fa-regular fa-newspaper"></i> News & Bulletins
            </a>

            <a href="{{ route('admin.tickets.index') }}" class="admin-nav-link {{ request()->routeIs('admin.tickets.*') ? 'active' : '' }}">
                <i class="fa-solid fa-headset"></i> Support Desk
            </a>

            <a href="{{ route('admin.wallets.index') }}" class="admin-nav-link {{ request()->routeIs('admin.wallets.*') ? 'active' : '' }}">
                <i class="fa-solid fa-sliders"></i> Site Options & Vault Settings
            </a>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-main">
            @if(session('success'))
                <div class="alert-box alert-success" style="margin-bottom: 20px;">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-box alert-error" style="margin-bottom: 20px;">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script src="{{ asset('js/marketplace.js') }}"></script>
    @stack('scripts')
</body>
</html>
