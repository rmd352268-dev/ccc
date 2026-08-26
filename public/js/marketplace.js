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

// Helper: Get localized text with fallback
function getI18nText(key, fallback = '') {
    const lang = localStorage.getItem('site_lang') || 'ru';
    if (typeof translations !== 'undefined' && translations[lang] && translations[lang][key]) {
        return translations[lang][key];
    }
    return fallback;
}

// Update single or multiple card buttons & row visually
function updateCardCartUI(cardId, inCart, animate = true) {
    const buttons = document.querySelectorAll(`button[data-card-id="${cardId}"], button[onclick*="addToCart(${cardId}"]`);
    const inCartLabel = getI18nText('btn_in_cart', 'В корзине');
    const addLabel = getI18nText('btn_add', 'В корзину');

    buttons.forEach(btn => {
        const row = btn.closest('tr');
        if (inCart) {
            btn.classList.add('in-cart');
            btn.title = inCartLabel;
            btn.innerHTML = `<i class="fa-solid fa-check"></i> <span data-i18n="btn_in_cart">${inCartLabel}</span>`;
            if (row) row.classList.add('row-in-cart');
            if (animate) {
                btn.classList.remove('cart-pop-anim');
                void btn.offsetWidth; // trigger reflow
                btn.classList.add('cart-pop-anim');
                setTimeout(() => btn.classList.remove('cart-pop-anim'), 500);
            }
        } else {
            btn.classList.remove('in-cart');
            btn.title = addLabel;
            btn.innerHTML = `<i class="fa-solid fa-cart-shopping"></i> <span data-i18n="btn_add">${addLabel}</span>`;
            if (row) row.classList.remove('row-in-cart');
        }
    });
}

// Add/Toggle Cart Single Card with Real-time Animation
function addToCart(cardId, btnEl = null) {
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = metaCsrf ? metaCsrf.getAttribute('content') : '';

    const btn = btnEl || document.querySelector(`button[data-card-id="${cardId}"]`);
    const isCurrentlyInCart = btn ? btn.classList.contains('in-cart') : false;
    const targetState = !isCurrentlyInCart;

    // Instant visual feedback with bounce animation
    updateCardCartUI(cardId, targetState, true);

    fetch(`/cart/toggle/${cardId}`, {
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
            updateCardCartUI(cardId, data.in_cart !== undefined ? data.in_cart : true, true);
            showToast(data.message);
        } else {
            // Revert state if error
            updateCardCartUI(cardId, isCurrentlyInCart, false);
            showToast(data.message, true);
        }
    })
    .catch(err => {
        updateCardCartUI(cardId, isCurrentlyInCart, false);
        showToast('Error updating cart', true);
    });
}

// Bulk Selection and Bulk Add
function toggleSelectAll(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.card-row-checkbox, .card-select-cb');
    checkboxes.forEach(cb => {
        cb.checked = masterCheckbox.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const selected = document.querySelectorAll('.card-row-checkbox:checked, .card-select-cb:checked');
    const countEl = document.getElementById('selected-count');
    const bulkBtn = document.getElementById('btn-bulk-add');
    if (countEl) countEl.textContent = selected.length;
    if (bulkBtn) {
        bulkBtn.disabled = selected.length === 0;
        bulkBtn.style.opacity = selected.length === 0 ? '0.5' : '1';
    }
}

function addSelectedToCart() {
    const selected = document.querySelectorAll('.card-row-checkbox:checked, .card-select-cb:checked');
    if (selected.length === 0) {
        showToast('No cards selected!', true);
        return;
    }

    const ids = Array.from(selected).map(cb => cb.value);
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = metaCsrf ? metaCsrf.getAttribute('content') : '';

    // Optimistically update selected cards UI
    ids.forEach(id => updateCardCartUI(id, true, true));

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
            if (data.added_ids) {
                data.added_ids.forEach(id => updateCardCartUI(id, true, true));
            }
            showToast(data.message);
            const master = document.getElementById('select-all') || document.getElementById('select-all-header');
            if (master) master.checked = false;
            toggleSelectAll({ checked: false });
        } else {
            showToast(data.message, true);
        }
    })
    .catch(() => {
        showToast('Error adding selected cards to cart', true);
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
