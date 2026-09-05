<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/seo.php';
if (!headers_sent()) { http_response_code(404); }
Page::$path = '/404';
Page::$title = 'Page not found | ORADENT Dental Care';
Page::$description = 'The page you were looking for does not exist.';
Page::$noindex = true;
require __DIR__ . '/includes/header.php';
?>
<section class="section">
  <div class="wrap wrap-narrow text-center">
    <span class="eyebrow">Error 404</span>
    <h1>We could not find that page</h1>
    <p class="lede">The link may be out of date, or the address mistyped. Here is the way back.</p>
    <div class="btn-row mt-2" style="justify-content:center">
      <a class="btn btn--primary" href="/">Go to the homepage</a>
      <a class="btn btn--ghost" href="/services">Browse treatments</a>
      <a class="btn btn--ghost" href="/book">Book an appointment</a>
    </div>
    <div class="grid grid--3 mt-3" style="text-align:left">
      <?php foreach (array_slice(SERVICES, 0, 3, true) as $slug => $s): ?>
      <a class="card card--link service-card" href="/services/<?= e($slug) ?>">
        <span class="icon-badge"><?= icon($s['icon']) ?></span>
        <h3><?= e($s['name']) ?></h3>
        <p><?= e($s['short']) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
