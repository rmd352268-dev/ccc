// Multilingual i18n Dictionary: Russian (ru - default), Chinese (zh), English (en)
const translations = {
    ru: {
        // App Branding
        brand_name: "Payate CC",
        
        // Header & Layout
        pst_time: "Тихоокеанское время:",
        welcome: "Добро пожаловать:",
        total_recharge: "Всего пополнено:",
        add_funds: "Пополнить",
        cart: "Корзина",
        profile: "Профиль",
        logout: "Выход",
        day_mode: "День",
        night_mode: "Ночь",
        
        // Navigation Tabs
        nav_news: "Новости",
        nav_cards: "Карты (CC)",
        nav_wholesale: "Опт (Пакеты)",
        nav_orders: "Заказы",
        nav_funds: "Пополнение",
        nav_commission: "Партнерка (Комиссия)",
        nav_tickets: "Поддержка",
        
        // Auth - Login Page (Russian Default)
        login_gateway: "ШЛЮЗ СЕТИ // TLS 1.3",
        login_fx_active: "FX: МАТРИЦА ВКЛ",
        login_fx_dark: "FX: ТЕМНЫЙ ФОН",
        login_banner_tag: "ТЕРМИНАЛЬНЫЙ ПРОТОКОЛ",
        login_banner_title: "PAYATE CC",
        login_encryption: "ШИФРОВАНИЕ:",
        login_node_cluster: "КЛАСТЕР НОДЫ:",
        login_auth_proto: "ПРОТОКОЛ АВТОРИЗАЦИИ:",
        login_title: "ВХОД В СИСТЕМУ",
        login_secure: "БЕЗОПАСНЫЙ УЗЕЛ",
        login_username_label: "Имя пользователя / Email",
        login_username_ph: "Введите ваш логин или email",
        login_pass_label: "Ключ доступа (Пароль)",
        login_pass_ph: "Введите ваш основной пароль",
        login_remember: "Запомнить устройство",
        login_public_shop: "Магазин карт →",
        login_captcha_ph: "Решите пример",
        login_btn: "ВОЙТИ В СИСТЕМУ",
        register_btn: "РЕГИСТРАЦИЯ",
        status_online: "СИСТЕМА ОНЛАЙН",
        encrypted_256: "256-БИТНОЕ ШИФРОВАНИЕ",
        
        // Auth - Register Page
        reg_banner_tag: "РЕГИСТРАЦИЯ КЛИЕНТА",
        reg_title: "СОЗДАНИЕ АККАУНТА",
        reg_username_label: "Имя пользователя (Логин)",
        reg_username_ph: "Придумайте уникальный логин",
        reg_email_label: "Email адрес",
        reg_email_ph: "Ваш действующий email",
        reg_pass_label: "Основной пароль",
        reg_pass_ph: "Минимум 4-6 символов",
        reg_pass_confirm_label: "Повторите пароль",
        reg_pass_confirm_ph: "Подтвердите основной пароль",
        reg_sec_pass_label: "Второй PIN безопасности (2FA)",
        reg_sec_pass_ph: "4-значный защитный PIN код",
        reg_telegram_label: "Telegram (Опционально)",
        reg_submit: "ЗАРЕГИСТРИРОВАТЬСЯ",
        reg_has_account: "Уже есть аккаунт? Войти",
        
        // Auth - Secondary 2FA PIN Page
        sec_banner_tag: "БЕЗОПАСНЫЙ АНКЛАВ",
        sec_banner_title: "2FA PIN",
        sec_verified_user: "ПОДТВЕРЖДЕННЫЙ КЛИЕНТ:",
        sec_pin_label: "Второй PIN безопасности (2FA)",
        sec_pin_ph: "•••• (Введите ваш PIN код)",
        sec_btn: "ПОДТВЕРДИТЬ И ВОЙТИ",
        sec_back: "← Вернуться к форме входа",
        
        // Filters & Marketplace
        filter_bins: "БИНы (BIN)",
        filter_bins_ph: "Используйте перенос строки для разделения нескольких записей.",
        filter_zips: "Индексы (ZIP)",
        filter_zips_ph: "Используйте перенос строки для разделения нескольких записей.",
        filter_bank: "Банк",
        filter_country: "Страна",
        filter_brand: "Бренд",
        filter_type: "Кредит / Дебет",
        filter_base: "База",
        filter_price_range: "Диапазон цен",
        filter_min_price: "Мин. цена",
        filter_max_price: "Макс. цена",
        filter_zip_toggle: "ZIP",
        filter_address_toggle: "Полный адрес",
        filter_ua_toggle: "User-Agent",
        filter_phone_toggle: "Телефон",
        filter_mail_toggle: "Почта",
        filter_email_pass_toggle: "Пароль к почте",
        btn_reset: "СБРОС",
        btn_search: "ПОИСК",
        
        // Table Headers
        th_bin: "БИН",
        th_brand: "Бренд",
        th_type: "Тип",
        th_country: "Страна",
        th_name: "Имя",
        th_address: "Адрес",
        th_zip: "ZIP",
        th_phone: "Тел.",
        th_mail: "Почта",
        th_ssn: "SSN",
        th_dob: "DOB",
        th_bank: "Банк",
        th_base: "База",
        th_refundable: "Возврат",
        th_price: "Цена",
        th_buy: "Купить / Корзина",
        btn_add: "В корзину",
        selected_cards: "Выбрано карт:",
        btn_bulk_add: "Добавить выбранные в корзину",
        page_indicator: "Страница",
        of_indicator: "из",
        items_indicator: "карт",
        prev_page: "‹ Назад",
        next_page: "Вперед ›",
        no_cards_found: "Карты по указанным фильтрам не найдены.",
        
        switched_lang: "Язык изменен на Русский 🇷🇺",
        switched_theme: "Тема переключена",
        added_to_cart: "Карта добавлена в корзину!",
        all_option: "Все"
    },
    
    en: {
        brand_name: "Payate CC",
        pst_time: "Pacific Standard Time:",
        welcome: "Welcome User:",
        total_recharge: "Total Recharged:",
        add_funds: "Add Funds",
        cart: "Shopping Cart",
        profile: "My Profile",
        logout: "Logout",
        day_mode: "Day",
        night_mode: "Night",
        
        nav_news: "News & Alerts",
        nav_cards: "Cards (Payate CC)",
        nav_wholesale: "Wholesale (Packs)",
        nav_orders: "My Orders",
        nav_funds: "Add Funds",
        nav_commission: "Affiliate & Commission",
        nav_tickets: "Support Desk",
        
        login_gateway: "GATEWAY ONLINE // TLS 1.3",
        login_fx_active: "FX: MATRIX ACTIVE",
        login_fx_dark: "FX: SOLID DARK",
        login_banner_tag: "TERMINAL PROTOCOL",
        login_banner_title: "PAYATE CC",
        login_encryption: "ENCRYPTION:",
        login_node_cluster: "NODE CLUSTER:",
        login_auth_proto: "AUTH PROTOCOL:",
        login_title: "MEMBER LOGIN",
        login_secure: "SECURE NODE",
        login_username_label: "Username / Email",
        login_username_ph: "Enter your username or email",
        login_pass_label: "Access Key",
        login_pass_ph: "Enter your primary password",
        login_remember: "Remember Device",
        login_public_shop: "Public Shop →",
        login_captcha_ph: "Solve Captcha",
        login_btn: "LOGIN",
        register_btn: "REGISTER",
        status_online: "SYSTEM ONLINE",
        encrypted_256: "256-BIT ENCRYPTED",
        
        reg_banner_tag: "CLIENT ENROLLMENT",
        reg_title: "ENROLL NEW ID",
        reg_username_label: "Username / Handle",
        reg_username_ph: "Create a unique username",
        reg_email_label: "Email Address",
        reg_email_ph: "Your valid email address",
        reg_pass_label: "Primary Password",
        reg_pass_ph: "Minimum 4-6 characters",
        reg_pass_confirm_label: "Confirm Password",
        reg_pass_confirm_ph: "Re-enter primary password",
        reg_sec_pass_label: "Secondary Security PIN (2FA)",
        reg_sec_pass_ph: "4-digit security PIN",
        reg_telegram_label: "Telegram (Optional)",
        reg_submit: "ENROLL ID",
        reg_has_account: "Already have account? Login",
        
        sec_banner_tag: "SECURE ENCLAVE",
        sec_banner_title: "2FA PIN",
        sec_verified_user: "VERIFIED IDENTITY:",
        sec_pin_label: "Secondary Security PIN",
        sec_pin_ph: "•••• (Enter your PIN code)",
        sec_btn: "VERIFY & ENTER PORTAL",
        sec_back: "← Return to Login",
        
        filter_bins: "BINs Filter",
        filter_bins_ph: "Use newlines to separate multiple BIN queries.",
        filter_zips: "ZIP Codes",
        filter_zips_ph: "Use newlines to separate multiple ZIP codes.",
        filter_bank: "Bank",
        filter_country: "Country",
        filter_brand: "Brand",
        filter_type: "Debit / Credit",
        filter_base: "Database Base",
        filter_price_range: "Price Range",
        filter_min_price: "Min Price",
        filter_max_price: "Max Price",
        filter_zip_toggle: "ZIP",
        filter_address_toggle: "Full Address",
        filter_ua_toggle: "User-Agent",
        filter_phone_toggle: "Phone",
        filter_mail_toggle: "Email",
        filter_email_pass_toggle: "Email Pass",
        btn_reset: "RESET",
        btn_search: "SEARCH",
        
        th_bin: "BIN",
        th_brand: "Brand",
        th_type: "Type",
        th_country: "Country",
        th_name: "Holder Name",
        th_address: "Address",
        th_zip: "ZIP",
        th_phone: "Phone",
        th_mail: "Email",
        th_ssn: "SSN",
        th_dob: "DOB",
        th_bank: "Bank",
        th_base: "Base",
        th_refundable: "Refund",
        th_price: "Price",
        th_buy: "Cart / Buy",
        btn_add: "Add to Cart",
        selected_cards: "Selected Cards:",
        btn_bulk_add: "Add Selected to Cart",
        page_indicator: "Page",
        of_indicator: "of",
        items_indicator: "cards",
        prev_page: "‹ Prev",
        next_page: "Next ›",
        no_cards_found: "No cards matching the selected criteria.",
        
        switched_lang: "Language switched to English 🇺🇸",
        switched_theme: "Theme toggled",
        added_to_cart: "Card added to cart!",
        all_option: "All"
    },

    zh: {
        brand_name: "Payate CC",
        pst_time: "太平洋时间:",
        welcome: "欢迎用户:",
        total_recharge: "累计充值:",
        add_funds: "充值余额",
        cart: "购物车",
        profile: "个人中心",
        logout: "退出登录",
        day_mode: "日间",
        night_mode: "夜间",
        
        nav_news: "最新资讯",
        nav_cards: "信用卡 (Payate CC)",
        nav_wholesale: "批发专区",
        nav_orders: "我的订单",
        nav_funds: "账户充值",
        nav_commission: "推广返佣 (Commission)",
        nav_tickets: "工单支持",
        
        login_gateway: "安全网关 // TLS 1.3",
        login_fx_active: "FX: 矩阵特效",
        login_fx_dark: "FX: 暗黑背景",
        login_banner_tag: "终端协议",
        login_banner_title: "PAYATE CC",
        login_encryption: "加密算法:",
        login_node_cluster: "节点集群:",
        login_auth_proto: "认证协议:",
        login_title: "会员登录",
        login_secure: "安全节点",
        login_username_label: "用户名 / 电子邮箱",
        login_username_ph: "输入您的用户名或邮箱",
        login_pass_label: "访问密钥 (密码)",
        login_pass_ph: "输入您的主密码",
        login_remember: "记住当前设备",
        login_public_shop: "公共卡商城 →",
        login_captcha_ph: "计算验证码",
        login_btn: "登录系统",
        register_btn: "注册新账号",
        status_online: "系统正常在线",
        encrypted_256: "256位高强度加密",
        
        reg_banner_tag: "客户注册协议",
        reg_title: "创建会员账号",
        reg_username_label: "用户名 (ID)",
        reg_username_ph: "设置唯一的登录名",
        reg_email_label: "电子邮箱",
        reg_email_ph: "有效的联系邮箱",
        reg_pass_label: "主登录密码",
        reg_pass_ph: "至少4-6位字符",
        reg_pass_confirm_label: "确认主密码",
        reg_pass_confirm_ph: "再次输入主密码",
        reg_sec_pass_label: "第二安全PIN码 (2FA)",
        reg_sec_pass_ph: "4位数字安全PIN码",
        reg_telegram_label: "Telegram (选填)",
        reg_submit: "立即注册",
        reg_has_account: "已有账号？直接登录",
        
        sec_banner_tag: "安全隔离区",
        sec_banner_title: "2FA PIN",
        sec_verified_user: "已验证用户:",
        sec_pin_label: "第二安全PIN码 (2FA)",
        sec_pin_ph: "•••• (输入您的PIN码)",
        sec_btn: "验证并进入控制台",
        sec_back: "← 返回登录页面",
        
        filter_bins: "卡头 (BIN)",
        filter_bins_ph: "每行输入一个卡头进行批量搜索。",
        filter_zips: "邮编 (ZIP)",
        filter_zips_ph: "每行输入一个邮政编码。",
        filter_bank: "开户行",
        filter_country: "国家/地区",
        filter_brand: "卡品牌",
        filter_type: "卡类型 (信用卡/借记卡)",
        filter_base: "数据库批次",
        filter_price_range: "价格区间",
        filter_min_price: "最低价格",
        filter_max_price: "最高价格",
        filter_zip_toggle: "包含邮编",
        filter_address_toggle: "包含完整地址",
        filter_ua_toggle: "包含浏览器UA",
        filter_phone_toggle: "包含手机号",
        filter_mail_toggle: "包含电子邮箱",
        filter_email_pass_toggle: "包含邮箱密码",
        btn_reset: "重置筛选",
        btn_search: "立即搜索",
        
        th_bin: "卡头 (BIN)",
        th_brand: "品牌",
        th_type: "类型",
        th_country: "国家",
        th_name: "持卡人姓名",
        th_address: "地址",
        th_zip: "邮编",
        th_phone: "电话",
        th_mail: "邮箱",
        th_ssn: "SSN",
        th_dob: "出生日期",
        th_bank: "发卡行",
        th_base: "数据库",
        th_refundable: "可退款",
        th_price: "价格",
        th_buy: "购买/加购",
        btn_add: "加入购物车",
        selected_cards: "已选中卡片:",
        btn_bulk_add: "批量加入购物车",
        page_indicator: "第",
        of_indicator: "页，共",
        items_indicator: "张卡片",
        prev_page: "‹ 上一页",
        next_page: "下一页 ›",
        no_cards_found: "未找到符合条件的卡片数据。",
        
        switched_lang: "已切换为中文 🇨🇳",
        switched_theme: "已切换主题",
        added_to_cart: "已成功加入购物车！",
        all_option: "全部"
    }
};

// Current active language (RU is the default!)
let currentLang = localStorage.getItem('site_lang') || 'ru';

// Apply translations across all data-i18n attributes
function applyTranslations(lang) {
    if (!translations[lang]) lang = 'ru';
    currentLang = lang;
    localStorage.setItem('site_lang', lang);
    document.documentElement.setAttribute('lang', lang);

    // Translate all standard text nodes
    document.querySelectorAll('[data-i18n]').forEach(el => {
        const key = el.getAttribute('data-i18n');
        if (translations[lang][key]) {
            el.textContent = translations[lang][key];
        }
    });

    // Translate all placeholders
    document.querySelectorAll('[data-i18n-ph]').forEach(el => {
        const key = el.getAttribute('data-i18n-ph');
        if (translations[lang][key]) {
            el.placeholder = translations[lang][key];
        }
    });

    // Update Dropdown UI if present
    const flagEl = document.getElementById('current-lang-flag');
    const labelEl = document.getElementById('current-lang-label');
    if (flagEl && labelEl) {
        if (lang === 'ru') {
            flagEl.textContent = '🇷🇺';
            labelEl.textContent = 'Русский';
        } else if (lang === 'zh') {
            flagEl.textContent = '🇨🇳';
            labelEl.textContent = '中文';
        } else {
            flagEl.textContent = '🇺🇸';
            labelEl.textContent = 'English';
        }
    }
}

function setLanguage(lang) {
    applyTranslations(lang);
    if (typeof showToast === 'function') {
        showToast(translations[lang].switched_lang || 'Language updated');
    }
}

// Automatically apply Russian (RU) default on DOM load
document.addEventListener('DOMContentLoaded', () => {
    applyTranslations(currentLang);
});
