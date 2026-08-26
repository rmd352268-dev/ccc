<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Tor-Only Mode Enforcement
    |--------------------------------------------------------------------------
    |
    | When enabled (true), visitors using regular browsers (Chrome, Safari,
    | Edge, Mobile browsers, etc.) will be stealth-blocked. Only requests
    | originating from the Tor Network (Tor Exit Nodes, .onion services)
    | or verified Tor Browser clients will be granted access.
    |
    */
    'tor_only_enforce' => env('TOR_ONLY_ENFORCE', false),

    /*
    |--------------------------------------------------------------------------
    | Tor .onion Address
    |--------------------------------------------------------------------------
    |
    | If your website is configured with a Tor Hidden Service (.onion domain),
    | define it here (e.g. 'xyz123abc456.onion').
    | The application will automatically broadcast the 'Onion-Location'
    | header to prompt Tor Browser users to switch to the onion address.
    |
    */
    'tor_onion_address' => env('TOR_ONION_ADDRESS', null),

    /*
    |--------------------------------------------------------------------------
    | Allow Localhost / Private Networks
    |--------------------------------------------------------------------------
    |
    | When set to true, requests from 127.0.0.1, ::1, or local subnets
    | will not be blocked, allowing easy local development and maintenance.
    |
    */
    'tor_allow_local' => env('TOR_ALLOW_LOCAL', true),

    /*
    |--------------------------------------------------------------------------
    | Emergency Bypass Key
    |--------------------------------------------------------------------------
    |
    | A secret token that allows site administrators to access the site
    | temporarily from any standard browser by adding '?bypass_tor=YOUR_KEY'
    | to the URL or providing the 'X-Tor-Bypass' HTTP header.
    |
    */
    'tor_bypass_key' => env('TOR_BYPASS_KEY', 'super_secure_emergency_bypass_key_2026'),

    /*
    |--------------------------------------------------------------------------
    | Action When Blocked
    |--------------------------------------------------------------------------
    |
    | Defines the response behavior for non-Tor clients:
    | - 'blank': Returns a completely empty 404 Not Found (stealth mode - recommended).
    | - '403': Returns HTTP 403 Forbidden.
    | - 'fake_error': Returns a generic offline / connection error message.
    | - 'tor_notice': Displays a page instructing the user to download Tor Browser.
    |
    */
    'tor_block_action' => env('TOR_BLOCK_ACTION', 'blank'),

    /*
    |--------------------------------------------------------------------------
    | Tor DNSBL Lookup Zone
    |--------------------------------------------------------------------------
    |
    | The official Tor Project DNS exit list service used for real-time
    | validation of Tor exit node IP addresses.
    |
    */
    'tor_dnsbl_zone' => env('TOR_DNSBL_ZONE', 'dnsel.torproject.org'),

    /*
    |--------------------------------------------------------------------------
    | Tor IP Cache Duration (in seconds)
    |--------------------------------------------------------------------------
    |
    | Caching Tor exit node verification results ensures lightning-fast page
    | loads without sending repeated DNS queries for every single request.
    | Default: 3600 seconds (1 hour).
    |
    */
    'tor_cache_ttl' => env('TOR_CACHE_TTL', 3600),
];
