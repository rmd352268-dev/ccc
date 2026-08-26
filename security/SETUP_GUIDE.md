# 🔒 সম্পূর্ণ টোর সিকিউরিটি ও অ্যানোনিমিটি গাইড (Tor Only & Zero-Leak Setup Guide)

এই ডকুমেন্টটিতে আপনার ওয়েবসাইটকে সাধারণ ব্রাউজার থেকে সম্পূর্ণ লুকিয়ে রাখা, শুধুমাত্র **Tor Browser** এবং **.onion** নেটওয়ার্কে চালানো এবং আপনার সার্ভারের আসল আইপি ও লোকেশন ১০০% সুরক্ষিত রাখার ধাপগুলো বিস্তারিত দেওয়া হলো।

---

## ১. লারাভেল অ্যাপ্লিকেশন কনফিগারেশন (.env)

আপনার প্রজেক্ট রুটের `.env` ফাইলে নিচের সেটিংসগুলো পাবেন:

```dotenv
# Tor-only মোড চালু করতে true দিন (সাধারণ ব্রাউজার ব্লক হবে)
TOR_ONLY_ENFORCE=true

# যদি আপনার টোর অনিয়ন ডোমেন থাকে, এখানে দিন (যেমন: xyz123456789.onion)
TOR_ONION_ADDRESS=

# লোকালহোস্টে কাজ করার সময় অ্যালাও রাখতে true (সার্ভারেও true রাখলে টোর হিডেন সার্ভিস কাজ করবে)
TOR_ALLOW_LOCAL=true

# জরুরি প্রয়োজনে সাধারণ ব্রাউজার দিয়ে অ্যাডমিন অ্যাক্সেসের সিক্রেট কী
TOR_BYPASS_KEY=super_secure_emergency_bypass_key_2026

# সাধারণ ব্রাউজার দিয়ে ঢুকলে কী অ্যাকশন দেখাবে:
# 'blank'      -> সম্পূর্ণ ফাঁকা / 404 Not Found (Stealth Mode - Best)
# '403'        -> 403 Forbidden
# 'fake_error' -> ভুয়া 404 Apache/Nginx সার্ভার এরর পেজ
# 'tor_notice' -> "Tor Browser Required" নোটিশ পেজ
TOR_BLOCK_ACTION=blank
```

---

## ২. সাধারণ ব্রাউজার ব্লক ও সিক্রেট বাইপাস করার নিয়ম

1. যখন `TOR_ONLY_ENFORCE=true` থাকবে, তখন **Google Chrome, Microsoft Edge, Safari, Opera বা মোবাইল ব্রাউজার** দিয়ে ডোমেনে ঢুকলে কোন কিছুই আসবে না (ব্ল্যাঙ্ক 404 দেখাবে)।
2. **শুধুমাত্র Tor Browser** দিয়ে ঢুকলে সম্পূর্ণ ওয়েবসাইট ওপেন হবে।
3. **জরুরি বাইপাস (Emergency Admin Bypass):** যদি কখনো সাধারণ ব্রাউজার দিয়ে আপনাকে চেক করতে হয়, তখন লিংকের শেষে সিক্রেট কী যোগ করে ব্রাউজ করুন:
   ```
   https://yourdomain.com/?bypass_tor=super_secure_emergency_bypass_key_2026
   ```
   একবার এই লিংক ওপেন করলে আপনার ওই ব্রাউজার সেশনে বাইপাস অ্যাক্টিভ হয়ে যাবে।

---

## ৩. সার্ভারের আসল IP ও লোকেশন ১০০% লুকানোর নিয়ম (Linux VPS)

আপনার সার্ভারের রিয়েল আইপি লুকানোর সবচেয়ে নিরাপদ উপায় হলো **Tor Hidden Service (.onion)** ব্যবহার করা। এতে ইন্টারনেটের কোনো হ্যাকার বা স্ক্যানার (Shodan / Censys) আপনার সার্ভারের আসল আইপি খুঁজে পাবে না।

### ধাপ ১: সার্ভারে Tor ইনস্টল করুন
```bash
sudo apt update
sudo apt install tor -y
```

### ধাপ ২: Tor কনফিগার করুন
`/etc/tor/torrc` ফাইলটি এডিট করুন:
```bash
sudo nano /etc/tor/torrc
```
নিচের লাইনগুলো ফাইলের শেষে যোগ করুন:
```nginx
HiddenServiceDir /var/lib/tor/laravel_onion_service/
HiddenServicePort 80 127.0.0.1:80
HiddenServiceVersion 3
```
সেভ করে বের হন (Ctrl+O, Enter, Ctrl+X)।

### ধাপ ৩: Tor সার্ভিস রিস্টার্ট দিন এবং অনিয়ন অ্যাড্রেস নিন
```bash
sudo systemctl restart tor
sudo cat /var/lib/tor/laravel_onion_service/hostname
```
> এটি আপনাকে একটি গোপন `.onion` ডোমেন দেবে (যেমন: `vwyxz123456789abcdef...onion`)।

### ধাপ ৪: Nginx শুধুমাত্র 127.0.0.1 (লোকালহোস্ট) এ বাইন্ড করুন
আপনার Nginx কনফিগারেশনে (`/etc/nginx/sites-available/default`) নিশ্চিত করুন:
```nginx
listen 127.0.0.1:80;
listen [::1]:80;
server_name localhost *.onion;
```
> ⚠️ **গুরুত্বপূর্ণ:** `listen 80` বা পাবলিক আইপিতে লিসেন বন্ধ রাখলে ইন্টারনেটের সাধারণ স্ক্যানার পোর্ট ৮০ বন্ধ দেখতে পাবে এবং আপনার আইপি সম্পূর্ণ অদৃশ্য থাকবে!

---

## ৪. সিকিউরিটি হেডার্স (Zero IP Leakage Features)

এই মিডলওয়্যারটি স্বয়ংক্রিয়ভাবে নিচের হেডারগুলো রেসপন্সে পাঠায়:
- **`Permissions-Policy: geolocation=(), camera=(), microphone=()`**: ব্রাউজার যাতে কোনোভাবেই ভিজিটর বা সার্ভারের জিপিএস/লোকেশন শনাক্ত করতে না পারে।
- **`Referrer-Policy: no-referrer`**: অন্য কোনো থার্ড পার্টি সাইটে ভিজিটর বা ডোমেনের তথ্য ফাঁস হবে না।
- **`X-Frame-Options: DENY`**: আইফ্রেম ও ক্লিকজ্যাকিং প্রতিরোধ।
- **`X-Powered-By & Server fingerprint removal`**: পিএইচপি বা সার্ভারের কোনো ভার্সন তথ্য প্রকাশ পাবে না।

---

## ৫. সব ফাইল তৈরি সম্পন্ন হয়েছে:
- 📁 [app/Http/Middleware/TorSecurityMiddleware.php](file:///c:/Users/hp/Desktop/ccc/app/Http/Middleware/TorSecurityMiddleware.php)
- 📁 [config/security.php](file:///c:/Users/hp/Desktop/ccc/config/security.php)
- 📁 [security/torrc](file:///c:/Users/hp/Desktop/ccc/security/torrc)
- 📁 [security/nginx-tor.conf](file:///c:/Users/hp/Desktop/ccc/security/nginx-tor.conf)
- 📁 [security/apache-tor.conf](file:///c:/Users/hp/Desktop/ccc/security/apache-tor.conf)
