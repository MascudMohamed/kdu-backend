<?php
/**
 * Bootstrap: autoload paths, error handling, CORS, JSON helpers.
 * Every public/api/*.php includes this first.
 */

declare(strict_types=1);

$root = dirname(__DIR__);

require_once $root . '/includes/helpers.php';
require_once $root . '/includes/Response.php';
require_once $root . '/includes/Validator.php';
require_once $root . '/includes/Database.php';

$dbConfig = require $root . '/config/database.php';
/** @var array<string,mixed> $app */
$app = $dbConfig['app'];

// Never display PHP errors as HTML in the API response body.
ini_set('display_errors', '0');
error_reporting(E_ALL);

set_exception_handler(static function (Throwable $e) use ($app): void {
    $debug = !empty($app['APP_DEBUG']);
    // Log server-side in production (file or syslog); keep client message generic.
    error_log('[KDU API] ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine());
    Response::json([
        'ok' => false,
        'error' => $debug ? $e->getMessage() : 'Server error',
    ], 500);
});

// CORS: only allow listed origins (stops random websites from calling your API from browsers).
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed = $app['CORS_ORIGINS'] ?? [];
if (is_string($allowed)) {
    $allowed = array_filter(array_map('trim', explode(',', $allowed)));
}
if ($origin !== '' && is_array($allowed) && in_array($origin, $allowed, true)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Vary: Origin');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    http_response_code(204);
    exit;
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
