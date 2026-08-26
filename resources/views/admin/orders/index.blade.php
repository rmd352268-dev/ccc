@extends('admin.layout')

@section('title', 'Client Orders History')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 800; color: #FFF;">Client Orders & Purchases</h2>
    <p style="font-size: 13px; color: #94A3B8; margin-top: 3px;">Full log of all completed purchases made on the marketplace.</p>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date & Time</th>
                    <th>Cards Purchased</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th class="text-center">Export</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td style="font-family: var(--font-mono); font-weight: 700; color: #60A5FA;">
                            {{ $order->order_number }}
                        </td>
                        <td style="color: #94A3B8; font-size: 12px;">{{ $order->created_at->format('Y-m-d H:i:s') }}</td>
                        <td><span class="type-badge">{{ $order->item_count }} Cards</span></td>
                        <td style="font-weight: 700; color: var(--accent-emerald); font-family: var(--font-mono);">
                            ${{ number_format($order->total_amount, 2) }}
                        </td>
                        <td>
                            <span style="background: rgba(16,185,129,0.15); color: #34D399; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                COMPLETED
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('orders.downloadTxt', $order->id) }}" class="btn-reset" style="padding: 3px 8px; font-size: 11px; text-decoration: none;">
                                <i class="fa-solid fa-download"></i> TXT
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No client orders placed yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bulk-bar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 11.5px; color: var(--text-muted);">Total: {{ $orders->total() }} orders</span>
            <form action="{{ route('admin.orders.clearAll') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Clear all order purchase history?');">
                @csrf
                <button type="submit" class="btn-reset" style="background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11.5px; font-weight: 800; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Clear All Orders (Очистить историю)
                </button>
            </form>
        </div>
        <div>{{ $orders->links() }}</div>
    </div>
</div>
@endsection
