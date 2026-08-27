# 🔒 Complete Tor Security & Anonymity Setup Guide (Zero-Leak Setup)

This document provides instructions for keeping your web server hidden, operating exclusively via **Tor Browser** and the **.onion** network, and protecting the server's real IP and location.

---

## 1. Laravel Application Configuration (.env)

In your project root `.env` file, configure the following parameters:

```ini
# Set to true to enable Tor-only enforcement (blocks non-Tor browsers)
TOR_ONLY_ENFORCE=false

# If you have a custom onion domain, specify it here
TOR_ONION_DOMAIN=7625n5aonepn2vui2qfpnj27kyv565eq7ztwpuowa4heemu2zvy5h5ad.onion

# Allow localhost development
TOR_ALLOW_LOCALHOST=true

# Secret bypass key for emergency admin access
TOR_BYPASS_KEY=master_admin_emergency_bypass_7788

# Action when blocked browser connects: 'blank', 'fake_error', or 'tor_notice'
TOR_BLOCK_ACTION=blank
```

---

## 2. Browser Blocking & Emergency Access

1. When `TOR_ONLY_ENFORCE=true`, non-Tor browsers will receive a blank response or a generic 404 error.
2. Only **Tor Browser** connecting to the `.onion` address can access the website.
3. **Emergency Bypass:** To access from a regular browser during maintenance:
   ```
   http://your-domain.com/?tor_key=master_admin_emergency_bypass_7788
   ```

---

## 3. Server Anonymity & Zero-Leak Configuration (Linux VPS)

To prevent IP detection by network scanners:

### Step 1: Install Tor
```bash
sudo apt update && sudo apt install tor -y
```

### Step 2: Configure Hidden Service
Edit `/etc/tor/torrc`:
```text
HiddenServiceDir /var/lib/tor/hidden_service/
HiddenServicePort 80 127.0.0.1:8000
```

### Step 3: Restart Tor
```bash
sudo systemctl restart tor
sudo cat /var/lib/tor/hidden_service/hostname
```

### Step 4: Bind Web Server to 127.0.0.1 Only
Ensure Nginx or PHP is bound to `127.0.0.1` and not public interfaces `0.0.0.0`.

---

## 4. Security Headers (Zero IP Leakage Features)
- `Permissions-Policy: geolocation=(), camera=(), microphone=()`: Prevents browser location tracking.
- `Referrer-Policy: no-referrer`: Prevents leaking domain or user referral metadata.
- `X-Frame-Options: DENY`: Prevents iframe clickjacking attacks.
- `X-Content-Type-Options: nosniff`: Prevents MIME-type sniffing vulnerabilities.
- `Server Fingerprint Removal`: Suppresses PHP / Nginx version signatures.
