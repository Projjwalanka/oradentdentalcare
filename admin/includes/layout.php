<?php
declare(strict_types=1);

/**
 * Shared chrome for every admin screen.
 * Call admin_head($title) at the top and admin_foot() at the bottom.
 */

require_once dirname(__DIR__, 2) . '/includes/media_lib.php';   // brand_icon_links()

function admin_head(string $title, ?array $user = null): void
{
    $nav = [
        '/admin/'              => ['Dashboard',   'calendar'],
        '/admin/appointments.php' => ['Appointments', 'clock'],
        '/admin/blocks.php'    => ['Block time',  'close'],
        '/admin/messages.php'  => ['Messages',    'mail'],
        '/admin/analytics.php' => ['Insights',    'star'],
    ];
    $cur = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    ?>
<!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= e($title) ?> · ORADENT Admin</title>
<?= brand_icon_links() ?>
<link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">

<?php if ($user): ?>
<header class="adm-top">
  <div class="adm-top__in">
    <a class="adm-brand" href="/admin/">
      <span class="brand__name">ORA<em>DENT</em></span>
      <span class="tag">Admin</span>
    </a>
    <nav class="adm-nav" aria-label="Admin sections">
      <?php foreach ($nav as $href => [$label, $ico]): ?>
        <a href="<?= e($href) ?>"<?= $cur === $href ? ' aria-current="page"' : '' ?>>
          <?= icon($ico) ?><span><?= e($label) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="adm-user">
      <span class="adm-user__name"><?= e($user['name']) ?></span>
      <a class="btn btn--ghost btn--sm" href="/admin/logout.php">Sign out</a>
    </div>
  </div>
</header>
<?php endif; ?>

<main class="adm-main">
<?php
}

function admin_foot(): void
{
    ?>
</main>
<footer class="adm-foot">
  <span>ORADENT Dental Care · admin</span>
  <span><a href="/" target="_blank" rel="noopener">View website →</a></span>
</footer>
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>
<?php
}

/** Coloured status chip used in the appointment tables. */
function status_chip(string $status): string
{
    $map = [
        'confirmed' => ['Confirmed', 'ok'],
        'completed' => ['Completed', 'done'],
        'cancelled' => ['Cancelled', 'bad'],
        'no_show'   => ['No-show',   'warn'],
    ];
    [$label, $tone] = $map[$status] ?? [$status, 'ok'];
    return '<span class="chip chip--' . $tone . '">' . e($label) . '</span>';
}
