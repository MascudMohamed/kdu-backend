<?php
/**
 * Health check — verify PHP + optional DB connectivity.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

$out = [
    'ok' => true,
    'service' => 'kdu-backend',
    'php' => PHP_VERSION,
    'time' => gmdate('c'),
];

$configPath = dirname(__DIR__) . '/config/env.local.php';
if (!is_readable($configPath)) {
    $out['db'] = 'not_configured';
    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** @var array<string,mixed> $appCfg */
$appCfg = require $configPath;
$debug = !empty($appCfg['APP_DEBUG']);

try {
    require_once dirname(__DIR__) . '/includes/Database.php';
    $pdo = Database::pdo();
    $pdo->query('SELECT 1');
    $out['db'] = 'connected';
} catch (Throwable $e) {
    $out['ok'] = false;
    $out['db'] = 'error';
    // Local debugging only — remove detail in production (APP_DEBUG false).
    if ($debug) {
        $out['db_error'] = $e->getMessage();
        $out['hint'] = 'Start MySQL in XAMPP, create database `kdu_global` (import sql/schema.sql), and match DB_USER/DB_PASS in config/env.local.php.';
    }
}

echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
