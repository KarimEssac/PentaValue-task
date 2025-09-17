<?php

return [

'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],
    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],
'openweather' => [
    'api_key' => env('OPENWEATHER_API_KEY'),
    'default_city' => env('OPENWEATHER_DEFAULT_CITY', 'Cairo'),
],
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
