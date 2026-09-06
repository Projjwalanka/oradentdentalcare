<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/showcase.php';
require_once __DIR__ . '/includes/slider.php';
require_once __DIR__ . '/includes/media.php';   // CLINIC_FEATURES

Page::$path        = '/gallery';
Page::$title       = 'Inside the Clinic — Hygiene, Equipment & Standards | ORADENT Dental Care';
Page::$description = 'How ORADENT Dental Care in ' . CLINIC['locality'] . ', Kolkata handles sterilisation, instruments and clinical process — with photographs of the treatment room, entry and consulting area.';
Page::$image       = '/assets/img/clinic-operatory-1200.jpg';
Page::$breadcrumbs = [
    ['name' => 'Home',   'url' => url()],
    ['name' => 'Clinic', 'url' => url('gallery')],
];

require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol><li><a href="/">Home</a></li><li>Clinic</li></ol>
    </nav>
    <h1>Inside the clinic</h1>
    <p>What the treatment room looks like, how instruments are handled between patients,
       and the standards behind every appointment.</p>
  </div>
</div>

<section class="section">
  <div class="wrap">
    <?php showcase(slider_images(), 'Inside ORADENT Dental Care'); ?>
  </div>
</section>

<!-- ------------------------------------------------------------- features -->
<section class="section section--tint" style="padding-top:0;background:transparent">
  <div class="wrap">
    <div class="section-head">
      <span class="eyebrow">Our standards</span>
      <h2>The things that matter most happen between appointments</h2>
      <p>Patients judge a clinic on the chair and the waiting area. What actually protects
         you is what happens to the instruments after the previous patient leaves.</p>
    </div>

    <div class="grid grid--3">
      <?php foreach (CLINIC_FEATURES as $f): ?>
      <div class="card feature-card" data-reveal>
        <span class="icon-badge"><?= icon($f['icon']) ?></span>
        <h3><?= e($f['title']) ?></h3>
        <p><?= e($f['text']) ?></p>
        <ul>
          <?php foreach ($f['points'] as $pt): ?>
            <li><?= icon('check') ?><span><?= e($pt) ?></span></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ------------------------------------------------------- sterilisation -->
<section class="section">
  <div class="wrap">
    <div class="grid grid--2" style="gap:clamp(2rem,5vw,3.5rem);align-items:start">
      <div class="prose">
        <span class="eyebrow">Cross-infection control</span>
        <h2 style="margin-top:0">What happens to an instrument between two patients</h2>
        <p>
          It is a fair question to ask any clinic, and an easy one to answer badly. Here is the
          actual sequence, in order.
        </p>
        <ol style="counter-reset:step">
          <li><strong>Segregate.</strong> Used instruments go straight into a holding solution;
              sharps go into a puncture-proof container at the chairside, never loose on a tray.</li>
          <li><strong>Clean.</strong> Debris is scrubbed off — sterilisation does not work through
              a layer of dried blood or cement, which is why this step is not skipped.</li>
          <li><strong>Pouch.</strong> Instruments are sealed into pouches with an indicator strip
              that changes colour only if the cycle actually reached temperature.</li>
          <li><strong>Autoclave.</strong> Steam under pressure, for a full cycle. The pouch stays
              sealed afterwards, so instruments cannot be re-contaminated on a shelf.</li>
          <li><strong>Open in front of you.</strong> The pouch is opened at the chair, at the start
              of your appointment — so you can see the seal was intact.</li>
        </ol>
        <p>
          Anything that cannot be autoclaved is single-use and thrown away: needles, blades,
          gloves, masks, suction tips, cups, and the bib. Clinical waste and sharps leave through
          authorised bio-medical waste disposal, not domestic rubbish.
        </p>
      </div>

      <aside>
        <div class="aside-card">
          <h3>Ask us anything about it</h3>
          <p style="font-size:.94rem">
            If you would like to see the autoclave, the pouches or the waste process before you
            book, just ask when you come in. Nobody has ever been told no.
          </p>
          <ul>
            <li><?= icon('check-c') ?><span>Pouches opened at the chair, not beforehand</span></li>
            <li><?= icon('check-c') ?><span>Single-use items never reused between patients</span></li>
            <li><?= icon('check-c') ?><span>Footwear stays outside the treatment room</span></li>
          </ul>
          <div class="btn-row" style="margin-top:1.3rem;flex-direction:column">
            <a class="btn btn--primary btn--block" href="/book"><?= icon('calendar') ?> Book an appointment</a>
            <a class="btn btn--wa btn--block" target="_blank" rel="noopener"
               href="<?= e(whatsapp_link('Hello ORADENT, I had a question about hygiene and sterilisation at the clinic.')) ?>">
              <?= icon('whatsapp') ?> Ask on WhatsApp
            </a>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>

<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="cta-band">
      <div>
        <h2>Come and see for yourself</h2>
        <p>Book a slot online, or call in during consulting hours and have a look around.</p>
      </div>
      <div class="btn-row">
        <a class="btn btn--primary btn--lg" href="/book"><?= icon('calendar') ?> Book a visit</a>
        <a class="btn btn--on-dark btn--lg" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">
          <?= icon('directions') ?> Directions
        </a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
