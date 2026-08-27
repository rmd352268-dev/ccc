@extends('admin.layout')

@section('title', 'Master User Control & Profile Suite')

@section('content')
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="font-size: 22px; font-weight: 800; color: var(--text-primary); display: flex; align-items: center; gap: 10px;">
            <i class="fa-solid fa-users-gear" style="color: var(--gold-primary);"></i> Client Profiles & Financial Control Suite
        </h1>
        <p style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
            Manage all registered client accounts, edit profile details, suspend/activate accounts, zero balances, and reset passwords/PINs.
        </p>
    </div>

    <button type="button" class="btn-search" onclick="openAddUserModal()" style="padding: 8px 18px; font-size: 13px;">
        <i class="fa-solid fa-user-plus"></i> Add New Client
    </button>
</div>

<!-- Users Table -->
<div class="table-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username / Name</th>
                    <th>Balance</th>
                    <th>Total Recharged</th>
                    <th>Contact Information</th>
                    <th>Country / Tier</th>
                    <th>Account Status</th>
                    <th class="text-center" style="min-width: 260px;">Admin Control Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td style="font-family: monospace; font-weight: 700; color: var(--text-muted);">#{{ $user->id }}</td>
                        <td>
                            <strong style="color: var(--text-primary); font-size: 14px;">{{ $user->name }}</strong>
                            <div style="font-size: 12px; color: var(--gold-primary); font-family: monospace;">@<span>{{ $user->username }}</span></div>
                        </td>
                        <td>
                            <span style="font-size: 15px; font-weight: 800; color: {{ $user->balance > 0 ? '#059669' : '#EF4444' }}; font-family: monospace;">
                                ${{ number_format($user->balance, 2) }}
                            </span>
                        </td>
                        <td style="font-size: 13px; font-weight: 700; color: var(--text-primary); font-family: monospace;">
                            ${{ number_format($user->total_recharge, 2) }}
                        </td>
                        <td>
                            <div style="font-size: 12px; display: flex; flex-direction: column; gap: 2px;">
                                <span style="color: var(--text-primary);"><i class="fa-solid fa-envelope" style="color: #3B82F6;"></i> {{ $user->email }}</span>
                                @if($user->telegram)
                                    <span style="color: #0284C7;"><i class="fa-brands fa-telegram"></i> {{ $user->telegram }}</span>
                                @endif
                                @if($user->phone)
                                    <span style="color: #059669;"><i class="fa-solid fa-phone"></i> {{ $user->phone }}</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 12px;">
                                <span style="font-weight: 700;">{{ \App\Helpers\CountryHelper::getFlag($user->country) }} {{ $user->country }}</span>
                                <div style="font-size: 10px; color: var(--gold-dark); font-weight: 800; margin-top: 2px;">{{ $user->tier }}</div>
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-direction: column; gap: 4px;">
                                @if($user->status === 'active')
                                    <span style="background: rgba(5,150,105,0.15); color: #059669; border: 1px solid rgba(5,150,105,0.3); font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; width: fit-content;">
                                        <i class="fa-solid fa-circle-check"></i> ACTIVE
                                    </span>
                                @else
                                    <span style="background: rgba(239,68,68,0.15); color: #EF4444; border: 1px solid rgba(239,68,68,0.3); font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; width: fit-content;">
                                        <i class="fa-solid fa-ban"></i> BANNED
                                    </span>
                                @endif

                                @if($user->is_activated)
                                    <span style="background: rgba(16,185,129,0.12); color: #10B981; border: 1px solid rgba(16,185,129,0.35); font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; width: fit-content;" title="Account is permanently activated (Vault Unlocked)">
                                        <i class="fa-solid fa-lock-open"></i> ACTIVATED
                                    </span>
                                @else
                                    <span style="background: rgba(245,158,11,0.12); color: #F59E0B; border: 1px solid rgba(245,158,11,0.35); font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; width: fit-content;" title="Deposit required to unlock vault">
                                        <i class="fa-solid fa-lock"></i> LOCKED
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="text-center">
                            <div style="display: inline-flex; gap: 6px; align-items: center; flex-wrap: wrap; justify-content: center;">
                                <!-- Edit Profile Button -->
                                <button type="button" class="btn-search" style="padding: 5px 9px; font-size: 11px;" onclick="openEditUserModal({{ json_encode($user) }})" title="Edit User Information, Passwords, and PIN">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>

                                @if($user->role === 'admin' || $user->username === 'admin')
                                    <span style="font-size: 11px; font-weight: 800; color: #F59E0B; padding: 4px 8px; border-radius: 4px; background: rgba(245,158,11,0.15); border: 1px solid rgba(245,158,11,0.3);">
                                        <i class="fa-solid fa-crown"></i> Admin Master
                                    </span>
                                @else
                                    <!-- Toggle Vault Activation (1-Click) -->
                                    <form action="{{ route('admin.users.toggleActivate', $user->id) }}" method="POST" onsubmit="return confirm('{{ $user->is_activated ? 'Deactivate and lock vault for' : 'Permanently activate vault for' }} @{{ $user->username }}?')">
                                        @csrf
                                        <button type="submit" class="btn-reset" style="padding: 5px 8px; font-size: 11px; color: {{ $user->is_activated ? '#10B981' : '#F59E0B' }}; border-color: {{ $user->is_activated ? 'rgba(16,185,129,0.4)' : 'rgba(245,158,11,0.4)' }};" title="{{ $user->is_activated ? 'Deactivate Vault' : 'Activate Vault Permanently' }}">
                                            <i class="fa-solid {{ $user->is_activated ? 'fa-lock-open' : 'fa-bolt' }}"></i> {{ $user->is_activated ? 'Active' : 'Unlock' }}
                                        </button>
                                    </form>

                                    <!-- Ban / Unban Toggle Button -->
                                    <form action="{{ route('admin.users.toggleSuspend', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to {{ $user->status === 'active' ? 'BAN' : 'UNBAN' }} user @{{ $user->username }}?')">
                                        @csrf
                                        @if($user->status === 'active')
                                            <button type="submit" class="btn-reset" style="padding: 5px 9px; font-size: 11px; color: #EF4444; border-color: rgba(239,68,68,0.4);" title="Ban user from accessing website">
                                                <i class="fa-solid fa-ban"></i> Ban
                                            </button>
                                        @else
                                            <button type="submit" class="btn-reset" style="padding: 5px 9px; font-size: 11px; color: #10B981; border-color: rgba(16,185,129,0.4);" title="Unban user access">
                                                <i class="fa-solid fa-user-check"></i> Unban
                                            </button>
                                        @endif
                                    </form>

                                    <!-- Zero Balance Button (1-Click) -->
                                    <form action="{{ route('admin.users.zeroBalance', $user->id) }}" method="POST" onsubmit="return confirm('Zero out balance for @{{ $user->username }}? Note: User will remain active and able to browse cards.')">
                                        @csrf
                                        <button type="submit" class="btn-reset" style="padding: 5px 8px; font-size: 11px; color: #F59E0B; border-color: rgba(245,158,11,0.4);" title="Set Balance to $0.00">
                                            <i class="fa-solid fa-circle-xmark"></i> Zero $0
                                        </button>
                                    </form>

                                    <!-- Delete User Button -->
                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" onsubmit="return confirm('Delete client @{{ $user->username }} permanently?')">
                                        @csrf
                                        <button type="submit" class="btn-reset" style="padding: 5px 8px; font-size: 11px; color: #DC2626; border-color: rgba(220,38,38,0.3);" title="Delete user">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px; color: var(--text-muted);">
                            No client records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding: 12px 18px; border-top: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div style="display: flex; align-items: center; gap: 14px;">
            <span style="font-size: 11.5px; color: var(--text-muted);">Total: {{ $users->total() }} clients</span>
            <form action="{{ route('admin.users.clearAll') }}" method="POST" onsubmit="return confirm('⚠️ WARNING: Clear all non-admin client accounts from the database?');">
                @csrf
                <button type="submit" class="btn-reset" style="background: rgba(239, 68, 68, 0.12); color: #EF4444; border: 1px solid rgba(239, 68, 68, 0.4); font-size: 11.5px; font-weight: 800; padding: 5px 12px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-trash-can"></i> Clear All Users (Удалить всех)
                </button>
            </form>
        </div>
        <div>{{ $users->links('vendor.pagination.custom') }}</div>
    </div>
</div>

<!-- Edit User Full Profile Modal -->
<div id="edit-user-modal" class="modal-backdrop">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary);">
                <i class="fa-solid fa-user-pen" style="color: var(--gold-primary);"></i> Edit Client Information & Security Credentials
            </h3>
            <button type="button" class="modal-close" onclick="closeEditUserModal()">&times;</button>
        </div>
        <form id="edit-user-form" action="" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" id="edit_username" class="form-control" style="font-family: monospace; font-weight: 700;" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: #059669;">Account Balance ($)</label>
                    <input type="number" step="0.01" name="balance" id="edit_balance" class="form-control" style="border-color: #059669; font-weight: 800;" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Total Recharged ($)</label>
                    <input type="number" step="0.01" name="total_recharge" id="edit_total_recharge" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Status</label>
                    <select name="status" id="edit_status" class="form-select">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                        <option value="banned">Banned</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Telegram</label>
                    <input type="text" name="telegram" id="edit_telegram" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone</label>
                    <input type="text" name="phone" id="edit_phone" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" id="edit_country" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Account Tier</label>
                    <select name="tier" id="edit_tier" class="form-select">
                        <option value="Standard Member">Standard Member</option>
                        <option value="Verified VIP Member">Verified VIP Member</option>
                        <option value="Diamond Wholesaler">Diamond Wholesaler</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: #F59E0B;">Vault Activation Status</label>
                    <select name="is_activated" id="edit_is_activated" class="form-select">
                        <option value="1">Activated (Vault Unlocked - Can browse cards)</option>
                        <option value="0">Locked (Requires deposit to unlock)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: #3B82F6;">Reset Primary Password</label>
                    <input type="text" name="new_password" class="form-control" placeholder="Leave empty to keep current">
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: #10B981;">Reset Secondary PIN (2FA)</label>
                    <input type="text" name="new_secondary_password" class="form-control" placeholder="Leave empty to keep current PIN">
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-reset" onclick="closeEditUserModal()">Cancel</button>
                <button type="submit" class="btn-search">
                    <i class="fa-solid fa-check"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add User Modal -->
<div id="add-user-modal" class="modal-backdrop">
    <div class="modal-content" style="max-width: 650px;">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 800; color: var(--text-primary);">
                <i class="fa-solid fa-user-plus" style="color: var(--gold-primary);"></i> Create New Client Account
            </h3>
            <button type="button" class="modal-close" onclick="closeAddUserModal()">&times;</button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                <div class="form-group">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="client_user1">
                </div>
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required placeholder="user@gmail.com">
                </div>
                <div class="form-group">
                    <label class="form-label">Initial Balance ($)</label>
                    <input type="number" step="0.01" name="balance" value="0.00" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Primary Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                <div class="form-group">
                    <label class="form-label">Secondary PIN (2FA)</label>
                    <input type="text" name="secondary_password" class="form-control" placeholder="4-6 digit PIN">
                </div>
                <div class="form-group">
                    <label class="form-label" style="color: #F59E0B;">Vault Activation</label>
                    <select name="is_activated" class="form-select">
                        <option value="0">Locked (Requires deposit)</option>
                        <option value="1">Activated (Unlock immediately)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Telegram</label>
                    <input type="text" name="telegram" class="form-control" placeholder="@telegram_handle">
                </div>
                <div class="form-group">
                    <label class="form-label">Country</label>
                    <input type="text" name="country" class="form-control" placeholder="US">
                </div>
            </div>
            <div style="margin-top: 20px; display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-reset" onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="btn-search">Create Account</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditUserModal(user) {
    document.getElementById('edit-user-form').action = `{{ url('/airana1713admin/users') }}/${user.id}/update`;
    document.getElementById('edit_username').value = user.username || '';
    document.getElementById('edit_name').value = user.name || '';
    document.getElementById('edit_email').value = user.email || '';
    document.getElementById('edit_balance').value = user.balance || 0;
    document.getElementById('edit_total_recharge').value = user.total_recharge || 0;
    document.getElementById('edit_telegram').value = user.telegram || '';
    document.getElementById('edit_phone').value = user.phone || '';
    document.getElementById('edit_country').value = user.country || 'US';
    document.getElementById('edit_tier').value = user.tier || 'Verified Member';
    document.getElementById('edit_status').value = user.status || 'active';
    document.getElementById('edit_is_activated').value = (user.is_activated == 1 || user.is_activated === true) ? '1' : '0';
    
    document.getElementById('edit-user-modal').classList.add('active');
}
function closeEditUserModal() {
    document.getElementById('edit-user-modal').classList.remove('active');
}
function openAddUserModal() {
    document.getElementById('add-user-modal').classList.add('active');
}
function closeAddUserModal() {
    document.getElementById('add-user-modal').classList.remove('active');
}
</script>
@endpush
@endsection
