@extends('admin.layout')

@section('title', 'Edit Card #' . $card->id)

@section('content')
<div style="max-width: 920px; margin: 0 auto;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
        <div>
            <a href="{{ route('admin.cards.index') }}" style="color: var(--text-muted); font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Cards Inventory
            </a>
            <h1 style="font-size: 22px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-pen-to-square" style="color: var(--gold-primary);"></i>
                Edit Card #{{ $card->id }} (BIN: {{ $card->bin }})
            </h1>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                Modify card credentials, fullz info, status, or use <strong>Smart Auto-Filler</strong> to quickly replace all details at once.
            </p>
        </div>
        <a href="{{ route('admin.cards.index') }}" class="btn-reset" style="padding: 7px 16px; font-size: 13px; text-decoration: none;">
            <i class="fa-solid fa-arrow-left"></i> Back to Cards
        </a>
    </div>

    <!-- ==================================================== -->
    <!-- SMART AUTO-FILLER / QUICK PASTE SECTION              -->
    <!-- ==================================================== -->
    <div class="filter-card" style="background: linear-gradient(145deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); border: 1px solid rgba(245, 158, 11, 0.4); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35); border-radius: 12px; padding: 20px; margin-bottom: 24px; position: relative; overflow: hidden;">
        <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #F59E0B, #10B981);"></div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; flex-wrap: wrap; gap: 8px;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 30px; height: 30px; border-radius: 8px; background: rgba(245, 158, 11, 0.15); display: flex; align-items: center; justify-content: center; color: var(--gold-primary); font-size: 15px;">
                    <i class="fa-solid fa-bolt"></i>
                </div>
                <div>
                    <h3 style="font-size: 14px; font-weight: 800; color: #FFF; margin: 0; display: flex; align-items: center; gap: 8px;">
                        Quick Auto-Fill / Replace Card Details
                        <span style="font-size: 10.5px; font-weight: 700; background: rgba(16, 185, 129, 0.2); color: #10B981; border: 1px solid rgba(16, 185, 129, 0.3); padding: 2px 7px; border-radius: 20px;">
                            Auto-Detects All Formats
                        </span>
                    </h3>
                </div>
            </div>

            <button type="button" class="btn-reset" onclick="clearAutoFillBox()" style="padding: 3px 8px; font-size: 11px; color: #EF4444; border-color: rgba(239,68,68,0.35);">
                <i class="fa-solid fa-rotate-left"></i> Clear
            </button>
        </div>

        <div style="margin-bottom: 10px;">
            <textarea id="autofill_input" class="form-control" rows="2" style="font-family: var(--font-mono); font-size: 12px; line-height: 1.4; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #F8FAFC;" placeholder="Paste raw card line to overwrite fields (e.g. 4165980011223344|08/29|789|John Doe|10 Downing St|London|London|SW1A 2AA|GB|+447900112233|john@example.com)"></textarea>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
            <div id="autofill_status" style="font-size: 11.5px; color: var(--text-muted);">
                <i class="fa-solid fa-circle-info" style="color: var(--gold-primary);"></i> Paste card details to quickly update form fields.
            </div>

            <button type="button" onclick="executeAutoFill()" class="btn-search" style="padding: 6px 16px; font-size: 12.5px; font-weight: 800; background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%); color: #000; border: none; cursor: pointer;">
                <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Fill Fields
            </button>
        </div>
    </div>

    <!-- ==================================================== -->
    <!-- MAIN CARD EDIT FORM                                  -->
    <!-- ==================================================== -->
    <div class="filter-card" style="box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-radius: 12px; padding: 26px;">
        <form action="{{ route('admin.cards.update', $card->id) }}" method="POST" id="card-edit-form">
            @csrf

            <!-- Section 1: Core Credentials -->
            <div style="margin-bottom: 20px;">
                <h4 style="font-size: 13.5px; font-weight: 800; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-credit-card"></i> 1. Card Credentials & Core Info
                </h4>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="display: flex; justify-content: space-between;">
                            <span>Card Number (Full PAN) <span style="color:#EF4444;">*</span></span>
                            <span id="detected_bin_badge" style="font-size: 11px; font-weight: 700; color: #10B981;">BIN: <strong id="bin_text">{{ $card->bin }}</strong></span>
                        </label>
                        <input type="text" name="card_number" id="card_number" class="form-control" value="{{ old('card_number', $card->card_number) }}" required oninput="onCardNumberChange(this.value)">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Exp Date (MM/YY) <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="exp_date" id="exp_date" class="form-control" value="{{ old('exp_date', $card->exp_date) }}" required maxlength="7">
                    </div>

                    <div class="form-group">
                        <label class="form-label">CVV / CVC <span style="color:#EF4444;">*</span></label>
                        <input type="text" name="cvv" id="cvv" class="form-control" value="{{ old('cvv', $card->cvv) }}" required maxlength="4">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Card Brand</label>
                        <select name="brand" id="brand" class="form-select">
                            <option value="VISA" {{ strtoupper($card->brand) === 'VISA' ? 'selected' : '' }}>VISA</option>
                            <option value="MASTERCARD" {{ strtoupper($card->brand) === 'MASTERCARD' ? 'selected' : '' }}>MASTERCARD</option>
                            <option value="AMEX" {{ strtoupper($card->brand) === 'AMEX' ? 'selected' : '' }}>AMEX</option>
                            <option value="DISCOVER" {{ strtoupper($card->brand) === 'DISCOVER' ? 'selected' : '' }}>DISCOVER</option>
                            <option value="JCB" {{ strtoupper($card->brand) === 'JCB' ? 'selected' : '' }}>JCB</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Card Type</label>
                        <select name="type" id="type" class="form-select">
                            <option value="CREDIT" {{ strtoupper($card->type) === 'CREDIT' ? 'selected' : '' }}>CREDIT</option>
                            <option value="DEBIT" {{ strtoupper($card->type) === 'DEBIT' ? 'selected' : '' }}>DEBIT</option>
                            <option value="PREPAID" {{ strtoupper($card->type) === 'PREPAID' ? 'selected' : '' }}>PREPAID</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price Checked ($ USD) <span style="color:#EF4444;">*</span></label>
                        <input type="number" step="0.01" name="price_c" id="price_c" class="form-control" value="{{ old('price_c', $card->price_c) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price Unchecked ($ USD)</label>
                        <input type="number" step="0.01" name="price_unc" id="price_unc" class="form-control" value="{{ old('price_unc', $card->price_unc) }}" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank" id="bank" class="form-control" value="{{ old('bank', $card->bank) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Base Name</label>
                        <input type="text" name="base_name" id="base_name" class="form-control" value="{{ old('base_name', $card->base_name) }}" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Inventory Status</label>
                        <select name="status" id="status" class="form-select">
                            <option value="available" {{ $card->status === 'available' ? 'selected' : '' }}>Available (For Sale)</option>
                            <option value="sold" {{ $card->status === 'sold' ? 'selected' : '' }}>Sold</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Country</label>
                    <select id="country_select" class="form-select" onchange="syncCountryFromSelect(this)">
                        @foreach($countries as $c)
                            <option value="{{ $c['code'] }}" data-name="{{ $c['name'] }}" {{ strtoupper($card->country_code) === $c['code'] ? 'selected' : '' }}>
                                {{ $c['flag'] }} {{ $c['name'] }} ({{ $c['code'] }})
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="country_code" id="country_code" value="{{ $card->country_code }}">
                    <input type="hidden" name="country_name" id="country_name" value="{{ $card->country_name }}">
                </div>
            </div>

            <!-- Section 2: Fullz Details -->
            <div style="margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <h4 style="font-size: 13.5px; font-weight: 800; color: #93C5FD; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-address-card"></i> 2. Holder Fullz Information (Optional)
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Holder Full Name</label>
                        <input type="text" name="holder_name" id="holder_name" class="form-control" value="{{ old('holder_name', $card->holder_name) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Phone Number</label>
                        <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone', $card->phone) }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr 1fr; gap: 16px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Street Address</label>
                        <input type="text" name="address" id="address" class="form-control" value="{{ old('address', $card->address) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">City</label>
                        <input type="text" name="city" id="city" class="form-control" value="{{ old('city', $card->city) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">State / Province</label>
                        <input type="text" name="state" id="state" class="form-control" value="{{ old('state', $card->state) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">ZIP / Postal Code</label>
                        <input type="text" name="zip" id="zip" class="form-control" value="{{ old('zip', $card->zip) }}">
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 24px;">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email', $card->email) }}">
                </div>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; border-top: 1px solid var(--border-color); padding-top: 20px;">
                <a href="{{ route('admin.cards.index') }}" class="btn-reset" style="text-decoration: none; padding: 10px 20px;">Cancel</a>
                <button type="submit" class="btn-search" style="padding: 10px 26px; font-size: 14px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-floppy-disk"></i> Save Card Changes
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.field-highlight {
    animation: flashHighlight 1.5s ease-out;
}
@keyframes flashHighlight {
    0% {
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.6);
        border-color: #10B981 !important;
        background-color: rgba(16, 185, 129, 0.1) !important;
    }
    100% {
        box-shadow: none;
    }
}
</style>

@push('scripts')
<script>
function syncCountryFromSelect(select) {
    const opt = select.options[select.selectedIndex];
    const countryCode = opt.value;
    const countryName = opt.getAttribute('data-name') || opt.text;
    document.getElementById('country_code').value = countryCode;
    document.getElementById('country_name').value = countryName;
}

function setCountryByCodeOrName(val) {
    if (!val) return false;
    const select = document.getElementById('country_select');
    const cleanVal = val.trim().toUpperCase();
    
    for (let i = 0; i < select.options.length; i++) {
        const opt = select.options[i];
        const code = opt.value.toUpperCase();
        const name = (opt.getAttribute('data-name') || '').toUpperCase();
        
        if (code === cleanVal || name.includes(cleanVal) || cleanVal.includes(name)) {
            select.selectedIndex = i;
            syncCountryFromSelect(select);
            return true;
        }
    }
    return false;
}

function detectBrandFromNumber(num) {
    const clean = num.replace(/\D/g, '');
    if (clean.startsWith('4')) return 'VISA';
    if (/^(5[1-5]|2[2-7])/.test(clean)) return 'MASTERCARD';
    if (/^3[47]/.test(clean)) return 'AMEX';
    if (/^(6011|65|64[4-9])/.test(clean)) return 'DISCOVER';
    if (/^35/.test(clean)) return 'JCB';
    return null;
}

function onCardNumberChange(val) {
    const clean = (val || '').replace(/\D/g, '');
    const binBadge = document.getElementById('detected_bin_badge');
    const binText = document.getElementById('bin_text');
    
    if (clean.length >= 6) {
        binText.textContent = clean.substring(0, 6);
        binBadge.style.display = 'inline-block';
    } else {
        binBadge.style.display = 'none';
    }

    const brand = detectBrandFromNumber(clean);
    if (brand) {
        const brandSelect = document.getElementById('brand');
        if (brandSelect) brandSelect.value = brand;
    }
}

// Helpers for Token Classification
function isAddressToken(token) {
    if (!token || typeof token !== 'string') return false;
    const str = token.trim();
    if (!str) return false;
    if (/^\d+[A-Za-z0-9\-\/]*\s+[A-Za-z]/i.test(str)) return true;
    if (/\b(street|st|avenue|ave|road|rd|boulevard|blvd|lane|ln|drive|dr|way|court|ct|square|sq|place|pl|terrace|ter|highway|hwy|building|bldg|apartment|apt|suite|ste|unit|floor|fl|box|p\.?o\.?\s*box|house|flat|rue|via|calle|strasse|str|weg|platz|straat)\b/i.test(str)) {
        return true;
    }
    if (/\d/.test(str) && /[a-zA-Z]/.test(str) && /\s/.test(str)) {
        return true;
    }
    return false;
}

function isZipToken(token) {
    if (!token || typeof token !== 'string') return false;
    const str = token.trim();
    if (!str) return false;
    if (/^\d{5}(-\d{4})?$/.test(str)) return true;
    if (/^[A-Z]{1,2}\d[A-Z\d]?\s*\d[A-Z]{2}$/i.test(str)) return true;
    if (/^[A-Z]\d[A-Z]\s*\d[A-Z]\d$/i.test(str) || /^\d{4}\s*[A-Z]{2}$/i.test(str)) return true;
    if (/^\d{4,6}$/.test(str)) return true;
    return false;
}

function parseCardRawText(text) {
    if (!text || typeof text !== 'string') return null;
    const raw = text.trim();
    if (!raw) return null;

    const isoCountries = ['US','GB','UK','CA','AU','DE','FR','IT','ES','NL','BE','CH','SE','NO','DK','FI','RU','CN','JP','KR','IN','BD','PK','AE','SA','BR','MX','AR','CL','CO','ZA','NG','EG','TR','SG','MY','TH','ID','VN','PH','NZ','IE','AT','PL','PT','GR','CZ','RO','HU','UA','IL','QA','KW','HK','TW','KZ','UZ','AZ','GE','AM','BY','BG','HR','RS','SK','SI','LT','LV','EE','IS','LU','CY','MT','MA','KE','GH','CR','PA','DO','PR','EC','UY','VE'];
    const usStates = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'];

    let result = {};

    if (raw.startsWith('{') && raw.endsWith('}')) {
        try {
            const data = JSON.parse(raw);
            result.card_number = data.card_number || data.card || data.pan || data.number || '';
            result.exp_date = data.exp_date || data.exp || data.expiry || '';
            if (!result.exp_date && data.month && data.year) {
                result.exp_date = String(data.month).padStart(2, '0') + '/' + String(data.year).slice(-2);
            }
            result.cvv = data.cvv || data.cvc || data.security_code || '';
            
            let fname = data.first_name || data.fname || '';
            let lname = data.last_name || data.lname || '';
            if (fname || lname) {
                result.holder_name = (fname + ' ' + lname).trim();
            } else {
                result.holder_name = data.holder_name || data.name || data.cardholder || data.client || '';
            }

            result.address = data.address || data.street || '';
            result.city = data.city || '';
            result.state = data.state || data.province || '';
            result.zip = data.zip || data.postal || data.postcode || '';
            result.country = data.country || data.country_code || '';
            result.phone = data.phone || data.telephone || '';
            result.email = data.email || data.mail || '';
            result.bank = data.bank || data.bank_name || '';
            result.brand = data.brand || '';
            result.type = data.type || '';
            result.price_c = data.price_c || data.price || '';
            return result;
        } catch (e) {}
    }

    const hasLabels = /card|number|exp|cvv|name|first|last|address|city|zip|phone|email/i.test(raw);
    if (hasLabels && (raw.includes('\n') || raw.includes(':') || raw.includes('='))) {
        const lines = raw.split(/\r\n|\r|\n/);
        let extractedFromLabels = false;
        let fname = '', lname = '';

        lines.forEach(line => {
            const kvMatch = line.match(/^([^:=]+)[:=]\s*(.+)$/i);
            if (kvMatch) {
                extractedFromLabels = true;
                const k = kvMatch[1].trim().toLowerCase();
                const v = kvMatch[2].trim();

                if (/^(card|cc|number|pan|card_number|card_no)$/i.test(k)) result.card_number = v;
                else if (/^(exp|expiry|exp_date|expiration|date|expiration_date)$/i.test(k)) result.exp_date = v;
                else if (/^(cvv|cvc|cvv2|security|security_code|cvc2)$/i.test(k)) result.cvv = v;
                else if (/^(first_name|fname|firstname)$/i.test(k)) fname = v;
                else if (/^(last_name|lname|lastname|surname)$/i.test(k)) lname = v;
                else if (/^(name|holder|cardholder|fullz|client|holder_name|card_holder|fullname|full_name)$/i.test(k)) result.holder_name = v;
                else if (/^(address|street|addr|street_address|address1)$/i.test(k)) result.address = v;
                else if (/^(city|town)$/i.test(k)) result.city = v;
                else if (/^(state|province|region)$/i.test(k)) result.state = v;
                else if (/^(zip|postal|postcode|zipcode|postal_code)$/i.test(k)) result.zip = v;
                else if (/^(country|country_code|nation)$/i.test(k)) result.country = v;
                else if (/^(phone|tel|mobile|cell|telephone)$/i.test(k)) result.phone = v;
                else if (/^(email|mail|email_address)$/i.test(k)) result.email = v;
                else if (/^(bank|issuer|bank_name)$/i.test(k)) result.bank = v;
                else if (/^(brand)$/i.test(k)) result.brand = v;
                else if (/^(type)$/i.test(k)) result.type = v;
                else if (/^(price|price_c)$/i.test(k)) result.price_c = v;
            }
        });

        if (fname || lname) {
            result.holder_name = (fname + ' ' + lname).trim();
        }

        if (extractedFromLabels && result.card_number) {
            return result;
        }
    }

    let lineToParse = raw.split(/\r\n|\r|\n/)[0].trim();
    let delimiter = '|';
    if (lineToParse.includes('|')) delimiter = '|';
    else if (lineToParse.includes(';')) delimiter = ';';
    else if (lineToParse.includes('\t')) delimiter = '\t';
    else if ((lineToParse.match(/,/g) || []).length >= 2) delimiter = ',';
    else if ((lineToParse.match(/:/g) || []).length >= 2 && !lineToParse.startsWith('http')) delimiter = ':';

    const parts = lineToParse.split(delimiter).map(p => p.trim().replace(/^["']|["']$/g, ''));

    if (parts.length >= 1) {
        const cardCandidate = parts[0].replace(/\D/g, '');
        if (cardCandidate.length >= 12 && cardCandidate.length <= 19) {
            result.card_number = cardCandidate;
        }

        let nextIdx = 1;

        if (parts[1] && /^(\d{1,2})[\/\-\.](\d{2,4})$/.test(parts[1])) {
            const m = parts[1].match(/^(\d{1,2})[\/\-\.](\d{2,4})$/);
            result.exp_date = m[1].padStart(2, '0') + '/' + m[2].slice(-2);
            result.cvv = (parts[2] || '').replace(/\D/g, '').slice(0, 4);
            nextIdx = 3;
        } else if (
            parts[1] && /^\d{1,2}$/.test(parts[1]) && parseInt(parts[1]) >= 1 && parseInt(parts[1]) <= 12 &&
            parts[2] && (/^\d{2}$/.test(parts[2]) || /^\d{4}$/.test(parts[2]))
        ) {
            result.exp_date = parts[1].padStart(2, '0') + '/' + parts[2].slice(-2);
            result.cvv = (parts[3] || '').replace(/\D/g, '').slice(0, 4);
            nextIdx = 4;
        } else {
            if (parts[1]) result.exp_date = parts[1];
            if (parts[2]) result.cvv = parts[2].replace(/\D/g, '').slice(0, 4);
            nextIdx = 3;
        }

        const remaining = parts.slice(nextIdx);
        const geoTokens = [];

        for (let i = 0; i < remaining.length; i++) {
            const val = remaining[i].trim();
            if (!val || val.toLowerCase() === 'n' || val.toLowerCase() === 'n/a' || val === '-') continue;

            const uVal = val.toUpperCase();

            if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
                result.email = val;
            } else if (/^(\+?\d[\d\s\-\(\)]{7,}\d)$/.test(val) && !result.phone) {
                result.phone = val;
            } else if (/^(VISA|MASTERCARD|AMEX|DISCOVER|JCB)$/i.test(val) && !result.brand) {
                result.brand = uVal;
            } else if (/^(DEBIT|CREDIT|PREPAID)$/i.test(val) && !result.type) {
                result.type = uVal;
            } else if (/(bank|ltd|corp|fcu|credit union|bancorp|revolut|chase|barclays|hsbc|citi)/i.test(val) && !result.bank) {
                result.bank = uVal;
            } else if (usStates.includes(uVal) && !result.state) {
                result.state = uVal;
            } else if (isoCountries.includes(uVal) && !result.country) {
                result.country = uVal === 'UK' ? 'GB' : uVal;
            } else if (isZipToken(val) && !result.zip) {
                result.zip = val;
            } else {
                geoTokens.push(val);
            }
        }

        let addrIdx = -1;
        for (let i = 0; i < geoTokens.length; i++) {
            if (isAddressToken(geoTokens[i])) {
                addrIdx = i;
                break;
            }
        }

        if (addrIdx !== -1) {
            const nameParts = geoTokens.slice(0, addrIdx);
            if (nameParts.length > 0) {
                result.holder_name = nameParts.join(' ').trim();
            }
            result.address = geoTokens[addrIdx];

            const afterAddr = geoTokens.slice(addrIdx + 1);
            if (afterAddr[0] && !result.city) {
                result.city = afterAddr[0];
            }
            if (afterAddr[1] && !result.state) {
                result.state = afterAddr[1];
            }
            if (afterAddr[2] && !result.zip && isZipToken(afterAddr[2])) {
                result.zip = afterAddr[2];
            } else if (afterAddr[2] && !result.bank) {
                result.bank = afterAddr[2];
            }
        } else {
            if (geoTokens.length > 0) {
                if (/^[a-zA-Z\s\.\'\-]+$/.test(geoTokens[0])) {
                    if (geoTokens.length >= 2 && !/\s/.test(geoTokens[0]) && !/\s/.test(geoTokens[1]) && /^[a-zA-Z\.\'\-]+$/.test(geoTokens[1])) {
                        result.holder_name = geoTokens[0] + ' ' + geoTokens[1];
                        if (geoTokens[2] && !result.address) result.address = geoTokens[2];
                        if (geoTokens[3] && !result.city) result.city = geoTokens[3];
                        if (geoTokens[4] && !result.state) result.state = geoTokens[4];
                        if (geoTokens[5] && !result.zip) result.zip = geoTokens[5];
                    } else {
                        result.holder_name = geoTokens[0];
                        if (geoTokens[1] && !result.address) result.address = geoTokens[1];
                        if (geoTokens[2] && !result.city) result.city = geoTokens[2];
                        if (geoTokens[3] && !result.state) result.state = geoTokens[3];
                        if (geoTokens[4] && !result.zip) result.zip = geoTokens[4];
                    }
                } else {
                    result.address = geoTokens[0];
                    if (geoTokens[1] && !result.city) result.city = geoTokens[1];
                    if (geoTokens[2] && !result.state) result.state = geoTokens[2];
                    if (geoTokens[3] && !result.zip) result.zip = geoTokens[3];
                }
            }
        }

        if (result.holder_name && result.holder_name.includes(',')) {
            const np = result.holder_name.split(',');
            if (np.length === 2) {
                result.holder_name = np[1].trim() + ' ' + np[0].trim();
            }
        }

        if (!result.country && result.state && usStates.includes(result.state.toUpperCase())) {
            result.country = 'US';
        }
    }

    if (!result.card_number) {
        const ccMatch = raw.match(/\b([3-6]\d{3}[ -]?\d{4}[ -]?\d{4}[ -]?\d{3,7})\b/);
        if (ccMatch) result.card_number = ccMatch[1].replace(/\D/g, '');
    }

    if (!result.exp_date) {
        const expMatch = raw.match(/\b(0[1-9]|1[0-2])[\/\-\.](2[0-9]|3[0-9]|202[0-9]|203[0-9])\b/);
        if (expMatch) result.exp_date = expMatch[1].padStart(2, '0') + '/' + expMatch[2].slice(-2);
    }

    if (!result.cvv) {
        const cvvMatch = raw.match(/\b\d{3,4}\b/g);
        if (cvvMatch) {
            for (let c of cvvMatch) {
                if (c !== result.card_number && !result.exp_date?.includes(c)) {
                    result.cvv = c;
                    break;
                }
            }
        }
    }

    return result;
}

function executeAutoFill() {
    const input = document.getElementById('autofill_input');
    const statusDiv = document.getElementById('autofill_status');
    const parsed = parseCardRawText(input.value);

    if (!parsed || !parsed.card_number) {
        statusDiv.innerHTML = `<span style="color: #EF4444; font-weight: 700;"><i class="fa-solid fa-triangle-exclamation"></i> Could not detect valid card number. Please check format.</span>`;
        return;
    }

    let filledCount = 0;

    function setVal(id, val) {
        if (val === undefined || val === null || val === '') return;
        const el = document.getElementById(id);
        if (el) {
            el.value = val;
            el.classList.remove('field-highlight');
            void el.offsetWidth;
            el.classList.add('field-highlight');
            filledCount++;
        }
    }

    setVal('card_number', parsed.card_number);
    onCardNumberChange(parsed.card_number);

    if (parsed.exp_date) {
        let cleanExp = parsed.exp_date;
        const expM = cleanExp.match(/^(\d{1,2})[\/\-\.](\d{2,4})$/);
        if (expM) {
            cleanExp = expM[1].padStart(2, '0') + '/' + expM[2].slice(-2);
        }
        setVal('exp_date', cleanExp);
    }

    if (parsed.cvv) setVal('cvv', parsed.cvv);
    if (parsed.brand) {
        const brandSelect = document.getElementById('brand');
        if (brandSelect) {
            brandSelect.value = parsed.brand.toUpperCase();
            brandSelect.classList.add('field-highlight');
            filledCount++;
        }
    }
    if (parsed.type) {
        const typeSelect = document.getElementById('type');
        if (typeSelect) {
            typeSelect.value = parsed.type.toUpperCase();
            typeSelect.classList.add('field-highlight');
            filledCount++;
        }
    }
    if (parsed.price_c) setVal('price_c', parsed.price_c);
    if (parsed.bank) setVal('bank', parsed.bank);
    if (parsed.holder_name) setVal('holder_name', parsed.holder_name);
    if (parsed.phone) setVal('phone', parsed.phone);
    if (parsed.address) setVal('address', parsed.address);
    if (parsed.city) setVal('city', parsed.city);
    if (parsed.state) setVal('state', parsed.state);
    if (parsed.zip) setVal('zip', parsed.zip);
    if (parsed.email) setVal('email', parsed.email);

    if (parsed.country) {
        if (setCountryByCodeOrName(parsed.country)) {
            document.getElementById('country_select').classList.add('field-highlight');
            filledCount++;
        }
    }

    statusDiv.innerHTML = `
        <span style="color: #10B981; font-weight: 700;">
            <i class="fa-solid fa-circle-check"></i> Successfully parsed & auto-filled ${filledCount} field(s)! Click "Save Card Changes".
        </span>
    `;
}

// Auto-trigger parse when user pastes or types into the autofill box
const autofillEditInput = document.getElementById('autofill_input');
if (autofillEditInput) {
    autofillEditInput.addEventListener('input', function(e) {
        if (this.value.trim().length >= 15) {
            executeAutoFill();
        }
    });
    autofillEditInput.addEventListener('paste', function(e) {
        setTimeout(executeAutoFill, 80);
    });
}

function clearAutoFillBox() {
    const el = document.getElementById('autofill_input');
    if (el) el.value = '';
    const statusDiv = document.getElementById('autofill_status');
    if (statusDiv) {
        statusDiv.innerHTML = `<i class="fa-solid fa-circle-info" style="color: var(--gold-primary);"></i> <span>Paste details above to update form fields.</span>`;
    }
}
</script>
@endpush
@endsection
