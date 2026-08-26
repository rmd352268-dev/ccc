<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Helpers\CountryHelper;
use Illuminate\Http\Request;

class MarketplaceController extends Controller
{
    public function index(Request $request)
    {
        if (session('user_logged_in') !== true) {
            return redirect()->route('login');
        }

        $query = Card::where('status', 'available');

        // Bins Filter (Matches any 6-digit BIN or card number starting with / containing the search query)
        if ($request->filled('bins')) {
            $rawBins = $request->bins;
            // Split by whitespace, comma, newline, pipe, semicolon or dash
            $bins = preg_split('/[\s,\|\r\n;]+/', trim($rawBins));
            $bins = array_filter($bins, fn($b) => strlen(trim($b)) >= 1);

            if (!empty($bins)) {
                $query->where(function ($q) use ($bins) {
                    foreach ($bins as $bin) {
                        $cleanBin = preg_replace('/\D/', '', $bin);
                        if (!empty($cleanBin)) {
                            $q->orWhere('bin', 'like', "{$cleanBin}%")
                              ->orWhere('bin', 'like', "%{$cleanBin}%")
                              ->orWhere('card_number', 'like', "{$cleanBin}%");
                        } else {
                            $q->orWhere('bin', 'like', "%{$bin}%");
                        }
                    }
                });
            }
        }

        // Global / Single Search (if passed)
        if ($request->filled('search')) {
            $s = trim($request->search);
            $cleanS = preg_replace('/\D/', '', $s);
            $query->where(function ($q) use ($s, $cleanS) {
                if (!empty($cleanS)) {
                    $q->orWhere('bin', 'like', "{$cleanS}%")
                      ->orWhere('bin', 'like', "%{$cleanS}%")
                      ->orWhere('card_number', 'like', "{$cleanS}%");
                }
                $q->orWhere('bank', 'like', "%{$s}%")
                  ->orWhere('country_name', 'like', "%{$s}%")
                  ->orWhere('base_name', 'like', "%{$s}%");
            });
        }

        // Zips Filter
        if ($request->filled('zips')) {
            $zips = preg_split('/[\s,\|\r\n;]+/', trim($request->zips));
            $zips = array_filter($zips, fn($z) => strlen(trim($z)) >= 1);
            if (!empty($zips)) {
                $query->where(function ($q) use ($zips) {
                    foreach ($zips as $zip) {
                        $cleanZip = trim($zip);
                        $q->orWhere('zip', 'like', "{$cleanZip}%");
                    }
                });
            }
        }

        // Bank Filter
        if ($request->filled('bank') && strtolower($request->bank) !== 'all') {
            $bankQuery = trim($request->bank);
            $query->where('bank', 'like', "%{$bankQuery}%");
        }

        // Country Filter
        if ($request->filled('country') && strtolower($request->country) !== 'all') {
            $query->where('country_code', strtoupper($request->country));
        }

        // Brand
        if ($request->filled('brand') && strtolower($request->brand) !== 'all') {
            $query->where('brand', strtoupper($request->brand));
        }

        // Type
        if ($request->filled('type') && strtolower($request->type) !== 'all') {
            $query->where('type', strtoupper($request->type));
        }

        // Base Name
        if ($request->filled('base_name') && strtolower($request->base_name) !== 'all') {
            $query->where('base_name', $request->base_name);
        }

        // Toggles
        if ($request->boolean('has_address')) $query->where('has_address', true);
        if ($request->boolean('has_zip')) $query->where('has_zip', true);
        if ($request->boolean('has_user_agent')) $query->where('has_user_agent', true);
        if ($request->boolean('has_phone')) $query->where('has_phone', true);
        if ($request->boolean('has_mail')) $query->where('has_mail', true);
        if ($request->boolean('has_email_password')) $query->where('has_email_password', true);

        // Price Filter
        if ($request->filled('price_min')) {
            $query->where('price_c', '>=', (float)$request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price_c', '<=', (float)$request->price_max);
        }

        // 10 cards per page
        $cards = $query->latest('id')->paginate(10)->withQueryString();

        // Comprehensive Worldwide Countries List
        $countries = CountryHelper::getAllCountries();
        
        $brands = Card::select('brand')->distinct()->pluck('brand');
        $types = Card::select('type')->distinct()->pluck('type');
        $bases = Card::select('base_name')->distinct()->pluck('base_name');
        $banks = Card::select('bank')->distinct()->pluck('bank');

        $userId = session('user_id');
        $user = $userId ? \App\Models\User::find($userId) : null;
        $cryptoSettings = \App\Models\CryptoSetting::getSettings();
        $minDeposit = (float)($cryptoSettings->min_deposit ?? 10.00);

        $totalRecharged = $user ? (float)$user->total_recharge : (float)session('total_recharge', 0.00);
        $userBalance = $user ? (float)$user->balance : (float)session('user_balance', 0.00);

        $hasCompletedDeposit = false;
        if ($user) {
            $hasCompletedDeposit = \App\Models\Deposit::where('username', $user->username)
                ->where('status', 'completed')
                ->exists();
        }

        // If Admin disabled the vault lock, everyone gets activated automatically
        $vaultLockEnabled = (bool)($cryptoSettings->activation_enabled ?? true);
        if (!$vaultLockEnabled) {
            $isActivated = true;
        } elseif ($user) {
            // PERMANENT ACTIVATION: Once a user deposits, their account remains activated forever even if balance becomes $0.00
            $isActivated = ($user->is_activated == 1)
                        || ($totalRecharged > 0)
                        || ($userBalance >= $minDeposit)
                        || $hasCompletedDeposit
                        || ($user->role === 'admin');

            // Save permanent activation to database model
            if ($isActivated && $user->is_activated != 1) {
                $user->is_activated = 1;
                $user->save();
            }
        } else {
            $isActivated = false;
        }

        // Fetch user's latest deposit record if any
        $latestDeposit = null;
        if ($user) {
            $latestDeposit = \App\Models\Deposit::where('username', $user->username)->latest()->first();
        }

        return view('marketplace.index', compact(
            'cards', 'countries', 'brands', 'types', 'bases', 'banks', 
            'isActivated', 'minDeposit', 'latestDeposit', 'user', 'cryptoSettings'
        ));
    }

    /**
     * Real-time live status and balance API endpoint for live frontend polling
     */
    public function liveStats()
    {
        if (session('user_logged_in') !== true) {
            return response()->json(['logged_in' => false]);
        }

        $userId = session('user_id');
        $user = $userId ? \App\Models\User::find($userId) : null;
        if (!$user && session()->has('user_username')) {
            $user = \App\Models\User::where('username', session('user_username'))->first();
        }

        if (!$user) {
            return response()->json(['logged_in' => false]);
        }

        $cart = session()->get('cart', []);

        return response()->json([
            'logged_in' => true,
            'username' => $user->username,
            'balance' => (float)$user->balance,
            'formatted_balance' => '$ ' . number_format($user->balance, 2),
            'total_recharge' => (float)$user->total_recharge,
            'formatted_recharge' => '$' . number_format($user->total_recharge, 2),
            'commission_balance' => (float)$user->commission_balance,
            'cart_count' => count($cart),
            'status' => $user->status,
        ]);
    }
}
