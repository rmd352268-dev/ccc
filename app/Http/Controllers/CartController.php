<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $cart = session()->get('cart', []);
        $cardIds = array_keys($cart);
        $cards = Card::whereIn('id', $cardIds)->get();
        $total = $cards->sum('price_c');

        return view('cart.index', compact('cards', 'total'));
    }

    public function add(Request $request, $id)
    {
        $card = Card::findOrFail($id);
        if ($card->status !== 'available') {
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Card is already sold!']);
            }
            return back()->with('error', 'Card is already sold!');
        }

        $cart = session()->get('cart', []);
        $cart[$id] = [
            'id' => $card->id,
            'bin' => $card->bin,
            'brand' => $card->brand,
            'price' => $card->price_c,
            'country' => $card->country_code,
        ];
        session()->put('cart', $cart);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart_count' => count($cart),
                'message' => "BIN {$card->bin} added to cart!",
            ]);
        }

        return back()->with('success', 'Added to cart!');
    }

    public function bulkAdd(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No cards selected!']);
        }

        $cards = Card::whereIn('id', $ids)->where('status', 'available')->get();
        $cart = session()->get('cart', []);
        foreach ($cards as $card) {
            $cart[$card->id] = [
                'id' => $card->id,
                'bin' => $card->bin,
                'brand' => $card->brand,
                'price' => $card->price_c,
                'country' => $card->country_code,
            ];
        }
        session()->put('cart', $cart);

        return response()->json([
            'success' => true,
            'cart_count' => count($cart),
            'message' => count($cards) . " cards added to cart!",
        ]);
    }

    public function remove(Request $request, $id)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
            session()->put('cart', $cart);
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'cart_count' => count($cart),
                'message' => 'Card removed from cart.',
            ]);
        }

        return back()->with('success', 'Card removed from cart.');
    }

    public function clear()
    {
        session()->forget('cart');
        return back()->with('success', 'Cart cleared.');
    }

    public function checkout(Request $request)
    {
        $userId = session('user_id');
        $username = session('user_username');

        if (!$userId) {
            return redirect()->route('login')->with('error', 'Please log in to complete your checkout.');
        }

        $user = User::find($userId);
        if (!$user) {
            return redirect()->route('login')->with('error', 'User account record not found.');
        }

        $cart = session()->get('cart', []);
        if (empty($cart)) {
            return back()->with('error', 'Your cart is empty!');
        }

        $cardIds = array_keys($cart);
        $cards = Card::whereIn('id', $cardIds)->where('status', 'available')->get();
        if ($cards->isEmpty()) {
            session()->forget('cart');
            return back()->with('error', 'The selected cards are no longer available.');
        }

        $totalAmount = (float)$cards->sum('price_c');
        $userBalance = (float)$user->balance;

        if ($userBalance < $totalAmount) {
            return back()->with('error', "Insufficient balance! Your current balance is $" . number_format($userBalance, 2) . ". Please add funds to proceed with this purchase.");
        }

        // Deduct balance from user database record
        $user->balance = max(0, $userBalance - $totalAmount);
        $user->save();

        session()->put('user_balance', (float)$user->balance);

        // Create Order scoped to this specific user
        $order = Order::create([
            'order_number' => 'ORD-' . strtoupper(bin2hex(random_bytes(4))),
            'user_id' => $user->id,
            'username' => $user->username,
            'total_amount' => $totalAmount,
            'item_count' => $cards->count(),
            'status' => 'completed',
        ]);

        foreach ($cards as $card) {
            OrderItem::create([
                'order_id' => $order->id,
                'card_id' => $card->id,
                'price' => $card->price_c,
                'card_details' => [
                    'bin' => $card->bin,
                    'card_number' => $card->card_number,
                    'exp_date' => $card->exp_date,
                    'cvv' => $card->cvv,
                    'holder_name' => $card->holder_name,
                    'brand' => $card->brand,
                    'type' => $card->type,
                    'bank' => $card->bank,
                    'country_code' => $card->country_code,
                    'country_name' => $card->country_name,
                    'address' => $card->address,
                    'city' => $card->city,
                    'state' => $card->state,
                    'zip' => $card->zip,
                    'phone' => $card->phone,
                    'email' => $card->email,
                    'user_agent' => $card->user_agent,
                    'email_password' => $card->email_password,
                    'base_name' => $card->base_name,
                    'refundable' => $card->refundable,
                ],
            ]);

            // Mark card as sold in inventory
            $card->status = 'sold';
            $card->save();
        }

        // Clear cart session
        session()->forget('cart');

        return redirect()->route('orders.show', $order->id)->with('success', "Order #{$order->order_number} completed successfully! Your card details are shown below.");
    }
}
