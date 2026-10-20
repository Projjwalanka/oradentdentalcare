<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/showcase.php';
require_once __DIR__ . '/includes/media_lib.php';
require_once __DIR__ . '/includes/media.php';   // CLINIC_FEATURES

/* Both come straight from their placeholder folders, assets/images/gallery/ and
   assets/videos/gallery/ — see assets/images/README.md. Add a file, it appears
   here; remove it, it goes. No code change either way. */
$photos = media_images('gallery');
$videos = media_videos('gallery');

Page::$path        = '/gallery';
Page::$title       = 'Gallery — Photos & Videos of the Clinic | ORADENT Dental Care';
Page::$description = 'Photographs and video from inside ORADENT Dental Care in ' . CLINIC['locality']
                   . ', ' . CLINIC['city'] . ' — the treatment room, equipment, sterilisation and entrance. '
                   . 'Real photographs of the actual clinic.';
if ($photos) {
    Page::$image = strtok($photos[0]['src'], '?');
}
Page::$breadcrumbs = [
    ['name' => 'Home',    'url' => url()],
    ['name' => 'Gallery', 'url' => url('gallery')],
];

require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol><li><a href="/">Home</a></li><li>Gallery</li></ol>
    </nav>
    <h1>Inside the clinic</h1>
    <p>Photographs and video of the actual clinic — the treatment room, the equipment,
       how instruments are handled, and what you will see when you walk in.</p>
  </div>
</div>

<?php if ($photos || $videos): ?>
<div class="wrap">
  <div class="tabs" data-tabs>
    <div class="tabs__list" role="tablist" aria-label="Gallery">
      <?php if ($photos): ?>
      <button class="tabs__tab" type="button" role="tab" id="tab-photos"
              aria-controls="panel-photos" aria-selected="true">
        <?= icon('sparkle') ?> Photos <span class="tabs__count"><?= count($photos) ?></span>
      </button>
      <?php endif; ?>
      <?php if ($videos): ?>
      <button class="tabs__tab" type="button" role="tab" id="tab-videos"
              aria-controls="panel-videos" aria-selected="<?= $photos ? 'false' : 'true' ?>">
        <?= icon('right') ?> Videos <span class="tabs__count"><?= count($videos) ?></span>
      </button>
      <?php endif; ?>
    </div>

    <?php if ($photos): ?>
    <div class="tabs__panel" role="tabpanel" id="panel-photos" aria-labelledby="tab-photos">
      <div class="gallery-grid">
        <?php foreach ($photos as $i => $p): ?>
        <figure class="gallery-item">
          <img src="<?= e($p['src']) ?>"
               <?= $p['srcset'] !== '' ? 'srcset="' . e($p['srcset']) . '" sizes="(min-width:900px) 32vw, 92vw"' : '' ?>
               width="<?= (int) $p['w'] ?>" height="<?= (int) $p['h'] ?>"
               alt="<?= e($p['alt']) ?>"
               loading="<?= $i < 2 ? 'eager' : 'lazy' ?>" decoding="async">
          <figcaption>
            <b><?= e($p['title']) ?></b>
            <?php if ($p['caption'] !== ''): ?><span><?= e($p['caption']) ?></span><?php endif; ?>
          </figcaption>
        </figure>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if ($videos): ?>
    <div class="tabs__panel" role="tabpanel" id="panel-videos" aria-labelledby="tab-videos"
         <?= $photos ? 'hidden' : '' ?>>
      <div class="gallery-grid gallery-grid--video">
        <?php foreach ($videos as $v): ?>
        <figure class="gallery-item">
          <?php media_video_tag($v); ?>
          <figcaption>
            <b><?= e($v['title']) ?></b>
            <?php if ($v['caption'] !== ''): ?><span><?= e($v['caption']) ?></span><?php endif; ?>
          </figcaption>
        </figure>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<!-- ------------------------------------------------------------- features -->
<section class="section">
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
<section class="section section--tint">
  <div class="wrap">
    <div class="grid grid--2" style="gap:clamp(2rem,5vw,3.5rem);align-items:start">
      <div class="prose">
        <span class="eyebrow">Cross-infection control</span>
        <h2 style="margin-top:0">What happens to an instrument between two patients</h2>
        <p>
          It is a fair question to ask any clinic, and an easy one to answer badly. Here is the
          actual sequence, in order.
        </p>
        <ol>
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
            <a class="btn btn--primary btn--block" href="/book"
               data-track="book_click" data-track-source="gallery">
              <?= icon('calendar') ?> Book an appointment
            </a>
            <a class="btn btn--wa btn--block" target="_blank" rel="noopener"
               data-track="whatsapp_click" data-track-source="gallery"
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
        <a class="btn btn--primary btn--lg" href="/book"
           data-track="book_click" data-track-source="gallery_cta">
          <?= icon('calendar') ?> Book a visit
        </a>
        <a class="btn btn--on-dark btn--lg" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener"
           data-track="directions_click" data-track-source="gallery_cta">
          <?= icon('directions') ?> Directions
        </a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
