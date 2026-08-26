<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Commission;
use App\Models\CryptoSetting;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    public function index()
    {
        $userId = session('user_id');
        $username = session('user_username', 'User');

        $user = $userId ? User::find($userId) : null;
        if (!$user && !empty($username)) {
            $user = User::where('username', $username)->first();
        }
        
        // Use user's saved referral code or generate stable code
        $referralCode = $user && !empty($user->referral_code) 
            ? $user->referral_code 
            : 'REF-' . strtoupper(substr(md5($username), 0, 6));

        $commissionBalance = $user ? (float)$user->commission_balance : (float)session()->get('commission_balance', 0.00);

        // Real aggregated statistics from SQLite DB
        $totalEarned = Commission::where('referrer_username', $username)->sum('commission_amount');
        $referredCount = User::where('referred_by', $username)->count();
        $referrals = User::where('referred_by', $username)->latest()->take(10)->get();
        $commissionHistory = Commission::where('referrer_username', $username)->latest()->take(20)->get();

        $cryptoSettings = CryptoSetting::getSettings();

        return view('commission.index', compact(
            'referralCode', 'commissionBalance', 'totalEarned', 
            'referredCount', 'referrals', 'commissionHistory', 'cryptoSettings'
        ));
    }

    public function transferToBalance(Request $request)
    {
        $userId = session('user_id');
        $username = session('user_username');

        $user = $userId ? User::find($userId) : null;
        if (!$user && !empty($username)) {
            $user = User::where('username', $username)->first();
        }

        $commission = $user ? (float)$user->commission_balance : (float)session()->get('commission_balance', 0.00);
        if ($commission <= 0) {
            return back()->with('error', 'No commission balance available to transfer.');
        }

        if ($user) {
            $user->balance = (float)$user->balance + $commission;
            $user->commission_balance = 0.00;
            $user->save();

            session()->put('user_balance', (float)$user->balance);
        }

        session()->put('commission_balance', 0.00);

        return back()->with('success', "Transferred $" . number_format($commission, 2) . " from Commission directly to your main Balance!");
    }
}
