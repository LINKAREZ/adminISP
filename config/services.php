<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as APIs, mail servers, etc. This file provides a sane default location
    | for this type of information, allowing packages to have a conventional
    | place to find your various credentials.
    |
    */

    'dni' => [
        'apisperu' => [
            'api_key' => env('APISPERU_API_KEY', ''),
            'url' => env('APISPERU_DNI_URL', rtrim(env('APISPERU_API_URL', 'https://dniruc.apisperu.com/api/v1'), '/') . '/dni'),
            'timeout' => env('APISPERU_TIMEOUT', 15),
            'use_query_token' => env('APISPERU_USE_QUERY_TOKEN', true),
        ],
    ],

    'ruc' => [
        'apisperu' => [
            'api_key' => env('APISPERU_API_KEY', ''),
            'url' => env('APISPERU_RUC_URL', rtrim(env('APISPERU_API_URL', 'https://dniruc.apisperu.com/api/v1'), '/') . '/ruc'),
            'timeout' => env('APISPERU_TIMEOUT', 15),
            'use_query_token' => env('APISPERU_USE_QUERY_TOKEN', true),
        ],
    ],

    'comprobantes' => [
        'empresa' => [
            'ruc' => env('EMPRESA_RUC', ''),
            'direccion' => env('EMPRESA_DIRECCION', ''),
            'telefono' => env('EMPRESA_TELEFONO', ''),
            'email' => env('EMPRESA_EMAIL', ''),
        ],
    ],

    'whatsapp' => [
        'api_url' => env('WHATSAPP_API_URL', ''),
        'api_token' => env('WHATSAPP_API_TOKEN', ''),
        'phone_number_id' => env('WHATSAPP_PHONE_NUMBER_ID', ''),
    ],

    'google' => [
        'maps_api_key' => env('GOOGLE_MAPS_API_KEY', ''),
    ],

    /*
     * Proveedor del mapa: 'leaflet' (por defecto), 'maplibre' (open source) o 'google' (requiere GOOGLE_MAPS_API_KEY).
     */
    'map_provider' => env('MAP_PROVIDER', 'leaflet'),

];
