<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

admin_session();

if (admin_user()) {
    header('Location: /admin/', true, 302);
    exit;
}

/* First run: no admin account exists yet. */
if (admin_count() === 0) {
    header('Location: /admin/setup.php', true, 302);
    exit;
}

$err  = null;
$wait = admin_lockout_remaining();
$next = (string) ($_GET['next'] ?? '/admin/');
if (!str_starts_with($next, '/admin')) {
    $next = '/admin/';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        $err = 'Session expired — please try again.';
    } elseif ($wait > 0) {
        $err = "Too many failed attempts. Try again in $wait minute" . ($wait > 1 ? 's' : '') . '.';
    } elseif (admin_login(trim((string) ($_POST['username'] ?? '')), (string) ($_POST['password'] ?? ''))) {
        header('Location: ' . $next, true, 303);
        exit;
    } else {
        $err  = 'Incorrect username or password.';
        $wait = admin_lockout_remaining();
    }
}

admin_head('Sign in');
?>
<div class="login-wrap">
  <div class="login-card">
    <span class="brand__name">ORA<em>DENT</em></span>
    <h1 style="margin-top:.8rem">Staff sign in</h1>
    <p class="login-lede">Appointments, patient messages and clinic availability.</p>

    <?php if ($err): ?>
      <div class="alert alert--bad" role="alert"><?= e($err) ?></div>
    <?php endif; ?>

    <form method="post" autocomplete="on">
      <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
      <label class="field">
        <span class="field__label">Username</span>
        <input class="input" type="text" name="username" required autofocus autocomplete="username"
               value="<?= e((string) ($_POST['username'] ?? '')) ?>">
      </label>
      <label class="field">
        <span class="field__label">Password</span>
        <input class="input" type="password" name="password" required autocomplete="current-password">
      </label>
      <button class="btn btn--primary btn--block btn--lg" type="submit" <?= $wait > 0 ? 'disabled' : '' ?>>
        Sign in
      </button>
    </form>

    <p class="field__hint" style="margin-top:1.4rem;text-align:center">
      <a href="/">← Back to the website</a>
    </p>
  </div>
</div>
<?php admin_foot(); ?>
