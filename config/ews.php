<?php

return [
    'url' => env('EWS_URL'),
    'username' => env('EWS_USERNAME'),
    'password' => env('EWS_PASSWORD'),
    'domain' => env('EWS_DOMAIN'),
    'version' => env('EWS_VERSION', 'Exchange2016'),
    'verify_ssl' => env('EWS_VERIFY_SSL', true),
];