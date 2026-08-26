@extends('layouts.app')

@section('title', 'Cards Marketplace (Payate CC)')

@section('content')
@if(!$isActivated)
    <!-- 🔒 2-COLUMN SIDE-BY-SIDE ACTIVATION VAULT & BONUS TIERS LAYOUT -->
    <style>
        .vault-split-container {
            display: grid;
            grid-template-columns: 1.2fr 0.8fr;
            gap: 24px;
            max-width: 1140px;
            margin: 16px auto 40px auto;
            align-items: start;
        }

        @media (max-width: 960px) {
            .vault-split-container {
                grid-template-columns: 1fr;
            }
        }

        /* 🌓 Theme-Adaptive Colors (Night vs Day Mode) */
        /* 🌑 Night Mode */
        [data-theme="dark"] .vault-card-container {
            background: rgba(9, 14, 26, 0.96);
            border: 1.5px solid rgba(245, 158, 11, 0.45);
            box-shadow: 0 0 50px rgba(245, 158, 11, 0.14), 0 25px 50px -12px rgba(0, 0, 0, 0.9);
        }
        [data-theme="dark"] .bonus-side-container {
            background: rgba(9, 14, 26, 0.96);
            border: 1.5px solid rgba(245, 158, 11, 0.35);
            box-shadow: 0 0 40px rgba(245, 158, 11, 0.1);
        }
        [data-theme="dark"] .vault-panel-box {
            background: rgba(5, 9, 17, 0.94);
            border: 1.5px dashed rgba(245, 158, 11, 0.35);
        }
        [data-theme="dark"] .vault-feature-box {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        [data-theme="dark"] .vault-feature-box:hover {
            border-color: rgba(245, 158, 11, 0.4);
            background: rgba(245, 158, 11, 0.05);
        }
        [data-theme="dark"] .bonus-row-item {
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.07);
        }
        [data-theme="dark"] .bonus-row-item:hover {
            border-color: #F59E0B;
            background: rgba(245, 158, 11, 0.06);
        }
        [data-theme="dark"] .theme-title { color: #FFFFFF; }
        [data-theme="dark"] .theme-sub { color: #94A3B8; }
        [data-theme="dark"] .btn-vault-help-btn {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #E2E8F0;
        }

        /* ☀️ Day Mode (Light Pearl White with Amber Accents) */
        [data-theme="light"] .vault-card-container {
            background: #FFFFFF;
            border: 1.5px solid rgba(217, 119, 6, 0.45);
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.08), 0 0 30px rgba(245, 158, 11, 0.18);
        }
        [data-theme="light"] .bonus-side-container {
            background: #FFFFFF;
            border: 1.5px solid rgba(217, 119, 6, 0.35);
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.06);
        }
        [data-theme="light"] .vault-panel-box {
            background: #F8FAFC;
            border: 1.5px dashed rgba(217, 119, 6, 0.4);
        }
        [data-theme="light"] .vault-feature-box {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
        }
        [data-theme="light"] .vault-feature-box:hover {
            border-color: #F59E0B;
            background: #FFFBEB;
        }
        [data-theme="light"] .bonus-row-item {
            background: #F8FAFC;
            border: 1px solid #E2E8F0;
        }
        [data-theme="light"] .bonus-row-item:hover {
            border-color: #F59E0B;
            background: #FFFBEB;
        }
        [data-theme="light"] .theme-title { color: #0F172A; }
        [data-theme="light"] .theme-sub { color: #475569; }
        [data-theme="light"] .btn-vault-help-btn {
            background: #F1F5F9;
            border: 1px solid #CBD5E1;
            color: #1E293B;
        }
        [data-theme="light"] .btn-vault-help-btn:hover {
            background: #E2E8F0;
            color: #0F172A;
        }

        /* Container & Cards Styling */
        .vault-card-container {
            border-radius: 20px;
            padding: 30px 26px;
            backdrop-filter: blur(20px);
        }

        .bonus-side-container {
            border-radius: 20px;
            padding: 26px 22px;
            backdrop-filter: blur(20px);
        }

        .vault-lock-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: rgba(245, 158, 11, 0.12);
            border: 1.5px solid rgba(245, 158, 11, 0.45);
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 10.5px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            color: #F59E0B;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            margin-bottom: 12px;
        }

        .vault-main-heading {
            font-size: 28px;
            font-weight: 900;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            line-height: 1.2;
        }

        .vault-heading-icon {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #040711;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 19px;
            box-shadow: 0 0 20px rgba(245, 158, 11, 0.5);
            flex-shrink: 0;
        }

        .vault-intro-desc {
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 22px;
        }

        /* 1. Header Box */
        .vault-header-block {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 20px;
        }

        .vault-lock-cube {
            width: 54px;
            height: 54px;
            background: linear-gradient(135deg, #F59E0B 0%, #B45309 100%);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: #040711;
            box-shadow: 0 0 25px rgba(245, 158, 11, 0.5);
            flex-shrink: 0;
        }

        .vault-step-tag {
            font-family: 'JetBrains Mono', monospace;
            font-size: 10.5px;
            font-weight: 800;
            color: #94A3B8;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }

        .vault-price-row {
            display: flex;
            align-items: baseline;
            gap: 6px;
        }

        .vault-big-price {
            font-size: 32px;
            font-weight: 900;
            color: #F59E0B;
            font-family: 'JetBrains Mono', monospace;
            line-height: 1;
        }

        .vault-price-caption {
            font-size: 12px;
            color: #64748B;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }

        /* 2. Latest Deposit Status Box */
        .vault-panel-box {
            border-radius: 12px;
            padding: 14px 16px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
        }

        .deposit-status-icon-wrap {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .deposit-status-icon-wrap.rejected { background: rgba(239, 68, 68, 0.15); border: 1.5px solid #EF4444; color: #EF4444; }
        .deposit-status-icon-wrap.pending { background: rgba(245, 158, 11, 0.15); border: 1.5px solid #F59E0B; color: #F59E0B; }
        .deposit-status-icon-wrap.none { background: rgba(100, 116, 139, 0.15); border: 1.5px solid #64748B; color: #94A3B8; }

        .deposit-tag-badge {
            font-size: 9.5px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            padding: 2px 6px;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .deposit-tag-badge.rejected { background: rgba(239, 68, 68, 0.2); color: #EF4444; }
        .deposit-tag-badge.pending { background: rgba(245, 158, 11, 0.2); color: #F59E0B; }
        .deposit-tag-badge.none { background: rgba(100, 116, 139, 0.2); color: #94A3B8; }

        /* 3. Benefit 2x2 Grid */
        .vault-features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 22px;
        }

        .vault-feature-box {
            border-radius: 10px;
            padding: 12px 14px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            transition: all 0.2s ease;
        }

        .feature-icon-circle {
            width: 28px;
            height: 28px;
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            flex-shrink: 0;
            background: rgba(245, 158, 11, 0.12);
        }

        .feature-item-title {
            font-size: 12px;
            font-weight: 800;
            margin-bottom: 2px;
        }

        .feature-item-desc {
            font-size: 11px;
            line-height: 1.4;
        }

        /* 4. Action CTA Buttons */
        .vault-cta-row {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 10px;
            margin-bottom: 16px;
        }

        .btn-deposit-activate-btn {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            color: #040711;
            font-weight: 900;
            font-size: 13.5px;
            font-family: 'JetBrains Mono', monospace;
            letter-spacing: 0.5px;
            padding: 14px 20px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            box-shadow: 0 0 30px rgba(245, 158, 11, 0.45);
            transition: all 0.2s ease;
            text-transform: uppercase;
        }
        .btn-deposit-activate-btn:hover {
            background: linear-gradient(135deg, #FBBF24 0%, #F59E0B 100%);
            transform: translateY(-2px);
            box-shadow: 0 0 45px rgba(245, 158, 11, 0.7);
            color: #040711;
        }

        .btn-vault-help-btn {
            font-weight: 800;
            font-size: 12.5px;
            font-family: 'JetBrains Mono', monospace;
            padding: 14px 16px;
            border-radius: 10px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }

        .vault-micro-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 10.5px;
            color: #64748B;
            font-family: 'JetBrains Mono', monospace;
            flex-wrap: wrap;
        }

        /* 🎁 Right Side: Compact Vertical Bonus List */
        .bonus-side-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .bonus-side-title {
            font-size: 16px;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .bonus-side-desc {
            font-size: 12px;
            margin-bottom: 16px;
            line-height: 1.45;
        }

        .bonus-list-wrap {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 480px;
            overflow-y: auto;
            padding-right: 4px;
        }

        .bonus-row-item {
            border-radius: 10px;
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            transition: all 0.2s ease;
        }

        .bonus-row-left {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bonus-tier-symbol {
            font-size: 16px;
            line-height: 1;
        }

        .bonus-deposit-amount {
            font-size: 14px;
            font-weight: 900;
            font-family: 'JetBrains Mono', monospace;
        }

        .bonus-extra-tag {
            font-size: 11px;
            font-weight: 800;
            font-family: 'JetBrains Mono', monospace;
            padding: 2px 7px;
            border-radius: 5px;
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid #10B981;
            color: #10B981;
        }

        .bonus-total-got {
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            color: #94A3B8;
        }
        [data-theme="light"] .bonus-total-got { color: #64748B; }

        .btn-side-recharge {
            background: var(--bg-input);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            font-weight: 800;
            font-size: 11px;
            font-family: 'JetBrains Mono', monospace;
            padding: 5px 10px;
            border-radius: 6px;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.15s ease;
        }
        .btn-side-recharge:hover {
            background: #F59E0B;
            color: #040711;
            border-color: #F59E0B;
        }

        .referral-side-banner {
            background: rgba(16, 185, 129, 0.08);
            border: 1px solid rgba(16, 185, 129, 0.25);
            border-radius: 10px;
            padding: 12px;
            margin-top: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>

    <div class="vault-split-container">
        <!-- 👈 LEFT COLUMN: Primary Activation Vault Card -->
        <div class="vault-card-container">
            <div class="vault-lock-badge">
                <i class="fa-solid fa-lock"></i> MEMBERS VAULT - LOCKED
            </div>

            <h1 class="vault-main-heading theme-title">
                <span class="vault-heading-icon"><i class="fa-solid fa-lock"></i></span>
                <span>{{ $cryptoSettings->activation_title ?? 'Activate Your Account' }}</span>
            </h1>

            <p class="vault-intro-desc theme-sub">
                {{ $cryptoSettings->activation_subtitle ?? "The marketplace is reserved for verified members. Make a one-time minimum deposit of $" . number_format($minDeposit, 2) . " to unlock the vault — funds stay yours, ready to spend." }}
            </p>

            <!-- 1. One-Time Activation Header Box -->
            <div class="vault-header-block">
                <div class="vault-lock-cube">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <div>
                    <div class="vault-step-tag">ONE-TIME ACTIVATION</div>
                    <div class="vault-price-row">
                        <span class="vault-big-price">${{ number_format($minDeposit, 2) }}</span>
                        <span class="vault-price-caption">minimum</span>
                    </div>
                    <div style="font-size: 11.5px; color: #CBD5E1; margin-top: 3px; display: flex; align-items: center; gap: 5px;">
                        <i class="fa-solid fa-bolt" style="color: #F59E0B;"></i> Credited instantly to your wallet — no fees, no expiry
                    </div>
                </div>
            </div>

            <!-- 2. Latest Deposit Status Box -->
            <div class="vault-panel-box">
                <div style="display: flex; align-items: center; gap: 12px;">
                    <div class="deposit-status-icon-wrap {{ ($latestDeposit && $latestDeposit->status === 'rejected') ? 'rejected' : (($latestDeposit && $latestDeposit->status === 'pending') ? 'pending' : 'none') }}">
                        @if($latestDeposit && $latestDeposit->status === 'rejected')
                            <i class="fa-solid fa-xmark"></i>
                        @elseif($latestDeposit && $latestDeposit->status === 'pending')
                            <i class="fa-solid fa-hourglass-half"></i>
                        @else
                            <i class="fa-solid fa-circle-notch"></i>
                        @endif
                    </div>

                    <div>
                        <div style="display: flex; align-items: center; gap: 6px;">
                            <span style="font-size: 10px; font-weight: 800; color: #64748B; font-family: 'JetBrains Mono', monospace; letter-spacing: 1px;">LATEST DEPOSIT</span>
                            @if($latestDeposit && $latestDeposit->status === 'rejected')
                                <span class="deposit-tag-badge rejected">REJECTED</span>
                            @elseif($latestDeposit && $latestDeposit->status === 'pending')
                                <span class="deposit-tag-badge pending">PENDING</span>
                            @else
                                <span class="deposit-tag-badge none">INACTIVE</span>
                            @endif
                        </div>

                        <div style="font-size: 13px; font-weight: 800; margin-top: 2px;">
                            @if($latestDeposit && $latestDeposit->status === 'rejected')
                                <span style="color: #EF4444;"><i class="fa-solid fa-circle-xmark"></i> Deposit unsuccessful</span>
                            @elseif($latestDeposit && $latestDeposit->status === 'pending')
                                <span style="color: #F59E0B;"><i class="fa-solid fa-hourglass-half"></i> Deposit under review</span>
                            @else
                                <span style="color: #64748B;"><i class="fa-solid fa-circle-info"></i> No active deposit recorded</span>
                            @endif
                        </div>

                        <div style="font-size: 11px; color: #64748B; margin-top: 2px;">
                            @if($latestDeposit && $latestDeposit->status === 'rejected')
                                Last attempt was not completed. Please try again.
                            @elseif($latestDeposit && $latestDeposit->status === 'pending')
                                Waiting for confirmation. Unlocks automatically.
                            @else
                                Please recharge ${{ number_format($minDeposit, 2) }} or more to unlock the live cards.
                            @endif
                        </div>
                    </div>
                </div>

                <div style="text-align: right;">
                    <span style="font-size: 16px; font-weight: 900; color: #F59E0B; font-family: 'JetBrains Mono', monospace;">${{ number_format($minDeposit, 2) }}</span>
                    <div style="font-size: 9.5px; color: #64748B; font-family: 'JetBrains Mono', monospace; margin-top: 4px;">
                        <i class="fa-solid fa-rotate"></i> auto-refresh
                    </div>
                </div>
            </div>

            <!-- 3. Benefit 2x2 Grid (Dynamic from Admin Panel) -->
            <div class="vault-features-grid">
                @php
                    $perksList = $cryptoSettings->getPerks();
                @endphp
                @foreach($perksList as $p)
                    <div class="vault-feature-box">
                        <div class="feature-icon-circle" style="color: {{ $p['color'] ?? '#F59E0B' }};">
                            <i class="fa-solid {{ $p['icon'] ?? 'fa-circle-check' }}"></i>
                        </div>
                        <div>
                            <div class="feature-item-title theme-title">{{ $p['title'] }}</div>
                            <div class="feature-item-desc theme-sub">{{ $p['desc'] }}</div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- 4. CTA Buttons -->
            <div class="vault-cta-row">
                <a href="{{ route('funds.index') }}" class="btn-deposit-activate-btn">
                    <i class="fa-solid fa-credit-card"></i> <span>Deposit Now & Activate</span> <i class="fa-solid fa-arrow-right"></i>
                </a>
                <a href="{{ route('tickets.index') }}" class="btn-vault-help-btn">
                    <i class="fa-solid fa-headset"></i> <span>Need Help?</span>
                </a>
            </div>

            <!-- 5. Footer Micro Tags -->
            <div class="vault-micro-footer">
                <span><i class="fa-solid fa-lock"></i> Encrypted</span>
                <span>•</span>
                <span><i class="fa-solid fa-bolt"></i> Instant Credit</span>
                <span>•</span>
                <span><i class="fa-brands fa-bitcoin"></i> Crypto Accepted</span>
                <span>•</span>
                <span><i class="fa-solid fa-bullseye"></i> One-Time Only</span>
            </div>
        </div>

        <!-- 👉 RIGHT COLUMN: Dynamic Side-by-Side Deposit Bonus Tiers List -->
        <div class="bonus-side-container">
            <div class="bonus-side-header">
                <div class="bonus-side-title theme-title">
                    <i class="fa-solid fa-gift" style="color: #F59E0B;"></i> <span>Deposit Bonus Tiers</span>
                </div>
                <span style="font-size: 10.5px; font-weight: 800; font-family: monospace; color: #10B981; background: rgba(16,185,129,0.12); padding: 2px 7px; border-radius: 4px;">VIP BOOST</span>
            </div>

            <p class="bonus-side-desc theme-sub">
                Recharge any amount to get direct extra bonus credits added into your wallet balance:
            </p>

            <div class="bonus-list-wrap">
                @php
                    $bonusTiersList = $cryptoSettings->getBonusTiers();
                @endphp
                @foreach($bonusTiersList as $t)
                    <div class="bonus-row-item">
                        <div class="bonus-row-left">
                            <span class="bonus-tier-symbol">{{ $t['icon'] ?? '⭐' }}</span>
                            <div>
                                <div class="bonus-deposit-amount theme-title">${{ number_format($t['deposit'], 2) }}</div>
                                <div class="bonus-total-got">Total: ${{ number_format($t['total'], 2) }}</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span class="bonus-extra-tag">+${{ number_format($t['bonus'], 2) }}</span>
                            <a href="{{ route('funds.index') }}" class="btn-side-recharge">Deposit</a>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Referral Bonus Info Box (50% Commission) -->
            <div class="referral-side-banner">
                <i class="fa-solid fa-users" style="color: #10B981; font-size: 18px; flex-shrink: 0;"></i>
                <div style="font-size: 11.5px; line-height: 1.4;">
                    <strong>Affiliate Reward:</strong> Earn <strong style="color: #10B981;">{{ number_format($cryptoSettings->referral_commission_percent ?? 50.00, 0) }}%</strong> lifetime commission on all deposits made by your invited referrals!
                </div>
            </div>
        </div>
    </div>

@else
    <!-- UNLOCKED: FULL MARKETPLACE TABLE & SEARCH FILTERS -->
    <!-- Filter Section (Matches Screenshot Layout Exactly) -->
    <section class="filter-section">
        <form id="filter-form" action="{{ route('marketplace.index') }}" method="GET">
            <div class="filter-grid">
                <!-- Column 1: Bins, Zips, Bank -->
                <div class="filter-card">
                    <div class="form-group">
                        <label class="form-label" for="bins" data-i18n="filter_bins">БИНы (BIN)</label>
                        <textarea name="bins" id="bins" class="form-control" data-i18n-placeholder="filter_bins_ph" placeholder="Используйте перенос строки для разделения нескольких записей.">{{ request('bins') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="zips" data-i18n="filter_zips">Индексы (ZIP)</label>
                        <textarea name="zips" id="zips" class="form-control" data-i18n-placeholder="filter_zips_ph" placeholder="Используйте перенос строки для разделения нескольких записей.">{{ request('zips') }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="bank" data-i18n="filter_bank">Банк</label>
                        <select name="bank" id="bank" class="form-select">
                            <option value="All" data-i18n="all_option">Все</option>
                            @foreach($banks as $b)
                                <option value="{{ $b }}" {{ request('bank') == $b ? 'selected' : '' }}>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Column 2: Worldwide Countries & Toggles -->
                <div class="filter-card">
                    <div class="form-group">
                        <label class="form-label" for="country" data-i18n="filter_country">Страна</label>
                        <select name="country" id="country" class="form-select">
                            <option value="All" data-i18n="all_option">Все страны (Worldwide)</option>
                            @foreach($countries as $c)
                                <option value="{{ $c['code'] }}" {{ request('country') == $c['code'] ? 'selected' : '' }}>
                                    {{ $c['flag'] }} {{ $c['name'] }} ({{ $c['code'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="toggle-grid">
                        <div class="toggle-item">
                            <span class="toggle-label" data-i18n="filter_zip_toggle">ZIP</span>
                            <label class="switch">
                                <input type="checkbox" name="has_zip" value="1" {{ request('has_zip') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="toggle-item">
                            <span class="toggle-label" data-i18n="filter_address_toggle">Полный адрес</span>
                            <label class="switch">
                                <input type="checkbox" name="has_address" value="1" {{ request('has_address') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="toggle-item">
                            <span class="toggle-label" data-i18n="filter_phone_toggle">Телефон</span>
                            <label class="switch">
                                <input type="checkbox" name="has_phone" value="1" {{ request('has_phone') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="toggle-item">
                            <span class="toggle-label" data-i18n="filter_email_toggle">Email / Почта</span>
                            <label class="switch">
                                <input type="checkbox" name="has_mail" value="1" {{ request('has_mail') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="toggle-item">
                            <span class="toggle-label" data-i18n="filter_dob_toggle">Дата рождения</span>
                            <label class="switch">
                                <input type="checkbox" name="has_dob" value="1" {{ request('has_dob') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>

                        <div class="toggle-item">
                            <span class="toggle-label" data-i18n="filter_ssn_toggle">SSN / ИНН</span>
                            <label class="switch">
                                <input type="checkbox" name="has_ssn" value="1" {{ request('has_ssn') ? 'checked' : '' }}>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Column 3: Brand, Credit/Debit, Base, Price Range -->
                <div class="filter-card">
                    <div class="form-group">
                        <label class="form-label" for="brand" data-i18n="filter_brand">Тип карты / Платежная система</label>
                        <select name="brand" id="brand" class="form-select">
                            <option value="All" data-i18n="all_option">Все</option>
                            @foreach($brands as $br)
                                <option value="{{ $br }}" {{ request('brand') == $br ? 'selected' : '' }}>{{ $br }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="type" data-i18n="filter_type">Категория (Credit / Debit)</label>
                        <select name="type" id="type" class="form-select">
                            <option value="All" data-i18n="all_option">Все</option>
                            @foreach($types as $tp)
                                <option value="{{ $tp }}" {{ request('type') == $tp ? 'selected' : '' }}>{{ $tp }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="base" data-i18n="filter_base">База</label>
                        <select name="base" id="base" class="form-select">
                            <option value="All" data-i18n="all_option">Все</option>
                            @foreach($bases as $bs)
                                <option value="{{ $bs }}" {{ request('base') == $bs ? 'selected' : '' }}>{{ $bs }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" data-i18n="filter_price">Диапазон цен ($)</label>
                        <div class="price-range-inputs">
                            <input type="number" name="price_min" class="form-control" placeholder="Мин ($)" data-i18n-placeholder="filter_min_price" value="{{ request('price_min') }}" step="0.5">
                            <span style="color: var(--text-muted);">-</span>
                            <input type="number" name="price_max" class="form-control" placeholder="Макс ($)" data-i18n-placeholder="filter_max_price" value="{{ request('price_max') }}" step="0.5">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Row -->
            <div class="filter-actions">
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-magnifying-glass"></i> <span data-i18n="btn_search">Поиск карт</span>
                </button>
                <a href="{{ route('marketplace.index') }}" class="btn-reset">
                    <i class="fa-solid fa-rotate-left"></i> <span data-i18n="btn_reset">Сбросить фильтры</span>
                </a>
            </div>
        </form>
    </section>

    <!-- Cards Data Table Card -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 36px;" class="text-center">
                            <input type="checkbox" id="select-all" class="table-checkbox" onclick="toggleSelectAll(this)">
                        </th>
                        <th data-i18n="th_type">Тип</th>
                        <th data-i18n="th_bin">БИН</th>
                        <th data-i18n="th_exp">Срок</th>
                        <th data-i18n="th_category">Категория</th>
                        <th data-i18n="th_country">Страна</th>
                        <th data-i18n="th_state">Штат</th>
                        <th data-i18n="th_city">Город</th>
                        <th data-i18n="th_zip">ZIP</th>
                        <th class="text-center" data-i18n="th_address">Адрес</th>
                        <th class="text-center" data-i18n="th_phone">Тел</th>
                        <th class="text-center" data-i18n="th_email">Email</th>
                        <th class="text-center" data-i18n="th_ssn">SSN</th>
                        <th class="text-center" data-i18n="th_dob">Д/Р</th>
                        <th data-i18n="th_bank">Банк</th>
                        <th data-i18n="th_base">База</th>
                        <th class="text-center" data-i18n="th_refund">Возврат</th>
                        <th data-i18n="th_price">Цена</th>
                        <th class="text-center" data-i18n="th_action">Купить</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $cartSession = session('cart', []);
                    @endphp
                    @forelse($cards as $card)
                        @php
                            $inCart = isset($cartSession[$card->id]);
                        @endphp
                        <tr id="card-row-{{ $card->id }}" class="{{ $inCart ? 'row-in-cart' : '' }}">
                            <td class="text-center">
                                <input type="checkbox" class="table-checkbox card-select-cb card-row-checkbox" value="{{ $card->id }}" onchange="updateSelectedCount()">
                            </td>
                            <td>
                                <span class="brand-badge brand-{{ strtolower($card->brand) }}">
                                    {{ $card->brand }}
                                </span>
                            </td>
                            <td style="font-family: monospace; font-weight: 700; color: var(--gold-primary);">
                                {{ $card->bin }}******
                            </td>
                            <td style="font-family: monospace; color: #CBD5E1;">
                                {{ $card->exp_date ?? '12/28' }}
                            </td>
                            <td>
                                <span class="type-badge">{{ $card->type }}</span>
                            </td>
                            <td>
                                <span title="{{ $card->country_name }}">
                                    {{ \App\Helpers\CountryHelper::getFlag($card->country_code) }} {{ $card->country_code }}
                                </span>
                            </td>
                            <td>{{ $card->state }}</td>
                            <td>{{ $card->city }}</td>
                            <td style="font-family: monospace;">{{ $card->zip }}</td>
                            <td class="text-center">
                                <span class="status-icon {{ $card->has_address ? 'status-yes' : 'status-no' }}">{{ $card->has_address ? '✓' : '✕' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="status-icon {{ $card->has_phone ? 'status-yes' : 'status-no' }}">{{ $card->has_phone ? '✓' : '✕' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="status-icon {{ $card->has_mail ? 'status-yes' : 'status-no' }}">{{ $card->has_mail ? '✓' : '✕' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="status-icon {{ $card->has_ssn ? 'status-yes' : 'status-no' }}">{{ $card->has_ssn ? '✓' : '✕' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="status-icon {{ $card->has_dob ? 'status-yes' : 'status-no' }}">{{ $card->has_dob ? '✓' : '✕' }}</span>
                            </td>
                            <td style="color: var(--text-primary); font-size: 12px; font-weight: 600;">
                                {{ $card->bank }}
                            </td>
                            <td style="color: var(--text-muted); font-size: 11px; font-family: monospace; font-weight: 600;">
                                {{ $card->base_name }}
                            </td>
                            <td class="text-center">
                                <span class="refundable-badge" style="font-size: 11px; font-weight: 800; color: #059669;">Yes</span>
                            </td>
                            <td>
                                <div class="price-display">
                                    <span class="price-c">C$ {{ number_format($card->price_c, 2) }}</span>
                                    <span class="price-unc">/ UNC: ${{ number_format($card->price_unc, 2) }}</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" 
                                        class="btn-buy-cart {{ $inCart ? 'in-cart' : '' }}" 
                                        data-card-id="{{ $card->id }}" 
                                        onclick="addToCart({{ $card->id }}, this)" 
                                        title="{{ $inCart ? 'In Shopping Cart' : 'Add to Shopping Cart' }}">
                                    @if($inCart)
                                        <i class="fa-solid fa-check"></i> <span data-i18n="btn_in_cart">В корзине</span>
                                    @else
                                        <i class="fa-solid fa-cart-shopping"></i> <span data-i18n="btn_add">В корзину</span>
                                    @endif
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="19" style="text-align: center; padding: 36px; color: var(--text-muted);">
                                <i class="fa-solid fa-box-open" style="font-size: 28px; margin-bottom: 8px; display: block; opacity: 0.4;"></i>
                                <span data-i18n="no_cards_found">Карты по указанным фильтрам не найдены.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Bulk Action & Compact Pagination Bar -->
        <div class="bulk-bar">
            <div style="display: flex; align-items: center; gap: 12px;">
                <span style="font-size: 12px; color: var(--text-muted);">
                    <span data-i18n="selected_cards">Выбрано карт:</span> <strong id="selected-count" style="color: #059669;">0</strong>
                </span>
                <button type="button" id="btn-bulk-add" class="btn-search" style="padding: 5px 12px; font-size: 12px; opacity: 0.5;" disabled onclick="addSelectedToCart()">
                    <i class="fa-solid fa-cart-arrow-down"></i> <span data-i18n="btn_bulk_add">Добавить выбранные в корзину</span>
                </button>
            </div>

            <div>
                {{ $cards->links('vendor.pagination.custom') }}
            </div>
        </div>
    </div>
@endif
@endsection
