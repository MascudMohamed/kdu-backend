<?php

declare(strict_types=1);

/**
 * Consistent JSON responses + HTTP status codes (REST-style).
 */
final class Response
{
    /**
     * @param array<string,mixed> $data
     */
    public static function json(array $data, int $status = 200): never
    {
        http_response_code($status);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        exit;
    }
}
