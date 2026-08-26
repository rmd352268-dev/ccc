<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function index()
    {
        $userId = session('user_id');
        $username = session('user_username');

        // Strictly filter orders by the active user only - No cross-user leaks!
        if (!$userId && !$username) {
            return redirect()->route('login')->with('error', 'Please log in to view your orders.');
        }

        $orders = Order::with('items')
            ->where(function ($q) use ($userId, $username) {
                if ($userId) $q->where('user_id', $userId);
                if ($username) $q->orWhere('username', $username);
            })
            ->latest()
            ->paginate(15);

        return view('orders.index', compact('orders'));
    }

    public function show($id)
    {
        $userId = session('user_id');
        $username = session('user_username');

        if (!$userId && !$username) {
            return redirect()->route('login')->with('error', 'Please log in to view order details.');
        }

        $order = Order::with('items')
            ->where(function ($q) use ($userId, $username) {
                if ($userId) $q->where('user_id', $userId);
                if ($username) $q->orWhere('username', $username);
            })
            ->findOrFail($id);

        return view('orders.show', compact('order'));
    }

    public function downloadTxt($id)
    {
        $userId = session('user_id');
        $username = session('user_username');

        if (!$userId && !$username) {
            return redirect()->route('login')->with('error', 'Please log in to download order file.');
        }

        $order = Order::with('items')
            ->where(function ($q) use ($userId, $username) {
                if ($userId) $q->where('user_id', $userId);
                if ($username) $q->orWhere('username', $username);
            })
            ->findOrFail($id);

        $filename = "order_{$order->order_number}.txt";

        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($order) {
            echo "========================================\n";
            echo "ORDER: {$order->order_number}\n";
            echo "DATE: {$order->created_at}\n";
            echo "TOTAL: \${$order->total_amount} ({$order->item_count} items)\n";
            echo "========================================\n\n";

            foreach ($order->items as $item) {
                $d = $item->card_details;
                echo "CARD: " . ($d['card_number'] ?? '') . "|" . ($d['exp_date'] ?? '') . "|" . ($d['cvv'] ?? '') . "\n";
                echo "NAME: " . ($d['holder_name'] ?? '') . "\n";
                echo "ADDRESS: " . ($d['address'] ?? '') . ", " . ($d['city'] ?? '') . ", " . ($d['state'] ?? '') . " " . ($d['zip'] ?? '') . "\n";
                echo "COUNTRY: " . ($d['country_name'] ?? '') . " (" . ($d['country_code'] ?? '') . ")\n";
                echo "PHONE: " . ($d['phone'] ?? '') . "\n";
                echo "EMAIL: " . ($d['email'] ?? '') . "\n";
                if (!empty($d['email_password'])) echo "EMAIL PASS: " . $d['email_password'] . "\n";
                if (!empty($d['user_agent'])) echo "USER AGENT: " . $d['user_agent'] . "\n";
                echo "BANK: " . ($d['bank'] ?? '') . "\n";
                echo "----------------------------------------\n";
            }
        }, 200, $headers);
    }

    public function downloadRawTxt($id)
    {
        $userId = session('user_id');
        $username = session('user_username');

        if (!$userId && !$username) {
            return redirect()->route('login')->with('error', 'Please log in to download order file.');
        }

        $order = Order::with('items')
            ->where(function ($q) use ($userId, $username) {
                if ($userId) $q->where('user_id', $userId);
                if ($username) $q->orWhere('username', $username);
            })
            ->findOrFail($id);

        $filename = "cards_{$order->order_number}_raw.txt";

        $headers = [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        return response()->stream(function () use ($order) {
            foreach ($order->items as $item) {
                $d = $item->card_details;
                $line = ($d['card_number'] ?? '') . '|' . 
                        ($d['exp_date'] ?? '') . '|' . 
                        ($d['cvv'] ?? '') . '|' . 
                        ($d['holder_name'] ?? '') . '|' . 
                        ($d['address'] ?? '') . '|' . 
                        ($d['city'] ?? '') . '|' . 
                        ($d['state'] ?? '') . '|' . 
                        ($d['zip'] ?? '') . '|' . 
                        ($d['country_code'] ?? '') . '|' . 
                        ($d['phone'] ?? '') . '|' . 
                        ($d['email'] ?? '');
                echo trim($line, '|') . "\n";
            }
        }, 200, $headers);
    }

    public function refund(Request $request, $orderId, $itemId)
    {
        $userId = session('user_id');
        $username = session('user_username');

        $order = Order::where(function ($q) use ($userId, $username) {
            if ($userId) $q->where('user_id', $userId);
            if ($username) $q->orWhere('username', $username);
        })->findOrFail($orderId);

        $item = OrderItem::where('order_id', $order->id)->where('id', $itemId)->firstOrFail();

        // Add back balance to database user
        $user = $userId ? \App\Models\User::find($userId) : \App\Models\User::where('username', $username)->first();
        if ($user) {
            $user->balance += $item->price;
            $user->save();
            session()->put('user_balance', (float)$user->balance);
        }

        // Mark item refunded
        $details = $item->card_details;
        $details['refunded'] = true;
        $item->card_details = $details;
        $item->save();

        return back()->with('success', "Card refunded successfully! $" . number_format($item->price, 2) . " returned to your balance.");
    }
}
