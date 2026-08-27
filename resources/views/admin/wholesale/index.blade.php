@extends('admin.layout')

@section('title', 'Wholesale Bundles Management')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">Wholesale Packages & Bundles</h2>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 3px;">Configure bulk packages, attach card files, and adjust discounted wholesale rates.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    <!-- Create New Pack Form -->
    <div class="filter-card">
        <h3 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 14px;">
            <i class="fa-solid fa-plus-circle" style="color: var(--gold-primary);"></i> Create New Package
        </h3>
        <form action="{{ route('admin.wholesale.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Package Title</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. 50x UK Premium Pack" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Package highlights and details..."></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label class="form-label">Card Count</label>
                    <input type="number" name="card_count" class="form-control" value="50" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Sale Price ($)</label>
                    <input type="number" step="0.5" name="price" class="form-control" value="65.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Regular Price ($)</label>
                    <input type="number" step="0.5" name="original_price" class="form-control" value="100.00">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 12px;">
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control" value="United Kingdom" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" class="form-control" value="Debit & Credit" required>
                </div>
            </div>

            <!-- Optional Custom Cards File/Text for Wholesale -->
            <div class="form-group" style="margin-bottom: 14px;">
                <label class="form-label" style="display: flex; justify-content: space-between; align-items: center;">
                    <span><i class="fa-solid fa-file-arrow-up" style="color: var(--gold-primary);"></i> Attach Cards File (.txt / .csv) (Optional)</span>
                </label>
                <input type="file" name="cards_file" accept=".txt,.csv" class="form-control" style="padding: 5px 8px; font-size: 11px;">
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Or Paste Card Lines (1 line per card)</label>
                <textarea name="cards_data" class="form-control" rows="3" style="font-family: monospace; font-size: 11px;" placeholder="CARD|EXP|CVV|NAME... (optional, delivered on purchase)"></textarea>
            </div>

            <button type="submit" class="btn-search" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-plus"></i> Save Package
            </button>
        </form>
    </div>

    <!-- Existing Packs List -->
    <div class="table-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Cards</th>
                        <th>Price</th>
                        <th>Country / Type</th>
                        <th>Data Status</th>
                        <th class="text-center" style="min-width: 120px;">Admin Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($packs as $p)
                        <tr>
                            <td style="font-weight: 700; color: var(--text-primary);">{{ $p->title }}</td>
                            <td><span class="type-badge">{{ $p->card_count }} pcs</span></td>
                            <td style="font-weight: 800; color: #059669; font-family: monospace;">${{ number_format($p->price, 2) }}</td>
                            <td style="font-size: 12px; color: var(--text-muted);">{{ $p->country }} ({{ $p->type }})</td>
                            <td>
                                @if(!empty($p->cards_data))
                                    <span style="background: rgba(16,185,129,0.15); color: #10B981; font-size: 10px; font-weight: 700; padding: 2px 6px; border-radius: 4px;">
                                        <i class="fa-solid fa-file-lines"></i> Custom Data Attached
                                    </span>
                                @else
                                    <span style="color: var(--text-muted); font-size: 10.5px;">Auto Generated</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div style="display: inline-flex; gap: 6px; align-items: center;">
                                    <button type="button" class="btn-search" style="padding: 4px 8px; font-size: 11px;" onclick="openEditWholesaleModal({{ json_encode($p) }})" title="Edit Package">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </button>
                                    <form action="{{ route('admin.wholesale.delete', $p->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this wholesale pack?');">
                                        @csrf
                                        <button type="submit" class="btn-reset" style="padding: 4px 8px; font-size: 11px; color: #EF4444; border-color: rgba(239,68,68,0.3);" title="Delete">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">No packages created yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="padding: 12px 18px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center;">
            <span style="font-size: 11.5px; color: var(--text-muted);">Total: {{ count($packs) }} packages</span>
            <form action="{{ route('admin.wholesale.clearAll') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Clear all wholesale packages?');">
                @csrf
                <button type="submit" class="btn-reset" style="background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11.5px; font-weight: 800; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Clear All Wholesale (Удалить все)
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Edit Wholesale Pack Modal -->
<div id="edit-wholesale-modal" class="modal-backdrop">
    <div class="modal-content" style="max-width: 550px;">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary);">
                <i class="fa-solid fa-pen-to-square" style="color: var(--gold-primary);"></i> Edit Wholesale Package
            </h3>
            <button type="button" class="modal-close" onclick="closeEditWholesaleModal()">&times;</button>
        </div>
        <form id="edit-wholesale-form" action="" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label class="form-label">Package Title</label>
                <input type="text" name="title" id="edit_ws_title" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea name="description" id="edit_ws_desc" class="form-control" rows="2"></textarea>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="form-group">
                    <label class="form-label">Card Count</label>
                    <input type="number" name="card_count" id="edit_ws_count" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Sale Price ($)</label>
                    <input type="number" step="0.5" name="price" id="edit_ws_price" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Regular Price ($)</label>
                    <input type="number" step="0.5" name="original_price" id="edit_ws_orig_price" class="form-control">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" id="edit_ws_country" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Type</label>
                    <input type="text" name="type" id="edit_ws_type" class="form-control" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label">Upload New Cards File (.txt / .csv)</label>
                <input type="file" name="cards_file" accept=".txt,.csv" class="form-control" style="padding: 5px 8px; font-size: 11px;">
            </div>

            <div class="form-group" style="margin-bottom: 16px;">
                <label class="form-label">Attached Cards Data (1 line per card)</label>
                <textarea name="cards_data" id="edit_ws_cards_data" class="form-control" rows="3" style="font-family: monospace; font-size: 11px;"></textarea>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-reset" onclick="closeEditWholesaleModal()">Cancel</button>
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-floppy-disk"></i> Update Package
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditWholesaleModal(pack) {
    document.getElementById('edit-wholesale-form').action = `{{ url('/airana1713admin/wholesale') }}/${pack.id}/update`;
    document.getElementById('edit_ws_title').value = pack.title || '';
    document.getElementById('edit_ws_desc').value = pack.description || '';
    document.getElementById('edit_ws_count').value = pack.card_count || 0;
    document.getElementById('edit_ws_price').value = pack.price || 0;
    document.getElementById('edit_ws_orig_price').value = pack.original_price || (pack.price * 1.5).toFixed(2);
    document.getElementById('edit_ws_country').value = pack.country || '';
    document.getElementById('edit_ws_type').value = pack.type || '';
    document.getElementById('edit_ws_cards_data').value = pack.cards_data || '';
    document.getElementById('edit-wholesale-modal').classList.add('active');
}
function closeEditWholesaleModal() {
    document.getElementById('edit-wholesale-modal').classList.remove('active');
}
</script>
@endpush
@endsection
