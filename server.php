<?php

use FRankenPHP\Server;

require_once 'vendor/autoload.php';

Server::create([
    'document_root' => __DIR__ . '/public',
    'router' => __DIR__ . '/public/index.php',
    'worker' => [
        'count' => 4,
        'max_requests' => 1000,
    ],
    'http' => [
        'address' => ':80',
        'host' => '0.0.0.0',
    ],
    'https' => [
        'address' => ':443',
        'host' => '0.0.0.0',
        'cert' => '/app/certs/server.crt',
        'key' => '/app/certs/server.key',
    ],
])->start();