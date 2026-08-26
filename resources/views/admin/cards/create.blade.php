@extends('admin.layout')

@section('title', 'Add New Card')

@section('content')
<div style="max-width: 800px;">
    <div style="margin-bottom: 20px;">
        <a href="{{ route('admin.cards.index') }}" style="color: #94A3B8; font-size: 12px; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Back to Inventory
        </a>
        <h2 style="font-size: 20px; font-weight: 800; color: #FFF; margin-top: 4px;">Add Single Card</h2>
    </div>

    <div class="filter-card">
        <form action="{{ route('admin.cards.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Card Number (16 Digits)</label>
                    <input type="text" name="card_number" class="form-control" placeholder="4165980011223344" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Exp Date (MM/YY)</label>
                    <input type="text" name="exp_date" class="form-control" placeholder="08/29" required>
                </div>
                <div class="form-group">
                    <label class="form-label">CVV</label>
                    <input type="text" name="cvv" class="form-control" placeholder="789" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Brand</label>
                    <select name="brand" class="form-select">
                        <option value="VISA">VISA</option>
                        <option value="MASTERCARD">MASTERCARD</option>
                        <option value="AMEX">AMEX</option>
                        <option value="DISCOVER">DISCOVER</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="DEBIT">DEBIT</option>
                        <option value="CREDIT">CREDIT</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Price ($ USD)</label>
                    <input type="number" step="0.1" name="price_c" class="form-control" value="2.50" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank" class="form-control" placeholder="REVOLUT, LTD." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Base Dump Name</label>
                    <input type="text" name="base_name" class="form-control" value="{{ date('Y_m_d') }}_GB_FR_MANUAL" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 14px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Country Code (2 Letters)</label>
                    <input type="text" name="country_code" class="form-control" value="GB" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Country Name</label>
                    <input type="text" name="country_name" class="form-control" value="United Kingdom" required>
                </div>
            </div>

            <h4 style="font-size: 14px; font-weight: 700; color: #E2E8F0; margin: 18px 0 10px 0; border-top: 1px solid var(--border-color); padding-top: 14px;">
                Holder Fullz Information (Optional)
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Holder Name</label>
                    <input type="text" name="holder_name" class="form-control" placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" class="form-control" placeholder="+44 7900 112233">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="address" class="form-control" placeholder="10 Downing St">
                </div>
                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" placeholder="London">
                </div>
                <div class="form-group">
                    <label class="form-label">ZIP</label>
                    <input type="text" name="zip" class="form-control" placeholder="SW1A 2AA">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 20px;">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" placeholder="john.doe@example.com">
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <a href="{{ route('admin.cards.index') }}" class="btn-reset" style="text-decoration: none;">Cancel</a>
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-plus"></i> Save Card to Stock
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
