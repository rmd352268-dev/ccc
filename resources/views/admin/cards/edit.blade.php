@extends('admin.layout')

@section('title', 'Edit Card #' . $card->id)

@section('content')
<div style="max-width: 900px; margin: 0 auto;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <div>
            <h1 style="font-size: 22px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-pen-to-square" style="color: var(--gold-primary);"></i> Edit Card #{{ $card->id }} (BIN: {{ $card->bin }})
            </h1>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                Modify card number, fullz details, pricing, or status.
            </p>
        </div>
        <a href="{{ route('admin.cards.index') }}" class="btn-reset" style="padding: 7px 16px; font-size: 13px; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Back to Cards
        </a>
    </div>

    <div class="filter-card" style="padding: 26px;">
        <form action="{{ route('admin.cards.update', $card->id) }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                
                <div class="form-group">
                    <label class="form-label">Card Number (Full PAN)</label>
                    <input type="text" name="card_number" class="form-control" value="{{ old('card_number', $card->card_number) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Expiration Date (MM/YY)</label>
                    <input type="text" name="exp_date" class="form-control" value="{{ old('exp_date', $card->exp_date) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">CVV / CVC Code</label>
                    <input type="text" name="cvv" class="form-control" value="{{ old('cvv', $card->cvv) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Card Brand</label>
                    <select name="brand" class="form-select">
                        <option value="VISA" {{ $card->brand === 'VISA' ? 'selected' : '' }}>VISA</option>
                        <option value="MASTERCARD" {{ $card->brand === 'MASTERCARD' ? 'selected' : '' }}>MASTERCARD</option>
                        <option value="AMEX" {{ $card->brand === 'AMEX' ? 'selected' : '' }}>AMERICAN EXPRESS (AMEX)</option>
                        <option value="DISCOVER" {{ $card->brand === 'DISCOVER' ? 'selected' : '' }}>DISCOVER</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Card Type</label>
                    <select name="type" class="form-select">
                        <option value="CREDIT" {{ $card->type === 'CREDIT' ? 'selected' : '' }}>CREDIT</option>
                        <option value="DEBIT" {{ $card->type === 'DEBIT' ? 'selected' : '' }}>DEBIT</option>
                        <option value="PREPAID" {{ $card->type === 'PREPAID' ? 'selected' : '' }}>PREPAID</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Country</label>
                    <select name="country_code" class="form-select">
                        @foreach($countries as $c)
                            <option value="{{ $c['code'] }}" {{ $card->country_code === $c['code'] ? 'selected' : '' }}>
                                {{ $c['flag'] }} {{ $c['name'] }} ({{ $c['code'] }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank" class="form-control" value="{{ old('bank', $card->bank) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Base Name</label>
                    <input type="text" name="base_name" class="form-control" value="{{ old('base_name', $card->base_name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Price (Checked - C$)</label>
                    <input type="number" step="0.01" name="price_c" class="form-control" value="{{ old('price_c', $card->price_c) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Price (Unchecked - UNC$)</label>
                    <input type="number" step="0.01" name="price_unc" class="form-control" value="{{ old('price_unc', $card->price_unc) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Holder Name</label>
                    <input type="text" name="holder_name" class="form-control" value="{{ old('holder_name', $card->holder_name) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Street Address</label>
                    <input type="text" name="address" class="form-control" value="{{ old('address', $card->address) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">City</label>
                    <input type="text" name="city" class="form-control" value="{{ old('city', $card->city) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">State / Province</label>
                    <input type="text" name="state" class="form-control" value="{{ old('state', $card->state) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">ZIP / Postal Code</label>
                    <input type="text" name="zip" class="form-control" value="{{ old('zip', $card->zip) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Inventory Status</label>
                    <select name="status" class="form-select">
                        <option value="available" {{ $card->status === 'available' ? 'selected' : '' }}>Available (For Sale)</option>
                        <option value="sold" {{ $card->status === 'sold' ? 'selected' : '' }}>Sold</option>
                    </select>
                </div>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end; gap: 12px;">
                <a href="{{ route('admin.cards.index') }}" class="btn-reset">Cancel</a>
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-floppy-disk"></i> Save Card Changes
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
