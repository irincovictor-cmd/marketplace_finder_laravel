<?php

return [
    // Disabled by default: never launch a paid Apify run during installation or tests.
    'scan_enabled' => (bool) env('MARKETPLACE_SCAN_ENABLED', false),
    'apify_token' => env('APIFY_TOKEN', ''),
    'max_charge_usd' => 0.10, // Actor parameter; NOT a verified billing ceiling.
];
