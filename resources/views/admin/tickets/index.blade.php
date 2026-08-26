@extends('admin.layout')

@section('title', 'Support Tickets Desk')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 800; color: #FFF;">Support Desk & Client Tickets</h2>
    <p style="font-size: 13px; color: #94A3B8; margin-top: 3px;">Respond to client inquiries and resolve tickets.</p>
</div>

<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Ticket ID</th>
                    <th>Subject</th>
                    <th>Department</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Last Message</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $t)
                    <tr>
                        <td style="font-family: var(--font-mono); font-weight: 700; color: #60A5FA;">{{ $t->ticket_number }}</td>
                        <td style="font-weight: 600; color: #FFF;">{{ $t->subject }}</td>
                        <td><span class="type-badge">{{ $t->department }}</span></td>
                        <td>
                            <span style="font-size: 11px; font-weight: 700; color: {{ $t->priority === 'High' ? '#EF4444' : ($t->priority === 'Medium' ? '#F59E0B' : '#94A3B8') }};">
                                {{ $t->priority }}
                            </span>
                        </td>
                        <td>
                            <span style="font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px; background: {{ $t->status === 'Open' ? 'rgba(59,130,246,0.2)' : ($t->status === 'Answered' ? 'rgba(16,185,129,0.2)' : 'rgba(255,255,255,0.05)') }}; color: {{ $t->status === 'Open' ? '#60A5FA' : ($t->status === 'Answered' ? '#34D399' : '#94A3B8') }};">
                                {{ $t->status }}
                            </span>
                        </td>
                        <td style="color: #94A3B8; font-size: 11px;">{{ $t->updated_at->diffForHumans() }}</td>
                        <td class="text-center">
                            <a href="{{ route('admin.tickets.show', $t->id) }}" class="btn-search" style="padding: 3px 10px; font-size: 11px; text-decoration: none;">
                                <i class="fa-solid fa-reply"></i> Open & Reply
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" style="text-align: center; padding: 30px; color: var(--text-muted);">No support tickets registered.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="bulk-bar" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 11.5px; color: var(--text-muted);">Total: {{ $tickets->total() }} tickets</span>
            <form action="{{ route('admin.tickets.clearAll') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Clear all customer support tickets?');">
                @csrf
                <button type="submit" class="btn-reset" style="background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11.5px; font-weight: 800; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Clear All Tickets (Очистить все тикеты)
                </button>
            </form>
        </div>
        <div>{{ $tickets->links('vendor.pagination.custom') }}</div>
    </div>
</div>
@endsection
