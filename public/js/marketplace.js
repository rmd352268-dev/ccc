// Independent Theme Switcher for Public Site vs Admin Panel
function isAdminArea() {
    return window.location.pathname.startsWith('/admin');
}

function getThemeStorageKey() {
    return isAdminArea() ? 'admin_site_theme' : 'public_site_theme';
}

function getDefaultTheme() {
    // Both Public site and Dashboard default to Night Mode ('dark')
    return 'dark';
}

function initTheme() {
    const key = getThemeStorageKey();
    const defaultTheme = getDefaultTheme();
    const savedTheme = localStorage.getItem(key) || defaultTheme;
    
    document.documentElement.setAttribute('data-theme', savedTheme);
    updateThemeIcon(savedTheme);
}

function toggleTheme() {
    const key = getThemeStorageKey();
    const currentTheme = document.documentElement.getAttribute('data-theme') || getDefaultTheme();
    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
    
    document.documentElement.setAttribute('data-theme', newTheme);
    localStorage.setItem(key, newTheme);
    updateThemeIcon(newTheme);
    
    const lang = localStorage.getItem('site_lang') || 'ru';
    const msg = lang === 'ru' 
        ? (newTheme === 'dark' ? 'Включен Ночной режим 🌙' : 'Включен Дневной режим ☀️')
        : `Switched to ${newTheme === 'dark' ? 'Night' : 'Day'} Mode`;
    showToast(msg);
}

function updateThemeIcon(theme) {
    const icons = document.querySelectorAll('.theme-toggle-icon');
    icons.forEach(icon => {
        const lang = localStorage.getItem('site_lang') || 'ru';
        const dayText = lang === 'ru' ? ' День' : (lang === 'zh' ? ' 日间' : ' Day');
        const nightText = lang === 'ru' ? ' Ночь' : (lang === 'zh' ? ' 夜间' : ' Night');
        if (theme === 'dark') {
            icon.className = 'fa-solid fa-sun theme-toggle-icon';
            if (icon.nextSibling) icon.nextSibling.textContent = dayText;
        } else {
            icon.className = 'fa-solid fa-moon theme-toggle-icon';
            if (icon.nextSibling) icon.nextSibling.textContent = nightText;
        }
    });
}

document.addEventListener('DOMContentLoaded', initTheme);
initTheme();

// Real-time Pacific Time Digital Clock
function updatePacificTime() {
    const clockEl = document.getElementById('pst-clock');
    if (!clockEl) return;
    
    const options = {
        timeZone: 'America/Los_Angeles',
        hour12: false,
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    };
    const formatter = new Intl.DateTimeFormat([], options);
    clockEl.textContent = formatter.format(new Date());
}
setInterval(updatePacificTime, 1000);
updatePacificTime();

// Toast Notifications
function showToast(message, isError = false) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    
    const toast = document.createElement('div');
    toast.className = 'toast';
    if (isError) {
        toast.style.borderLeftColor = '#EF4444';
    }
    toast.innerHTML = (isError ? '⚠️ ' : '✅ ') + message;
    container.appendChild(toast);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.2s';
        setTimeout(() => toast.remove(), 250);
    }, 3000);
}

// Add to Cart Single Card
function addToCart(cardId) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    fetch(`/cart/add/${cardId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('cart-badge');
            if (badge) {
                badge.textContent = data.cart_count;
                badge.style.display = data.cart_count > 0 ? 'flex' : 'none';
            }
            showToast(data.message);
        } else {
            showToast(data.message, true);
        }
    })
    .catch(err => {
        showToast('Error adding to cart', true);
    });
}

// Bulk Selection and Bulk Add
function toggleSelectAll(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.card-row-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.card-row-checkbox:checked');
    const countEl = document.getElementById('selected-count');
    const bulkBtn = document.getElementById('btn-bulk-add');
    if (countEl) countEl.textContent = selected.length;
    if (bulkBtn) {
        bulkBtn.disabled = selected.length === 0;
        bulkBtn.style.opacity = selected.length === 0 ? '0.5' : '1';
    }
}

function addSelectedToCart() {
    const selected = document.querySelectorAll('.card-row-checkbox:checked');
    if (selected.length === 0) {
        showToast('No cards selected!', true);
        return;
    }

    const ids = Array.from(selected).map(cb => cb.value);
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/cart/bulk-add', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ ids: ids })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            const badge = document.getElementById('cart-badge');
            if (badge) {
                badge.textContent = data.cart_count;
                badge.style.display = 'flex';
            }
            showToast(data.message);
            const master = document.getElementById('select-all-header');
            if (master) master.checked = false;
            toggleSelectAll({ checked: false });
        } else {
            showToast(data.message, true);
        }
    });
}

// Copy to Clipboard
function copyText(text, label = 'Text') {
    navigator.clipboard.writeText(text).then(() => {
        showToast(`${label} copied to clipboard!`);
    });
}

// Modal handlers for Update Recharge
function openUpdateRechargeModal(currency = 'USDT-TRC20') {
    const select = document.getElementById('recharge-currency-select');
    if (select) select.value = currency;
    const m = document.getElementById('update-recharge-modal');
    if (m) m.classList.add('active');
}
function closeUpdateRechargeModal() {
    const m = document.getElementById('update-recharge-modal');
    if (m) m.classList.remove('active');
}
