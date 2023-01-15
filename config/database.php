<?php

return [
    'driver' => getenv('DATABASE_DRIVER') ?: 'mysql',
    'connections' => [
        'mysql' => [
            'host' => getenv('MYSQL_HOST') ?: '127.0.0.1',
            'port' => getenv('MYSQL_PORT') ?: '3306',
            'user' => getenv('MYSQL_USER') ?: 'root',
            'pass' => getenv('MYSQL_PASS') ?: '',
            'data' => getenv('MYSQL_DATA') ?: 'application',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ]
    ]
];