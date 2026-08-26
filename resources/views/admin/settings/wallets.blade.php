@extends('admin.layout')

@section('title', 'Admin Security Credentials, Wallets & Telegram')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Payate CC - Security Credentials, Wallets & Telegram Bot</h2>
    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 3px;">
        Manage Master Admin 3-factor passwords, configure receiving crypto wallets, upload custom QR Barcode images, and link Telegram for 1-click approvals.
    </p>
</div>

<div class="filter-card" style="max-width: 850px;">
    <form action="{{ route('admin.wallets.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <!-- 🔒 Account Activation & Minimum Deposit Requirement Configuration -->
        <div style="background: rgba(245, 158, 11, 0.07); border: 1.5px solid rgba(245, 158, 11, 0.4); border-radius: 12px; padding: 22px; margin-bottom: 26px; box-shadow: 0 0 25px rgba(245, 158, 11, 0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 16px; font-weight: 800; color: #F59E0B; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-lock" style="font-size: 20px;"></i> Account Activation, Bonus Tiers & Referral Controls
                </h3>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; color: #F59E0B; cursor: pointer;">
                        <input type="checkbox" name="activation_enabled" value="1" {{ ($settings->activation_enabled ?? true) ? 'checked' : '' }}>
                        <span>Vault Lock Active</span>
                    </label>
                    <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 800; color: #10B981; cursor: pointer;">
                        <input type="checkbox" name="bonus_enabled" value="1" {{ ($settings->bonus_enabled ?? true) ? 'checked' : '' }}>
                        <span>Bonus Tiers Active</span>
                    </label>
                </div>
            </div>
            
            <p style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 16px;">
                Manually control everything on the <strong>Option 2 (Cards Marketplace)</strong> Activation screen, custom perks, bonus amounts, and 50% referral program.
            </p>

            <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 16px; align-items: start; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="color: #F59E0B; font-weight: 800;">Activation Header Title</label>
                    <input type="text" name="activation_title" class="form-control" value="{{ $settings->activation_title ?? 'Activate Your Account' }}" required>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="color: #F59E0B; font-weight: 800;">Minimum Deposit ($ USD)</label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #F59E0B; font-weight: 900; font-size: 16px;">$</span>
                        <input type="number" step="0.5" id="min_deposit_input" name="min_deposit" class="form-control" value="{{ $settings->min_deposit ?? 10.00 }}" style="padding-left: 28px; font-size: 16px; font-weight: 900; color: #F59E0B; font-family: monospace;" required>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="color: #10B981; font-weight: 800;">Referral Commission (% Percent)</label>
                    <div style="position: relative;">
                        <input type="number" step="1" name="referral_commission_percent" class="form-control" value="{{ $settings->referral_commission_percent ?? 50.00 }}" style="padding-right: 28px; font-size: 16px; font-weight: 900; color: #10B981; font-family: monospace;" required>
                        <span style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); color: #10B981; font-weight: 900; font-size: 16px;">%</span>
                    </div>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Activation Subtitle / Instructions</label>
                <textarea name="activation_subtitle" class="form-control" rows="2">{{ $settings->activation_subtitle ?? 'The marketplace is reserved for verified members. Make a one-time minimum deposit of $10.00 to unlock the vault — funds stay yours, ready to spend.' }}</textarea>
            </div>

            <div style="margin-bottom: 18px;">
                <label class="form-label" style="font-size: 11.5px; color: var(--text-muted);">Quick Minimum Deposit Presets:</label>
                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn-reset" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;" onclick="document.getElementById('min_deposit_input').value='5.00'">$5.00</button>
                    <button type="button" class="btn-reset" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700; border-color: #F59E0B; color: #F59E0B;" onclick="document.getElementById('min_deposit_input').value='10.00'">$10.00 (Default)</button>
                    <button type="button" class="btn-reset" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;" onclick="document.getElementById('min_deposit_input').value='15.00'">$15.00</button>
                    <button type="button" class="btn-reset" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;" onclick="document.getElementById('min_deposit_input').value='20.00'">$20.00</button>
                    <button type="button" class="btn-reset" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;" onclick="document.getElementById('min_deposit_input').value='25.00'">$25.00</button>
                    <button type="button" class="btn-reset" style="padding: 4px 10px; font-size: 11.5px; font-weight: 700;" onclick="document.getElementById('min_deposit_input').value='50.00'">$50.00</button>
                </div>
            </div>

            <!-- 🎁 Side-by-Side 2-Column Section for Bonus Tiers & Community Perks -->
            <div style="display: grid; grid-template-columns: 1.15fr 0.85fr; gap: 16px; margin-top: 18px; align-items: start;">
                <!-- Column 1: 10 Deposit Bonus Tiers (Compact Scrollable Grid) -->
                <div style="background: rgba(5, 9, 17, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <h4 style="font-size: 13px; font-weight: 800; color: #F59E0B; margin: 0; display: flex; align-items: center; gap: 6px;">
                            <i class="fa-solid fa-gift"></i> 10 Deposit Bonus Tiers
                        </h4>
                        <span style="font-size: 10px; font-weight: 800; color: var(--text-muted); font-family: monospace;">SCROLLABLE</span>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; max-height: 270px; overflow-y: auto; padding-right: 4px;">
                        @php
                            $activeTiers = $settings->getBonusTiers();
                        @endphp
                        @foreach($activeTiers as $idx => $tier)
                            <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(245,158,11,0.25); border-radius: 6px; padding: 8px;">
                                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
                                    <input type="text" name="tier_icons[]" value="{{ $tier['icon'] ?? '⭐' }}" style="width: 26px; text-align: center; background: transparent; border: 1px solid var(--border-color); border-radius: 4px; font-size: 12px; padding: 1px 0;">
                                    <span style="font-size: 10px; font-weight: 800; color: var(--gold-primary);">Tier {{ $idx + 1 }}</span>
                                </div>
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 4px;">
                                    <div>
                                        <label style="font-size: 9.5px; color: var(--text-muted); display: block;">Deposit</label>
                                        <input type="number" step="1" name="tier_deposits[]" value="{{ $tier['deposit'] }}" class="form-control" style="font-size: 11px; padding: 2px 4px; font-weight: 800; font-family: monospace;">
                                    </div>
                                    <div>
                                        <label style="font-size: 9.5px; color: #10B981; display: block;">Bonus</label>
                                        <input type="number" step="0.5" name="tier_bonuses[]" value="{{ $tier['bonus'] }}" class="form-control" style="font-size: 11px; padding: 2px 4px; font-weight: 800; font-family: monospace; color: #10B981;">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Column 2: 4 Community Perks Configuration -->
                <div style="background: rgba(5, 9, 17, 0.6); border: 1px solid rgba(255,255,255,0.08); border-radius: 10px; padding: 14px;">
                    <h4 style="font-size: 13px; font-weight: 800; color: #38BDF8; margin-bottom: 10px; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-shield-halved"></i> 4 Community Perk Boxes
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                        @php
                            $activePerks = $settings->getPerks();
                        @endphp
                        @foreach($activePerks as $pIdx => $perk)
                            <div style="background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.08); border-radius: 6px; padding: 8px;">
                                <label style="font-size: 10px; font-weight: 800; color: var(--gold-primary);">Box {{ $pIdx + 1 }} Title</label>
                                <input type="text" name="perk_titles[]" value="{{ $perk['title'] }}" class="form-control" style="font-size: 11px; padding: 3px 6px; margin-bottom: 4px;">
                                <label style="font-size: 9.5px; color: var(--text-muted);">Description</label>
                                <input type="text" name="perk_descs[]" value="{{ $perk['desc'] }}" class="form-control" style="font-size: 10.5px; padding: 3px 6px;">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- 🛡️ Master Admin 3-Factor Hardened Credentials Configuration -->
        <div style="background: rgba(245, 158, 11, 0.05); border: 1.5px solid rgba(245, 158, 11, 0.3); border-radius: 12px; padding: 22px; margin-bottom: 26px;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--gold-primary); margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-shield-halved" style="font-size: 20px;"></i> Master Admin 3-Factor Security Credentials
            </h3>
            <p style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 18px;">
                To log into the Admin Control Desk, the system requires all 3 security layers in sequence (Username + Pass 1 ➔ Pass 2 ➔ Pass 3 PIN). You can update them below:
            </p>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label" style="color: var(--gold-primary);">Master Admin Username (Identifier)</label>
                    <input type="text" name="admin_username" class="form-control" value="{{ $settings->admin_username ?? 'payate_root_admin' }}" style="font-family: monospace; font-weight: 800;" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: var(--gold-primary);">Level 1 Primary Password</label>
                    <input type="text" name="admin_pass_1" class="form-control" value="{{ $settings->admin_pass_1 ?? 'Payate#Core@2026!Master' }}" style="font-family: monospace; font-weight: 800;" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label" style="color: #00E5FF;">Level 2 Secondary Security Key</label>
                    <input type="text" name="admin_pass_2" class="form-control" value="{{ $settings->admin_pass_2 ?? 'PayateSec#7788@Enclave' }}" style="font-family: monospace; font-weight: 800;" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: #10B981;">Level 3 Tertiary Master PIN (6-Digits)</label>
                    <input type="text" name="admin_pass_3" class="form-control" value="{{ $settings->admin_pass_3 ?? '992831' }}" maxlength="10" style="font-family: monospace; font-weight: 800; letter-spacing: 2px;" required>
                </div>
            </div>
        </div>

        <!-- 🤖 Telegram Instant Bot Notification & 1-Click Approval Settings -->
        <div style="background: rgba(14, 165, 233, 0.08); border: 1.5px solid rgba(14, 165, 233, 0.35); border-radius: 12px; padding: 22px; margin-bottom: 26px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 10px;">
                <h3 style="font-size: 16px; font-weight: 800; color: #0284C7; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-brands fa-telegram" style="font-size: 24px;"></i> Telegram Instant Notification & 1-Click Approval Bot
                </h3>
                <label style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 700; color: var(--text-primary); cursor: pointer;">
                    <input type="checkbox" name="telegram_notify_enabled" value="1" {{ ($settings->telegram_notify_enabled ?? true) ? 'checked' : '' }}>
                    <span>Enable Telegram Alerts</span>
                </label>
            </div>
            
            <p style="font-size: 12.5px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 16px;">
                When a user submits a deposit on the website, your Telegram Bot will instantly send a notification with a direct <strong>[ ✅ Approve ]</strong> button. Clicking that button will automatically credit the user's balance without needing to log in to the admin panel!
            </p>

            <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="color: #0284C7;">Telegram Bot Token (from @BotFather)</label>
                    <input type="text" name="telegram_bot_token" class="form-control" value="{{ $settings->telegram_bot_token }}" placeholder="e.g. 7123456789:ABCdefGhIJKlmNoPQRsTUVwxyZ" style="font-family: monospace;">
                    <span style="font-size: 10.5px; color: var(--text-muted); margin-top: 4px; display: block;">
                        Create a bot via <strong>@BotFather</strong> on Telegram and paste the token here.
                    </span>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="color: #0284C7;">Admin Chat ID (from @userinfobot)</label>
                    <input type="text" name="telegram_chat_id" class="form-control" value="{{ $settings->telegram_chat_id }}" placeholder="e.g. 123456789 or @channel" style="font-family: monospace;">
                    <span style="font-size: 10.5px; color: var(--text-muted); margin-top: 4px; display: block;">
                        Your personal Telegram ID or group/channel ID.
                    </span>
                </div>
            </div>

            <!-- ⌨️ 4-Dot Keyboard Menu Customization -->
            <div style="background: rgba(5, 9, 17, 0.6); border: 1px solid rgba(14, 165, 233, 0.3); border-radius: 10px; padding: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <label class="form-label" style="color: #38BDF8; font-weight: 800; margin-bottom: 0; display: flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-table-cells-large"></i> Telegram Bot 4-Dot Menu Buttons (Persistent Reply Keyboard)
                    </label>
                    <span style="font-size: 10px; font-weight: 800; color: #10B981; font-family: monospace;">:: 4-DOT GRID ACTIVE</span>
                </div>
                <p style="font-size: 11.5px; color: var(--text-muted); margin-bottom: 10px; line-height: 1.4;">
                    Configure the quick buttons that appear when tapping the 4-dots <strong>[ :: ]</strong> icon on Telegram. Enter button rows separated by line breaks, and columns separated by <code>|</code> pipe symbol:
                </p>
                <textarea name="telegram_custom_buttons" class="form-control" rows="5" style="font-family: monospace; font-size: 12px; font-weight: 700; color: #38BDF8; background: rgba(0,0,0,0.3);">{{ $settings->telegram_custom_buttons ?? "🚀 /start | 📊 Live Status\n💰 Pending Deposits | 👥 User Management\n💳 Cards Vault | 🎫 Support Desk\n📢 News Feed | ⚙️ Crypto Settings\n📦 Wholesale Packs | 📋 Orders & Sales" }}</textarea>
                <span style="font-size: 10.5px; color: var(--text-muted); margin-top: 6px; display: block;">
                    💡 <em>Example: <code>🚀 /start | 📊 Live Status</code> creates a 2-button row at the bottom of your Telegram chat.</em>
                </span>
            </div>
        </div>


        <!-- 1. Bitcoin (BTC) Configuration -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 24px;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-brands fa-bitcoin" style="color: #F7931A; font-size: 22px;"></i> Bitcoin (BTC) Configuration
            </h3>

            <div class="form-group">
                <label class="form-label">Bitcoin Receiving Wallet Address</label>
                <input type="text" name="btc_address" class="form-control" value="{{ $settings->btc_address }}" style="font-family: monospace; font-weight: 700;" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;">
                <div class="form-group">
                    <label class="form-label">Displayed BTC Rate ($ USD / USDT)</label>
                    <input type="text" name="btc_rate" class="form-control" value="{{ $settings->btc_rate }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Upload Custom BTC QR Barcode Image</label>
                    <input type="file" name="btc_qr_image" class="form-control" accept="image/*">
                    @if($settings->btc_qr)
                        <div style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                            <img src="{{ asset($settings->btc_qr) }}" alt="BTC QR Preview" style="width: 50px; height: 50px; border: 1px solid var(--border-color); border-radius: 6px; object-fit: contain;">
                            <span style="font-size: 11px; color: #10B981; font-weight: 700;">✓ Custom QR Uploaded</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 2. Litecoin (LTC) Configuration -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 24px;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-litecoin-sign" style="color: #345D9D; font-size: 20px;"></i> Litecoin (LTC) Configuration
            </h3>

            <div class="form-group">
                <label class="form-label">Litecoin Receiving Wallet Address</label>
                <input type="text" name="ltc_address" class="form-control" value="{{ $settings->ltc_address }}" style="font-family: monospace; font-weight: 700;" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;">
                <div class="form-group">
                    <label class="form-label">Displayed LTC Rate ($ USD / USDT)</label>
                    <input type="text" name="ltc_rate" class="form-control" value="{{ $settings->ltc_rate }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Upload Custom LTC QR Barcode Image</label>
                    <input type="file" name="ltc_qr_image" class="form-control" accept="image/*">
                    @if($settings->ltc_qr)
                        <div style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                            <img src="{{ asset($settings->ltc_qr) }}" alt="LTC QR Preview" style="width: 50px; height: 50px; border: 1px solid var(--border-color); border-radius: 6px; object-fit: contain;">
                            <span style="font-size: 11px; color: #10B981; font-weight: 700;">✓ Custom QR Uploaded</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- 3. Tether USDT (TRC-20) Configuration -->
        <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 20px; margin-bottom: 24px;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-circle-dollar-to-slot" style="color: #26A17B; font-size: 20px;"></i> Tether USDT (TRC-20) Configuration
            </h3>

            <div class="form-group">
                <label class="form-label">USDT (TRC-20) Receiving Wallet Address</label>
                <input type="text" name="usdt_address" class="form-control" value="{{ $settings->usdt_address }}" style="font-family: monospace; font-weight: 700;" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; align-items: start;">
                <div class="form-group">
                    <label class="form-label">Network / Protocol</label>
                    <input type="text" class="form-control" value="TRC-20 (Tron Network)" disabled style="background: rgba(255,255,255,0.04); font-weight: 700; color: #26A17B;">
                </div>
                <div class="form-group">
                    <label class="form-label">Upload Custom USDT QR Barcode Image</label>
                    <input type="file" name="usdt_qr_image" class="form-control" accept="image/*">
                    @if($settings->usdt_qr)
                        <div style="margin-top: 8px; display: flex; align-items: center; gap: 10px;">
                            <img src="{{ asset($settings->usdt_qr) }}" alt="USDT QR Preview" style="width: 50px; height: 50px; border: 1px solid var(--border-color); border-radius: 6px; object-fit: contain;">
                            <span style="font-size: 11px; color: #10B981; font-weight: 700;">✓ Custom QR Uploaded</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <button type="submit" class="btn-search" style="padding: 12px 36px; font-size: 14px;">
                <i class="fa-solid fa-floppy-disk"></i> Save Admin Credentials, Wallets & Telegram
            </button>
        </div>
    </form>

    <div style="margin-top: 24px; padding-top: 18px; border-top: 1px dashed rgba(239,68,68,0.3); display: flex; justify-content: space-between; align-items: center;">
        <div>
            <div style="font-size: 13px; font-weight: 800; color: #EF4444;">Сброс всех настроек (Reset Settings)</div>
            <div style="font-size: 11px; color: var(--text-muted);">Сбросить все адреса, кошельки, лимиты и бонусы к начальным значениям</div>
        </div>
        <form action="{{ route('admin.wallets.resetDefault') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Reset all site options, wallets, and activation tiers to factory defaults?');">
            @csrf
            <button type="submit" class="btn-reset" style="background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 12px; font-weight: 800; padding: 8px 18px; display: inline-flex; align-items: center; gap: 6px;">
                <i class="fa-solid fa-rotate-left"></i> Reset All Options to Default (Сбросить все)
            </button>
        </form>
    </div>
</div>
@endsection
