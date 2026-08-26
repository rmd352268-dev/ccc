<!DOCTYPE html>
<html lang="ru" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Payate CC') - Payate CC</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('favicon.ico') }}">
    <link rel="stylesheet" href="{{ asset('css/custom.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Language Dropdown Selector */
        .lang-dropdown {
            position: relative;
            display: inline-block;
        }
        .lang-dropdown-btn {
            background: var(--bg-input);
            border: 1.5px solid var(--border-color);
            color: var(--text-primary);
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 800;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
        }
        .lang-dropdown-btn:hover {
            border-color: var(--gold-primary);
        }
        .lang-dropdown-menu {
            display: none;
            position: absolute;
            top: calc(100% + 4px);
            right: 0;
            background: var(--bg-surface);
            border: 1.5px solid var(--border-color);
            border-radius: 8px;
            box-shadow: var(--card-shadow);
            min-width: 140px;
            z-index: 1000;
            overflow: hidden;
        }
        .lang-dropdown.active .lang-dropdown-menu {
            display: block;
        }
        .lang-dropdown-item {
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: background 0.15s;
            text-decoration: none;
        }
        .lang-dropdown-item:hover {
            background: var(--bg-card-hover);
            color: var(--gold-primary);
        }

        /* 1-Hour Security Session Badge */
        .session-timer-badge {
            background: rgba(16, 185, 129, 0.12);
            border: 1px solid rgba(16, 185, 129, 0.35);
            color: #10B981;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            font-family: monospace;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    @php
        $userId = session('user_id');
        $activeUser = $userId ? \App\Models\User::find($userId) : null;
        $activeUsername = $activeUser ? $activeUser->username : session()->get('user_username', 'Guest');
        $userBalance = $activeUser ? (float)$activeUser->balance : (float)session()->get('user_balance', 0.00);
        $totalRecharge = $activeUser ? (float)$activeUser->total_recharge : (float)session()->get('total_recharge', 0.00);
        $cartCount = count(session()->get('cart', []));
        $userProfile = session()->get('user_profile', ['username' => $activeUsername]);
        
        $loginTimestamp = session('user_login_timestamp', time());
        $elapsedSeconds = time() - $loginTimestamp;
        $sessionRemainingSeconds = max(0, 3600 - $elapsedSeconds);
    @endphp

    <!-- Top Header Bar -->
    <header class="top-header">
        <div class="container top-header-inner">
            <div class="header-left">
                <a href="{{ route('marketplace.index') }}" style="display: inline-flex; align-items: center; gap: 8px; text-decoration: none; margin-right: 6px;">
                    <img src="{{ asset('images/logo.png') }}" alt="Payate CC" style="height: 24px; width: 24px; object-fit: contain; filter: drop-shadow(0 0 6px rgba(245, 158, 11, 0.4));">
                    <span style="font-weight: 900; font-size: 14px; color: var(--text-primary); letter-spacing: 0.5px; font-family: 'JetBrains Mono', monospace;">PAYATE <span style="color: var(--gold-primary);">CC</span></span>
                </a>
                <div class="time-badge">
                    <i class="fa-regular fa-clock" style="color: var(--gold-dark);"></i> <span data-i18n="pst_time">Тихоокеанское время:</span> <span id="pst-clock" style="font-weight: 800;">00:00:00</span>
                </div>
                <div class="user-badge">
                    <i class="fa-solid fa-circle-user" style="color: var(--gold-primary);"></i> <span data-i18n="welcome">Добро пожаловать:</span> <a href="{{ route('profile.index') }}" title="Profile"><span>{{ $userProfile['username'] ?? $activeUsername }}</span></a>
                </div>
                <div class="balance-box">
                    <span class="balance-val">$ {{ number_format($userBalance, 2) }}</span>
                    <span class="recharge-val">(<span data-i18n="total_recharge">Всего пополнено:</span> ${{ number_format($totalRecharge, 2) }})</span>
                </div>
                <a href="{{ route('funds.index') }}" class="btn-add-funds">
                    <i class="fa-solid fa-plus-circle"></i> <span data-i18n="add_funds">Пополнить</span>
                </a>
            </div>

            <div class="header-right">
                <!-- 1-Hour Security Session Countdown Pill -->
                <div class="session-timer-badge" title="Security Timeout: 1-hour session automatically expires and logs out">
                    <i class="fa-solid fa-shield-halved"></i> <span id="session-countdown-text">60:00</span>
                </div>

                <!-- 🌐 Multilingual Language Switcher (RU default, ZH, EN) -->
                <div class="lang-dropdown" id="lang-dropdown">
                    <button type="button" class="lang-dropdown-btn" onclick="toggleLangDropdown(event)">
                        <span id="current-lang-flag">🇷🇺</span> <span id="current-lang-label">Русский</span> <i class="fa-solid fa-chevron-down" style="font-size: 10px; margin-left: 2px;"></i>
                    </button>
                    <div class="lang-dropdown-menu">
                        <div class="lang-dropdown-item" onclick="selectLang('ru')">
                            <span>🇷🇺</span> Русский
                        </div>
                        <div class="lang-dropdown-item" onclick="selectLang('zh')">
                            <span>🇨🇳</span> 中文
                        </div>
                        <div class="lang-dropdown-item" onclick="selectLang('en')">
                            <span>🇺🇸</span> English
                        </div>
                    </div>
                </div>

                <!-- Day / Night Mode Switcher -->
                <button type="button" class="btn-theme-toggle" onclick="toggleTheme()" title="Toggle Day/Night Mode">
                    <i class="fa-solid fa-moon theme-toggle-icon"></i><span id="theme-label" data-i18n="night_mode"> Ночь</span>
                </button>

                <a href="{{ route('profile.index') }}" class="cart-header-btn" title="My Profile & Account Settings">
                    <i class="fa-solid fa-user-gear"></i> <span data-i18n="profile">Профиль</span>
                </a>

                <a href="{{ route('cart.index') }}" class="cart-header-btn" title="View Cart">
                    <i class="fa-solid fa-cart-shopping" style="color: var(--gold-primary);"></i> <span data-i18n="cart">Корзина</span>
                    <span id="cart-badge" class="cart-count-badge" style="{{ $cartCount > 0 ? '' : 'display:none;' }}">
                        {{ $cartCount }}
                    </span>
                </a>

                <a href="{{ route('profile.index') }}" class="cart-header-btn" title="Settings">
                    <i class="fa-solid fa-gear"></i>
                </a>
                <a href="{{ route('logout') }}" class="cart-header-btn" title="Logout">
                    <i class="fa-solid fa-arrow-right-from-bracket"></i> <span data-i18n="logout">Выход</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Navigation Bar -->
    <nav class="main-nav">
        <div class="container">
            <div class="nav-tabs-wrapper">
                <a href="{{ route('news.index') }}" class="nav-tab-item {{ request()->routeIs('news.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-newspaper"></i> <span data-i18n="nav_news">Новости</span>
                </a>
                <a href="{{ route('marketplace.index') }}" class="nav-tab-item {{ request()->routeIs('marketplace.*') || request()->routeIs('cvv2.*') || request()->routeIs('shop.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-credit-card"></i> <span data-i18n="nav_cards">Карты (CC)</span>
                </a>
                <a href="{{ route('wholesale.index') }}" class="nav-tab-item {{ request()->routeIs('wholesale.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-box-archive"></i> <span data-i18n="nav_wholesale">Опт (Пакеты)</span>
                </a>
                <a href="{{ route('orders.index') }}" class="nav-tab-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-receipt"></i> <span data-i18n="nav_orders">Заказы</span>
                </a>
                <a href="{{ route('funds.index') }}" class="nav-tab-item {{ request()->routeIs('funds.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wallet"></i> <span data-i18n="nav_funds">Пополнение</span>
                </a>
                <a href="{{ route('commission.index') }}" class="nav-tab-item {{ request()->routeIs('commission.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i> <span data-i18n="nav_commission">Партнерка</span>
                </a>
                <a href="{{ route('tickets.index') }}" class="nav-tab-item {{ request()->routeIs('tickets.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-headset"></i> <span data-i18n="nav_tickets">Поддержка</span>
                </a>
                <a href="{{ route('profile.index') }}" class="nav-tab-item {{ request()->routeIs('profile.*') ? 'active' : '' }}" style="flex: 0 0 60px;" title="Profile & Settings">
                    <i class="fa-solid fa-gear"></i>
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content Slot -->
    <main class="container" style="padding-top: 20px; padding-bottom: 40px;">
        @if(session('success'))
            <div class="alert-box alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Toast Notifications Placeholder -->
    <div id="toast-container" class="toast-container"></div>

    <script src="{{ asset('js/i18n.js') }}"></script>
    <script src="{{ asset('js/marketplace.js') }}"></script>
    <script>
        function toggleLangDropdown(e) {
            e.stopPropagation();
            document.getElementById('lang-dropdown').classList.toggle('active');
        }
        function selectLang(lang) {
            setLanguage(lang);
            document.getElementById('lang-dropdown').classList.remove('active');
        }
        document.addEventListener('click', () => {
            const dropdown = document.getElementById('lang-dropdown');
            if (dropdown) dropdown.classList.remove('active');
        });

        // 🛡️ 1-Hour Strict Session Auto-Logout Timer & Reload
        let remainingSeconds = {{ $sessionRemainingSeconds }};
        const countdownElement = document.getElementById('session-countdown-text');

        function formatSessionTime(secs) {
            const m = Math.floor(secs / 60);
            const s = secs % 60;
            return `${m.toString().padStart(2, '0')}:${s.toString().padStart(2, '0')}`;
        }

        if (countdownElement) {
            countdownElement.textContent = formatSessionTime(remainingSeconds);
        }

        const sessionInterval = setInterval(() => {
            remainingSeconds--;
            if (countdownElement) {
                countdownElement.textContent = formatSessionTime(Math.max(0, remainingSeconds));
            }

            if (remainingSeconds <= 0) {
                clearInterval(sessionInterval);
                alert('Security Timeout: Your 1-hour session has expired. You will now be redirected to log in again.');
                window.location.href = "{{ route('logout') }}";
            }
        }, 1000);
    </script>
    @stack('scripts')
</body>
</html>
