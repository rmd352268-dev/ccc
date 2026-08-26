@extends('layouts.app')

@section('title', 'Shopping Cart - Checkout')

@section('content')
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
    <!-- Left: Cart Items -->
    <div>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h2 style="font-size: 18px; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-cart-shopping" style="color: var(--accent-green);"></i> Shopping Cart ({{ count($cards) }})
            </h2>
            @if(count($cards) > 0)
                <form action="{{ route('cart.clear') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn-reset" style="padding: 4px 12px; font-size: 12px;">
                        <i class="fa-solid fa-trash-can"></i> Clear Cart
                    </button>
                </form>
            @endif
        </div>

        <div class="table-card">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Bin</th>
                            <th>Brand</th>
                            <th>Type</th>
                            <th>Country</th>
                            <th>Bank</th>
                            <th>Price</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($cards as $card)
                            <tr>
                                <td><span class="bin-tag">{{ $card->bin }}</span></td>
                                <td><span class="brand-badge brand-{{ strtolower($card->brand) }}">{{ $card->brand }}</span></td>
                                <td><span class="type-badge">{{ $card->type }}</span></td>
                                <td>
                                    <span class="country-code">{{ $card->country_name }} ({{ $card->country_code }})</span>
                                </td>
                                <td style="color: #CBD5E1; font-size: 12px;">{{ $card->bank }}</td>
                                <td><span class="price-c">C$ {{ number_format($card->price_c, 2) }}</span></td>
                                <td class="text-center">
                                    <form action="{{ route('cart.remove', $card->id) }}" method="POST" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn-cart-action" style="color: #EF4444; border-color: rgba(239,68,68,0.3);" title="Remove">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                                    <i class="fa-solid fa-cart-shopping" style="font-size: 32px; margin-bottom: 10px; display: block; opacity: 0.4;"></i>
                                    Your cart is currently empty.
                                    <div style="margin-top: 12px;">
                                        <a href="{{ route('marketplace.index') }}" class="btn-search" style="text-decoration: none;">
                                            <i class="fa-solid fa-arrow-left"></i> Browse Cvv2 Marketplace
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Right: Summary -->
    <div>
        <div class="filter-card">
            <h3 style="font-size: 16px; font-weight: 700; color: #FFF; margin-bottom: 16px; border-bottom: 1px solid rgba(255,255,255,0.08); padding-bottom: 10px;">
                Order Summary
            </h3>

            @php
                $userId = session('user_id');
                $activeUser = $userId ? \App\Models\User::find($userId) : \App\Models\User::where('username', session('user_username'))->first();
                $userBalance = $activeUser ? (float)$activeUser->balance : (float)session()->get('user_balance', 0.00);
                $hasEnough = $userBalance >= $total;
            @endphp

            <div style="display: flex; justify-content: space-between; font-size: 13px; color: #94A3B8; margin-bottom: 8px;">
                <span>Total Items:</span>
                <span style="font-weight: 600; color: #FFF;">{{ count($cards) }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 13px; color: #94A3B8; margin-bottom: 8px;">
                <span>Subtotal:</span>
                <span style="font-weight: 600; color: #FFF;">${{ number_format($total, 2) }}</span>
            </div>

            <div style="display: flex; justify-content: space-between; font-size: 13px; color: #94A3B8; margin-bottom: 16px;">
                <span>Your Balance:</span>
                <span style="font-weight: 700; color: {{ $hasEnough ? '#10B981' : '#EF4444' }};">
                    ${{ number_format($userBalance, 2) }}
                </span>
            </div>

            <div style="border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 12px; margin-bottom: 20px; display: flex; justify-content: space-between; font-size: 16px;">
                <span style="font-weight: 600; color: #FFF;">Grand Total:</span>
                <span style="font-weight: 700; color: #10B981;">${{ number_format($total, 2) }}</span>
            </div>

            @if(count($cards) > 0)
                @if($hasEnough)
                    <form action="{{ route('cart.checkout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn-search" style="width: 100%; justify-content: center; padding: 12px; font-size: 14px;">
                            <i class="fa-solid fa-lock"></i> Buy & Reveal Details
                        </button>
                    </form>
                @else
                    <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 6px; padding: 10px; font-size: 12px; color: #FCA5A5; margin-bottom: 12px;">
                        ⚠️ Insufficient balance to complete this purchase. Please add funds.
                    </div>
                    <button type="button" class="btn-search" onclick="openDepositModal()" style="width: 100%; justify-content: center; background: linear-gradient(135deg, #3B82F6, #1D4ED8);">
                        <i class="fa-solid fa-plus-circle"></i> Add Funds to Balance
                    </button>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
