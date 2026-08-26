@extends('layouts.app')

@section('title', 'Wholesale Bulk Packs - Discounted Rates')

@section('content')
<div style="margin-bottom: 24px;">
    <h2 style="font-size: 20px; font-weight: 700; color: var(--text-primary); display: flex; align-items: center; gap: 8px;">
        <i class="fa-solid fa-box-archive" style="color: var(--gold-primary);"></i> Wholesale Packages & Bulk Bundles
    </h2>
    <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
        Purchase high-volume bulk bundles with up to 40% discount compared to single retail cards. Once purchased, bundles are exclusively allocated to your account.
    </p>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 20px;">
    @forelse($packs as $pack)
        <div class="filter-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; overflow: hidden;">
            <div style="position: absolute; top: 12px; right: -30px; background: #EF4444; color: #FFF; font-size: 10px; font-weight: 800; padding: 4px 30px; transform: rotate(45deg); box-shadow: 0 2px 8px rgba(0,0,0,0.5);">
                SALE
            </div>

            <div>
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                    <span class="type-badge" style="background: rgba(59,130,246,0.15); color: #3B82F6;">{{ $pack->country }}</span>
                    <span class="type-badge">{{ $pack->type }}</span>
                </div>

                <h3 style="font-size: 17px; font-weight: 700; color: var(--text-primary); margin-bottom: 8px;">
                    {{ $pack->title }}
                </h3>

                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5; margin-bottom: 16px;">
                    {{ $pack->description }}
                </p>

                <div style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 6px; padding: 10px 14px; margin-bottom: 16px;">
                    <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--text-secondary); margin-bottom: 4px;">
                        <span>Quantity:</span>
                        <strong style="color: var(--text-primary);">{{ $pack->card_count }} Cards</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--text-secondary);">
                        <span>Per Card Rate:</span>
                        <strong style="color: #10B981;">${{ number_format($pack->price / max(1, $pack->card_count), 2) }}/ea</strong>
                    </div>
                </div>
            </div>

            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <div>
                        <span style="font-size: 12px; color: var(--text-muted); text-decoration: line-through;">${{ number_format($pack->original_price, 2) }}</span>
                        <div style="font-size: 22px; font-weight: 800; color: #10B981; font-family: 'JetBrains Mono', monospace;">${{ number_format($pack->price, 2) }}</div>
                    </div>
                    <form action="{{ route('wholesale.buy', $pack->id) }}" method="POST" onsubmit="return confirm('Confirm purchase of wholesale pack \'{{ $pack->title }}\' for ${{ number_format($pack->price, 2) }}?');">
                        @csrf
                        <button type="submit" class="btn-search" style="padding: 10px 20px;">
                            <i class="fa-solid fa-bolt"></i> Buy Bundle
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1 / -1; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; text-align: center; padding: 50px 20px; color: var(--text-muted);">
            <i class="fa-solid fa-boxes-stacked" style="font-size: 42px; opacity: 0.35; margin-bottom: 12px; display: block;"></i>
            <h3 style="font-size: 16px; font-weight: 700; color: var(--text-primary); margin-bottom: 6px;">No Wholesale Packs Available</h3>
            <p style="font-size: 13px; max-width: 500px; margin: 0 auto 16px auto;">
                All current wholesale packages have been purchased. New bundles will appear here as soon as they are stocked by administration.
            </p>
            <a href="{{ route('marketplace.index') }}" class="btn-search" style="display: inline-flex; text-decoration: none; padding: 8px 18px;">
                <i class="fa-solid fa-credit-card"></i> Browse Single Cards
            </a>
        </div>
    @endforelse
</div>
@endsection
