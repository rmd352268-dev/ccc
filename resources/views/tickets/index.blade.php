@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-headset" style="color: var(--accent-green);"></i> Support Tickets
        </h2>
        <p style="font-size: 13px; color: #94A3B8; margin-top: 4px;">
            Need help with your orders or balance? Open a ticket with our 24/7 support team.
        </p>
    </div>

    <a href="{{ route('tickets.create') }}" class="btn-search" style="text-decoration: none;">
        <i class="fa-solid fa-plus"></i> Open New Ticket
    </a>
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
                    <th>Last Updated</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tickets as $ticket)
                    <tr>
                        <td style="font-family: var(--font-mono); font-weight: 600; color: #60A5FA;">
                            {{ $ticket->ticket_number }}
                        </td>
                        <td style="font-weight: 600; color: #FFF;">
                            <a href="{{ route('tickets.show', $ticket->id) }}" style="color: inherit; text-decoration: none;">
                                {{ $ticket->subject }}
                            </a>
                        </td>
                        <td><span class="type-badge">{{ $ticket->department }}</span></td>
                        <td>
                            @if($ticket->priority === 'High')
                                <span style="color: #EF4444; font-weight: 700; font-size: 12px;">High</span>
                            @elseif($ticket->priority === 'Medium')
                                <span style="color: #F59E0B; font-weight: 600; font-size: 12px;">Medium</span>
                            @else
                                <span style="color: #94A3B8; font-size: 12px;">Low</span>
                            @endif
                        </td>
                        <td>
                            @if($ticket->status === 'Open')
                                <span style="background: rgba(59,130,246,0.15); color: #60A5FA; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 4px;">Open</span>
                            @elseif($ticket->status === 'Answered')
                                <span style="background: rgba(16,185,129,0.15); color: #34D399; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 4px;">Answered</span>
                            @else
                                <span style="background: rgba(255,255,255,0.05); color: #94A3B8; font-size: 11px; font-weight: 600; padding: 2px 6px; border-radius: 4px;">Closed</span>
                            @endif
                        </td>
                        <td style="color: #94A3B8; font-size: 12px;">{{ $ticket->updated_at->diffForHumans() }}</td>
                        <td class="text-center">
                            <a href="{{ route('tickets.show', $ticket->id) }}" class="btn-reset" style="padding: 4px 10px; font-size: 12px; text-decoration: none;">
                                <i class="fa-regular fa-comment-dots"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px; color: var(--text-muted);">
                            No support tickets created yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
