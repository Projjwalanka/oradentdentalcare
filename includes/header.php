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
    '/gallery'   => 'Clinic',
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
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= e($title) ?>">
<meta name="twitter:description" content="<?= e($desc) ?>">
<meta name="twitter:image" content="<?= e(url(ltrim(Page::$image, '/'))) ?>">

<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<link rel="apple-touch-icon" href="/assets/img/apple-touch-icon.png">
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
      <a class="topbar__item topbar__hide-sm" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">
        <?= icon('pin') ?><?= e(CLINIC['locality']) ?>
      </a>
      <a class="topbar__item" href="<?= e(tel_link()) ?>">
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
      <svg class="brand__mark" viewBox="0 0 48 48" aria-hidden="true">
        <circle cx="24" cy="24" r="23" fill="none" stroke="#182a5a" stroke-width="2"/>
        <path d="M24 12.5c-2.6 0-3.7 1.1-6.4 1.1-3.5 0-6 2.7-6 6.9 0 3.5 1.1 5.3 2.1 8.5.8 2.7 1 6.4 1.8 9 .5 1.6 1.3 2.7 2.6 2.7 1.9 0 2.2-2.2 2.7-4.8.5-2.4 1-4.2 3.2-4.2s2.7 1.8 3.2 4.2c.5 2.6.8 4.8 2.7 4.8 1.3 0 2.1-1.1 2.6-2.7.8-2.6 1-6.3 1.8-9 1-3.2 2.1-5 2.1-8.5 0-4.2-2.5-6.9-6-6.9-2.7 0-3.8-1.1-6.4-1.1Z"
              fill="none" stroke="#0a7297" stroke-width="2.1" stroke-linejoin="round"/>
        <path d="M20.6 19.4 24 28.6l3.4-9.2" fill="none" stroke="#182a5a" stroke-width="1.8"
              stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
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

    <a class="btn btn--primary header__cta" href="/book">
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
      <a class="btn btn--primary btn--block" href="/book"><?= icon('calendar') ?> Book Appointment</a>
      <a class="btn btn--wa btn--block" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp us</a>
      <a class="btn btn--ghost btn--block" href="<?= e(tel_link()) ?>"><?= icon('phone') ?> <?= e(CLINIC['phone_display']) ?></a>
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
