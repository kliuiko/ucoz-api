<?php

return [
    // get your credential http://uapi.ucoz.com
    'website' => env('UCOZ_WEBSITE', null),
    'cache_ttl' => env('UCOZ_CACHE_TTL', 60 * 5), //seconds
    'oauth' => [
        'oauth_consumer_key' => env('UCOZ_CONSUMER_KEY', null),
        'oauth_consumer_secret' => env('UCOZ_SECRET', null),
        'oauth_token' => env('UCOZ_TOKEN', null),
        'oauth_token_secret' => env('UCOZ_TOKEN_SECRET', null),
    ]
];
