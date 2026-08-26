@extends('admin.layout')

@section('title', 'Recharge Verification & Approval Desk')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Deposit & Recharge Approvals</h2>
        <p style="font-size: 13px; color: var(--text-secondary); margin-top: 3px;">Verify client transaction hashes and approve balance credits.</p>
    </div>

    @if($pendingCount > 0)
        <span style="background: rgba(245,158,11,0.15); color: #F59E0B; border: 1px solid rgba(245,158,11,0.3); font-size: 12px; font-weight: 700; padding: 6px 14px; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
            <i class="fa-solid fa-bell"></i> {{ $pendingCount }} Pending Approval(s)
        </span>
    @endif
</div>

<!-- Filters -->
<div class="filter-card" style="margin-bottom: 16px; padding: 12px 16px;">
    <div style="display: flex; gap: 10px;">
        <a href="{{ route('admin.recharges.index', ['status' => 'all']) }}" class="btn-reset" style="padding: 6px 14px; font-size: 12px; text-decoration: none; {{ request('status') === 'all' || !request('status') ? 'border-color: var(--gold-primary); color: var(--gold-primary); font-weight: 700;' : '' }}">All Deposits</a>
        <a href="{{ route('admin.recharges.index', ['status' => 'pending']) }}" class="btn-reset" style="padding: 6px 14px; font-size: 12px; text-decoration: none; {{ request('status') === 'pending' ? 'border-color: #F59E0B; color: #F59E0B; font-weight: 700;' : '' }}">Pending Only ({{ $pendingCount }})</a>
        <a href="{{ route('admin.recharges.index', ['status' => 'completed']) }}" class="btn-reset" style="padding: 6px 14px; font-size: 12px; text-decoration: none; {{ request('status') === 'completed' ? 'border-color: #10B981; color: #10B981; font-weight: 700;' : '' }}">Approved / Completed</a>
        <a href="{{ route('admin.recharges.index', ['status' => 'rejected']) }}" class="btn-reset" style="padding: 6px 14px; font-size: 12px; text-decoration: none; {{ request('status') === 'rejected' ? 'border-color: #EF4444; color: #EF4444; font-weight: 700;' : '' }}">Rejected</a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ref ID</th>
                    <th>User</th>
                    <th>Gateway</th>
                    <th>Amount</th>
                    <th>TxID / Blockchain Hash</th>
                    <th>Date & Time</th>
                    <th>Status</th>
                    <th class="text-center">Verification Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($deposits as $d)
                    <tr>
                        <td style="font-family: 'JetBrains Mono', monospace; font-size: 12px; font-weight: 700; color: #3B82F6;">{{ $d->trx_id }}</td>
                        <td style="font-weight: 600; color: var(--text-primary);">{{ $d->username ?? 'asadulislam17p' }}</td>
                        <td><span class="type-badge">{{ $d->currency }}</span></td>
                        <td style="font-weight: 800; color: #10B981; font-family: 'JetBrains Mono', monospace; font-size: 14px;">
                            ${{ number_format($d->amount, 2) }}
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: var(--text-secondary); max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                    {{ $d->txid ?? 'N/A' }}
                                </span>
                                @if($d->txid)
                                    <button type="button" class="btn-reset" style="padding: 2px 6px; font-size: 10px;" onclick="copyText('{{ $d->txid }}', 'TxID')" title="Copy TxID">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                        <td style="font-size: 11px; color: var(--text-muted);">{{ $d->created_at->format('Y-m-d H:i') }}</td>
                        <td>
                            @if($d->status === 'completed')
                                <span style="background: rgba(16,185,129,0.15); color: #10B981; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                                    Approved
                                </span>
                            @elseif($d->status === 'pending')
                                <span style="background: rgba(245,158,11,0.15); color: #F59E0B; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                                    Pending Review
                                </span>
                            @else
                                <span style="background: rgba(239,68,68,0.15); color: #EF4444; font-size: 11px; font-weight: 700; padding: 2px 7px; border-radius: 4px;">
                                    Rejected
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($d->status === 'pending')
                                <div style="display: flex; gap: 6px; justify-content: center;">
                                    <!-- Approve Button -->
                                    <form action="{{ route('admin.recharges.approve', $d->id) }}" method="POST" onsubmit="return confirm('Approve this recharge and credit ${{ number_format($d->amount, 2) }} to {{ $d->username }}?');">
                                        @csrf
                                        <button type="submit" class="btn-search" style="padding: 4px 10px; font-size: 11px; background: #10B981;" title="Approve & Credit Balance">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                    </form>

                                    <!-- Reject Button -->
                                    <form action="{{ route('admin.recharges.reject', $d->id) }}" method="POST" onsubmit="return confirm('Reject this deposit request?');">
                                        @csrf
                                        <button type="submit" class="btn-reset" style="padding: 4px 10px; font-size: 11px; color: #EF4444; border-color: rgba(239,68,68,0.3);" title="Reject Request">
                                            <i class="fa-solid fa-xmark"></i> Reject
                                        </button>
                                    </form>
                                </div>
                            @else
                                <span style="color: var(--text-muted); font-size: 11px;">Processed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No deposit requests found matching criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bulk-bar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 11.5px; color: var(--text-muted);">Total: {{ $deposits->total() }} records</span>
            <form action="{{ route('admin.recharges.clearAll') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Clear all deposit and recharge transaction logs?');">
                @csrf
                <button type="submit" class="btn-reset" style="background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11.5px; font-weight: 800; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Clear All Recharges (Очистить журнал)
                </button>
            </form>
        </div>
        <div>{{ $deposits->links() }}</div>
    </div>
</div>
@endsection
