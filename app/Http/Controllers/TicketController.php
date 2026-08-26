<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index()
    {
        $userId = session('user_id');
        $username = session('user_username');

        if (!$userId && !$username) {
            return redirect()->route('login')->with('error', 'Please log in to access support tickets.');
        }

        $tickets = Ticket::with('messages')
            ->where(function ($q) use ($userId, $username) {
                if ($userId) $q->where('user_id', $userId);
                if ($username) $q->orWhere('username', $username);
            })
            ->latest()
            ->get();

        return view('tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('tickets.create');
    }

    public function store(Request $request)
    {
        $userId = session('user_id');
        $username = session('user_username');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please log in to submit a ticket.');
        }

        $request->validate([
            'subject' => 'required|string|max:200',
            'department' => 'required|string',
            'priority' => 'required|string',
            'message' => 'required|string',
        ]);

        $ticket = Ticket::create([
            'ticket_number' => 'TCK-' . mt_rand(10000, 99999),
            'user_id' => $userId,
            'username' => $username,
            'subject' => $request->subject,
            'department' => $request->department,
            'priority' => $request->priority,
            'status' => 'Open',
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender' => 'user',
            'message' => $request->message,
        ]);

        return redirect()->route('tickets.show', $ticket->id)->with('success', 'Ticket created successfully! Our support staff will review shortly.');
    }

    public function show($id)
    {
        $userId = session('user_id');
        $username = session('user_username');

        $ticket = Ticket::with('messages')
            ->where(function ($q) use ($userId, $username) {
                if ($userId) $q->where('user_id', $userId);
                if ($username) $q->orWhere('username', $username);
            })
            ->findOrFail($id);

        return view('tickets.show', compact('ticket'));
    }

    public function reply(Request $request, $id)
    {
        $userId = session('user_id');
        $username = session('user_username');

        $request->validate(['message' => 'required|string']);

        $ticket = Ticket::where(function ($q) use ($userId, $username) {
            if ($userId) $q->where('user_id', $userId);
            if ($username) $q->orWhere('username', $username);
        })->findOrFail($id);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender' => 'user',
            'message' => $request->message,
        ]);

        $ticket->update(['status' => 'Open']);

        return back()->with('success', 'Reply submitted.');
    }
}
