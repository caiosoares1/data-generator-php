<?php

return [
    'default' => env('DB_CONNECTION', 'pgsql'),

    'connections' => [
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '5432'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8',
            'prefix' => '',
            'prefix_indexes' => true,
            'schema' => 'public',
            'sslmode' => env('DB_SSLMODE', 'require'),
            'sslcert' => env('DB_SSLCERT'),
            'sslkey' => env('DB_SSLKEY'),
            'sslrootcert' => env('DB_SSLROOTCERT'),
            'options' => [
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_PERSISTENT => false,
            ],
        ],
    ],
];