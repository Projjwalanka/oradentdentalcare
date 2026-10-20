<?php
declare(strict_types=1);

require_once __DIR__ . '/seo.php';
require_once __DIR__ . '/icons.php';

/* Must happen before a single byte of output, or the session cookie (and with
   it every CSRF token) is silently dropped. */
session_start_once();

$title = Page::$title !== '' ? Page::$title : CLINIC['name'] . ' | Dental Clinic in ' . CLINIC['locality'] . ', ' . CLINIC['city'];
$desc  = Page::$description;
$status = open_status();
$open   = $status['open'];

header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Frame-Options: SAMEORIGIN');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), interest-cohort=()');

$navItems = [
    '/'          => 'Home',
    '/about'     => 'About',
    '/services'  => 'Services',
    '/gallery'   => 'Gallery',
    '/faq'       => 'FAQ',
    '/contact'   => 'Contact',
];
?>
<!doctype html>
<html lang="en-IN">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

<title><?= e($title) ?></title>
<meta name="description" content="<?= e($desc) ?>">
<link rel="canonical" href="<?= e(Page::canonical()) ?>">
<?php if (Page::$noindex): ?>
<meta name="robots" content="noindex, nofollow">
<?php else: ?>
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<?php endif; ?>

<meta name="theme-color" content="#0b1b3d">
<meta name="author" content="<?= e(CLINIC['doctor']) ?>">
<meta name="geo.region" content="IN-<?= e(CLINIC['region_code']) ?>">
<meta name="geo.placename" content="<?= e(CLINIC['locality'] . ', ' . CLINIC['city']) ?>">
<meta name="geo.position" content="<?= CLINIC['lat'] ?>;<?= CLINIC['lng'] ?>">
<meta name="ICBM" content="<?= CLINIC['lat'] ?>, <?= CLINIC['lng'] ?>">

<meta property="og:type" content="<?= e(Page::$type) ?>">
<meta property="og:site_name" content="<?= e(CLINIC['name']) ?>">
<meta property="og:locale" content="en_IN">
<meta property="og:title" content="<?= e($title) ?>">
<meta property="og:description" content="<?= e($desc) ?>">
<meta property="og:url" content="<?= e(Page::canonical()) ?>">
<meta property="og:image" content="<?= e(url(ltrim(Page::$image, '/'))) ?>">
<?php if (Page::$image === '/assets/images/brand/social-share.jpg'): /* the only image whose size is fixed */ ?>
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<?php endif; ?>
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="twitter:image" content="<?= e(url(ltrim(Page::$image, '/'))) ?>">

<?= brand_icon_links() ?>
<link rel="manifest" href="/site.webmanifest">
<link rel="stylesheet" href="<?= e(asset('assets/css/style.css')) ?>">

<script type="application/ld+json"><?= schema_graph() ?></script>
</head>
<body>
<a class="skip-link" href="#main">Skip to main content</a>

<div class="topbar">
  <div class="wrap topbar__in">
    <span class="topbar__item">
      <span class="status-dot<?= $open ? '' : ' status-dot--shut' ?>" aria-hidden="true"></span>
      <?= e($status['label']) ?>
<span class="topbar__hide-sm"><?= $status['detail'] !== '' ? ' ' . e($status['detail']) : '' ?> · <?= e(hours_inline()) ?></span>
    </span>
    <div class="topbar__right">
      <a class="topbar__item topbar__hide-sm" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener"
         data-track="directions_click" data-track-source="topbar">
        <?= icon('pin') ?><?= e(CLINIC['locality']) ?>
      </a>
      <a class="topbar__item" href="<?= e(tel_link()) ?>"
         data-track="call_click" data-track-source="topbar">
        <?= icon('phone') ?><?= e(CLINIC['phone_display']) ?>
      </a>
    </div>

    <?php /* Small screens have no room for the hours beside the status, so they
             wrap onto their own full-width row rather than disappearing. */ ?>
    <span class="topbar__hours-sm"><?= e(hours_inline()) ?></span>
  </div>
</div>

<header class="header">
  <div class="wrap header__in">
    <a class="brand" href="/" aria-label="<?= e(CLINIC['name']) ?> — home">
      <?= brand_mark() ?>
      <span class="brand__text">
        <span class="brand__name">ORA<em>DENT</em></span>
        <span class="brand__sub">Dental Care</span>
      </span>
    </a>

    <nav class="nav" aria-label="Main">
      <ul class="nav__list">
        <?php foreach ($navItems as $href => $label): ?>
        <li><a class="nav__link" href="<?= e($href) ?>"<?= is_active($href) && ($href !== '/' || current_path() === '/') ? ' aria-current="page"' : '' ?>><?= e($label) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <a class="btn btn--primary header__cta" href="/book"
       data-track="book_click" data-track-source="header">
      <?= icon('calendar') ?> Book Appointment
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="drawer" aria-label="Open menu">
      <span class="nav-toggle__open"><?= icon('menu') ?></span>
      <span class="nav-toggle__close"><?= icon('close') ?></span>
    </button>
  </div>
</header>

<div class="drawer" id="drawer" data-open="false" hidden>
  <div class="drawer__panel" role="dialog" aria-modal="true" aria-label="Menu">
    <div class="drawer__head">
      <span class="brand__name">ORA<em>DENT</em></span>
      <button class="nav-toggle" type="button" data-drawer-close aria-label="Close menu"><?= icon('close') ?></button>
    </div>
    <?php foreach ($navItems as $href => $label): ?>
      <a class="drawer__link" href="<?= e($href) ?>"<?= is_active($href) && ($href !== '/' || current_path() === '/') ? ' aria-current="page"' : '' ?>><?= e($label) ?></a>
    <?php endforeach; ?>
    <div class="drawer__actions">
      <a class="btn btn--primary btn--block" href="/book"
         data-track="book_click" data-track-source="drawer"><?= icon('calendar') ?> Book Appointment</a>
      <a class="btn btn--wa btn--block" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"
         data-track="whatsapp_click" data-track-source="drawer"><?= icon('whatsapp') ?> WhatsApp us</a>
      <a class="btn btn--ghost btn--block" href="<?= e(tel_link()) ?>"
         data-track="call_click" data-track-source="drawer"><?= icon('phone') ?> <?= e(CLINIC['phone_display']) ?></a>
    </div>
    <p class="drawer__meta">
      <?= e(CLINIC['street']) ?><br><?= e(CLINIC['locality'] . ', ' . CLINIC['city'] . ' ' . CLINIC['postal']) ?><br>
      <?php foreach (hours_summary() as $row): ?>
        <strong><?= e($row['days']) ?></strong> <?= e($row['open'] ? implode(' & ', $row['sessions']) : 'Closed') ?><br>
      <?php endforeach; ?>
    </p>
  </div>
</div>

<main id="main">
