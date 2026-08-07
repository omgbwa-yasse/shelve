<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mcp' => [
        'url' => env('MCP_SERVER_URL', 'http://localhost:3000'),
        'api_key' => env('MCP_API_KEY'),
    ],

    // Tesseract OCR integration (optional)
    'tesseract' => [
        // Full path to tesseract binary (enable OCR when set)
        // Windows example: C:\\Program Files\\Tesseract-OCR\\tesseract.exe
        'bin' => env('TESSERACT_BIN'),
        // Language(s) codes (e.g., 'fra', 'eng', or 'fra+eng') if language packs installed
        'lang' => env('TESSERACT_LANG', 'eng'),
        // Optional: path to pdftoppm (Poppler) used to rasterize PDFs for OCR
        // Windows example: C:\\Program Files\\Poppler\\bin\\pdftoppm.exe
        'pdftoppm_bin' => env('PDFTOPPM_BIN', 'pdftoppm'),
    ],

];
