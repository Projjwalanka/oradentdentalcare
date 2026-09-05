<?php
declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/includes/functions.php';
require_once dirname(__DIR__, 2) . '/includes/data.php';
require_once dirname(__DIR__, 2) . '/includes/icons.php';

/**
 * Admin authentication. Separate session name from the public site so a patient
 * session can never be confused with a staff session.
 */
function admin_session(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }
    session_name(ADMIN_SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => !empty($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();

    // Idle timeout
    if (!empty($_SESSION['admin_id'])
        && (time() - (int) ($_SESSION['admin_seen'] ?? 0)) > ADMIN_SESSION_LIFETIME) {
        admin_logout();
    }
    $_SESSION['admin_seen'] = time();
}

function admin_user(): ?array
{
    admin_session();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    return [
        'id'   => (int) $_SESSION['admin_id'],
        'name' => (string) ($_SESSION['admin_name'] ?? 'Admin'),
        'user' => (string) ($_SESSION['admin_user'] ?? ''),
    ];
}

function require_admin(): array
{
    $u = admin_user();
    if ($u === null) {
        header('Location: /admin/login.php?next=' . rawurlencode($_SERVER['REQUEST_URI'] ?? '/admin/'), true, 302);
        exit;
    }
    return $u;
}

function admin_login(string $username, string $password): bool
{
    admin_session();
    $pdo = db();

    $st = $pdo->prepare('SELECT * FROM admin_users WHERE username = ? LIMIT 1');
    $st->execute([$username]);
    $row = $st->fetch();

    // Always run a hash comparison so a wrong username and a wrong password
    // take the same time — no user enumeration through timing.
    $hash = $row['password_hash'] ?? '$2y$12$usesomesillystringfooosillystringfoobarbazquxquux0000000';
    $ok   = password_verify($password, $hash) && $row !== false;

    $ins = $pdo->prepare('INSERT INTO login_attempts (ip, username) VALUES (?, ?)');

    if (!$ok) {
        $ins->execute([client_ip(), mb_substr($username, 0, 60)]);
        return false;
    }

    if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
        $up = $pdo->prepare('UPDATE admin_users SET password_hash = ? WHERE id = ?');
        $up->execute([password_hash($password, PASSWORD_DEFAULT), $row['id']]);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id']   = (int) $row['id'];
    $_SESSION['admin_name'] = $row['display_name'];
    $_SESSION['admin_user'] = $row['username'];
    $_SESSION['admin_seen'] = time();

    $pdo->prepare('UPDATE admin_users SET last_login_at = NOW() WHERE id = ?')->execute([$row['id']]);
    $pdo->prepare('DELETE FROM login_attempts WHERE ip = ?')->execute([client_ip()]);

    return true;
}

function admin_logout(): void
{
    admin_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** How many minutes this IP must wait, or 0 if it may try now. */
function admin_lockout_remaining(): int
{
    if (!db_available()) {
        return 0;
    }
    $st = db()->prepare(
        'SELECT COUNT(*) AS n, MAX(attempted_at) AS last FROM login_attempts
          WHERE ip = ? AND attempted_at > (NOW() - INTERVAL ? MINUTE)'
    );
    $st->execute([client_ip(), ADMIN_LOCKOUT_MINUTES]);
    $r = $st->fetch();

    if ((int) $r['n'] < ADMIN_MAX_LOGIN_TRIES) {
        return 0;
    }
    $since = (time() - strtotime((string) $r['last'])) / 60;
    return max(1, (int) ceil(ADMIN_LOCKOUT_MINUTES - $since));
}

function admin_csrf(): string
{
    admin_session();
    if (empty($_SESSION['admin_csrf'])) {
        $_SESSION['admin_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['admin_csrf'];
}

function admin_csrf_check(?string $t): bool
{
    admin_session();
    return is_string($t) && !empty($_SESSION['admin_csrf']) && hash_equals($_SESSION['admin_csrf'], $t);
}

/** Any admin users configured yet? Drives the one-time setup screen. */
function admin_count(): int
{
    if (!db_available()) {
        return -1;
    }
    return (int) db()->query('SELECT COUNT(*) FROM admin_users')->fetchColumn();
}
