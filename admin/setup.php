<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

admin_session();

/*
 * One-time creation of the first staff account. Refuses to run the moment an
 * account exists, so it cannot be used to add a second admin later.
 */
$count = admin_count();

if ($count === -1) {
    admin_head('Setup');
    echo '<div class="login-wrap"><div class="login-card">'
       . '<h1>Database not reachable</h1>'
       . '<p class="login-lede">Check the credentials in your <code>.env</code> file and that the '
       . 'database has been created and <code>sql/schema.sql</code> imported.</p></div></div>';
    admin_foot();
    exit;
}

if ($count > 0) {
    admin_head('Setup');
    echo '<div class="login-wrap"><div class="login-card">'
       . '<h1>Already set up</h1>'
       . '<p class="login-lede">An administrator account already exists, so this page is disabled. '
       . 'Delete the row from <code>admin_users</code> if you need to start again.</p>'
       . '<a class="btn btn--primary btn--block" href="/admin/login.php">Go to sign in</a>'
       . '</div></div>';
    admin_foot();
    exit;
}

$err = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $user = trim((string) ($_POST['username'] ?? ''));
    $name = trim((string) ($_POST['display_name'] ?? ''));
    $pw   = (string) ($_POST['password'] ?? '');
    $pw2  = (string) ($_POST['password2'] ?? '');

    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        $err = 'Session expired — please try again.';
    } elseif (!preg_match('/^[a-zA-Z0-9._-]{3,60}$/', $user)) {
        $err = 'Username must be 3–60 characters: letters, digits, dot, dash or underscore.';
    } elseif (mb_strlen($name) < 2) {
        $err = 'Please enter a display name.';
    } elseif (mb_strlen($pw) < 12) {
        $err = 'Use a password of at least 12 characters.';
    } elseif (!preg_match('/[A-Za-z]/', $pw) || !preg_match('/\d/', $pw)) {
        $err = 'Include at least one letter and one number.';
    } elseif ($pw !== $pw2) {
        $err = 'The two passwords do not match.';
    } else {
        $st = db()->prepare(
            'INSERT INTO admin_users (username, password_hash, display_name) VALUES (?, ?, ?)'
        );
        $st->execute([$user, password_hash($pw, PASSWORD_DEFAULT), $name]);
        header('Location: /admin/login.php', true, 303);
        exit;
    }
}

admin_head('Create the admin account');
?>
<div class="login-wrap">
  <div class="login-card">
    <span class="brand__name">ORA<em>DENT</em></span>
    <h1 style="margin-top:.8rem">Create the admin account</h1>
    <p class="login-lede">
      This runs once. After the account is created this page disables itself automatically.
    </p>

    <?php if ($err): ?><div class="alert alert--bad" role="alert"><?= e($err) ?></div><?php endif; ?>

    <form method="post" autocomplete="off">
      <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
      <label class="field">
        <span class="field__label">Your name</span>
        <input class="input" type="text" name="display_name" required
               value="<?= e((string) ($_POST['display_name'] ?? 'Dr. Nabanita Chaklader')) ?>">
      </label>
      <label class="field">
        <span class="field__label">Username</span>
        <input class="input" type="text" name="username" required autocomplete="off"
               value="<?= e((string) ($_POST['username'] ?? '')) ?>">
      </label>
      <label class="field">
        <span class="field__label">Password</span>
        <input class="input" type="password" name="password" required minlength="12" autocomplete="new-password">
        <span class="field__hint">At least 12 characters, with a letter and a number.</span>
      </label>
      <label class="field">
        <span class="field__label">Repeat password</span>
        <input class="input" type="password" name="password2" required autocomplete="new-password">
      </label>
      <button class="btn btn--primary btn--block btn--lg" type="submit">Create account</button>
    </form>
  </div>
</div>
<?php admin_foot(); ?>
