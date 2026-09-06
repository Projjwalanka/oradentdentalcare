<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';

$slug = (string) ($_GET['slug'] ?? '');
$svc  = service($slug);

if ($svc === null) {
    http_response_code(404);
    require __DIR__ . '/404.php';
    exit;
}

Page::$path        = '/services/' . $slug;
Page::$title       = $svc['title'];
Page::$description = $svc['meta'];
Page::$type        = 'article';
Page::$faqs        = $svc['faqs'];
Page::$breadcrumbs = [
    ['name' => 'Home',       'url' => url()],
    ['name' => 'Services',   'url' => url('services')],
    ['name' => $svc['name'], 'url' => url('services/' . $slug)],
];
Page::$schema[] = [
    '@type'       => 'MedicalProcedure',
    '@id'         => Page::canonical() . '#procedure',
    'name'        => $svc['name'],
    'description' => $svc['short'],
    'url'         => Page::canonical(),
    'procedureType' => 'https://schema.org/TherapeuticProcedure',
    'howPerformed'  => $svc['lead'],
    'provider'      => ['@id' => url('#clinic')],
];

$others = array_filter(SERVICES, static fn($k) => $k !== $slug, ARRAY_FILTER_USE_KEY);

require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol>
        <li><a href="/">Home</a></li>
        <li><a href="/services">Services</a></li>
        <li><?= e($svc['name']) ?></li>
      </ol>
    </nav>
    <h1><?= e($svc['name']) ?> in <?= e(CLINIC['locality']) ?>, <?= e(CLINIC['city']) ?></h1>
    <p><?= e($svc['short']) ?></p>
  </div>
</div>

<section class="section">
  <div class="wrap" style="display:grid;gap:clamp(2rem,5vw,3.5rem);align-items:start">
    <style>@media (min-width:1000px){.svc-layout{grid-template-columns:1fr 340px}}</style>
    <div class="svc-layout" style="display:grid;gap:clamp(2rem,5vw,3rem);align-items:start">

      <article class="prose">
        <div class="factbar">
          <div><b><?= e($svc['duration']) ?></b>Typical appointment</div>
          <div><b><?= e($svc['sittings']) ?></b>Visits needed</div>
          <div><b>Local anaesthetic</b>Comfort</div>
        </div>

        <p class="lede"><?= e($svc['lead']) ?></p>

        <?php foreach ($svc['sections'] as $sec): ?>
          <h2><?= e($sec['h']) ?></h2>
          <?php foreach ($sec['p'] as $para): ?>
            <p><?= e($para) ?></p>
          <?php endforeach; ?>
        <?php endforeach; ?>

        <h2>Questions patients ask about <?= e($svc['name']) ?></h2>
        <div class="faq">
          <?php foreach ($svc['faqs'] as $i => [$q, $a]): ?>
          <details class="faq__item"<?= $i === 0 ? ' open' : '' ?>>
            <summary><?= e($q) ?></summary>
            <div class="faq__answer"><?= e($a) ?></div>
          </details>
          <?php endforeach; ?>
        </div>

        <div class="cta-band mt-3">
          <div>
            <h2 style="font-size:1.5rem">Book <?= e($svc['name']) ?></h2>
            <p>Choose a slot at <?= e(CLINIC['locality']) ?> — confirmed instantly.</p>
          </div>
          <div class="btn-row">
            <a class="btn btn--primary" href="/book?service=<?= e($slug) ?>"><?= icon('calendar') ?> Book online</a>
            <a class="btn btn--on-dark" href="<?= e(tel_link()) ?>"><?= icon('phone') ?> Call</a>
          </div>
        </div>
      </article>

      <aside>
        <div class="aside-card">
          <h3>Book this treatment</h3>
          <ul>
            <li><?= icon('clock') ?><span>Mon–Sat · 10 AM–1 PM &amp; 5–9 PM</span></li>
            <li><?= icon('calendar') ?><span>Slots open <?= BOOKING_WINDOW_DAYS ?> days ahead</span></li>
            <li><?= icon('pin') ?><span><?= e(CLINIC['locality'] . ', ' . CLINIC['city']) ?></span></li>
          </ul>
          <div class="btn-row" style="margin-top:1.3rem;flex-direction:column">
            <a class="btn btn--primary btn--block" href="/book?service=<?= e($slug) ?>">Book appointment</a>
            <a class="btn btn--wa btn--block" target="_blank" rel="noopener"
               href="<?= e(whatsapp_link('Hello ORADENT, I would like to ask about ' . $svc['name'] . '.')) ?>">
              <?= icon('whatsapp') ?> Ask a question
            </a>
          </div>
        </div>

        <div class="card" style="margin-top:1.2rem">
          <h3 style="font-size:1.02rem">Other treatments</h3>
          <ul style="list-style:none;padding:0;margin:.8rem 0 0;display:grid;gap:.55rem;font-size:.93rem">
            <?php foreach (array_slice($others, 0, 6, true) as $s2 => $o): ?>
            <li><a href="/services/<?= e($s2) ?>" style="text-decoration:none"><?= e($o['name']) ?></a></li>
            <?php endforeach; ?>
            <li><a href="/services"><strong>All treatments →</strong></a></li>
          </ul>
        </div>
      </aside>

    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
