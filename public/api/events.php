<?php
/**
 * GET /api/events.php?limit=20
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/EventRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$limit = max(1, min(100, (int) ($_GET['limit'] ?? 20)));

$repo = new EventRepository();
$items = $repo->upcoming($limit);

Response::json([
    'ok' => true,
    'data' => $items,
]);
