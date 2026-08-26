@extends('layouts.app')

@section('title', '💎 Super Shop - High Balance & VIP Private Vault')

@section('content')
<!-- Super Shop VIP Hero Header -->
<div style="background: linear-gradient(135deg, rgba(24, 99, 149, 0.25) 0%, rgba(212, 175, 55, 0.2) 50%, rgba(14, 14, 22, 0.9) 100%); border: 1px solid rgba(212, 175, 55, 0.4); border-radius: 14px; padding: 22px 26px; margin-bottom: 22px; box-shadow: 0 10px 30px rgba(0,0,0,0.6);">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 14px;">
        <div>
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                <span class="status-badge-sale"><i class="fa-solid fa-crown"></i> PRIVATE VAULT</span>
                <span class="status-badge-new"><i class="fa-solid fa-gem"></i> VIP EXCLUSIVE</span>
                <span style="font-size: 11px; font-weight: 800; color: var(--gold-bright); letter-spacing: 0.5px;">99% VALID GUARANTEE</span>
            </div>
            <h1 class="font-display" style="font-size: 22px; font-weight: 800; color: #FFF; letter-spacing: -0.02em;">
                💎 SUPER SHOP & VIP PRIVATE VAULT
            </h1>
            <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px; max-width: 800px;">
                Exclusive High-Tier Cards with Guaranteed High Spending Limits ($5,000 - $25,000+), Fullz (SSN, DOB, Phone, UA, Email Pass), and Top-Tier Banking Institutions.
            </p>
        </div>

        <div style="text-align: right;">
            <div style="font-size: 12px; color: var(--text-secondary);">Available VIP Stock:</div>
            <div class="font-display" style="font-size: 24px; font-weight: 800; color: var(--gold-bright);">
                {{ $cards->total() }} Cards
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-card" style="margin-bottom: 20px;">
    <form action="{{ route('super_shop.index') }}" method="GET">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px; align-items: flex-end;">
            <div class="form-group">
                <label class="form-label">Search BINs (6 digits)</label>
                <input type="text" name="bins" value="{{ request('bins') }}" class="form-control" placeholder="e.g. 414720, 542418">
            </div>

            <div class="form-group">
                <label class="form-label">Search Bank Name</label>
                <input type="text" name="bank" value="{{ request('bank') }}" class="form-control" placeholder="e.g. CHASE, BARCLAYS">
            </div>

            <div class="form-group">
                <label class="form-label">Country</label>
                <select name="country" class="form-select">
                    <option value="all">-- All Countries --</option>
                    @foreach($countries as $c)
                        <option value="{{ $c->country_code }}" {{ request('country') == $c->country_code ? 'selected' : '' }}>
                            {{ $c->country_name }} ({{ $c->country_code }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn-search" style="flex: 1; justify-content: center;">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="{{ route('super_shop.index') }}" class="btn-reset" style="padding: 8px 14px;">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>
        </div>
    </form>
</div>

<!-- Super Shop Cards Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 40px;" class="text-center">
                        <input type="checkbox" id="select-all-header" onchange="toggleSelectAll(this)">
                    </th>
                    <th>VIP Tier</th>
                    <th>BIN / Brand</th>
                    <th>Type</th>
                    <th>Bank Institution</th>
                    <th>Country</th>
                    <th>Includes Fullz</th>
                    <th>Price</th>
                    <th class="text-center" style="width: 110px;">Purchase</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cards as $card)
                    <tr>
                        <td class="text-center">
                            <input type="checkbox" class="card-row-checkbox" value="{{ $card->id }}" onchange="updateSelectedCount()">
                        </td>
                        <td>
                            @if($card->brand === 'AMEX')
                                <span class="status-badge-sale"><i class="fa-solid fa-crown"></i> CENTURION</span>
                            @elseif($card->price_c >= 3.50)
                                <span class="status-badge-new"><i class="fa-solid fa-gem"></i> WORLD ELITE</span>
                            @else
                                <span class="status-badge-sale"><i class="fa-solid fa-star"></i> SUPER VIP</span>
                            @endif
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 6px;">
                                <span class="bin-tag">{{ $card->bin }}******</span>
                                <span class="brand-badge brand-{{ strtolower($card->brand) }}">{{ $card->brand }}</span>
                            </div>
                        </td>
                        <td><span class="type-badge">{{ $card->type }}</span></td>
                        <td style="color: var(--text-primary); font-weight: 600;">{{ $card->bank }}</td>
                        <td>
                            <div class="country-cell">
                                <span class="country-flag">{{ $card->flag }}</span>
                                <span class="country-code">{{ $card->country_code }}</span>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; gap: 4px; font-size: 11px;">
                                <span title="Holder Name" style="color: {{ $card->has_name ? '#10B981' : '#64748B' }}; font-weight: 700;">NAME</span> •
                                <span title="Full Address" style="color: {{ $card->has_address ? '#10B981' : '#64748B' }}; font-weight: 700;">ADDR</span> •
                                <span title="Phone" style="color: {{ $card->has_phone ? '#10B981' : '#64748B' }}; font-weight: 700;">PHONE</span> •
                                <span title="User Agent / IP" style="color: {{ $card->has_user_agent ? '#10B981' : '#64748B' }}; font-weight: 700;">UA</span>
                            </div>
                        </td>
                        <td>
                            <div class="price-display">
                                <span class="price-c">${{ number_format($card->price_c, 2) }}</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn-buy-cart" onclick="addToCart({{ $card->id }})">
                                <i class="fa-solid fa-cart-shopping"></i> Add
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No Super Shop cards match your filter criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Bulk Action Footer -->
    <div class="bulk-bar">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 13px; color: var(--text-secondary);">Selected: <strong id="selected-count" style="color: var(--gold-bright);">0</strong> items</span>
            <button type="button" id="btn-bulk-add" class="btn-search" onclick="addSelectedToCart()" style="padding: 6px 16px; font-size: 12px;" disabled>
                <i class="fa-solid fa-cart-plus"></i> Add Selected to Cart
            </button>
        </div>
        <div>
            {{ $cards->links() }}
        </div>
    </div>
</div>
@endsection
