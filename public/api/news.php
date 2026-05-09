<?php
/**
 * GET /api/news.php?q=&page=&per_page=
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/NewsRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$q = Validator::cleanString((string) ($_GET['q'] ?? ''), 120);
$pg = pagination_params();

$repo = new NewsRepository();
$result = $repo->listFiltered($q, $pg['per_page'], $pg['offset']);

Response::json([
    'ok' => true,
    'data' => $result['items'],
    'meta' => pagination_meta($result['total'], $pg['page'], $pg['per_page']),
]);
