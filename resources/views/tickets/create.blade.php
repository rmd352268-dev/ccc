@extends('layouts.app')

@section('title', 'Open New Support Ticket')

@section('content')
<div style="max-width: 700px; margin: 0 auto;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('tickets.index') }}" style="color: #94A3B8; text-decoration: none; font-size: 13px; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
            <i class="fa-solid fa-arrow-left"></i> Back to Tickets
        </a>
        <h2 style="font-size: 20px; font-weight: 700; color: #FFF;">
            Open New Support Ticket
        </h2>
    </div>

    <div class="filter-card">
        <form action="{{ route('tickets.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" placeholder="Brief summary of your question or issue" required>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Department</label>
                    <select name="department" class="form-select">
                        <option value="General Support">General Support</option>
                        <option value="Billing & Deposit">Billing & Deposit</option>
                        <option value="Card Replacement">Card Replacement</option>
                        <option value="API & Wholesale">API & Wholesale</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Priority</label>
                    <select name="priority" class="form-select">
                        <option value="Low">Low</option>
                        <option value="Medium" selected>Medium</option>
                        <option value="High">High (Urgent)</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Message Details</label>
                <textarea name="message" class="form-control" style="min-height: 120px; height: 120px;" placeholder="Please describe your inquiry in detail..." required></textarea>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <a href="{{ route('tickets.index') }}" class="btn-reset" style="text-decoration: none;">Cancel</a>
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-paper-plane"></i> Submit Ticket
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
