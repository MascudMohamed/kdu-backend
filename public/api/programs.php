<?php
/**
 * GET /api/programs.php?q=&level=&page=&per_page=
 * Returns JSON list + pagination meta.
 */

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';
require_once dirname(__DIR__, 2) . '/includes/ProgramRepository.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    Response::json(['ok' => false, 'error' => 'Method not allowed'], 405);
}

$q = Validator::cleanString((string) ($_GET['q'] ?? ''), 120);
$level = isset($_GET['level']) ? Validator::cleanString((string) $_GET['level'], 40) : '';
$pg = pagination_params();

$repo = new ProgramRepository();
$result = $repo->listFiltered($q, $level, $pg['per_page'], $pg['offset']);

Response::json([
    'ok' => true,
    'data' => $result['items'],
    'meta' => pagination_meta($result['total'], $pg['page'], $pg['per_page']),
]);
