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

/**
 * Why the database is not reachable, in terms someone can act on.
 *
 * Distinguishes the four things that actually go wrong on a fresh deploy —
 * missing .env, wrong password, wrong database name, schema not imported —
 * because "could not connect" alone sends people hunting in the wrong place.
 *
 * @return array{ok:bool, reason:string, detail:string, fix:string}
 */
function db_diagnosis(): array
{
    $envFile = dirname(__DIR__) . '/.env';
    $envDir  = basename(dirname($envFile));

    // A folder called .env is an easy mistake to make in File Manager, and it
    // looks identical to a working setup in a directory listing.
    if (is_dir($envFile)) {
        $inside = array_values(array_diff(scandir($envFile) ?: [], ['.', '..']));
        return [
            'ok'     => false,
            'reason' => '.env is a folder, not a file',
            'detail' => 'Found a directory at ' . $envDir . '/.env'
                      . ($inside ? ' containing ' . implode(', ', array_slice($inside, 0, 5)) . '.' : '.')
                      . ' The settings must be in a single file named .env, not inside a folder of that name.',
            'fix'    => 'Move the settings out: create one file called exactly ".env" directly in '
                      . $envDir . ' (beside index.php), paste the DB_ lines into it, then delete the '
                      . '.env folder. The name matters — .env.prod, env.txt or .env.php are not read.',
        ];
    }

    if (!is_file($envFile) || !is_readable($envFile)) {
        return [
            'ok'     => false,
            'reason' => 'No .env file',
            'detail' => 'Expected a file at ' . $envDir . '/.env on the server.',
            'fix'    => 'Create .env in the same folder as index.php (public_html), '
                      . 'copying .env.example and filling in the real database name, '
                      . 'user and password. Turn on "show hidden files" in File Manager '
                      . 'or you will not see it after creating it.',
        ];
    }

    try {
        $pdo = db_connect();
    } catch (Throwable $e) {
        $msg  = $e->getMessage();
        $code = (string) $e->getCode();

        /* Order matters: MySQL reports "no such database" as 1049, but reports
           "database exists, this user has no rights to it" as 1044 — and 1044's
           message also contains "access denied", so both database-name cases
           must be matched before the plain wrong-password case below. */
        if (str_contains($msg, '1049') || stripos($msg, 'unknown database') !== false) {
            return ['ok' => false, 'reason' => 'That database does not exist',
                    'detail' => 'The server accepted the login but has no database named "' . DB_NAME . '".',
                    'fix' => 'Check DB_NAME in .env matches the database in hPanel → Databases '
                           . 'exactly, including the u123456789_ prefix.'];
        }
        if (str_contains($msg, '1044')) {
            return ['ok' => false, 'reason' => 'This user cannot open that database',
                    'detail' => 'The login was accepted, but user "' . DB_USER . '" has no rights '
                              . 'on database "' . DB_NAME . '".',
                    'fix' => 'Either DB_NAME is the wrong database, or this user was never added to '
                           . 'it. In hPanel → Databases, check the user listed under this database '
                           . 'matches DB_USER in .env.'];
        }
        if (str_contains($msg, '1045') || stripos($msg, 'access denied') !== false) {
            return db_credentials_diagnosis($envDir);
        }
        if (str_contains($msg, '2002') || stripos($msg, 'connection refused') !== false) {
            return ['ok' => false, 'reason' => 'No database server at ' . DB_HOST,
                    'detail' => 'Nothing answered on ' . DB_HOST . ':' . DB_PORT . '.',
                    'fix' => 'On Hostinger DB_HOST is almost always "localhost". Check that line in .env.'];
        }
        return ['ok' => false, 'reason' => 'Could not connect',
                'detail' => DEBUG ? $msg : 'Error code ' . $code . '.',
                'fix' => 'Check every DB_ value in .env against hPanel → Databases.'];
    }

    try {
        $pdo->query('SELECT 1 FROM appointments LIMIT 1');
    } catch (Throwable) {
        return ['ok' => false, 'reason' => 'Database is empty — the schema was not imported',
                'detail' => 'Connected to "' . DB_NAME . '", but the appointments table is missing.',
                'fix' => 'Open phpMyAdmin for this database and import sql/schema.sql from the repository.'];
    }

    return ['ok' => true, 'reason' => 'Connected', 'detail' => '', 'fix' => ''];
}

/**
 * "Access denied" has several quite different causes, and they need different
 * fixes. This inspects the *shape* of what was actually parsed out of .env —
 * never the password itself — to say which one it is.
 *
 * @return array{ok:bool, reason:string, detail:string, fix:string}
 */
function db_credentials_diagnosis(string $envDir): array
{
    $prefix = static fn(string $v): ?string =>
        preg_match('/^(u\d{6,12})_/', $v, $m) ? $m[1] : null;

    $userPrefix = $prefix(DB_USER);
    $namePrefix = $prefix(DB_NAME);

    // Every value still at its built-in default means the file was found but
    // nothing in it parsed — usually "export DB_USER=", a different key name,
    // or the DB_ lines simply are not in this file.
    if (DB_USER === 'root' && DB_NAME === 'oradent' && DB_PASS === '') {
        return ['ok' => false, 'reason' => 'The .env file was found, but nothing was read from it',
                'detail' => 'Every setting is still at its built-in default (user "root", database '
                          . '"oradent", no password), so the connection was attempted with credentials '
                          . 'that do not exist on this server.',
                'fix' => 'Open ' . $envDir . '/.env and check each line reads exactly '
                       . 'KEY=value with no "export", no spaces around the "=", and no quotes — '
                       . 'for example DB_USER=u123456789_oradent. Lines starting with # are ignored.'];
    }

    if (str_contains(DB_PASS, 'change-this') || str_contains(DB_USER, 'uXXXXXXXX')) {
        return ['ok' => false, 'reason' => 'The .env file still has the example values in it',
                'detail' => 'It was copied from .env.example but the placeholders were never replaced.',
                'fix' => 'Replace DB_NAME, DB_USER and DB_PASS in ' . $envDir . '/.env with the real '
                       . 'values from hPanel → Databases → Management.'];
    }

    if (DB_PASS === '') {
        return ['ok' => false, 'reason' => 'No database password was read from .env',
                'detail' => 'DB_PASS came out empty, and this server does not accept a blank password.',
                'fix' => 'Check the DB_PASS line in ' . $envDir . '/.env. If the password contains a '
                       . '# the rest of that line is fine, but a line that *starts* with # is treated '
                       . 'as a comment and skipped entirely.'];
    }

    if ($userPrefix === null && $namePrefix !== null) {
        return ['ok' => false, 'reason' => 'DB_USER is missing the account prefix',
                'detail' => 'DB_NAME starts with "' . $namePrefix . '_" but DB_USER does not. On '
                          . 'Hostinger both carry the same prefix.',
                'fix' => 'Change DB_USER in ' . $envDir . '/.env to the full name shown in hPanel → '
                       . 'Databases, e.g. ' . $namePrefix . '_oradent.'];
    }

    if ($userPrefix !== null && $namePrefix !== null && $userPrefix !== $namePrefix) {
        return ['ok' => false, 'reason' => 'DB_USER and DB_NAME belong to different accounts',
                'detail' => 'DB_USER is prefixed "' . $userPrefix . '_" but DB_NAME is prefixed "'
                          . $namePrefix . '_". One of the two was copied from somewhere else.',
                'fix' => 'Both must come from the same row in hPanel → Databases. Correct whichever '
                       . 'one is wrong in ' . $envDir . '/.env.'];
    }

    return ['ok' => false, 'reason' => 'The database password was not accepted',
            'detail' => 'DB_USER ' . ($userPrefix !== null ? 'looks right (prefixed "' . $userPrefix . '_")'
                                                           : 'has no u123456789_ prefix, which is unusual on Hostinger')
                      . ', and a password of ' . strlen(DB_PASS) . ' characters was read from .env — '
                      . 'but the server rejected the pair.',
            'fix' => 'Surrounding quotes and stray whitespace are stripped when .env is read, so if '
                   . 'that character count matches the password you set, the mismatch is the value '
                   . 'itself. The reliable fix: in hPanel → Databases, use "Change password" on this '
                   . 'user, set one made only of letters and digits, and paste that exact value after '
                   . 'DB_PASS= in ' . $envDir . '/.env.'];
}
