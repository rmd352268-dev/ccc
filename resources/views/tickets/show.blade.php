@extends('layouts.app')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div style="max-width: 850px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
            <a href="{{ route('tickets.index') }}" style="color: #94A3B8; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Tickets
            </a>
            <h2 style="font-size: 20px; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 10px;">
                <span>{{ $ticket->subject }}</span>
                <span class="type-badge">{{ $ticket->department }}</span>
            </h2>
        </div>

        <div>
            <span style="font-family: var(--font-mono); color: #60A5FA; font-weight: 700;">{{ $ticket->ticket_number }}</span>
        </div>
    </div>

    <!-- Messages Thread -->
    <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
        @foreach($ticket->messages as $msg)
            @php $isSupport = ($msg->sender === 'support'); @endphp
            <div class="filter-card" style="{{ $isSupport ? 'border-left: 4px solid #3B82F6; background: rgba(59,130,246,0.05);' : 'border-left: 4px solid var(--accent-green);' }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid {{ $isSupport ? 'fa-shield-halved' : 'fa-user' }}" style="color: {{ $isSupport ? '#60A5FA' : '#10B981' }};"></i>
                        <strong style="color: #FFF; font-size: 13px;">{{ $isSupport ? 'Support Agent' : 'You (Client)' }}</strong>
                    </div>
                    <span style="font-size: 11px; color: #94A3B8;">{{ $msg->created_at->format('M d, H:i') }}</span>
                </div>
                <div style="font-size: 13px; color: #CBD5E1; line-height: 1.6; white-space: pre-wrap;">{{ $msg->message }}</div>
            </div>
        @endforeach
    </div>

    <!-- Reply Box -->
    <div class="filter-card">
        <h3 style="font-size: 15px; font-weight: 700; color: #FFF; margin-bottom: 12px;">
            Add a Reply
        </h3>
        <form action="{{ route('tickets.reply', $ticket->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <textarea name="message" class="form-control" style="min-height: 100px; height: 100px;" placeholder="Write your reply..." required></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; margin-top: 12px;">
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-reply"></i> Send Reply
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
