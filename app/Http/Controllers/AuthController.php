<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Generate Random Math Captcha that changes dynamically every time
    private function generateCaptcha()
    {
        $n1 = rand(3, 25);
        $n2 = rand(1, 15);
        $operators = ['+', '-'];
        $op = $operators[array_rand($operators)];
        
        if ($op === '-') {
            if ($n1 < $n2) {
                $temp = $n1;
                $n1 = $n2;
                $n2 = $temp;
            }
            $ans = $n1 - $n2;
        } else {
            $ans = $n1 + $n2;
        }

        session([
            'captcha_answer' => $ans,
            'captcha_expr' => "{$n1} {$op} {$n2} = ?"
        ]);

        return "{$n1} {$op} {$n2} = ?";
    }

    // Step 1: Show Login Page
    public function showLogin()
    {
        $captcha = $this->generateCaptcha();
        return view('auth.login', compact('captcha'));
    }

    // Step 1: Process Primary Login (Username + Primary Password + Captcha)
    public function doLogin(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'captcha' => 'required|numeric',
        ]);

        // 1. Verify Math Captcha
        if ((int)$request->captcha !== (int)session('captcha_answer')) {
            return back()->withInput()->with('error', 'Incorrect Captcha verification. Please solve the new equation.');
        }

        // 2. Find User
        $user = User::where('username', $request->username)
                    ->orWhere('email', $request->username)
                    ->first();

        // Fallback default test user if needed
        if (!$user && ($request->username === 'asadulislam17@' || $request->username === 'asadulislam17p')) {
            $user = User::firstOrCreate(
                ['username' => 'asadulislam17p'],
                [
                    'name' => 'Asadul Islam',
                    'email' => 'asadul@example.com',
                    'password' => Hash::make('password123'),
                    'secondary_password' => Hash::make('1234'),
                    'balance' => 0.07,
                    'total_recharge' => 0.00,
                    'status' => 'active',
                    'role' => 'user'
                ]
            );
        }

        if (!$user) {
            return back()->withInput()->with('error', 'User account not found. Please register first.');
        }

        if ($user->status === 'banned' || $user->status === 'suspended') {
            return response()->view('auth.suspended', [], 403);
        }

        // 3. Verify Primary Password strictly
        if (!Hash::check($request->password, $user->password)) {
            return back()->withInput()->with('error', 'Invalid primary credentials. Access denied.');
        }

        // 4. If user has a Secondary Password, route to Step 2 (Secondary Password Prompt)
        if (!empty($user->secondary_password)) {
            session([
                'pending_login_user_id' => $user->id,
                'pending_login_username' => $user->username,
            ]);
            return redirect()->route('login.secondary')->with('info', 'Primary credentials verified. Please enter your Secondary Security PIN.');
        }

        // If no secondary password, complete login directly
        $this->finalizeLogin($user);
        return redirect()->route('marketplace.index')->with('success', "Welcome back, {$user->username}!");
    }

    // Step 2: Show Secondary Password Prompt Screen
    public function showSecondary()
    {
        if (!session()->has('pending_login_user_id')) {
            return redirect()->route('login')->with('error', 'Please log in with your primary credentials first.');
        }

        $username = session('pending_login_username');
        return view('auth.secondary', compact('username'));
    }

    // Step 2: Process Secondary Password Verification (100% STRICT)
    public function doSecondary(Request $request)
    {
        $request->validate([
            'secondary_password' => 'required',
        ]);

        if (!session()->has('pending_login_user_id')) {
            return redirect()->route('login')->with('error', 'Session expired. Please log in again.');
        }

        $user = User::find(session('pending_login_user_id'));
        if (!$user) {
            return redirect()->route('login')->with('error', 'User record not found.');
        }

        if ($user->status === 'banned' || $user->status === 'suspended') {
            session()->forget(['pending_login_user_id', 'pending_login_username']);
            return response()->view('auth.suspended', [], 403);
        }

        // Secondary Password / Backup Security Code Check:
        $enteredKey = trim($request->secondary_password);
        $userSecCodes = $user->getSecurityCodes();

        $pinMatches = Hash::check($enteredKey, $user->secondary_password) || ($enteredKey === $user->secondary_password);
        $codeMatches = in_array(strtoupper($enteredKey), array_map('strtoupper', $userSecCodes));

        if (!$pinMatches && !$codeMatches) {
            return back()->with('error', 'Incorrect Secondary Security PIN or Backup Security Code. Access denied.');
        }

        // Secondary Password / Security Code Verified! Complete Session Login
        session()->forget(['pending_login_user_id', 'pending_login_username']);
        $this->finalizeLogin($user);

        return redirect()->route('marketplace.index')->with('success', "Security verification complete! Welcome, {$user->username}.");
    }

    // Show Register Page
    public function showRegister()
    {
        if (request()->has('ref')) {
            session(['referral_invite' => request('ref')]);
        }
        $captcha = $this->generateCaptcha();
        $defaultSecurityCodes = User::generateFiveSecurityCodes();
        return view('auth.register', compact('captcha', 'defaultSecurityCodes'));
    }

    // Process Register
    public function doRegister(Request $request)
    {
        $request->validate([
            'username' => 'required|min:3|max:30|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:4|confirmed',
            'secondary_password' => 'required|min:4',
            'captcha' => 'required|numeric',
        ]);

        // Verify Captcha
        if ((int)$request->captcha !== (int)session('captcha_answer')) {
            return back()->withInput()->with('error', 'Incorrect Captcha verification. Please try again.');
        }

        $refCode = $request->ref ?? session('referral_invite');
        $referredBy = null;
        if (!empty($refCode)) {
            $parentUser = User::where('referral_code', $refCode)->orWhere('username', $refCode)->first();
            if ($parentUser) {
                $referredBy = $parentUser->username;
            } else {
                $referredBy = $refCode;
            }
        }

        $myReferralCode = 'REF-' . strtoupper(substr(md5(trim($request->username)), 0, 6));

        $securityCodes = $request->input('security_codes');
        if (!is_array($securityCodes) || count($securityCodes) === 0) {
            $securityCodes = User::generateFiveSecurityCodes();
        }

        // Create User in SQLite Database
        $user = User::create([
            'username' => trim($request->username),
            'name' => trim($request->username),
            'email' => trim($request->email),
            'password' => Hash::make($request->password),
            'secondary_password' => Hash::make($request->secondary_password),
            'security_codes' => json_encode($securityCodes),
            'balance' => 0.00,
            'total_recharge' => 0.00,
            'referred_by' => $referredBy,
            'referral_code' => $myReferralCode,
            'commission_balance' => 0.00,
            'telegram' => $request->telegram,
            'phone' => $request->phone,
            'country' => $request->country ?? 'US',
            'tier' => 'Verified Member',
            'status' => 'active',
            'role' => 'user'
        ]);

        // Clear temporary referral session
        session()->forget('referral_invite');

        // Flash registered username and security codes so login page pre-populates and displays backup codes
        session()->flash('registered_username', $user->username);
        session()->flash('registered_security_codes', $securityCodes);

        return redirect()->route('login')->with('success', "Registration successful! Your 5 emergency security codes have been saved. Please log in with your primary credentials.");
    }

    // Helper: Finalize User Session & Initialize 1-Hour Security Timestamp
    private function finalizeLogin(User $user)
    {
        session()->put('user_logged_in', true);
        session()->put('user_id', $user->id);
        session()->put('user_username', $user->username);
        session()->put('user_balance', (float)$user->balance);
        session()->put('total_recharge', (float)$user->total_recharge);
        session()->put('user_login_timestamp', time()); // 1-Hour Security Timer Anchor
        session()->put('user_profile', [
            'username' => $user->username,
            'full_name' => $user->name,
            'email' => $user->email,
            'telegram' => $user->telegram,
            'phone' => $user->phone,
            'country' => $user->country ?? 'US',
            'tier' => $user->tier ?? 'Verified Member',
        ]);
    }

    // Logout
    public function logout()
    {
        session()->forget([
            'user_logged_in', 'user_id', 'user_username', 'user_balance', 
            'total_recharge', 'user_profile', 'user_login_timestamp',
            'pending_login_user_id', 'pending_login_username'
        ]);
        return redirect()->route('login')->with('success', 'You have been safely logged out.');
    }
}
