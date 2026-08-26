@extends('admin.layout')

@section('title', 'Upload Card File & Bulk Import')

@section('content')
<div style="max-width: 960px; margin: 0 auto;">
    <!-- Breadcrumb & Header -->
    <div style="margin-bottom: 22px; display: flex; justify-content: space-between; align-items: flex-start;">
        <div>
            <a href="{{ route('admin.cards.index') }}" style="color: var(--text-muted); font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                <i class="fa-solid fa-arrow-left"></i> Back to Cards Inventory
            </a>
            <h2 style="font-size: 22px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
                <i class="fa-solid fa-file-arrow-up" style="color: var(--gold-primary);"></i>
                Upload Card File / Bulk Import
            </h2>
            <p style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                Upload a <code>.txt</code> or <code>.csv</code> file or paste card lines. When a customer buys any card, <strong>only that buyer receives the specific card data</strong> and it is immediately marked as sold.
            </p>
        </div>
        <a href="{{ route('admin.cards.index') }}" class="btn-reset" style="padding: 6px 14px; font-size: 12px; text-decoration: none;">
            <i class="fa-solid fa-list-check"></i> View Inventory
        </a>
    </div>

    <!-- Security & Exclusivity Banner -->
    <div style="background: linear-gradient(135deg, rgba(245,158,11,0.1) 0%, rgba(16,185,129,0.08) 100%); border: 1px solid rgba(245,158,11,0.3); border-radius: 10px; padding: 14px 18px; margin-bottom: 22px; display: flex; align-items: center; gap: 14px;">
        <div style="width: 42px; height: 42px; border-radius: 50%; background: rgba(245,158,11,0.2); display: flex; align-items: center; justify-content: center; color: var(--gold-primary); font-size: 18px; flex-shrink: 0;">
            <i class="fa-solid fa-shield-halved"></i>
        </div>
        <div style="font-size: 12.5px; color: var(--text-primary); line-height: 1.5;">
            <strong style="color: var(--gold-primary);">100% Exclusive Delivery Guarantee:</strong><br>
            All uploaded cards are directly added to live inventory. When purchased, the exact card data is exclusively delivered to the buyer's order receipt and download file, then immediately marked as sold.
        </div>
    </div>

    <div class="filter-card" style="box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
        <form action="{{ route('admin.cards.bulkStore') }}" method="POST" enctype="multipart/form-data" id="bulk-upload-form">
            @csrf

            <!-- Section 1: File Upload or Direct Paste -->
            <div style="margin-bottom: 22px;">
                <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--gold-primary); margin-bottom: 8px; display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-cloud-arrow-up"></i> 1. Choose File (.TXT / .CSV) or Drag & Drop</span>
                    <span id="detected-count" style="font-size: 11.5px; font-weight: 700; color: #10B981; background: rgba(16,185,129,0.15); padding: 2px 8px; border-radius: 4px; display: none;">0 items detected</span>
                </label>

                <!-- Dropzone Area -->
                <div id="dropzone" style="border: 2px dashed rgba(245,158,11,0.4); border-radius: 10px; background: rgba(245,158,11,0.03); padding: 28px 20px; text-align: center; cursor: pointer; transition: all 0.2s ease; position: relative;">
                    <input type="file" name="card_file" id="card_file" accept=".txt,.csv,.dat,.json" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer;">
                    
                    <div id="dropzone-content">
                        <i class="fa-solid fa-file-arrow-up" style="font-size: 38px; color: var(--gold-primary); margin-bottom: 10px;"></i>
                        <h4 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 4px;">
                            Click to Browse File or Drag & Drop Here
                        </h4>
                        <p style="font-size: 12px; color: var(--text-muted);">
                            Supported formats: <strong>.TXT</strong>, <strong>.CSV</strong>, <strong>.DAT</strong> (Unlimited lines)
                        </p>
                    </div>

                    <div id="file-selected-badge" style="display: none; align-items: center; justify-content: center; gap: 10px; font-size: 14px; font-weight: 700; color: #10B981;">
                        <i class="fa-solid fa-circle-check" style="font-size: 20px;"></i>
                        <span id="selected-file-name">filename.txt</span>
                        <span id="selected-file-size" style="font-size: 11px; color: var(--text-muted); font-weight: 400;">(0 KB)</span>
                        <button type="button" onclick="clearFileSelection(event)" style="background: none; border: none; color: #EF4444; cursor: pointer; margin-left: 8px; font-size: 14px;" title="Remove File">&times;</button>
                    </div>
                </div>
            </div>

            <!-- Section 2: Direct Paste Textarea (Alternative/Additional) -->
            <div class="form-group" style="margin-bottom: 22px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <label class="form-label" style="font-size: 13px; font-weight: 700; color: var(--text-primary); margin-bottom: 0;">
                        <i class="fa-solid fa-align-left"></i> 2. Or Paste Raw Card Data (1 line per card)
                    </label>
                    <button type="button" class="btn-reset" onclick="fillSampleCards()" style="padding: 2px 8px; font-size: 11px; color: var(--gold-primary); border-color: rgba(245,158,11,0.3);">
                        <i class="fa-regular fa-lightbulb"></i> Load Sample Data
                    </button>
                </div>
                <textarea name="raw_cards" id="raw_cards" class="form-control" style="min-height: 160px; font-family: var(--font-mono); font-size: 12px; line-height: 1.6;" placeholder="CARD_NUMBER|EXP_DATE|CVV|NAME|ADDRESS|ZIP|PHONE|EMAIL
4165980011223344|09/28|482|John Smith|10 High Street|SW1A 1AA|+447911122333|john@example.com
5131620022334455|11/29|891|Camille Dubois|15 Rue de Paris|75008|+33612345678|camille@example.fr
4569330033445566|05/27|193|Marc Peeters|45 Wetstraat|1000|+32470123456|marc@example.be"></textarea>
            </div>

            <!-- Section 3: Default Overrides & Settings -->
            <div style="background: rgba(15,23,42,0.6); border: 1px solid var(--border-color); border-radius: 8px; padding: 18px; margin-bottom: 22px;">
                <h4 style="font-size: 13px; font-weight: 700; color: var(--gold-primary); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 14px;">
                    <i class="fa-solid fa-sliders"></i> Default Batch Settings (Auto-applied if missing in file)
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 14px;">
                    <div class="form-group">
                        <label class="form-label">Country</label>
                        <select name="default_country_code" id="default_country_code" class="form-control" onchange="updateCountryName(this)">
                            @foreach($countries as $c)
                                <option value="{{ $c['code'] }}" data-name="{{ $c['name'] }}" {{ $c['code'] === 'US' ? 'selected' : '' }}>
                                    {{ $c['flag'] }} {{ $c['code'] }} - {{ $c['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="default_country_name" id="default_country_name" value="United States">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="default_bank" class="form-control" value="CHASE BANK, N.A." required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price Checked ($)</label>
                        <input type="number" step="0.1" name="default_price" class="form-control" value="2.50" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Price Unchecked ($)</label>
                        <input type="number" step="0.1" name="default_price_unc" class="form-control" value="2.00" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 2fr 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label">Base Tag / Batch Identifier</label>
                        <input type="text" name="default_base" class="form-control" value="{{ date('Y_m_d') }}_FILE_INJECTION (war***)" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Default Card Brand</label>
                        <select name="default_brand" class="form-control">
                            <option value="VISA">VISA (Auto-detects from BIN)</option>
                            <option value="MASTERCARD">MASTERCARD</option>
                            <option value="AMEX">AMEX</option>
                            <option value="DISCOVER">DISCOVER</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Card Type</label>
                        <select name="default_type" class="form-control">
                            <option value="CREDIT">CREDIT</option>
                            <option value="DEBIT">DEBIT</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Format Guidelines -->
            <div style="background: rgba(59,130,246,0.06); border: 1px dashed rgba(59,130,246,0.3); border-radius: 8px; padding: 14px 18px; margin-bottom: 22px; font-size: 12px; color: #93C5FD; line-height: 1.6;">
                <strong>💡 Supported Format Variations:</strong><br>
                • <strong>Simple:</strong> <code>CARD_NUMBER|MM/YY|CVV</code><br>
                • <strong>Separated Exp:</strong> <code>CARD_NUMBER|MM|YY|CVV</code><br>
                • <strong>Fullz Details:</strong> <code>CARD_NUMBER|MM/YY|CVV|HOLDER_NAME|ADDRESS|CITY|STATE|ZIP|PHONE|EMAIL</code><br>
                • <strong>Supported Delimiters:</strong> Pipe (<code>|</code>), Semicolon (<code>;</code>), Comma (<code>,</code>), Tab. BIN, brand, and flags are automatically detected!
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px;">
                <a href="{{ route('admin.cards.index') }}" class="btn-reset" style="text-decoration: none; padding: 10px 18px;">Cancel</a>
                <button type="submit" class="btn-search" style="padding: 10px 24px; font-size: 14px; font-weight: 800;">
                    <i class="fa-solid fa-cloud-arrow-up"></i> Start File Injection & Import Cards
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function updateCountryName(select) {
    const opt = select.options[select.selectedIndex];
    const countryName = opt.getAttribute('data-name');
    document.getElementById('default_country_name').value = countryName || 'United States';
}

const fileInput = document.getElementById('card_file');
const dropzone = document.getElementById('dropzone');
const dropzoneContent = document.getElementById('dropzone-content');
const fileBadge = document.getElementById('file-selected-badge');
const fileNameSpan = document.getElementById('selected-file-name');
const fileSizeSpan = document.getElementById('selected-file-size');
const rawCardsText = document.getElementById('raw_cards');
const detectedCountSpan = document.getElementById('detected-count');

fileInput.addEventListener('change', function(e) {
    if (this.files && this.files[0]) {
        const file = this.files[0];
        fileNameSpan.textContent = file.name;
        fileSizeSpan.textContent = '(' + (file.size / 1024).toFixed(1) + ' KB)';
        dropzoneContent.style.display = 'none';
        fileBadge.style.display = 'flex';
        dropzone.style.borderColor = '#10B981';
        dropzone.style.background = 'rgba(16,185,129,0.06)';

        // Read file client-side to count lines
        const reader = new FileReader();
        reader.onload = function(evt) {
            const text = evt.target.result;
            const lines = text.split(/\r\n|\r|\n/).filter(l => l.trim().length > 0);
            detectedCountSpan.textContent = lines.length + ' cards in file';
            detectedCountSpan.style.display = 'inline-block';
        };
        reader.readAsText(file);
    }
});

function clearFileSelection(e) {
    e.stopPropagation();
    fileInput.value = '';
    dropzoneContent.style.display = 'block';
    fileBadge.style.display = 'none';
    dropzone.style.borderColor = 'rgba(245,158,11,0.4)';
    dropzone.style.background = 'rgba(245,158,11,0.03)';
    updateDetectedLines();
}

rawCardsText.addEventListener('input', updateDetectedLines);

function updateDetectedLines() {
    if (!fileInput.files || fileInput.files.length === 0) {
        const lines = rawCardsText.value.split(/\r\n|\r|\n/).filter(l => l.trim().length > 0);
        if (lines.length > 0) {
            detectedCountSpan.textContent = lines.length + ' cards entered';
            detectedCountSpan.style.display = 'inline-block';
        } else {
            detectedCountSpan.style.display = 'none';
        }
    }
}

function fillSampleCards() {
    rawCardsText.value = `4165980011223344|09/28|482|John Smith|10 High Street|London|London|SW1A 1AA|+447911122333|john@example.com
5131620022334455|11/29|891|Camille Dubois|15 Rue de Paris|Paris|IDF|75008|+33612345678|camille@example.fr
4569330033445566|05/27|193|Marc Peeters|45 Wetstraat|Brussels|Brussels|1000|+32470123456|marc@example.be
4000123456789010|08/28|554|Alex Mercer|124 Broadway|New York|NY|10001|+12125550199|alex.m@example.com`;
    updateDetectedLines();
}
</script>
@endpush
@endsection
