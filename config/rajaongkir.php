<?php

return [
    'base_url'            => env('RAJAONGKIR_BASE_URL', 'https://rajaongkir.komerce.id/api/v1'),
    'key'                 => env('RAJAONGKIR_API_KEY'),
    'origin_district_id'  => (int) env('RAJAONGKIR_ORIGIN_DISTRICT_ID', 0),
    'default_weight'      => (int) env('RAJAONGKIR_DEFAULT_WEIGHT_GRAM', 1000),

    // kurir default untuk hit ongkir (pisahkan koma)
    'default_couriers'    => env('RAJAONGKIR_DEFAULT_COURIERS',
        'jnt,jne'
    ),

    'enabled'     => env('RAJAONGKIR_ENABLED', true),
    'ui_dev_mode' => env('RAJAONGKIR_UI_DEV_MODE', false),

    'cache_ttl' => [
        // dalam detik
        'province' => env('RAJAONGKIR_CACHE_PROVINCE', 60 * 60 * 24 * 7), // 7 hari
        'city'     => env('RAJAONGKIR_CACHE_CITY',     60 * 60 * 24 * 7),
        'district' => env('RAJAONGKIR_CACHE_DISTRICT', 60 * 60 * 24 * 7),
        'cost'     => env('RAJAONGKIR_CACHE_COST',     60 * 60),          // 60 menit
    ],
];
