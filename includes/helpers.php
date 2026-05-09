<?php

declare(strict_types=1);

/**
 * Read JSON body for POST APIs (Content-Type: application/json).
 *
 * @return array<string,mixed>
 */
function read_json_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw === '') {
        return [];
    }
    try {
        $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        return is_array($data) ? $data : [];
    } catch (JsonException) {
        return [];
    }
}

/**
 * Pagination: page starts at 1; per_page capped by config.
 *
 * @return array{page:int,per_page:int,offset:int}
 */
function pagination_params(): array
{
    $cfg = require dirname(__DIR__) . '/config/database.php';
    $app = $cfg['app'];
    $max = (int) ($app['MAX_PER_PAGE'] ?? 50);

    $page = max(1, (int) ($_GET['page'] ?? 1));
    $per = max(1, min($max, (int) ($_GET['per_page'] ?? 10)));
    $offset = ($page - 1) * $per;

    return ['page' => $page, 'per_page' => $per, 'offset' => $offset];
}

/**
 * @param int $total Total rows (unfiltered count for current query).
 */
function pagination_meta(int $total, int $page, int $perPage): array
{
    $pages = max(1, (int) ceil($total / $perPage));
    return [
        'page' => $page,
        'per_page' => $perPage,
        'total' => $total,
        'total_pages' => $pages,
    ];
}

/**
 * Escape LIKE wildcards so user input cannot broaden the match unexpectedly.
 */
function escape_like(string $value): string
{
    return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
}
