<?php
/**
 * Loads env and returns PDO connection options.
 */

declare(strict_types=1);

$configPath = dirname(__DIR__) . '/config/env.local.php';
if (!is_readable($configPath)) {
    $configPath = dirname(__DIR__) . '/config/env.example.php';
}

/** @var array<string,mixed> $config */
$config = require $configPath;

return [
    'dsn' => sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=%s',
        $config['DB_HOST'],
        (int) $config['DB_PORT'],
        $config['DB_NAME'],
        $config['DB_CHARSET']
    ),
    'user' => (string) $config['DB_USER'],
    'pass' => (string) $config['DB_PASS'],
    'options' => [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false, // real prepared statements on MySQL
    ],
    'app' => $config,
];
