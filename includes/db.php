<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/config.php';

/**
 * Opens (and memoises) the PDO connection. Throws on failure so callers can
 * decide whether to degrade gracefully or stop.
 */
function db_connect(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT, DB_NAME);

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_STRINGIFY_FETCHES  => false,
        PDO::ATTR_TIMEOUT            => 5,
    ]);
    $pdo->exec("SET time_zone = '+05:30'");

    return $pdo;
}

/**
 * Connection for code paths that genuinely cannot continue without a database
 * (the booking endpoints, the admin panel).
 */
function db(): PDO
{
    try {
        return db_connect();
    } catch (Throwable $e) {
        error_log('[oradent] DB connection failed: ' . $e->getMessage());
        if (DEBUG) {
            throw $e;
        }
        if (!headers_sent()) {
            http_response_code(503);
            header('Retry-After: 300');
        }
        exit('The booking system is temporarily unavailable. Please call ' . CLINIC_PHONE_DISPLAY . '.');
    }
}

/**
 * True when the database is reachable. Lets the marketing pages render normally
 * even if the database is down — only the booking widget degrades.
 */
function db_available(): bool
{
    static $ok = null;
    if ($ok !== null) {
        return $ok;
    }
    try {
        db_connect()->query('SELECT 1');
        $ok = true;
    } catch (Throwable $e) {
        error_log('[oradent] DB unavailable: ' . $e->getMessage());
        $ok = false;
    }
    return $ok;
}
