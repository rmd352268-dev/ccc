@extends('layouts.app')

@section('title', 'My Orders')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-receipt" style="color: var(--accent-green);"></i> Order History
    </h2>
    <a href="{{ route('marketplace.index') }}" class="btn-reset" style="text-decoration: none;">
        <i class="fa-solid fa-plus"></i> Buy More Cards
    </a>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Items</th>
                    <th>Total Price</th>
                    <th>Status</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    <tr>
                        <td>
                            <a href="{{ route('orders.show', $order->id) }}" style="color: #60A5FA; font-weight: 600; text-decoration: none; font-family: var(--font-mono);">
                                {{ $order->order_number }}
                            </a>
                        </td>
                        <td style="color: #94A3B8; font-size: 12px;">{{ $order->created_at->format('Y-m-d H:i') }}</td>
                        <td><span class="type-badge">{{ $order->item_count }} Cards</span></td>
                        <td><span class="price-c">${{ number_format($order->total_amount, 2) }}</span></td>
                        <td>
                            <span style="background: rgba(16,185,129,0.15); color: #34D399; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 4px;">
                                Completed
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('orders.show', $order->id) }}" class="btn-reset" style="padding: 4px 10px; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="fa-solid fa-eye"></i> View Details
                            </a>
                            <a href="{{ route('orders.downloadTxt', $order->id) }}" class="btn-search" style="padding: 4px 10px; font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; margin-left: 6px;">
                                <i class="fa-solid fa-download"></i> TXT
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            <i class="fa-solid fa-receipt" style="font-size: 32px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                            No orders found yet. Any cards purchased will appear here with full data and instant export.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($orders->hasPages())
        <div class="bulk-bar">
            <div></div>
            <div>{{ $orders->links() }}</div>
        </div>
    @endif
</div>
@endsection
