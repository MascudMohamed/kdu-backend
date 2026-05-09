<?php

declare(strict_types=1);

/**
 * Thin PDO wrapper — one connection per request.
 */
final class Database
{
    private static ?PDO $pdo = null;

    public static function pdo(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $cfg = require dirname(__DIR__) . '/config/database.php';
        self::$pdo = new PDO($cfg['dsn'], $cfg['user'], $cfg['pass'], $cfg['options']);
        return self::$pdo;
    }
}
