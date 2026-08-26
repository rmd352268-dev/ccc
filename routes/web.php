<?php

use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\FundController;
use App\Http\Controllers\WholesaleController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\CommissionController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Authentication Routes (2-Step Login with Captcha & Secondary Password)
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'doLogin'])->name('login.post');
Route::get('/login/secondary', [AuthController::class, 'showSecondary'])->name('login.secondary');
Route::post('/login/secondary', [AuthController::class, 'doSecondary'])->name('login.secondary.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'doRegister'])->name('register.post');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout.post');

// Public Marketplace / Cvv2 (Payate CC / CruzerCC)
Route::get('/', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/cvv2', [MarketplaceController::class, 'index'])->name('cvv2.index');
Route::get('/shop', [MarketplaceController::class, 'index'])->name('shop.index');

// User Profile Settings (With editable Username)
Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
Route::post('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
Route::post('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

// News
Route::get('/news', [NewsController::class, 'index'])->name('news.index');

// Wholesale
Route::get('/wholesale', [WholesaleController::class, 'index'])->name('wholesale.index');
Route::post('/wholesale/{id}/buy', [WholesaleController::class, 'buyPack'])->name('wholesale.buy');

// Cart
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/toggle/{id}', [CartController::class, 'toggle'])->name('cart.toggle');
Route::post('/cart/add/{id}', [CartController::class, 'add'])->name('cart.add');
Route::post('/cart/bulk-add', [CartController::class, 'bulkAdd'])->name('cart.bulkAdd');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/clear', [CartController::class, 'clear'])->name('cart.clear');
Route::post('/cart/checkout', [CartController::class, 'checkout'])->name('cart.checkout');

// Orders (Non-refundable)
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
Route::get('/orders/{id}/download/txt', [OrderController::class, 'downloadTxt'])->name('orders.downloadTxt');
Route::get('/orders/{id}/download/raw', [OrderController::class, 'downloadRawTxt'])->name('orders.downloadRaw');

// Add Funds / Crypto Recharge
Route::get('/funds', [FundController::class, 'index'])->name('funds.index');
Route::get('/recharge', [FundController::class, 'index'])->name('funds.recharge');
Route::post('/funds/submit-recharge', [FundController::class, 'submitRecharge'])->name('funds.submitRecharge');
Route::post('/funds/mock-add', [FundController::class, 'mockAdd'])->name('funds.mockAdd');

// Live Real-Time User Balance & Stats Endpoint
Route::get('/api/user/live-stats', [MarketplaceController::class, 'liveStats'])->name('api.user.liveStats');

// Telegram 1-Click Instant Action Endpoints
Route::get('/api/telegram/approve-deposit/{id}/{secret}', [FundController::class, 'telegramApproveDeposit'])->name('api.telegram.approve');
Route::get('/api/telegram/reject-deposit/{id}/{secret}', [FundController::class, 'telegramRejectDeposit'])->name('api.telegram.reject');

// Commission / Affiliate
Route::get('/commission', [CommissionController::class, 'index'])->name('commission.index');
Route::post('/commission/transfer', [CommissionController::class, 'transferToBalance'])->name('commission.transfer');

// Tickets
Route::get('/tickets', [TicketController::class, 'index'])->name('tickets.index');
Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');
Route::post('/tickets/{id}/reply', [TicketController::class, 'reply'])->name('tickets.reply');

// ==========================================
// HIDDEN ADMIN PANEL (Password Protected)
// ==========================================
Route::prefix('admin')->name('admin.')->group(function () {
    // 3-Step High Security Admin Auth Routes
    Route::get('/login', [AdminController::class, 'login'])->name('login');
    Route::post('/login', [AdminController::class, 'doLogin'])->name('doLogin');
    Route::get('/login/step2', [AdminController::class, 'showStep2'])->name('login.step2');
    Route::post('/login/step2', [AdminController::class, 'doStep2'])->name('login.step2.post');
    Route::get('/login/step3', [AdminController::class, 'showStep3'])->name('login.step3');
    Route::post('/login/step3', [AdminController::class, 'doStep3'])->name('login.step3.post');
    Route::get('/logout', [AdminController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    
    // Recharge Approvals Desk
    Route::get('/recharges', [AdminController::class, 'recharges'])->name('recharges.index');
    Route::post('/recharges/{id}/approve', [AdminController::class, 'approveRecharge'])->name('recharges.approve');
    Route::post('/recharges/{id}/reject', [AdminController::class, 'rejectRecharge'])->name('recharges.reject');

    // Crypto Wallets Config
    Route::get('/wallets', [AdminController::class, 'wallets'])->name('wallets.index');
    Route::post('/wallets', [AdminController::class, 'updateWallets'])->name('wallets.update');

    // Cards Inventory & Edit
    Route::get('/cards', [AdminController::class, 'cards'])->name('cards.index');
    Route::get('/cards/create', [AdminController::class, 'createCard'])->name('cards.create');
    Route::post('/cards', [AdminController::class, 'storeCard'])->name('cards.store');
    Route::get('/cards/{id}/edit', [AdminController::class, 'editCard'])->name('cards.edit');
    Route::post('/cards/{id}/update', [AdminController::class, 'updateCard'])->name('cards.update');
    Route::get('/cards/bulk', [AdminController::class, 'bulkImport'])->name('cards.bulk');
    Route::post('/cards/bulk', [AdminController::class, 'storeBulkImport'])->name('cards.bulkStore');
    Route::post('/cards/{id}/delete', [AdminController::class, 'deleteCard'])->name('cards.delete');
    Route::post('/cards/clear-sold', [AdminController::class, 'clearSoldCards'])->name('cards.clearSold');
    Route::post('/cards/clear-all', [AdminController::class, 'clearAllCards'])->name('cards.clearAll');

    // Orders
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders.index');
    Route::post('/orders/clear-all', [AdminController::class, 'clearAllOrders'])->name('orders.clearAll');

    // Recharge Approvals Desk
    Route::get('/recharges', [AdminController::class, 'recharges'])->name('recharges.index');
    Route::post('/recharges/{id}/approve', [AdminController::class, 'approveRecharge'])->name('recharges.approve');
    Route::post('/recharges/{id}/reject', [AdminController::class, 'rejectRecharge'])->name('recharges.reject');
    Route::post('/recharges/clear-all', [AdminController::class, 'clearAllRecharges'])->name('recharges.clearAll');

    // User & Profile Management Control Suite
    Route::get('/users', [AdminController::class, 'users'])->name('users.index');
    Route::post('/users', [AdminController::class, 'storeUser'])->name('users.store');
    Route::post('/users/{id}/update', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{id}/toggle-suspend', [AdminController::class, 'toggleSuspendUser'])->name('users.toggleSuspend');
    Route::post('/users/{id}/zero-balance', [AdminController::class, 'zeroUserBalance'])->name('users.zeroBalance');
    Route::post('/users/{id}/delete', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::post('/users/balance', [AdminController::class, 'updateBalance'])->name('users.updateBalance');
    Route::post('/users/clear-all', [AdminController::class, 'clearAllUsers'])->name('users.clearAll');

    // Wholesale Packs (CRUD)
    Route::get('/wholesale', [AdminController::class, 'wholesale'])->name('wholesale.index');
    Route::post('/wholesale', [AdminController::class, 'storeWholesale'])->name('wholesale.store');
    Route::post('/wholesale/{id}/update', [AdminController::class, 'updateWholesale'])->name('wholesale.update');
    Route::post('/wholesale/{id}/delete', [AdminController::class, 'deleteWholesale'])->name('wholesale.delete');
    Route::post('/wholesale/clear-all', [AdminController::class, 'clearAllWholesale'])->name('wholesale.clearAll');

    // News & Announcements (CRUD)
    Route::get('/news', [AdminController::class, 'news'])->name('news.index');
    Route::post('/news', [AdminController::class, 'storeNews'])->name('news.store');
    Route::post('/news/{id}/update', [AdminController::class, 'updateNews'])->name('news.update');
    Route::post('/news/{id}/delete', [AdminController::class, 'deleteNews'])->name('news.delete');
    Route::post('/news/clear-all', [AdminController::class, 'clearAllNews'])->name('news.clearAll');

    // Support Desk
    Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets.index');
    Route::get('/tickets/{id}', [AdminController::class, 'showTicket'])->name('tickets.show');
    Route::post('/tickets/{id}/reply', [AdminController::class, 'replyTicket'])->name('tickets.reply');
    Route::post('/tickets/clear-all', [AdminController::class, 'clearAllTickets'])->name('tickets.clearAll');

    // Wallets & Options Reset
    Route::post('/wallets/reset-default', [AdminController::class, 'resetDefaultWallets'])->name('wallets.resetDefault');
});
