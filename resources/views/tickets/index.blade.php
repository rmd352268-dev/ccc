@extends('layouts.app')

@section('title', 'Support Tickets')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700; color: #FFF; display: flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-headset" style="color: var(--accent-green);"></i> Support Desk
        </h2>
        <p style="font-size: 13px; color: #94A3B8; margin-top: 4px;">
            Need help with your orders or balance? Open a ticket or chat with our 24/7 live support bot.
        </p>
    </div>

    <div style="display: flex; gap: 10px; align-items: center;">
        <a href="https://t.me/payate_desk_bot" target="_blank" class="btn-search" style="background: linear-gradient(135deg, #0284C7, #0369A1); border-color: #38BDF8; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-brands fa-telegram" style="font-size: 16px;"></i> 24/7 Live Telegram Support
        </a>
        <a href="{{ route('tickets.create') }}" class="btn-search" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
            <i class="fa-solid fa-plus"></i> Open Web Ticket
        </a>
    </div>
</div>

<!-- 💬 24/7 Live Telegram Support Bot Hero Card -->
<div style="background: linear-gradient(135deg, rgba(14, 165, 233, 0.12), rgba(15, 23, 42, 0.85)); border: 1.5px solid rgba(56, 189, 248, 0.35); border-radius: 12px; padding: 18px 22px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.35);">
    <div style="display: flex; align-items: center; gap: 16px;">
        <div style="width: 50px; height: 50px; border-radius: 12px; background: rgba(56, 189, 248, 0.15); border: 1px solid rgba(56, 189, 248, 0.4); display: flex; align-items: center; justify-content: center; font-size: 26px; color: #38BDF8; flex-shrink: 0;">
            <i class="fa-brands fa-telegram"></i>
        </div>
        <div>
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                <h3 style="font-size: 16px; font-weight: 800; color: #FFF; margin: 0;">Instant Live Chat Support Bot</h3>
                <span style="background: rgba(34, 197, 94, 0.2); color: #4ADE80; border: 1px solid rgba(34, 197, 94, 0.4); font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 9999px; text-transform: uppercase; letter-spacing: 0.5px;">
                    ● Online 24/7
                </span>
            </div>
            <p style="font-size: 13px; color: #94A3B8; margin: 4px 0 0 0;">
                For the fastest support, click to message our Telegram Support Bot directly. Send screenshots, order numbers, or questions.
            </p>
        </div>
    </div>
    <a href="https://t.me/payate_desk_bot" target="_blank" style="background: #0284C7; hover: #0369A1; color: #FFF; padding: 10px 18px; border-radius: 8px; font-size: 13px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; box-shadow: 0 2px 10px rgba(2, 132, 199, 0.4);">
        <i class="fa-brands fa-telegram" style="font-size: 16px;"></i> Message @payate_desk_bot <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 11px;"></i>
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
