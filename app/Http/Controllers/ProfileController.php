<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Deposit;
use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function index()
    {
        $userId = session('user_id');
        $username = session('user_username');

        if (!$userId && !$username) {
            return redirect()->route('login')->with('error', 'Please log in to view your profile.');
        }

        $user = $userId ? User::find($userId) : User::where('username', $username)->first();

        $profile = [
            'username' => $user ? $user->username : $username,
            'full_name' => $user ? ($user->name ?? $user->username) : $username,
            'email' => $user ? $user->email : 'user@example.com',
            'telegram' => $user ? ($user->telegram ?? 'Not Set') : 'Not Set',
            'jabber' => $user ? ($user->jabber ?? 'Not Set') : 'Not Set',
            'phone' => $user ? ($user->phone ?? 'Not Set') : 'Not Set',
            'country' => $user ? ($user->country ?? 'United States (US)') : 'United States (US)',
            'timezone' => $user ? ($user->timezone ?? 'America/Los_Angeles') : 'America/Los_Angeles',
            'member_since' => $user ? $user->created_at->format('Y-m-d') : date('Y-m-d'),
            'tier' => $user ? ($user->tier ?? 'Verified Member') : 'Verified Member',
        ];

        $userBalance = $user ? (float)$user->balance : 0.00;
        $totalRecharge = $user ? (float)$user->total_recharge : 0.00;

        // Total orders strictly for this user only
        $totalOrders = (!empty($userId) || !empty($username))
            ? Order::where(function ($q) use ($userId, $username) {
                if ($userId) $q->where('user_id', $userId);
                if ($username) $q->orWhere('username', $username);
            })->count()
            : 0;

        // Recent deposits strictly for this user only
        $recentDeposits = !empty($username)
            ? Deposit::where('username', $username)->latest()->take(5)->get()
            : collect();

        return view('profile.index', compact('profile', 'userBalance', 'totalRecharge', 'totalOrders', 'recentDeposits'));
    }

    public function update(Request $request)
    {
        $userId = session('user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return redirect()->route('login')->with('error', 'User record not found.');
        }

        $request->validate([
            'username' => 'nullable|string|min:3|max:50|unique:users,username,' . $user->id,
            'full_name' => 'nullable|string|max:100',
            'email' => 'nullable|email|max:100|unique:users,email,' . $user->id,
            'telegram' => 'nullable|string|max:100',
            'jabber' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'country' => 'nullable|string|max:50',
            'timezone' => 'nullable|string|max:50',
        ]);

        if ($request->filled('username')) {
            $user->username = trim($request->username);
            session()->put('user_username', $user->username);
        }

        if ($request->filled('full_name')) $user->name = trim($request->full_name);
        if ($request->filled('email')) $user->email = trim($request->email);
        if ($request->has('telegram')) $user->telegram = trim($request->telegram);
        if ($request->has('jabber')) $user->jabber = trim($request->jabber);
        if ($request->has('phone')) $user->phone = trim($request->phone);
        if ($request->has('country')) $user->country = trim($request->country);
        if ($request->has('timezone')) $user->timezone = trim($request->timezone);

        $user->save();

        session()->put('user_profile', [
            'username' => $user->username,
            'full_name' => $user->name,
            'email' => $user->email,
            'telegram' => $user->telegram,
            'jabber' => $user->jabber,
            'phone' => $user->phone,
            'country' => $user->country,
            'tier' => $user->tier ?? 'Verified Member',
        ]);

        return redirect()->route('profile.index')->with('success', 'Profile and Settings updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $userId = session('user_id');
        $user = $userId ? User::find($userId) : null;

        if (!$user) {
            return redirect()->route('login')->with('error', 'User record not found.');
        }

        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:4|confirmed',
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Current password does not match our records.');
        }

        $user->password = bcrypt($request->new_password);
        $user->save();

        return back()->with('success', 'Password updated successfully!');
    }
}
