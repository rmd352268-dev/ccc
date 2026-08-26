<?php

namespace App\Http\Controllers;

use App\Models\Deposit;
use App\Models\CryptoSetting;
use App\Services\TelegramNotificationService;
use Illuminate\Http\Request;

class FundController extends Controller
{
    public function index()
    {
        $settings = CryptoSetting::getSettings();
        $username = session('user_username');

        // Strictly isolate deposits by the active logged-in user - No cross-user leakage!
        $myRecharges = !empty($username) 
            ? Deposit::where('username', $username)->latest()->take(20)->get() 
            : collect();

        return view('funds.index', compact('settings', 'myRecharges'));
    }

    public function submitRecharge(Request $request)
    {
        $username = session('user_username');
        if (!$username) {
            return redirect()->route('login')->with('error', 'Please log in to submit a recharge request.');
        }

        $request->validate([
            'currency' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $settings = CryptoSetting::getSettings();
        $currency = $request->currency;
        $address = $settings->usdt_address;
        if (str_contains(strtolower($currency), 'btc') || str_contains(strtolower($currency), 'bitcoin')) {
            $address = $settings->btc_address;
        } elseif (str_contains(strtolower($currency), 'ltc') || str_contains(strtolower($currency), 'litecoin')) {
            $address = $settings->ltc_address;
        }

        $deposit = Deposit::create([
            'username' => $username,
            'trx_id' => 'DEP-' . strtoupper(bin2hex(random_bytes(5))),
            'currency' => $currency,
            'amount' => (float)$request->amount,
            'txid' => !empty($request->txid) ? trim($request->txid) : 'DIRECT_DEPOSIT',
            'address' => $address,
            'status' => 'pending',
        ]);

        // Send Instant Telegram Bot Alert to Admin with 1-Click Approve Button
        TelegramNotificationService::sendDepositAlert($deposit);

        return back()->with('success', "Recharge request for \${$request->amount} ({$currency}) submitted successfully! Your deposit is pending Admin approval and will be credited to your balance.");
    }

    public function telegramApproveDeposit($id, $secret)
    {
        $result = TelegramNotificationService::processApproval($id, $secret);
        return view('admin.telegram_action_result', [
            'success' => $result['success'],
            'message' => $result['message'],
            'deposit' => $result['deposit'] ?? null,
        ]);
    }

    public function telegramRejectDeposit($id, $secret)
    {
        $result = TelegramNotificationService::processRejection($id, $secret);
        return view('admin.telegram_action_result', [
            'success' => $result['success'],
            'message' => $result['message'],
            'deposit' => $result['deposit'] ?? null,
        ]);
    }

    public function mockAdd(Request $request)
    {
        return redirect()->route('funds.index');
    }
}
