<?php

declare(strict_types=1);

$localConfig = __DIR__ . '/database.local.php';

if (is_file($localConfig)) {
    return require $localConfig;
}

return [
    'host' => getenv('DB_HOST') ?: 'YOUR_DB_HOST',
    'port' => getenv('DB_PORT') ?: '3306',
    'name' => getenv('DB_NAME') ?: 'YOUR_DB_NAME',
    'user' => getenv('DB_USER') ?: 'YOUR_DB_USER',
    'pass' => getenv('DB_PASS') ?: 'YOUR_DB_PASSWORD',
    'charset' => 'utf8mb4',
];
