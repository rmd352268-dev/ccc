@extends('admin.layout')

@section('title', 'Bulk Add Cards (10 - 1000+ at once)')

@section('content')
<div style="max-width: 1100px; margin: 0 auto;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 10px;">
        <div>
            <a href="{{ route('admin.cards.index') }}" style="color: var(--text-muted); font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Cards Inventory
            </a>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-layer-group" style="color: var(--gold-primary);"></i>
                Bulk Multi-Card Importer (10 - 1000+ Cards)
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 3px;">
                Paste 10, 50, 100, or 1000+ card lines at once or upload a dump file. Everything is automatically parsed, formatted, and added to live inventory in 1 click.
            </p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.cards.create') }}" class="btn-reset" style="padding: 7px 14px; font-size: 12px; text-decoration: none;">
                <i class="fa-solid fa-plus"></i> Single Card Add
            </a>
            <a href="{{ route('admin.cards.index') }}" class="btn-reset" style="padding: 7px 14px; font-size: 12px; text-decoration: none;">
                <i class="fa-solid fa-list-check"></i> View Inventory
            </a>
        </div>
    </div>

    <!-- Security & Exclusivity Banner -->
    <div style="background: linear-gradient(135deg, rgba(245,158,11,0.12) 0%, rgba(16,185,129,0.08) 100%); border: 1px solid rgba(245,158,11,0.35); border-radius: 12px; padding: 14px 18px; margin-bottom: 22px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(245,158,11,0.2); display: flex; align-items: center; justify-content: center; color: var(--gold-primary); font-size: 18px; flex-shrink: 0;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div style="font-size: 12.5px; color: var(--text-primary); line-height: 1.5;">
            <strong style="color: var(--gold-primary);">High-Speed Batch Ingestion:</strong><br>
            All parsed cards are injected directly into stock with high-speed database transactions. Customers receive exclusive delivery upon checkout.
        </div>
    </div>

    <!-- MAIN FORM -->
    <form action="{{ route('admin.cards.bulkStore') }}" method="POST" enctype="multipart/form-data" id="bulk-upload-form">
        @csrf

        <!-- CARD 1: PASTE / UPLOAD SECTION -->
        <div class="filter-card" style="background: linear-gradient(145deg, rgba(30, 41, 59, 0.95) 0%, rgba(15, 23, 42, 0.98) 100%); border: 1px solid rgba(245, 158, 11, 0.4); box-shadow: 0 8px 24px rgba(0, 0, 0, 0.35); border-radius: 12px; padding: 22px; margin-bottom: 20px; position: relative;">
            <div style="position: absolute; top: 0; left: 0; width: 4px; height: 100%; background: linear-gradient(to bottom, #F59E0B, #10B981);"></div>

            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                <div>
                    <h3 style="font-size: 15px; font-weight: 800; color: #FFF; margin: 0; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-paste" style="color: var(--gold-primary);"></i>
                        1. Paste Raw Cards (1 Line Per Card)
                    </h3>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                        Supports Pipe (<code>|</code>), Semicolon (<code>;</code>), Colon (<code>:</code>), Tab, or CSV format.
                    </p>
                </div>

                <!-- Sample Loaders -->
                <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                    <button type="button" class="btn-reset" onclick="fillSample10Fullz()" style="padding: 4px 10px; font-size: 11.5px; color: var(--gold-primary); border-color: rgba(245,158,11,0.35); background: rgba(245,158,11,0.08);">
                        <i class="fa-regular fa-lightbulb"></i> Load 10 Fullz Sample
                    </button>
                    <button type="button" class="btn-reset" onclick="fillSample20Simple()" style="padding: 4px 10px; font-size: 11.5px; color: #93C5FD; border-color: rgba(59,130,246,0.35); background: rgba(59,130,246,0.08);">
                        <i class="fa-regular fa-clone"></i> Load 20 Simple Sample
                    </button>
                    <button type="button" class="btn-reset" onclick="clearBulkBox()" style="padding: 4px 10px; font-size: 11.5px; color: #EF4444; border-color: rgba(239,68,68,0.35);">
                        <i class="fa-solid fa-rotate-left"></i> Clear
                    </button>
                </div>
            </div>

            <div style="margin-bottom: 14px;">
                <textarea name="raw_cards" id="raw_cards" class="form-control" rows="8" style="font-family: var(--font-mono); font-size: 12px; line-height: 1.5; background: rgba(15, 23, 42, 0.85); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 8px; color: #F8FAFC;" placeholder="Paste 10, 50, or 100+ cards here, e.g.:
4165980011223344|08/29|789|John Doe|10 Downing St|London|Greater London|SW1A 2AA|GB|+447900112233|john.doe@example.com|REVOLUT, LTD.|DEBIT|2.50
5131620022334455|11/29|891|Camille Dubois|15 Rue de Paris|Paris|IDF|75008|FR|+33612345678|camille@example.fr|BNP PARIBAS|CREDIT|2.50
4000123456789010|05/28|554|Alex Mercer|124 Broadway|New York|NY|10001|US|+12125550199|alex.m@example.com|CHASE BANK|DEBIT|2.50"></textarea>
            </div>

            <!-- Alternative File Upload Dropzone -->
            <div style="border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 14px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <span style="font-size: 12px; font-weight: 700; color: var(--text-muted);">
                        <i class="fa-solid fa-file-arrow-up"></i> Or Upload .TXT / .CSV / .DAT File:
                    </span>
                    <span id="file-info-badge" style="font-size: 11px; color: #10B981; display: none;"></span>
                </div>
                <input type="file" name="card_file" id="card_file" accept=".txt,.csv,.dat,.json" class="form-control" style="font-size: 12px; background: rgba(15,23,42,0.6);">
            </div>
        </div>

        <!-- LIVE PARSING PREVIEW BADGE & TABLE -->
        <div id="preview_container" style="display: none; margin-bottom: 20px;">
            <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.35); border-radius: 10px; padding: 14px 18px; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 8px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="width: 32px; height: 32px; border-radius: 50%; background: #10B981; color: #000; display: flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 900;">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <div>
                        <h4 style="font-size: 14px; font-weight: 800; color: #10B981; margin: 0;" id="parsed_summary_title">
                            0 Cards Ready for Ingestion
                        </h4>
                        <p style="font-size: 11.5px; color: var(--text-muted); margin-top: 1px;" id="parsed_summary_desc">
                            All rows validated. Review parsed cards in the live table below before saving.
                        </p>
                    </div>
                </div>

                <button type="button" onclick="togglePreviewTable()" class="btn-reset" style="padding: 4px 12px; font-size: 11.5px; color: #10B981; border-color: rgba(16,185,129,0.4);">
                    <span id="preview_toggle_text"><i class="fa-solid fa-table-list"></i> Show Preview Table</span>
                </button>
            </div>

            <!-- Expandable Table -->
            <div id="preview_table_wrapper" class="table-card" style="display: none; max-height: 380px; overflow-y: auto; border: 1px solid rgba(16,185,129,0.3); margin-bottom: 20px;">
                <table class="data-table" style="font-size: 11.5px;">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Card Number & BIN</th>
                            <th>Brand / Type</th>
                            <th>Exp / CVV</th>
                            <th>Holder Name</th>
                            <th>Address, City, State, ZIP</th>
                            <th>Country</th>
                            <th>Phone / Email</th>
                        </tr>
                    </thead>
                    <tbody id="preview_tbody"></tbody>
                </table>
            </div>
        </div>

        <!-- CARD 2: DEFAULT BATCH SETTINGS -->
        <div class="filter-card" style="box-shadow: 0 10px 30px rgba(0,0,0,0.3); border-radius: 12px; padding: 22px; margin-bottom: 20px;">
            <h4 style="font-size: 13.5px; font-weight: 800; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-sliders"></i> 2. Default Batch Settings (Applied when missing in raw lines)
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                <div class="form-group">
                    <label class="form-label">Default Country</label>
                    <select name="default_country_code" id="default_country_code" class="form-select" onchange="updateCountryName(this)">
                        @foreach($countries as $c)
                            <option value="{{ $c['code'] }}" data-name="{{ $c['name'] }}" {{ $c['code'] === 'US' ? 'selected' : '' }}>
                                {{ $c['flag'] }} {{ $c['code'] }} - {{ $c['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <input type="hidden" name="default_country_name" id="default_country_name" value="United States">
                </div>

                <div class="form-group">
                    <label class="form-label">Default Bank Name</label>
                    <input type="text" name="default_bank" class="form-control" value="CHASE BANK, N.A." required>
                </div>

                <div class="form-group">
                    <label class="form-label">Price Checked ($ USD) <span style="color:#EF4444;">*</span></label>
                    <input type="number" step="0.01" name="default_price" class="form-control" value="2.50" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Price Unchecked ($ USD)</label>
                    <input type="number" step="0.01" name="default_price_unc" class="form-control" value="2.00" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Base Dump / Batch Identifier</label>
                    <input type="text" name="default_base" class="form-control" value="{{ date('Y_m_d') }}_BATCH_IMPORT" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Default Brand</label>
                    <select name="default_brand" class="form-select">
                        <option value="VISA">VISA (Auto-detects from PAN)</option>
                        <option value="MASTERCARD">MASTERCARD</option>
                        <option value="AMEX">AMEX</option>
                        <option value="DISCOVER">DISCOVER</option>
                        <option value="JCB">JCB</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Card Type</label>
                    <select name="default_type" class="form-select">
                        <option value="DEBIT">DEBIT</option>
                        <option value="CREDIT">CREDIT</option>
                        <option value="PREPAID">PREPAID</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px; padding: 10px 0 20px;">
            <a href="{{ route('admin.cards.index') }}" class="btn-reset" style="text-decoration: none; padding: 10px 20px;">Cancel</a>
            <button type="submit" id="btn_submit_bulk" class="btn-search" style="padding: 12px 30px; font-size: 14px; font-weight: 800; display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #10B981 0%, #059669 100%); border: none; box-shadow: 0 4px 16px rgba(16,185,129,0.4); cursor: pointer;">
                <i class="fa-solid fa-cloud-arrow-up"></i> <span id="submit_btn_text">Save All Cards to Live Stock</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function updateCountryName(select) {
    const opt = select.options[select.selectedIndex];
    const countryName = opt.getAttribute('data-name');
    document.getElementById('default_country_name').value = countryName || 'United States';
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

function parseCardLineJS(line) {
    if (!line || typeof line !== 'string') return null;
    const raw = line.trim();
    if (!raw) return null;

    let delimiter = '|';
    if (raw.includes('|')) delimiter = '|';
    else if (raw.includes(';')) delimiter = ';';
    else if (raw.includes('\t')) delimiter = '\t';
    else if ((raw.match(/,/g) || []).length >= 2) delimiter = ',';
    else if ((raw.match(/:/g) || []).length >= 2 && !raw.startsWith('http')) delimiter = ':';

    const parts = raw.split(delimiter).map(p => p.trim().replace(/^["']|["']$/g, ''));
    if (parts.length < 1) return null;

    const cardNum = parts[0].replace(/\D/g, '');
    if (cardNum.length < 12) return null;

    const bin = cardNum.substring(0, 6);
    let brand = 'VISA';
    if (cardNum.startsWith('4')) brand = 'VISA';
    else if (/^(5[1-5]|2[2-7])/.test(cardNum)) brand = 'MASTERCARD';
    else if (/^3[47]/.test(cardNum)) brand = 'AMEX';
    else if (/^(6011|65|64[4-9])/.test(cardNum)) brand = 'DISCOVER';
    else if (cardNum.startsWith('35')) brand = 'JCB';

    let expDate = '12/28';
    let cvv = '000';
    let nextIdx = 1;

    if (parts[1] && /^(\d{1,2})[\/\-\.](\d{2,4})$/.test(parts[1])) {
        const m = parts[1].match(/^(\d{1,2})[\/\-\.](\d{2,4})$/);
        expDate = m[1].padStart(2, '0') + '/' + m[2].slice(-2);
        cvv = (parts[2] || '').replace(/\D/g, '').slice(0, 4) || '000';
        nextIdx = 3;
    } else if (
        parts[1] && /^\d{1,2}$/.test(parts[1]) && parseInt(parts[1]) >= 1 && parseInt(parts[1]) <= 12 &&
        parts[2] && (/^\d{2}$/.test(parts[2]) || /^\d{4}$/.test(parts[2]))
    ) {
        expDate = parts[1].padStart(2, '0') + '/' + parts[2].slice(-2);
        cvv = (parts[3] || '').replace(/\D/g, '').slice(0, 4) || '000';
        nextIdx = 4;
    } else {
        if (parts[1]) expDate = parts[1];
        if (parts[2]) cvv = parts[2].replace(/\D/g, '').slice(0, 4) || '000';
        nextIdx = 3;
    }

    const isoCountries = ['US','GB','UK','CA','AU','DE','FR','IT','ES','NL','BE','CH','SE','NO','DK','FI','RU','CN','JP','KR','IN','BD','PK','AE','SA','BR','MX','AR','CL','CO','ZA','NG','EG','TR','SG','MY','TH','ID','VN','PH','NZ','IE','AT','PL','PT','GR','CZ','RO','HU','UA','IL','QA','KW','HK','TW','KZ','UZ','AZ','GE','AM','BY','BG','HR','RS','SK','SI','LT','LV','EE','IS','LU','CY','MT','MA','KE','GH','CR','PA','DO','PR','EC','UY','VE'];
    const usStates = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'];

    let email = '', phone = '', country = '', state = '', zip = '', type = 'DEBIT';
    const geoTokens = [];

    for (let i = nextIdx; i < parts.length; i++) {
        const val = parts[i].trim();
        if (!val || val.toLowerCase() === 'n' || val.toLowerCase() === 'n/a' || val === '-') continue;
        const uVal = val.toUpperCase();

        if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) {
            email = val;
        } else if (/^(\+?\d[\d\s\-\(\)]{7,}\d)$/.test(val) && !phone) {
            phone = val;
        } else if (/^(VISA|MASTERCARD|AMEX|DISCOVER|JCB)$/i.test(val)) {
            brand = uVal;
        } else if (/^(DEBIT|CREDIT|PREPAID)$/i.test(val)) {
            type = uVal;
        } else if (usStates.includes(uVal) && !state) {
            state = uVal;
        } else if (isoCountries.includes(uVal) && !country) {
            country = uVal === 'UK' ? 'GB' : uVal;
        } else if (isZipToken(val) && !zip) {
            zip = val;
        } else {
            geoTokens.push(val);
        }
    }

    let holderName = '', address = '', city = '';
    let addrIdx = -1;
    for (let i = 0; i < geoTokens.length; i++) {
        if (isAddressToken(geoTokens[i])) {
            addrIdx = i;
            break;
        }
    }

    if (addrIdx !== -1) {
        const nameParts = geoTokens.slice(0, addrIdx);
        if (nameParts.length > 0) holderName = nameParts.join(' ').trim();
        address = geoTokens[addrIdx];

        const afterAddr = geoTokens.slice(addrIdx + 1);
        if (afterAddr[0]) city = afterAddr[0];
        if (afterAddr[1] && !state) state = afterAddr[1];
        if (afterAddr[2] && !zip && isZipToken(afterAddr[2])) zip = afterAddr[2];
    } else {
        if (geoTokens.length > 0) {
            if (/^[a-zA-Z\s\.\'\-]+$/.test(geoTokens[0])) {
                if (geoTokens.length >= 2 && !/\s/.test(geoTokens[0]) && !/\s/.test(geoTokens[1]) && /^[a-zA-Z\.\'\-]+$/.test(geoTokens[1])) {
                    holderName = geoTokens[0] + ' ' + geoTokens[1];
                    if (geoTokens[2]) address = geoTokens[2];
                    if (geoTokens[3]) city = geoTokens[3];
                    if (geoTokens[4] && !state) state = geoTokens[4];
                    if (geoTokens[5] && !zip) zip = geoTokens[5];
                } else {
                    holderName = geoTokens[0];
                    if (geoTokens[1]) address = geoTokens[1];
                    if (geoTokens[2]) city = geoTokens[2];
                    if (geoTokens[3] && !state) state = geoTokens[3];
                    if (geoTokens[4] && !zip) zip = geoTokens[4];
                }
            } else {
                address = geoTokens[0];
                if (geoTokens[1]) city = geoTokens[1];
                if (geoTokens[2] && !state) state = geoTokens[2];
                if (geoTokens[3] && !zip) zip = geoTokens[3];
            }
        }
    }

    if (holderName && holderName.includes(',')) {
        const np = holderName.split(',');
        if (np.length === 2) holderName = np[1].trim() + ' ' + np[0].trim();
    }

    if (!country && state && usStates.includes(state.toUpperCase())) country = 'US';

    return {
        card_number: cardNum,
        bin: bin,
        brand: brand,
        type: type,
        exp_date: expDate,
        cvv: cvv,
        holder_name: holderName || 'N/A',
        address: address || '',
        city: city || '',
        state: state || '',
        zip: zip || '',
        country: country || 'US',
        phone: phone || '',
        email: email || ''
    };
}

let parsedCardsCache = [];

function updateLivePreview() {
    const raw = document.getElementById('raw_cards').value;
    const lines = raw.split(/\r\n|\r|\n/).filter(l => l.trim().length > 0);
    parsedCardsCache = [];

    lines.forEach(l => {
        const item = parseCardLineJS(l);
        if (item) parsedCardsCache.push(item);
    });

    const previewContainer = document.getElementById('preview_container');
    const titleEl = document.getElementById('parsed_summary_title');
    const descEl = document.getElementById('parsed_summary_desc');
    const tbody = document.getElementById('preview_tbody');
    const submitBtnText = document.getElementById('submit_btn_text');

    if (parsedCardsCache.length > 0) {
        previewContainer.style.display = 'block';
        titleEl.textContent = `✨ ${parsedCardsCache.length} Card(s) Successfully Parsed & Ready!`;
        descEl.textContent = `${lines.length} lines analyzed • ${parsedCardsCache.length} valid cards detected with BIN, Brand & Fullz mapping.`;
        submitBtnText.textContent = `Save All ${parsedCardsCache.length} Card(s) to Live Stock`;

        // Render preview table rows
        tbody.innerHTML = '';
        parsedCardsCache.slice(0, 100).forEach((c, idx) => {
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>#${idx + 1}</strong></td>
                <td>
                    <code style="color: #93C5FD; font-weight: 700;">${c.card_number}</code>
                    <span style="font-size: 10px; background: rgba(245,158,11,0.2); color: var(--gold-primary); padding: 1px 4px; border-radius: 3px; margin-left: 4px;">BIN ${c.bin}</span>
                </td>
                <td><span style="font-weight: 700;">${c.brand}</span> <small style="color: var(--text-muted);">(${c.type})</small></td>
                <td><code>${c.exp_date}</code> | <code>${c.cvv}</code></td>
                <td><strong style="color: #F8FAFC;">${c.holder_name}</strong></td>
                <td style="color: var(--text-muted);">${c.address} ${c.city} ${c.state} ${c.zip}</td>
                <td><span style="font-weight: 700; color: #10B981;">${c.country}</span></td>
                <td style="color: var(--text-muted); font-size: 11px;">${c.phone || ''} ${c.email || ''}</td>
            `;
            tbody.appendChild(tr);
        });
    } else {
        previewContainer.style.display = 'none';
        submitBtnText.textContent = 'Save All Cards to Live Stock';
    }
}

function togglePreviewTable() {
    const tableWrapper = document.getElementById('preview_table_wrapper');
    const toggleText = document.getElementById('preview_toggle_text');
    if (tableWrapper.style.display === 'none') {
        tableWrapper.style.display = 'block';
        toggleText.innerHTML = '<i class="fa-solid fa-chevron-up"></i> Hide Preview Table';
    } else {
        tableWrapper.style.display = 'none';
        toggleText.innerHTML = '<i class="fa-solid fa-table-list"></i> Show Preview Table (' + parsedCardsCache.length + ' cards)';
    }
}

document.getElementById('raw_cards').addEventListener('input', function() {
    updateLivePreview();
});

document.getElementById('card_file').addEventListener('change', function() {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        document.getElementById('file-info-badge').textContent = `File selected: ${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        document.getElementById('file-info-badge').style.display = 'inline-block';
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('raw_cards').value = e.target.result;
            updateLivePreview();
        };
        reader.readAsText(file);
    }
});

function clearBulkBox() {
    document.getElementById('raw_cards').value = '';
    document.getElementById('card_file').value = '';
    document.getElementById('file-info-badge').style.display = 'none';
    updateLivePreview();
}

function fillSample10Fullz() {
    document.getElementById('raw_cards').value = `4165980011223344|08/29|789|John Doe|10 Downing St|London|Greater London|SW1A 2AA|GB|+447900112233|john.doe@example.com|REVOLUT, LTD.|DEBIT|2.50
5131620022334455|11/29|891|Camille Dubois|15 Rue de Paris|Paris|IDF|75008|FR|+33612345678|camille@example.fr|BNP PARIBAS|CREDIT|2.50
4000123456789010|05/28|554|Alex Mercer|124 Broadway|New York|NY|10001|US|+12125550199|alex.m@example.com|CHASE BANK|DEBIT|2.50
4569330033445566|09/27|193|Marc Peeters|45 Wetstraat|Brussels|Brussels|1000|BE|+32470123456|marc@example.be|KBC BANK|DEBIT|2.50
378282001122334|12/28|3001|Robert Vance|550 South Hope St|Los Angeles|CA|90071|US|+12135550144|robert.v@example.com|AMERICAN EXPRESS|CREDIT|3.50
4147200055667788|04/29|621|David Miller|200 Bay Street|Toronto|ON|M5J 2J2|CA|+14165550188|david.m@example.com|TD BANK|DEBIT|2.50
5424180099887766|10/28|412|Sophie Martin|22 Rue du Rhone|Geneva|GE|1204|CH|+41225550177|sophie.m@example.ch|UBS SWITZERLAND|CREDIT|3.00
4916800044556677|03/30|845|Oliver Brown|50 George Street|Sydney|NSW|2000|AU|+61295550122|oliver.b@example.au|WESTPAC BANK|DEBIT|2.50
4242424242424242|07/29|424|Emma Watson|80 Strand|London|Greater London|WC2R 0RL|GB|+447911122333|emma.w@example.com|BARCLAYS BANK|DEBIT|2.50
5555550011223344|01/29|999|Liam Smith|100 Queen St|Melbourne|VIC|3000|AU|+61395550199|liam.s@example.au|ANZ BANK|DEBIT|2.50`;
    updateLivePreview();
}

function fillSample20Simple() {
    let sample = '';
    for (let i = 1; i <= 20; i++) {
        const pan = (4165980000000000 + i * 11111111);
        const expM = String((i % 12) + 1).padStart(2, '0');
        const expY = 28 + (i % 3);
        const cvv = String(100 + (i * 37) % 900);
        sample += `${pan}|${expM}/${expY}|${cvv}\n`;
    }
    document.getElementById('raw_cards').value = sample.trim();
    updateLivePreview();
}
</script>
@endpush
@endsection
