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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],


    // "Continue with Google". Leave the id empty to hide the buttons.
    // Create credentials at https://console.cloud.google.com/apis/credentials (OAuth client, Web application)
    // and add <APP_URL>/auth/google/callback as an authorised redirect URI.
    'google' => [
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT_URI'),   // optional; defaults to <APP_URL>/auth/google/callback
    ],

    // Optional OCR for prescriptions. Leave empty to use manual entry only.
    // Set to a class implementing App\Services\Contracts\PrescriptionExtractor.
    'prescription_ocr' => [
        'class' => env('PRESCRIPTION_OCR_CLASS'),
    ],

];
