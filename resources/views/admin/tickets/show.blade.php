@extends('admin.layout')

@section('title', 'Ticket #' . $ticket->ticket_number)

@section('content')
<div style="max-width: 850px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div>
            <a href="{{ route('admin.tickets.index') }}" style="color: #94A3B8; font-size: 12px; text-decoration: none;">
                <i class="fa-solid fa-arrow-left"></i> Back to Tickets
            </a>
            <h2 style="font-size: 20px; font-weight: 800; color: #FFF; margin-top: 4px;">
                {{ $ticket->subject }}
            </h2>
            <div style="display: flex; gap: 8px; margin-top: 6px;">
                <span class="type-badge">{{ $ticket->department }}</span>
                <span class="type-badge" style="color: #F59E0B;">Priority: {{ $ticket->priority }}</span>
                <span class="type-badge" style="color: var(--accent-emerald);">Status: {{ $ticket->status }}</span>
            </div>
        </div>

        <span style="font-family: var(--font-mono); color: #60A5FA; font-weight: 700;">{{ $ticket->ticket_number }}</span>
    </div>

    <!-- Messages Thread -->
    <div style="display: flex; flex-direction: column; gap: 14px; margin-bottom: 20px;">
        @foreach($ticket->messages as $msg)
            @php $isSupport = ($msg->sender === 'support'); @endphp
            <div class="filter-card" style="{{ $isSupport ? 'border-left: 4px solid #3B82F6; background: rgba(59,130,246,0.05);' : 'border-left: 4px solid var(--accent-emerald);' }}">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid {{ $isSupport ? 'fa-shield-halved' : 'fa-user' }}" style="color: {{ $isSupport ? '#60A5FA' : 'var(--accent-emerald)' }};"></i>
                        <strong style="color: #FFF; font-size: 13px;">{{ $isSupport ? 'Admin / Support Agent' : 'Client (User)' }}</strong>
                    </div>
                    <span style="font-size: 11px; color: #94A3B8;">{{ $msg->created_at->format('M d, H:i') }}</span>
                </div>
                <div style="font-size: 13px; color: #CBD5E1; line-height: 1.5; white-space: pre-wrap;">{{ $msg->message }}</div>
            </div>
        @endforeach
    </div>

    <!-- Reply Box -->
    <div class="filter-card">
        <h3 style="font-size: 15px; font-weight: 700; color: #FFF; margin-bottom: 12px;">Post Staff Reply</h3>
        <form action="{{ route('admin.tickets.reply', $ticket->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <textarea name="message" class="form-control" style="min-height: 110px;" placeholder="Type your response to the customer..." required></textarea>
            </div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 14px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 12px; color: #94A3B8;">Set Status:</span>
                    <select name="status" class="form-select" style="width: auto; padding: 4px 10px;">
                        <option value="Answered" selected>Answered</option>
                        <option value="Closed">Closed</option>
                        <option value="Open">Open</option>
                    </select>
                </div>

                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-paper-plane"></i> Send Reply
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
