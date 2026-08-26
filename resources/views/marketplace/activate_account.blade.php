@extends('layouts.app')

@section('title', 'Activate Your Account - Members Vault Locked')

@section('content')
<div style="max-width: 780px; margin: 30px auto 50px auto;">
    <!-- Top Pill -->
    <div style="text-align: center; margin-bottom: 14px;">
        <span style="background: rgba(212,175,55,0.12); border: 1px solid rgba(212,175,55,0.4); color: var(--gold-bright); font-size: 11px; font-weight: 800; padding: 4px 14px; border-radius: 20px; letter-spacing: 0.8px; text-transform: uppercase; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-lock"></i> MEMBERS VAULT · LOCKED
        </span>
    </div>

    <!-- Main Title & Subtitle -->
    <div style="text-align: center; margin-bottom: 28px;">
        <h1 class="font-display" style="font-size: 32px; font-weight: 900; color: #FFFFFF; letter-spacing: -0.02em; display: flex; align-items: center; justify-content: center; gap: 10px;">
            <i class="fa-solid fa-lock" style="color: var(--gold-bright);"></i> Activate Your Account
        </h1>
        <p style="font-size: 14px; color: var(--text-secondary); margin-top: 8px; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">
            The marketplace is reserved for verified members. Make a one-time minimum deposit of <strong style="color: var(--gold-bright);">$10.00</strong> to unlock the vault — funds stay yours, ready to spend.
        </p>
    </div>

    <!-- Main Gold-Bordered Vault Card Container -->
    <div style="background: linear-gradient(155deg, rgba(20, 20, 32, 0.95) 0%, rgba(10, 10, 16, 0.98) 100%); border: 1.5px solid rgba(212, 175, 55, 0.45); border-radius: 18px; padding: 28px; box-shadow: 0 20px 60px rgba(0, 0, 0, 0.85), 0 0 35px rgba(212, 175, 55, 0.15);">
        
        <!-- Activation Requirement Highlight Box -->
        <div style="display: flex; align-items: center; gap: 18px; margin-bottom: 22px;">
            <div style="width: 58px; height: 58px; border-radius: 14px; background: var(--gold-gradient); color: #070709; display: flex; align-items: center; justify-content: center; font-size: 26px; box-shadow: 0 0 20px rgba(212, 175, 55, 0.5); flex-shrink: 0;">
                <i class="fa-solid fa-lock"></i>
            </div>
            <div>
                <div style="font-size: 11px; font-weight: 800; color: var(--gold-bright); letter-spacing: 0.8px; text-transform: uppercase;">
                    ONE-TIME ACTIVATION
                </div>
                <div class="font-display" style="font-size: 32px; font-weight: 900; color: #FFFFFF; line-height: 1.1;">
                    $10.00 <span style="font-size: 13px; font-weight: 600; color: var(--text-muted);">minimum</span>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-top: 2px;">
                    <i class="fa-solid fa-bolt" style="color: var(--gold-bright);"></i> Credited instantly to your wallet — no fees, no expiry
                </div>
            </div>
        </div>

        <!-- Latest Deposit Status Box -->
        <div style="background: rgba(10, 10, 16, 0.8); border: 1px solid {{ isset($latestDeposit) && $latestDeposit->status === 'pending' ? 'rgba(245,158,11,0.4)' : 'rgba(239,68,68,0.35)' }}; border-radius: 12px; padding: 14px 18px; margin-bottom: 22px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ isset($latestDeposit) && $latestDeposit->status === 'pending' ? 'rgba(245,158,11,0.15)' : 'rgba(239,68,68,0.15)' }}; color: {{ isset($latestDeposit) && $latestDeposit->status === 'pending' ? '#F59E0B' : '#EF4444' }}; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    @if(isset($latestDeposit) && $latestDeposit->status === 'pending')
                        <i class="fa-solid fa-spinner fa-spin"></i>
                    @else
                        <i class="fa-solid fa-xmark"></i>
                    @endif
                </div>
                <div>
                    <div style="display: flex; align-items: center; gap: 6px;">
                        <span style="font-size: 10px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px;">LATEST DEPOSIT</span>
                        <span style="background: {{ isset($latestDeposit) && $latestDeposit->status === 'pending' ? 'rgba(245,158,11,0.2)' : 'rgba(239,68,68,0.2)' }}; color: {{ isset($latestDeposit) && $latestDeposit->status === 'pending' ? '#F59E0B' : '#EF4444' }}; font-size: 9px; font-weight: 800; padding: 1px 5px; border-radius: 3px;">
                            {{ isset($latestDeposit) ? strtoupper($latestDeposit->status) : 'NOT COMPLETED' }}
                        </span>
                    </div>
                    <div style="font-size: 13px; font-weight: 700; color: #FFF; margin-top: 1px;">
                        @if(isset($latestDeposit) && $latestDeposit->status === 'pending')
                            Deposit pending Admin verification
                        @else
                            Deposit unsuccessful / Required
                        @endif
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted);">
                        @if(isset($latestDeposit) && $latestDeposit->status === 'pending')
                            Awaiting admin approval on blockchain. Please wait a moment.
                        @else
                            Last attempt was not completed. Please try again.
                        @endif
                    </div>
                </div>
            </div>

            <div style="text-align: right;">
                <div style="font-size: 15px; font-weight: 800; color: var(--gold-bright); font-family: 'JetBrains Mono', monospace;">
                    $10.00
                </div>
                <a href="{{ route('funds.index') }}" style="font-size: 11px; color: #60A5FA; text-decoration: none;">
                    <i class="fa-solid fa-rotate"></i> Try Again
                </a>
            </div>
        </div>

        <!-- 4 Feature Grid Pills (2x2) -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;">
            <div style="background: rgba(14, 14, 22, 0.7); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
                <div style="font-size: 16px; color: #EF4444; margin-top: 2px;">🛡️</div>
                <div>
                    <strong style="color: #FFF; font-size: 12px; display: block;">Verified Status</strong>
                    <span style="font-size: 11px; color: var(--text-secondary);">Anti-fraud protection for the whole community</span>
                </div>
            </div>

            <div style="background: rgba(14, 14, 22, 0.7); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
                <div style="font-size: 16px; color: #F59E0B; margin-top: 2px;">⚡</div>
                <div>
                    <strong style="color: #FFF; font-size: 12px; display: block;">Instant Access</strong>
                    <span style="font-size: 11px; color: var(--text-secondary);">Unlocks the entire live inventory immediately</span>
                </div>
            </div>

            <div style="background: rgba(14, 14, 22, 0.7); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
                <div style="font-size: 16px; color: #3B82F6; margin-top: 2px;">💎</div>
                <div>
                    <strong style="color: #FFF; font-size: 12px; display: block;">Wallet Credit</strong>
                    <span style="font-size: 11px; color: var(--text-secondary);">Every cent goes to your balance — spend anytime</span>
                </div>
            </div>

            <div style="background: rgba(14, 14, 22, 0.7); border: 1px solid var(--border-subtle); border-radius: 10px; padding: 12px 14px; display: flex; align-items: flex-start; gap: 10px;">
                <div style="font-size: 16px; color: var(--gold-bright); margin-top: 2px;">👑</div>
                <div>
                    <strong style="color: #FFF; font-size: 12px; display: block;">Premium Perks</strong>
                    <span style="font-size: 11px; color: var(--text-secondary);">Auto replacement, priority support, member pricing</span>
                </div>
            </div>
        </div>

        <!-- Big Action Buttons -->
        <div style="display: grid; grid-template-columns: 3fr 1fr; gap: 12px; margin-bottom: 20px;">
            <a href="{{ route('funds.index') }}" class="btn-search" style="justify-content: center; text-decoration: none; padding: 14px; font-size: 15px; font-weight: 800; border-radius: 8px;">
                <i class="fa-solid fa-credit-card"></i> Deposit Now & Activate &rarr;
            </a>

            <a href="{{ route('tickets.index') }}" class="btn-reset" style="justify-content: center; text-decoration: none; padding: 14px; font-size: 13px; border-radius: 8px;">
                <i class="fa-solid fa-comments"></i> Need Help?
            </a>
        </div>

        <!-- Trust Badges Row -->
        <div style="display: flex; justify-content: center; align-items: center; gap: 16px; font-size: 11px; color: var(--text-muted); flex-wrap: wrap; border-top: 1px solid var(--border-subtle); padding-top: 14px;">
            <span><i class="fa-solid fa-lock" style="color: var(--gold-bright);"></i> Encrypted</span>
            <span>•</span>
            <span><i class="fa-solid fa-bolt" style="color: #10B981;"></i> Instant Credit</span>
            <span>•</span>
            <span><i class="fa-brands fa-bitcoin" style="color: #F7931A;"></i> Crypto Accepted</span>
            <span>•</span>
            <span><i class="fa-solid fa-shield" style="color: #3B82F6;"></i> One-Time Only</span>
        </div>
    </div>

    <!-- Bottom Footer Subtext -->
    <div style="text-align: center; margin-top: 22px; font-size: 12px; color: var(--text-muted);">
        This is a one-time activation. Once your deposit is confirmed, the marketplace unlocks permanently 🚀
    </div>
</div>
@endsection
