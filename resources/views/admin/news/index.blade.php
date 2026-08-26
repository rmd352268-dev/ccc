@extends('admin.layout')

@section('title', 'News & Announcements Management')

@section('content')
<div style="margin-bottom: 20px;">
    <h2 style="font-size: 20px; font-weight: 800; color: var(--text-primary);">News & Base Announcements</h2>
    <p style="font-size: 13px; color: var(--text-muted); margin-top: 3px;">Publish new base announcements, edit published notices, and manage public bulletin board.</p>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 20px;">
    <!-- Create News Form -->
    <div class="filter-card">
        <h3 style="font-size: 15px; font-weight: 700; color: var(--text-primary); margin-bottom: 14px;">
            <i class="fa-solid fa-bullhorn" style="color: var(--gold-primary);"></i> Publish Announcement
        </h3>
        <form action="{{ route('admin.news.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Announcement Title</label>
                <input type="text" name="title" class="form-control" placeholder="🔥 New High Validity US/UK Base Uploaded!" required>
            </div>

            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" class="form-select">
                    <option value="Database">Database Dump</option>
                    <option value="System">System Update</option>
                    <option value="Promotion">Promotion & Discount</option>
                    <option value="Notice">General Notice</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Content Body</label>
                <textarea name="content" class="form-control" style="min-height: 120px;" placeholder="Full details of the announcement..." required></textarea>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <span style="font-size: 12px; color: var(--text-muted);">Pin to Top of Feed</span>
                <label class="switch">
                    <input type="checkbox" name="is_pinned" value="1">
                    <span class="slider"></span>
                </label>
            </div>

            <button type="submit" class="btn-search" style="width: 100%; justify-content: center;">
                <i class="fa-solid fa-paper-plane"></i> Publish to Public Site
            </button>
        </form>
    </div>

    <!-- News List -->
    <div style="display: flex; flex-direction: column; gap: 12px;">
        @forelse($news as $n)
            <div class="filter-card" style="{{ $n->is_pinned ? 'border-left: 4px solid #059669;' : '' }}">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div>
                        @if($n->is_pinned)
                            <span class="type-badge" style="background: rgba(5,150,105,0.15); color: #059669; font-size: 10px; font-weight: 800;">PINNED</span>
                        @endif
                        <span class="type-badge" style="font-size: 10px;">{{ $n->category }}</span>
                        <strong style="color: var(--text-primary); font-size: 14px; margin-left: 4px;">{{ $n->title }}</strong>
                    </div>
                    <div style="display: flex; gap: 6px; align-items: center;">
                        <button type="button" class="btn-search" style="padding: 3px 8px; font-size: 11px;" onclick="openEditNewsModal({{ json_encode($n) }})" title="Edit Announcement">
                            <i class="fa-solid fa-pen-to-square"></i> Edit
                        </button>
                        <form action="{{ route('admin.news.delete', $n->id) }}" method="POST" onsubmit="return confirm('Delete this announcement?');">
                            @csrf
                            <button type="submit" class="btn-reset" style="padding: 3px 8px; font-size: 11px; color: #EF4444; border-color: rgba(239,68,68,0.3);" title="Delete">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <p style="font-size: 13px; color: var(--text-primary); line-height: 1.5;">{{ $n->content }}</p>
                <div style="font-size: 11px; color: var(--text-muted); margin-top: 8px; font-family: monospace;">
                    Published: {{ $n->created_at ? $n->created_at->format('Y-m-d H:i') : 'Just now' }}
                </div>
            </div>
        @empty
            <div class="filter-card" style="text-align: center; color: var(--text-muted); padding: 30px;">
                No announcements published.
            </div>
        @endforelse

        @if(count($news) > 0)
            <div style="margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                <span style="font-size: 11.5px; color: var(--text-muted);">Total: {{ count($news) }} notices</span>
                <form action="{{ route('admin.news.clearAll') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Clear all announcements and news?');">
                    @csrf
                    <button type="submit" class="btn-reset" style="background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11.5px; font-weight: 800; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                        <i class="fa-solid fa-trash-can"></i> Clear All News (Очистить все новости)
                    </button>
                </form>
            </div>
        @endif
    </div>
</div>

<!-- Edit News Modal -->
<div id="edit-news-modal" class="modal-backdrop">
    <div class="modal-content" style="max-width: 550px;">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary);">
                <i class="fa-solid fa-pen-to-square" style="color: var(--gold-primary);"></i> Edit Announcement
            </h3>
            <button type="button" class="modal-close" onclick="closeEditNewsModal()">&times;</button>
        </div>
        <form id="edit-news-form" action="" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Announcement Title</label>
                <input type="text" name="title" id="edit_news_title" class="form-control" required>
            </div>

            <div class="form-group">
                <label class="form-label">Category</label>
                <select name="category" id="edit_news_category" class="form-select">
                    <option value="Database">Database Dump</option>
                    <option value="System">System Update</option>
                    <option value="Promotion">Promotion & Discount</option>
                    <option value="Notice">General Notice</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Content Body</label>
                <textarea name="content" id="edit_news_content" class="form-control" style="min-height: 120px;" required></textarea>
            </div>

            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-reset" onclick="closeEditNewsModal()">Cancel</button>
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-floppy-disk"></i> Update Notice
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditNewsModal(item) {
    document.getElementById('edit-news-form').action = `/admin/news/${item.id}/update`;
    document.getElementById('edit_news_title').value = item.title || '';
    document.getElementById('edit_news_category').value = item.category || 'Database';
    document.getElementById('edit_news_content').value = item.content || '';
    document.getElementById('edit-news-modal').classList.add('active');
}
function closeEditNewsModal() {
    document.getElementById('edit-news-modal').classList.remove('active');
}
</script>
@endpush
@endsection
