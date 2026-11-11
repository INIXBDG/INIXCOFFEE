<?php
 
return [
    'user_id' => env('AUDIT_USER_ID'),
    'base_url' => env('AUDIT_BASE_URL'),
    'login_url' => env('AUDIT_LOGIN_URL'),
    'email' => env('AUDIT_EMAIL'),
    'password' => env('AUDIT_PASSWORD'),
    'email_selector' => env('AUDIT_EMAIL_SELECTOR'),
    'password_selector' => env('AUDIT_PASSWORD_SELECTOR'),
    'click' => env('AUDIT_CLICK'),
    'playwright_browsers_path' => env('PLAYWRIGHT_BROWSERS_PATH'),
    'playwright_search_paths' => array_values(array_filter(array_map('trim', explode(',', (string) env('AUDIT_PLAYWRIGHT_PATHS', ''))))),
];
 