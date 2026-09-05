<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/seo.php';

Page::$path        = '/gallery';
Page::$title       = 'Inside the Clinic | ORADENT Dental Care, Sonarpur, Kolkata';
Page::$description = 'Photographs of ORADENT Dental Care in Sonarpur, Kolkata — the treatment room, patient entry and consulting area. Real photos of the actual clinic, not stock imagery.';
Page::$image       = '/assets/img/clinic-operatory-1200.jpg';
Page::$breadcrumbs = [
    ['name' => 'Home',   'url' => url()],
    ['name' => 'Clinic', 'url' => url('gallery')],
];

$shots = [
    ['clinic-exterior', 'The clinic frontage on Maharani Indira Devy Road showing ORADENT Dental Care window graphics with treatments and opening hours',
     'Our frontage', 'The window graphics list our treatments and hours, so you can find us easily from the auto stand.'],
    ['clinic-operatory', 'ORADENT treatment room with dental chair, overhead operating light, delivery unit and instrument tray',
     'The treatment room', 'A modern chair, good operating light and a sterile tray laid out fresh for every patient.'],
    ['clinic-entrance', 'Patient entry door at ORADENT Dental Care with a notice asking visitors to remove footwear outside the chamber',
     'Patient entry', 'Footwear comes off outside the chamber — a small thing that keeps the treatment area genuinely clean.'],
    ['dr-nabanita-chaklader', 'Dr. Nabanita Chaklader seated at her consulting desk at ORADENT Dental Care',
     'The consulting desk', 'Every visit starts here with a conversation, before anyone sits in the chair.'],
];

require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol><li><a href="/">Home</a></li><li>Clinic</li></ol>
    </nav>
    <h1>Inside the clinic</h1>
    <p>Every photograph here is of the actual clinic in <?= e(CLINIC['locality']) ?> — no stock
       photography, so you know what to expect before you walk in.</p>
  </div>
</div>

<section class="section">
  <div class="wrap">
    <div class="grid grid--2">
      <?php foreach ($shots as $i => [$file, $alt, $title, $caption]): ?>
      <figure class="gallery-figure" data-reveal>
        <picture>
          <source type="image/webp" srcset="/assets/img/<?= e($file) ?>-800.webp 800w, /assets/img/<?= e($file) ?>-1200.webp 1200w"
                  sizes="(min-width: 620px) 46vw, 92vw">
          <img src="/assets/img/<?= e($file) ?>-800.jpg" width="800" height="600"
               loading="<?= $i < 2 ? 'eager' : 'lazy' ?>" decoding="async" alt="<?= e($alt) ?>">
        </picture>
        <figcaption><strong style="display:block;color:var(--navy-900)"><?= e($title) ?></strong><?= e($caption) ?></figcaption>
      </figure>
      <?php endforeach; ?>
    </div>

    <div class="cta-band mt-3">
      <div>
        <h2>Come and see for yourself</h2>
        <p>Book a 15-minute slot — or just call in during consulting hours and say hello.</p>
      </div>
      <div class="btn-row">
        <a class="btn btn--primary btn--lg" href="/book"><?= icon('calendar') ?> Book a visit</a>
        <a class="btn btn--on-dark btn--lg" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener"><?= icon('directions') ?> Directions</a>
      </div>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
