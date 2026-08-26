@extends('admin.layout')

@section('title', 'Cards Inventory Management')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Cards Inventory</h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 3px;">Manage all cards, edit records, add new stock, or clear sold records.</p>
    </div>

    <div style="display: flex; gap: 10px;">
        <form action="{{ route('admin.cards.clearSold') }}" method="POST" onsubmit="return confirm('Clear all sold cards from database?');">
            @csrf
            <button type="submit" class="btn-reset" style="color: #EF4444; border-color: rgba(239,68,68,0.3);">
                <i class="fa-solid fa-trash-can"></i> Clear Sold Cards
            </button>
        </form>
        <a href="{{ route('admin.cards.bulk') }}" class="btn-reset" style="text-decoration: none;">
            <i class="fa-solid fa-file-import"></i> Bulk Import
        </a>
        <a href="{{ route('admin.cards.create') }}" class="btn-search" style="text-decoration: none;">
            <i class="fa-solid fa-plus"></i> Add Single Card
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="filter-card" style="margin-bottom: 16px;">
    <form action="{{ route('admin.cards.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center;">
        <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search by BIN, Card Number, Bank, Base or Country..." style="flex: 2;">
        
        <button type="submit" class="btn-search" style="padding: 7px 16px;">
            <i class="fa-solid fa-filter"></i> Search & Filter
        </button>
    </form>
</div>

<!-- Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Bin</th>
                    <th>Brand</th>
                    <th>Type</th>
                    <th>Country</th>
                    <th>Card Number</th>
                    <th>Exp / CVV</th>
                    <th>Bank</th>
                    <th>Base</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th class="text-center" style="min-width: 140px;">Admin Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cards as $card)
                    <tr>
                        <td style="color: var(--text-muted); font-size: 11px; font-weight: 700;">#{{ $card->id }}</td>
                        <td><span class="bin-tag">{{ $card->bin }}</span></td>
                        <td>
                            @php $bLower = strtolower($card->brand); @endphp
                            @if($bLower === 'visa')
                                <span class="brand-badge brand-visa"><i class="fa-brands fa-cc-visa"></i> VISA</span>
                            @elseif($bLower === 'mastercard')
                                <span class="brand-badge brand-mastercard"><i class="fa-brands fa-cc-mastercard"></i> MC</span>
                            @elseif($bLower === 'amex')
                                <span class="brand-badge brand-amex"><i class="fa-brands fa-cc-amex"></i> AMEX</span>
                            @elseif($bLower === 'discover')
                                <span class="brand-badge brand-discover"><i class="fa-brands fa-cc-discover"></i> DISC</span>
                            @else
                                <span class="brand-badge brand-visa">{{ $card->brand }}</span>
                            @endif
                        </td>
                        <td><span class="type-badge">{{ $card->type }}</span></td>
                        <td>
                            <span class="country-code">{{ \App\Helpers\CountryHelper::getFlag($card->country_code) }} {{ $card->country_code }}</span>
                        </td>
                        <td style="font-family: monospace; font-size: 12px; color: #2563EB; font-weight: 700;">
                            {{ substr($card->card_number, 0, 6) }}******{{ substr($card->card_number, -4) }}
                        </td>
                        <td style="font-family: monospace; font-size: 11px; color: var(--text-primary);">
                            {{ $card->exp_date }} | {{ $card->cvv }}
                        </td>
                        <td style="color: var(--text-primary); font-size: 12px; font-weight: 600;">{{ $card->bank }}</td>
                        <td style="color: var(--text-muted); font-size: 11px; font-family: monospace;">{{ $card->base_name }}</td>
                        <td><span class="price-c">${{ number_format($card->price_c, 2) }}</span></td>
                        <td>
                            <span style="font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; background: {{ $card->status === 'available' ? 'rgba(5,150,105,0.15)' : 'rgba(220,38,38,0.15)' }}; color: {{ $card->status === 'available' ? '#059669' : '#DC2626' }};">
                                {{ strtoupper($card->status) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div style="display: inline-flex; gap: 6px; align-items: center;">
                                <a href="{{ route('admin.cards.edit', $card->id) }}" class="btn-search" style="padding: 4px 8px; font-size: 11px; text-decoration: none;" title="Edit Card">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <form action="{{ route('admin.cards.delete', $card->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this card?');">
                                    @csrf
                                    <button type="submit" class="btn-reset" style="padding: 4px 8px; font-size: 11px; color: #EF4444; border-color: rgba(239,68,68,0.3);" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="12" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No cards found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bulk-bar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <span style="font-size: 12px; color: var(--text-muted);">Total: {{ $cards->total() }} records</span>
            <form action="{{ route('admin.cards.clearAll') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Are you sure you want to completely CLEAR ALL credit cards from the database? This cannot be undone!');">
                @csrf
                <button type="submit" class="btn-reset" style="background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11.5px; font-weight: 800; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Clear All Cards (Удалить все)
                </button>
            </form>
        </div>
        <div>{{ $cards->links('vendor.pagination.custom') }}</div>
    </div>
</div>
@endsection
