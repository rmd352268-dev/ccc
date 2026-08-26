<?php

namespace App\Http\Controllers;

use App\Models\WholesalePack;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;

class WholesaleController extends Controller
{
    public function index()
    {
        if (session('user_logged_in') !== true) {
            return redirect()->route('login');
        }

        // Only display available packs that have NOT been purchased
        $packs = WholesalePack::where('status', 'available')->latest()->get();
        return view('wholesale.index', compact('packs'));
    }

    public function buyPack(Request $request, $id)
    {
        $userId = session('user_id');
        $username = session('user_username');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please log in to purchase wholesale packs.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User account record not found.');
        }

        $pack = WholesalePack::findOrFail($id);

        // Verify pack is still available
        if ($pack->status !== 'available') {
            return back()->with('error', 'This wholesale pack has already been sold and is no longer available.');
        }

        $userBalance = (float)$user->balance;

        if ($userBalance < $pack->price) {
            return back()->with('error', "Insufficient balance! Your balance is $" . number_format($userBalance, 2) . ". Pack price is $" . number_format($pack->price, 2) . ". Please add funds to proceed.");
        }

        // 1. Deduct user balance from database model & update session
        $user->balance = max(0, $userBalance - $pack->price);
        $user->save();
        session()->put('user_balance', (float)$user->balance);

        // 2. Mark pack as SOLD and assign buyer so it disappears immediately from all user views
        $pack->status = 'sold';
        $pack->buyer_username = $user->username;
        $pack->save();

        // 3. Create Order strictly for this buyer
        $order = Order::create([
            'order_number' => 'PACK-' . strtoupper(bin2hex(random_bytes(4))),
            'user_id' => $user->id,
            'username' => $user->username,
            'total_amount' => $pack->price,
            'item_count' => $pack->card_count,
            'status' => 'completed',
        ]);

        $lines = [];
        if (!empty($pack->cards_data)) {
            $lines = array_filter(preg_split('/\r\n|\r|\n/', trim($pack->cards_data)), fn($l) => !empty(trim($l)));
            $lines = array_values($lines);
        }

        $countToDeliver = !empty($lines) ? count($lines) : $pack->card_count;
        $packPricePerItem = round($pack->price / max(1, $countToDeliver), 2);

        // Update item count on order if custom file had more/specific lines
        if ($order->item_count !== $countToDeliver) {
            $order->item_count = $countToDeliver;
            $order->save();
        }

        for ($i = 0; $i < $countToDeliver; $i++) {
            if (isset($lines[$i])) {
                $line = trim($lines[$i]);
                $delimiter = strpos($line, '|') !== false ? '|' : (strpos($line, ';') !== false ? ';' : ',');
                $p = array_map('trim', explode($delimiter, $line));

                $cardNum = preg_replace('/\D/', '', $p[0] ?? '');
                $bin = substr($cardNum, 0, 6);
                $exp = $p[1] ?? '12/28';
                $cvv = preg_replace('/\D/', '', $p[2] ?? '000');
                $holder = $p[3] ?? ('Wholesale Customer #' . ($i + 1));
                $addr = $p[4] ?? 'Main St';
                $city = $p[5] ?? 'City';
                $state = $p[6] ?? 'ST';
                $zip = $p[7] ?? '10001';
                $phone = $p[8] ?? '+1 555-0100';
                $email = $p[9] ?? "pack_item" . ($i + 1) . "@securemail.com";

                OrderItem::create([
                    'order_id' => $order->id,
                    'price' => $packPricePerItem,
                    'card_details' => [
                        'bin' => $bin,
                        'card_number' => $cardNum,
                        'exp_date' => $exp,
                        'cvv' => $cvv,
                        'holder_name' => $holder,
                        'brand' => str_starts_with($cardNum, '4') ? 'VISA' : (preg_match('/^(5[1-5]|2[2-7])/', $cardNum) ? 'MASTERCARD' : 'VISA'),
                        'type' => $pack->type ?? 'DEBIT',
                        'bank' => 'BANK OF WHOLESALE',
                        'country_code' => 'US',
                        'country_name' => $pack->country ?? 'United States',
                        'address' => $addr,
                        'city' => $city,
                        'state' => $state,
                        'zip' => $zip,
                        'phone' => $phone,
                        'email' => $email,
                    ],
                ]);
            } else {
                $bin = '416598';
                $num = $bin . str_pad(mt_rand(100000000, 999999999), 10, '0', STR_PAD_LEFT);
                OrderItem::create([
                    'order_id' => $order->id,
                    'price' => $packPricePerItem,
                    'card_details' => [
                        'bin' => $bin,
                        'card_number' => $num,
                        'exp_date' => '09/28',
                        'cvv' => (string)mt_rand(100, 999),
                        'holder_name' => 'Wholesale Card #' . ($i + 1),
                        'brand' => 'VISA',
                        'type' => $pack->type ?? 'DEBIT',
                        'bank' => 'REVOLUT LTD',
                        'country_code' => 'GB',
                        'country_name' => $pack->country ?? 'United Kingdom',
                        'address' => (10 + $i) . ' Commercial St',
                        'city' => 'London',
                        'state' => 'London',
                        'zip' => 'E1 6BG',
                        'phone' => '+44 79' . mt_rand(1000000, 9999999),
                        'email' => 'pack_item' . ($i + 1) . '@securemail.com',
                    ],
                ]);
            }
        }

        return redirect()->route('orders.show', $order->id)->with('success', "Wholesale pack '{$pack->title}' purchased successfully! The pack has been allocated to your account and removed from the marketplace.");
    }
}
