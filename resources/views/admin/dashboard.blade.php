@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('content')
<!-- Metric Cards Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <!-- Pending Approvals -->
    <div class="filter-card" style="border-left: 4px solid #F59E0B;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">PENDING RECHARGES</span>
            <i class="fa-solid fa-hand-holding-dollar" style="color: #F59E0B; font-size: 20px;"></i>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--text-primary); margin-top: 8px; font-family: 'JetBrains Mono', monospace;">
            {{ $pendingRecharges }}
        </div>
        <div style="font-size: 11px; margin-top: 4px;">
            <a href="{{ route('admin.recharges.index', ['status' => 'pending']) }}" style="color: #F59E0B; text-decoration: none; font-weight: 700;">
                Review & Approve &rarr;
            </a>
        </div>
    </div>

    <!-- Stock Available -->
    <div class="filter-card" style="border-left: 4px solid #10B981;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">AVAILABLE STOCK</span>
            <i class="fa-solid fa-credit-card" style="color: #10B981; font-size: 20px;"></i>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--text-primary); margin-top: 8px; font-family: 'JetBrains Mono', monospace;">
            {{ $availableCards }} <span style="font-size: 13px; color: var(--text-muted); font-weight: normal;">/ {{ $totalCards }} Total</span>
        </div>
        <div style="font-size: 11px; color: #10B981; margin-top: 4px;">Ready for sale in shop</div>
    </div>

    <!-- Total Revenue -->
    <div class="filter-card" style="border-left: 4px solid var(--gold-primary);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">TOTAL REVENUE</span>
            <i class="fa-solid fa-sack-dollar" style="color: var(--gold-primary); font-size: 20px;"></i>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--text-primary); margin-top: 8px; font-family: 'JetBrains Mono', monospace;">
            ${{ number_format($totalRevenue, 2) }}
        </div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">From {{ $totalOrders }} orders placed</div>
    </div>

    <!-- User Current Balance -->
    <div class="filter-card" style="border-left: 4px solid #3B82F6;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 12px; font-weight: 700; color: var(--text-secondary);">ACTIVE CLIENT BALANCE</span>
            <i class="fa-solid fa-wallet" style="color: #3B82F6; font-size: 20px;"></i>
        </div>
        <div style="font-size: 26px; font-weight: 800; color: var(--text-primary); margin-top: 8px; font-family: 'JetBrains Mono', monospace;">
            ${{ number_format($userBalance, 2) }}
        </div>
        <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px;">Total Recharge: ${{ number_format($totalRecharge, 2) }}</div>
    </div>
</div>

<!-- Two Column Layout: Pending Recharges & Quick Actions -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px; margin-bottom: 24px;">
    <!-- Pending Deposits Box -->
    <div class="table-card" style="margin-bottom: 0;">
        <div style="padding: 12px 16px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 14px; font-weight: 700; color: var(--text-primary);">
                <i class="fa-solid fa-clock-rotate-left" style="color: #F59E0B; margin-right: 6px;"></i> Latest Pending Recharge Requests
            </h3>
            <a href="{{ route('admin.recharges.index') }}" style="font-size: 12px; color: #3B82F6; text-decoration: none;">View All</a>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Gateway</th>
                        <th>Amount</th>
                        <th>TxID</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendingDeposits as $pd)
                        <tr>
                            <td style="font-weight: 600;">{{ $pd->username }}</td>
                            <td><span class="type-badge">{{ $pd->currency }}</span></td>
                            <td style="font-weight: 700; color: #10B981;">${{ number_format($pd->amount, 2) }}</td>
                            <td style="font-family: 'JetBrains Mono', monospace; font-size: 11px; max-width: 150px; overflow: hidden; text-overflow: ellipsis;">{{ $pd->txid }}</td>
                            <td class="text-center">
                                <form action="{{ route('admin.recharges.approve', $pd->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Approve this recharge?');">
                                    @csrf
                                    <button type="submit" class="btn-search" style="padding: 3px 8px; font-size: 11px; background: #10B981;">Approve</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--text-muted);">No pending deposit requests.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Shortcuts Card -->
    <div class="filter-card">
        <h3 style="font-size: 14px; font-weight: 700; color: var(--text-primary); margin-bottom: 14px;">
            <i class="fa-solid fa-bolt" style="color: var(--gold-primary); margin-right: 6px;"></i> Quick Admin Tools
        </h3>
        <div style="display: flex; flex-direction: column; gap: 10px;">
            <a href="{{ route('admin.cards.bulk') }}" class="btn-search" style="justify-content: center; text-decoration: none; padding: 10px; font-weight: 800; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #000;">
                <i class="fa-solid fa-file-arrow-up"></i> Upload Card File
            </a>
            <a href="{{ route('admin.cards.create') }}" class="btn-reset" style="justify-content: center; text-decoration: none; padding: 8px;">
                <i class="fa-solid fa-plus"></i> Add Single Card
            </a>
            <a href="{{ route('admin.wallets.index') }}" class="btn-reset" style="justify-content: center; text-decoration: none; padding: 8px;">
                <i class="fa-solid fa-wallet"></i> Edit Crypto Wallets
            </a>
            <a href="{{ route('admin.news.index') }}" class="btn-reset" style="justify-content: center; text-decoration: none; padding: 8px;">
                <i class="fa-regular fa-newspaper"></i> Post News Notice
            </a>
        </div>
    </div>
</div>
@endsection
