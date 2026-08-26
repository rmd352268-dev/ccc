@extends('layouts.app')

@section('title', 'News & System Updates')

@section('content')
<div style="margin-bottom: 24px;">
    <h2 style="font-size: 20px; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 8px;">
        <i class="fa-regular fa-newspaper" style="color: var(--accent-green);"></i> System Announcements & News
    </h2>
    <p style="font-size: 13px; color: #94A3B8; margin-top: 4px;">
        Stay updated with new base dumps, database refreshes, promotions, and maintenance notices.
    </p>
</div>

<div style="display: flex; flex-direction: column; gap: 16px;">
    @foreach($news as $item)
        <div class="filter-card" style="{{ $item->is_pinned ? 'border-left: 4px solid var(--accent-green);' : '' }}">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    @if($item->is_pinned)
                        <span class="type-badge" style="background: rgba(16,185,129,0.2); color: #34D399;">
                            <i class="fa-solid fa-thumbtack"></i> PINNED
                        </span>
                    @endif
                    <span class="type-badge">{{ $item->category }}</span>
                    <h3 style="font-size: 16px; font-weight: 700; color: #FFF;">{{ $item->title }}</h3>
                </div>
                <span style="font-size: 12px; color: #94A3B8; font-family: var(--font-mono);">
                    {{ $item->created_at->format('M d, Y') }}
                </span>
            </div>

            <p style="font-size: 13px; color: #CBD5E1; line-height: 1.6;">
                {{ $item->content }}
            </p>
        </div>
    @endforeach
</div>
@endsection
