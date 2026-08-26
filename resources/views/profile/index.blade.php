@extends('layouts.app')

@section('title', 'Payate CC - User Profile & Account Settings')

@section('content')
<div style="max-width: 1200px; margin: 10px auto 40px auto;">
    
    <!-- Profile Page Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 22px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 class="font-display" style="font-size: 22px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-circle-user" style="color: var(--gold-primary);"></i> User Profile & Account Settings
            </h1>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                Manage your Payate CC account credentials, edit your username, contact information, and security preferences.
            </p>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('funds.index') }}" class="btn-search" style="padding: 7px 18px; font-size: 13px; text-decoration: none;">
                <i class="fa-solid fa-wallet"></i> Recharge Balance
            </a>
            <a href="{{ route('orders.index') }}" class="btn-reset" style="padding: 7px 18px; font-size: 13px; text-decoration: none;">
                <i class="fa-solid fa-receipt"></i> My Orders
            </a>
        </div>
    </div>

    <!-- Main 2-Column Grid -->
    <div style="display: grid; grid-template-columns: 340px 1fr; gap: 24px; align-items: flex-start;">
        
        <!-- Left Sidebar: Profile Card & Financial Stats -->
        <div style="display: flex; flex-direction: column; gap: 18px;">
            <!-- Profile Identity Card -->
            <div class="filter-card" style="text-align: center; padding: 26px 20px;">
                <div style="width: 84px; height: 84px; border-radius: 50%; background: var(--bg-surface); border: 2.5px solid var(--gold-primary); display: flex; align-items: center; justify-content: center; font-size: 38px; color: var(--gold-primary); margin: 0 auto 14px auto; box-shadow: 0 4px 15px rgba(0,0,0,0.15);">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <h2 style="font-size: 18px; font-weight: 800; color: var(--text-primary);">
                    {{ $profile['full_name'] ?? 'Asadul Islam' }}
                </h2>
                <div style="font-size: 13px; color: var(--gold-primary); font-weight: 800; font-family: monospace; margin-top: 2px;">
                    @<span>{{ $profile['username'] ?? 'asadulislam17p' }}</span>
                </div>

                <div style="margin-top: 12px; display: inline-flex; align-items: center; gap: 6px; background: rgba(5,150,105,0.12); border: 1px solid rgba(5,150,105,0.3); color: #059669; font-size: 11px; font-weight: 800; padding: 3px 10px; border-radius: 20px;">
                    <i class="fa-solid fa-shield-check"></i> {{ $profile['tier'] ?? 'Verified Member' }}
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 18px 0;">

                <div style="text-align: left; display: flex; flex-direction: column; gap: 10px; font-size: 12px;">
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Status:</span>
                        <strong style="color: #059669;"><i class="fa-solid fa-circle" style="font-size: 8px;"></i> Online</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Member Since:</span>
                        <strong style="color: var(--text-primary);">{{ $profile['member_since'] ?? '2026-01-15' }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Country:</span>
                        <strong style="color: var(--text-primary);">{{ $profile['country'] ?? 'Bangladesh' }}</strong>
                    </div>
                </div>
            </div>

            <!-- Financial Summary Card -->
            <div class="filter-card" style="padding: 20px;">
                <h3 style="font-size: 13px; font-weight: 800; color: var(--text-primary); margin-bottom: 14px; text-transform: uppercase; letter-spacing: 0.5px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-coins" style="color: var(--gold-primary);"></i> Wallet Overview
                </h3>

                <div style="background: var(--bg-input); border: 1.5px solid var(--border-color); border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Available Balance:</span>
                    <span style="font-size: 16px; font-weight: 800; color: #059669; font-family: monospace;">
                        $ {{ number_format($userBalance, 2) }}
                    </span>
                </div>

                <div style="background: var(--bg-input); border: 1.5px solid var(--border-color); border-radius: 8px; padding: 12px 14px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Total Recharge:</span>
                    <span style="font-size: 14px; font-weight: 700; color: var(--text-primary); font-family: monospace;">
                        $ {{ number_format($totalRecharge, 2) }}
                    </span>
                </div>

                <div style="background: var(--bg-input); border: 1.5px solid var(--border-color); border-radius: 8px; padding: 12px 14px; display: flex; justify-content: space-between; align-items: center;">
                    <span style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Cards Purchased:</span>
                    <span style="font-size: 14px; font-weight: 700; color: var(--text-primary);">
                        {{ $totalOrders }} Orders
                    </span>
                </div>
            </div>
        </div>

        <!-- Right Content: Editable Forms -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            
            <!-- Edit Profile Information Form -->
            <div class="filter-card" style="padding: 24px;">
                <h2 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-id-card" style="color: var(--gold-primary);"></i> Edit Personal & Contact Information
                </h2>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 20px;">
                    You can change your username, full name, contact handles, and regional preferences at any time.
                </p>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        
                        <div class="form-group">
                            <label class="form-label" for="username">Username (@username)</label>
                            <input type="text" name="username" id="username" class="form-control" value="{{ old('username', $profile['username'] ?? 'asadulislam17p') }}" placeholder="Your unique username" required style="border-color: var(--gold-primary); font-weight: 700;">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="full_name">Full Name</label>
                            <input type="text" name="full_name" id="full_name" class="form-control" value="{{ old('full_name', $profile['full_name'] ?? 'Asadul Islam') }}" placeholder="Your Full Name" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="email">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $profile['email'] ?? 'asadul@example.com') }}" placeholder="email@domain.com" required>
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number / WhatsApp</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $profile['phone'] ?? '+880 1700-000000') }}" placeholder="+880 1700-000000">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="telegram">Telegram Handle</label>
                            <input type="text" name="telegram" id="telegram" class="form-control" value="{{ old('telegram', $profile['telegram'] ?? '@asadul_buyer') }}" placeholder="@username">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="jabber">Jabber / XMPP / Discord</label>
                            <input type="text" name="jabber" id="jabber" class="form-control" value="{{ old('jabber', $profile['jabber'] ?? 'asadul@xmpp.is') }}" placeholder="user@jabber.org">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="country">Country / Region</label>
                            <input type="text" name="country" id="country" class="form-control" value="{{ old('country', $profile['country'] ?? 'Bangladesh (BD)') }}" placeholder="Country">
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="timezone">Timezone</label>
                            <select name="timezone" id="timezone" class="form-select">
                                <option value="America/Los_Angeles" {{ ($profile['timezone'] ?? '') == 'America/Los_Angeles' ? 'selected' : '' }}>Pacific Time (PST/PDT)</option>
                                <option value="UTC" {{ ($profile['timezone'] ?? '') == 'UTC' ? 'selected' : '' }}>Universal Time (UTC)</option>
                                <option value="Asia/Dhaka" {{ ($profile['timezone'] ?? '') == 'Asia/Dhaka' ? 'selected' : '' }}>Bangladesh Standard Time (BST / UTC+6)</option>
                                <option value="Europe/London" {{ ($profile['timezone'] ?? '') == 'Europe/London' ? 'selected' : '' }}>London (GMT/BST)</option>
                            </select>
                        </div>
                    </div>

                    <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-search" style="padding: 9px 24px; font-size: 13px;">
                            <i class="fa-solid fa-floppy-disk"></i> Save Profile Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password & Security Update Form -->
            <div class="filter-card" style="padding: 24px;">
                <h2 style="font-size: 16px; font-weight: 800; color: var(--text-primary); margin-bottom: 6px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-lock" style="color: var(--gold-primary);"></i> Change Account Password
                </h2>
                <p style="font-size: 12px; color: var(--text-muted); margin-bottom: 20px;">
                    Ensure your account stays secure with a strong passphrase.
                </p>

                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px;">
                        <div class="form-group">
                            <label class="form-label" for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password" class="form-control" placeholder="Min. 6 chars" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label" for="new_password_confirmation">Confirm Password</label>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" class="form-control" placeholder="Repeat new password" required>
                        </div>
                    </div>

                    <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                        <button type="submit" class="btn-reset" style="padding: 9px 24px; font-size: 13px;">
                            <i class="fa-solid fa-key"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
