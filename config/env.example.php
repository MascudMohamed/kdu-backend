<?php
/**
 * Copy this file to env.local.php and fill in real values.
 * env.local.php is blocked from direct HTTP access via .htaccess.
 *
 * Why: Keeps secrets out of version control and (ideally) out of the web root.
 */

declare(strict_types=1);

return [
    'APP_ENV' => 'development', // development | production
    'APP_DEBUG' => true,

    'DB_HOST' => '127.0.0.1',
    'DB_PORT' => 3306,
    'DB_NAME' => 'kdu_global',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_CHARSET' => 'utf8mb4',

    /**
     * Allowed CORS origins (no trailing slash).
     * In production, list your real frontend URL(s). Empty = same-origin only.
     */
    'CORS_ORIGINS' => [
        'http://localhost:5500',
        'http://127.0.0.1:5500',
        'http://localhost',
    ],

    /** Max items per page (pagination cap — prevents abuse). */
    'MAX_PER_PAGE' => 50,
];
