<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default GeoIP Service
    |--------------------------------------------------------------------------
    |
    | Supported services: "ipapi"
    |
    */

    'service' => 'ipapi',

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | We disable cache because local/database cache does not support tags
    |
    */

    'cache' => false,

    /*
    |--------------------------------------------------------------------------
    | Services Configuration
    |--------------------------------------------------------------------------
    */

    'services' => [

        'ipapi' => [
            'class' => \Torann\GeoIP\Services\IPApi::class,
            'key'   => null, // free, no API key needed
        ],

    ],

];
