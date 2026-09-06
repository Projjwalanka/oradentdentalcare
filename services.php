<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';

Page::$path        = '/services';
Page::$title       = 'Dental Treatments in Sonarpur, Kolkata | ORADENT Dental Care';
Page::$description = 'Root canal treatment, dental implants, oral surgery, extractions, scaling, fillings, cosmetic and children\'s dentistry in Sonarpur, Kolkata. See what each treatment involves and book online.';
Page::$breadcrumbs = [
    ['name' => 'Home',     'url' => url()],
    ['name' => 'Services', 'url' => url('services')],
];
$listItems = [];
foreach (array_keys(SERVICES) as $i => $slug) {
    $listItems[] = [
        '@type'    => 'ListItem',
        'position' => $i + 1,
        'name'     => SERVICES[$slug]['name'],
        'url'      => url('services/' . $slug),
    ];
}
Page::$schema[] = [
    '@type'           => 'ItemList',
    '@id'             => url('services') . '#list',
    'name'            => 'Dental treatments at ORADENT Dental Care',
    'itemListElement' => $listItems,
];

require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol><li><a href="/">Home</a></li><li>Services</li></ol>
    </nav>
    <h1>Dental treatments</h1>
    <p>Everything from a routine clean to surgical removal of an impacted wisdom tooth — with what
       each treatment actually involves, how long it takes, and when it is the right choice.</p>
  </div>
</div>

<section class="section">
  <div class="wrap">
    <div class="grid grid--3">
      <?php foreach (SERVICES as $slug => $s): ?>
      <a class="card card--link service-card" href="/services/<?= e($slug) ?>" data-reveal>
        <span class="icon-badge"><?= icon($s['icon']) ?></span>
        <h3><?= e($s['name']) ?></h3>
        <p><?= e($s['short']) ?></p>
        <span class="service-card__more">What it involves <?= icon('arrow') ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--tint">
  <div class="wrap wrap-narrow text-center">
    <span class="eyebrow">Not sure what you need?</span>
    <h2>Start with an examination</h2>
    <p class="lede">
      Most patients arrive knowing something is wrong but not what. A consultation — examination,
      an X-ray where it is needed, and a written plan with costs — is the sensible first step, and
      and it takes one short appointment.
    </p>
    <div class="btn-row mt-2" style="justify-content:center">
      <a class="btn btn--primary btn--lg" href="/book"><?= icon('calendar') ?> Book an examination</a>
      <a class="btn btn--wa btn--lg" href="<?= e(whatsapp_link('Hello ORADENT, I am not sure which treatment I need. Could you advise?')) ?>"
         target="_blank" rel="noopener"><?= icon('whatsapp') ?> Ask on WhatsApp</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
