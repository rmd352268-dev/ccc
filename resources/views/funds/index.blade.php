@extends('layouts.app')

@section('title', 'Add Funds - Crypto Recharge')

@section('content')
<style>
    /* Exact Layout Styling matching the reference screenshot */
    .funds-top-notice {
        text-align: center;
        margin: 10px 0 28px 0;
        padding: 0 15px;
    }
    .funds-top-notice h2 {
        font-size: 15px;
        font-weight: 700;
        color: #E11D48;
        line-height: 1.6;
        letter-spacing: 0.2px;
    }

    .crypto-cards-container {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        max-width: 1160px;
        margin: 0 auto 36px auto;
    }

    .crypto-card-white {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 26px 20px 22px 20px;
        box-shadow: var(--card-shadow);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .crypto-card-white:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
    }

    .crypto-header-brand {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 6px;
    }
    .crypto-header-brand span {
        font-size: 22px;
        font-weight: 900;
        color: var(--text-primary);
        letter-spacing: -0.5px;
    }

    .crypto-sub-label {
        font-size: 11.5px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 12px;
    }

    .crypto-address-pill {
        background: var(--bg-input);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 11px;
        font-family: 'JetBrains Mono', monospace;
        color: var(--gold-primary);
        font-weight: 700;
        max-width: 100%;
        word-break: break-all;
        cursor: pointer;
        margin-bottom: 16px;
        transition: background 0.15s, border-color 0.15s;
        user-select: all;
    }
    .crypto-address-pill:hover {
        border-color: var(--gold-primary);
    }

    .qr-wrapper {
        width: 160px;
        height: 160px;
        background: #FFFFFF;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 18px;
    }
    .qr-wrapper img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    .crypto-info-row {
        width: 100%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 12px;
        color: var(--text-secondary);
        font-weight: 600;
        padding: 4px 6px;
        margin-bottom: 4px;
    }
    .crypto-info-row strong {
        color: var(--text-primary);
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
    }
    .crypto-info-row strong span {
        font-size: 10px;
        color: var(--text-muted);
        font-weight: 600;
    }

    .user-account-badge-box {
        width: 100%;
        background: rgba(245, 158, 11, 0.08);
        border: 1px dashed rgba(245, 158, 11, 0.3);
        border-radius: 6px;
        padding: 6px 10px;
        font-size: 11.5px;
        color: var(--text-secondary);
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 10px 0 14px 0;
    }
    .user-account-badge-box strong {
        color: #F59E0B;
        font-family: 'JetBrains Mono', monospace;
    }

    .btn-update-recharge-green {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: #FFFFFF;
        border: none;
        border-radius: 8px;
        padding: 10px 20px;
        font-size: 13px;
        font-weight: 800;
        font-family: 'JetBrains Mono', monospace;
        cursor: pointer;
        width: 100%;
        transition: background 0.15s, transform 0.15s;
        box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }
    .btn-update-recharge-green:hover {
        background: linear-gradient(135deg, #34D399 0%, #10B981 100%);
        transform: translateY(-2px);
    }

    .funds-bottom-notes {
        max-width: 900px;
        margin: 10px auto 40px auto;
        text-align: center;
        font-size: 12px;
        color: var(--text-secondary);
        line-height: 1.8;
    }
    .funds-bottom-notes h3 {
        font-size: 14px;
        font-weight: 800;
        color: #E11D48;
        margin-bottom: 10px;
    }

    /* Modal for Update Recharge */
    .recharge-modal-backdrop {
        display: none;
        position: fixed;
        top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        z-index: 2000;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .recharge-modal-backdrop.active {
        display: flex;
    }
    .recharge-modal-card {
        background: var(--bg-card);
        border: 1.5px solid var(--border-color);
        border-radius: 16px;
        width: 100%;
        max-width: 520px;
        padding: 28px;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.5);
        position: relative;
    }

    .recharge-warning-box {
        background: rgba(239, 68, 68, 0.08);
        border: 1px solid rgba(239, 68, 68, 0.4);
        border-radius: 8px;
        padding: 12px 14px;
        margin-top: 16px;
        box-shadow: 0 0 15px rgba(239, 68, 68, 0.08);
    }
    .recharge-warning-title {
        font-size: 11.5px;
        font-weight: 800;
        color: #EF4444;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 4px;
    }
    .recharge-warning-desc {
        font-size: 11px;
        line-height: 1.5;
        color: #F87171;
        font-weight: 600;
    }
    [data-theme="light"] .recharge-warning-box {
        background: #FEF2F2;
        border-color: #FCA5A5;
    }
    [data-theme="light"] .recharge-warning-desc {
        color: #B91C1C !important;
    }

    @media (max-width: 900px) {
        .crypto-cards-container {
            grid-template-columns: 1fr;
            max-width: 420px;
        }
    }
</style>

<!-- Top Warning / Header Notice -->
<div class="funds-top-notice">
    <h2>A 1 USDT processing fee applies to USDT payments. btc needs more than 6 confirmations, if it does not arrive, please click Update recharge</h2>
</div>

<!-- 3 Crypto Recharge Cards Grid -->
<div class="crypto-cards-container">
    <!-- Card 1: Bitcoin (BTC) -->
    <div class="crypto-card-white">
        <div class="crypto-header-brand">
            <i class="fa-brands fa-bitcoin" style="color: #F7931A; font-size: 28px;"></i>
            <span>bitcoin</span>
        </div>
        <div class="crypto-sub-label">BTC</div>

        <div class="crypto-address-pill" onclick="copyCryptoAddress('{{ $settings->btc_address }}', 'BTC Address')" title="Click to copy address">
            {{ $settings->btc_address }}
        </div>

        <div class="qr-wrapper">
            <img src="{{ $settings->btc_qr ? asset($settings->btc_qr) : 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($settings->btc_address) }}" alt="Bitcoin QR Code">
        </div>

        <div class="crypto-info-row">
            <span>Min Top-Up:</span>
            <strong>${{ number_format($settings->min_deposit ?? 10.00, 2) }} <span>USD</span></strong>
        </div>

        <div class="crypto-info-row">
            <span>Rate:</span>
            <strong>${{ $settings->btc_rate ?? '69,525.00' }} <span>USD</span></strong>
        </div>

        <div class="user-account-badge-box">
            <span>Credited To:</span>
            <strong>@ {{ session('user_username', 'User') }}</strong>
        </div>

        <button type="button" class="btn-update-recharge-green" onclick="openRechargeModal('Bitcoin (BTC)', '{{ $settings->btc_address }}')">
            <i class="fa-solid fa-paper-plane"></i> Update Recharge
        </button>
    </div>

    <!-- Card 2: Litecoin (LTC) -->
    <div class="crypto-card-white">
        <div class="crypto-header-brand">
            <i class="fa-solid fa-litecoin-sign" style="color: #345D9D; font-size: 26px;"></i>
            <span>litecoin</span>
        </div>
        <div class="crypto-sub-label">LTC</div>

        <div class="crypto-address-pill" onclick="copyCryptoAddress('{{ $settings->ltc_address }}', 'LTC Address')" title="Click to copy address">
            {{ $settings->ltc_address }}
        </div>

        <div class="qr-wrapper">
            <img src="{{ $settings->ltc_qr ? asset($settings->ltc_qr) : 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($settings->ltc_address) }}" alt="Litecoin QR Code">
        </div>

        <div class="crypto-info-row">
            <span>Min Top-Up:</span>
            <strong>${{ number_format($settings->min_deposit ?? 10.00, 2) }} <span>USD</span></strong>
        </div>

        <div class="crypto-info-row">
            <span>Rate:</span>
            <strong>${{ $settings->ltc_rate ?? '46.33' }} <span>USD</span></strong>
        </div>

        <div class="user-account-badge-box">
            <span>Credited To:</span>
            <strong>@ {{ session('user_username', 'User') }}</strong>
        </div>

        <button type="button" class="btn-update-recharge-green" onclick="openRechargeModal('Litecoin (LTC)', '{{ $settings->ltc_address }}')">
            <i class="fa-solid fa-paper-plane"></i> Update Recharge
        </button>
    </div>

    <!-- Card 3: Tether USDT-TRC20 -->
    <div class="crypto-card-white">
        <div class="crypto-header-brand">
            <i class="fa-solid fa-circle-dollar-to-slot" style="color: #26A17B; font-size: 26px;"></i>
            <span>tether <span style="font-size: 13px; color: var(--text-muted); font-weight: 700;">(TRC20)</span></span>
        </div>
        <div class="crypto-sub-label">USDT-TRC20</div>

        <div class="crypto-address-pill" onclick="copyCryptoAddress('{{ $settings->usdt_address }}', 'USDT-TRC20 Address')" title="Click to copy address">
            {{ $settings->usdt_address }}
        </div>

        <div class="qr-wrapper">
            <img src="{{ $settings->usdt_qr ? asset($settings->usdt_qr) : 'https://api.qrserver.com/v1/create-qr-code/?size=160x160&data=' . urlencode($settings->usdt_address) }}" alt="USDT TRC20 QR Code">
        </div>

        <div class="crypto-info-row">
            <span>Min Top-Up:</span>
            <strong>${{ number_format($settings->min_deposit ?? 10.00, 2) }} <span>USD</span></strong>
        </div>

        <div class="crypto-info-row">
            <span>Network:</span>
            <strong>TRON (TRC20)</strong>
        </div>

        <div class="user-account-badge-box">
            <span>Credited To:</span>
            <strong>@ {{ session('user_username', 'User') }}</strong>
        </div>

        <button type="button" class="btn-update-recharge-green" onclick="openRechargeModal('USDT-TRC20', '{{ $settings->usdt_address }}')">
            <i class="fa-solid fa-paper-plane"></i> Update Recharge
        </button>
    </div>
</div>

<!-- Bottom Explanatory Notes -->
<div class="funds-bottom-notes">
    <h3>A 1 USDT processing fee applies to USDT payments.</h3>
    <p>Bitcoin fees vary depending on the time of day, the load on the Bitcoin network, and can be as high per transaction $40, and thus became extremely unpopular.</p>
    <p>I now only accept transfers to "bc" segregated witness addresses to minimize the cost per transaction.</p>
    <p>Consider buying/sending in other cryptocurrencies such as Litecoin, or USDT trc20.</p>
    <p>You'll probably find many crypto wallets and exchanges in Google. TrustWallet Applications are one option you might consider installing.</p>
</div>

<!-- My Account Specific Recharge Requests History Table -->
<div class="table-card" style="margin-top: 30px;">
    <div style="padding: 14px 18px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
        <h3 style="font-size: 14px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-clock-rotate-left" style="color: var(--gold-primary);"></i> My Recharge Status & Verification History
        </h3>
        <span style="font-size: 11px; color: var(--text-muted);">Account: <strong>{{ session('user_username', 'Active User') }}</strong></span>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>Gateway</th>
                    <th>Amount</th>
                    <th>Receiving Wallet Address</th>
                    <th>Sender Account / TxID</th>
                    <th>Submitted Time</th>
                    <th>Verification Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($myRecharges as $rec)
                    <tr style="{{ ($rec->status === 'deducted' || $rec->amount < 0) ? 'background: rgba(239, 68, 68, 0.04);' : '' }}">
                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 700; color: {{ ($rec->status === 'deducted' || $rec->amount < 0) ? '#EF4444' : '#3B82F6' }};">
                            {{ $rec->trx_id }}
                        </td>
                        <td>
                            @if($rec->status === 'deducted' || $rec->amount < 0)
                                <span class="type-badge" style="background: rgba(239,68,68,0.12); color: #EF4444; border: 1px solid rgba(239,68,68,0.3);">
                                    <i class="fa-solid fa-arrow-down"></i> Deduction
                                </span>
                            @else
                                <span class="type-badge">{{ $rec->currency }}</span>
                            @endif
                        </td>
                        <td style="font-weight: 800; font-family: 'JetBrains Mono', monospace; font-size: 13px;">
                            @if($rec->amount < 0 || $rec->status === 'deducted')
                                <span style="color: #EF4444;">-${{ number_format(abs($rec->amount), 2) }}</span>
                            @else
                                <span style="color: #10B981;">+${{ number_format($rec->amount, 2) }}</span>
                            @endif
                        </td>
                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis;" title="{{ $rec->address }}">
                            {{ $rec->address ?? 'N/A' }}
                        </td>
                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-primary);">
                            <div><strong>@ {{ $rec->username }}</strong></div>
                            @if(!empty($rec->telegram_username))
                                <div style="color: #38BDF8; font-size: 10px; margin-top: 1px;"><i class="fa-brands fa-telegram"></i> {{ $rec->telegram_username }}</div>
                            @endif
                            @if(!empty($rec->txid) && $rec->txid !== 'DIRECT_DEPOSIT')
                                <div style="color: var(--text-muted); font-size: 10px; margin-top: 1px;" title="{{ $rec->txid }}">{{ substr($rec->txid, 0, 14) }}...</div>
                            @endif
                        </td>
                        <td style="font-size: 11px; color: var(--text-muted);">
                            {{ $rec->created_at->format('Y-m-d H:i') }}
                        </td>
                        <td>
                            @if($rec->status === 'deducted' || $rec->amount < 0)
                                <span class="status-badge" style="background: rgba(239,68,68,0.15); color: #EF4444; border: 1px solid rgba(239,68,68,0.3);">
                                    <i class="fa-solid fa-minus-circle"></i> Deducted by Admin
                                </span>
                            @elseif($rec->status === 'completed')
                                <span class="status-badge" style="background: rgba(16,185,129,0.15); color: #10B981; border: 1px solid rgba(16,185,129,0.3);">
                                    <i class="fa-solid fa-check-circle"></i> Completed & Credited
                                </span>
                            @elseif($rec->status === 'rejected')
                                <span class="status-badge" style="background: rgba(239,68,68,0.15); color: #EF4444; border: 1px solid rgba(239,68,68,0.3);">
                                    <i class="fa-solid fa-times-circle"></i> Rejected
                                </span>
                            @else
                                <span class="status-badge" style="background: rgba(245,158,11,0.15); color: #F59E0B; border: 1px solid rgba(245,158,11,0.3);">
                                    <i class="fa-solid fa-clock"></i> Pending Confirmation
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted); font-size: 13px;">
                            <i class="fa-solid fa-inbox" style="font-size: 24px; opacity: 0.4; margin-bottom: 6px; display: block;"></i>
                            No recharge requests submitted yet for this account.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Update Recharge / Submit Deposit Request with Full Sender Details -->
<div class="recharge-modal-backdrop" id="rechargeModal">
    <div class="recharge-modal-card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 8px; margin: 0;">
                <i class="fa-solid fa-file-invoice-dollar" style="color: var(--gold-primary);"></i> Submit Payment Verification
            </h3>
            <button type="button" onclick="closeRechargeModal()" style="background: none; border: none; font-size: 22px; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>

        <form action="{{ route('funds.submitRecharge') }}" method="POST">
            @csrf
            <input type="hidden" name="currency" id="modal_currency" value="USDT-TRC20">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: var(--text-primary);">Selected Gateway</label>
                    <div id="modal_gateway_name" style="font-weight: 800; color: var(--gold-primary); font-size: 13.5px; padding: 6px 0;">USDT-TRC20</div>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: var(--text-primary);">Credited Account</label>
                    <div style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 800; color: #10B981; padding: 6px 0; font-family: monospace;">
                        <i class="fa-solid fa-circle-user"></i> @ {{ session('user_username', 'Active User') }}
                    </div>
                </div>
            </div>

            <!-- Address Display with 1-Click Copy Button -->
            <div class="form-group" style="margin-bottom: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 4px;">
                    <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: var(--text-primary); margin: 0;">Receiving Payment Address</label>
                    <button type="button" onclick="copyModalAddress()" style="background: rgba(16,185,129,0.15); border: 1px solid rgba(16,185,129,0.4); color: #10B981; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;">
                        <i class="fa-regular fa-copy"></i> Copy Address
                    </button>
                </div>
                <div id="modal_address_text" style="font-family: 'JetBrains Mono', monospace; font-size: 11px; background: var(--bg-input); border: 1px solid var(--border-color); padding: 9px 12px; border-radius: 8px; word-break: break-all; color: var(--gold-primary); font-weight: 700;">
                    -
                </div>
            </div>

            <!-- 1. Deposit Amount Field -->
            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: var(--text-primary);">Deposit Amount ($ USD / USDT)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 900; color: #10B981;">$</span>
                    <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="Enter Deposit Amount (e.g. 50.00)" style="padding-left: 28px; font-weight: 800; font-family: monospace; font-size: 14.5px;" required autofocus>
                </div>
            </div>

            <!-- 2. Account Name Field -->
            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: var(--text-primary);">Account Name (Registered Website Username)</label>
                <input type="text" name="account_name" value="{{ session('user_username', '') }}" class="form-control" placeholder="Enter your website account name..." style="font-family: monospace; font-size: 12.5px; font-weight: 700;" required>
                <span style="font-size: 10.5px; color: var(--text-muted); margin-top: 3px; display: block;">
                    * The username of the account you opened and are currently logged into for receiving the balance.
                </span>
            </div>

            <!-- 3. Telegram Username Field -->
            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: var(--text-primary);">Telegram Username (@username)</label>
                <div style="position: relative;">
                    <span style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); font-weight: 800; color: #38BDF8;"><i class="fa-brands fa-telegram"></i></span>
                    <input type="text" name="telegram_username" class="form-control" placeholder="@your_telegram_username" style="padding-left: 32px; font-family: monospace; font-size: 12.5px; font-weight: 600;" required>
                </div>
                <span style="font-size: 10.5px; color: var(--text-muted); margin-top: 3px; display: block;">
                    * Enter your Telegram username for instant payment verification, proof review, and fast credit.
                </span>
            </div>

            <!-- 4. Optional TxID / Hash / Sender Details -->
            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label" style="font-size: 11.5px; font-weight: 700; color: var(--text-primary);">Transaction Hash / Sender Wallet (Optional)</label>
                <input type="text" name="txid" class="form-control" placeholder="Transaction Hash (TxID) or Sender Address (Optional)..." style="font-family: monospace; font-size: 12px;">
            </div>

            <!-- Red Warning Notice in English -->
            <div class="recharge-warning-box">
                <div class="recharge-warning-title">
                    <i class="fa-solid fa-triangle-exclamation"></i> Warning / Important Notice
                </div>
                <div class="recharge-warning-desc">
                    Please provide accurate and genuine transaction details. Submitting fake, altered, or incorrect payment proofs will result in an <strong>immediate permanent ban</strong> of your account and forfeiture of all assets!
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px;">
                <button type="button" class="btn-reset" onclick="closeRechargeModal()">Cancel</button>
                <button type="submit" class="btn-search" style="padding: 10px 24px; font-weight: 800; font-size: 13px;">
                    <i class="fa-solid fa-paper-plane"></i> Submit Recharge
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function copyCryptoAddress(text, label) {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(text).then(() => {
                alert(label + ' copied to clipboard: ' + text);
            }).catch(() => {});
        } else {
            prompt('Copy ' + label + ':', text);
        }
    }

    function copyModalAddress() {
        const address = document.getElementById('modal_address_text').textContent.trim();
        if (address && address !== '-') {
            copyCryptoAddress(address, 'Payment Address');
        }
    }

    function openRechargeModal(gateway, address) {
        document.getElementById('modal_currency').value = gateway;
        document.getElementById('modal_gateway_name').textContent = gateway;
        document.getElementById('modal_address_text').textContent = address;
        document.getElementById('rechargeModal').classList.add('active');
    }

    function closeRechargeModal() {
        document.getElementById('rechargeModal').classList.remove('active');
    }
</script>
@endsection
