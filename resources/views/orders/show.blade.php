@extends('layouts.app')

@section('title', 'Order Details - ' . $order->order_number)

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <a href="{{ route('orders.index') }}" style="color: #94A3B8; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Orders
        </a>
        <h2 style="font-size: 20px; font-weight: 700; color: #FFF; font-family: var(--font-mono);">
            {{ $order->order_number }}
        </h2>
    </div>

    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <button type="button" class="btn-reset" onclick="copyAllOrderCards()" style="padding: 7px 14px; font-size: 12px; font-weight: 700;">
            <i class="fa-regular fa-copy"></i> Copy All Data
        </button>
        <a href="{{ route('orders.downloadRaw', $order->id) }}" class="btn-reset" style="text-decoration: none; padding: 7px 14px; font-size: 12px; font-weight: 700; color: #10B981; border-color: rgba(16,185,129,0.4);">
            <i class="fa-solid fa-file-code"></i> Download Raw TXT
        </a>
        <a href="{{ route('orders.downloadTxt', $order->id) }}" class="btn-search" style="text-decoration: none; padding: 7px 14px; font-size: 12px;">
            <i class="fa-solid fa-download"></i> Download Full Receipt
        </a>
    </div>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Card Number</th>
                    <th>Exp</th>
                    <th>CVV</th>
                    <th>Holder Name</th>
                    <th>Address / City / State / ZIP</th>
                    <th>Phone / Email</th>
                    <th>Bank</th>
                    <th>Price</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $idx => $item)
                    @php
                        $d = $item->card_details;
                        $cardString = ($d['card_number'] ?? '') . '|' . ($d['exp_date'] ?? '') . '|' . ($d['cvv'] ?? '') . '|' . ($d['holder_name'] ?? '') . '|' . ($d['address'] ?? '') . '|' . ($d['zip'] ?? '') . '|' . ($d['phone'] ?? '') . '|' . ($d['email'] ?? '');
                    @endphp
                    <tr>
                        <td style="color: var(--text-muted); font-size: 11px;">{{ $idx + 1 }}</td>
                        <td>
                            <span style="font-family: var(--font-mono); font-weight: 700; color: #60A5FA; letter-spacing: 0.5px;">
                                {{ chunk_split($d['card_number'] ?? '0000000000000000', 4, ' ') }}
                            </span>
                        </td>
                        <td>
                            <span style="font-family: var(--font-mono); font-weight: 600; color: #CBD5E1;">
                                {{ $d['exp_date'] ?? 'MM/YY' }}
                            </span>
                        </td>
                        <td>
                            <span style="font-family: var(--font-mono); font-weight: 700; color: #10B981; background: rgba(16,185,129,0.15); padding: 2px 6px; border-radius: 4px;">
                                {{ $d['cvv'] ?? '***' }}
                            </span>
                        </td>
                        <td style="font-weight: 600; color: #FFF;">
                            {{ $d['holder_name'] ?? 'N/A' }}
                        </td>
                        <td style="font-size: 12px; color: #94A3B8; max-width: 250px; white-space: normal;">
                            {{ $d['address'] ?? '' }}, {{ $d['city'] ?? '' }}, {{ $d['state'] ?? '' }} {{ $d['zip'] ?? '' }} ({{ $d['country_code'] ?? '' }})
                        </td>
                        <td style="font-size: 12px; color: #CBD5E1; max-width: 200px; white-space: normal;">
                            <div><i class="fa-solid fa-phone" style="font-size: 10px; color: var(--text-muted);"></i> {{ $d['phone'] ?? 'N/A' }}</div>
                            <div style="color: #60A5FA; font-size: 11px;"><i class="fa-regular fa-envelope"></i> {{ $d['email'] ?? 'N/A' }}</div>
                        </td>
                        <td style="font-size: 12px; color: #CBD5E1;">
                            {{ $d['bank'] ?? 'N/A' }}
                        </td>
                        <td>
                            <span class="price-c">${{ number_format($item->price, 2) }}</span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn-reset" style="padding: 4px 10px; font-size: 12px;" onclick="copyText('{{ $cardString }}', 'Full Card Data')" title="Copy Full Card Data">
                                <i class="fa-regular fa-copy"></i> Copy
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@push('scripts')
<script>
function copyAllOrderCards() {
    const cardLines = [
        @foreach($order->items as $item)
            @php
                $d = $item->card_details;
                $cs = ($d['card_number'] ?? '') . '|' . ($d['exp_date'] ?? '') . '|' . ($d['cvv'] ?? '') . '|' . ($d['holder_name'] ?? '') . '|' . ($d['address'] ?? '') . '|' . ($d['city'] ?? '') . '|' . ($d['state'] ?? '') . '|' . ($d['zip'] ?? '') . '|' . ($d['country_code'] ?? '') . '|' . ($d['phone'] ?? '') . '|' . ($d['email'] ?? '');
            @endphp
            "{{ trim($cs, '|') }}",
        @endforeach
    ];
    copyText(cardLines.join("\n"), "All {{ count($order->items) }} Card Lines");
}
</script>
@endpush
@endsection
