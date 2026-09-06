<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/showcase.php';
require_once __DIR__ . '/includes/slider.php';
require_once __DIR__ . '/includes/media.php';   // CLINIC_FEATURES

Page::$path        = '/';
Page::$title       = 'Dentist in Sonarpur, Kolkata | ORADENT Dental Care — Dr. Nabanita Chaklader';
Page::$description = 'ORADENT Dental Care is a dental clinic in Sonarpur, Kolkata led by Dr. Nabanita Chaklader, BDS (WBUHS) — root canal treatment, implants, oral surgery and family dentistry. Open Mon–Sat. Book online in under a minute.';
Page::$image       = '/assets/img/og-default.jpg';
Page::$faqs        = array_slice(GENERAL_FAQS, 0, 6);

$pageScripts = [];
require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="wrap hero__in">
    <div>
      <span class="pill"><?= icon('pin') ?> <?= e(CLINIC['neighbourhood']) ?>, <?= e(CLINIC['city']) ?></span>
      <h1>Healthy smile,<span>happy life.</span></h1>
      <p class="hero__lede">
        Dental care and oral surgery in <?= e(CLINIC['locality']) ?> led by <strong style="color:#fff"><?= e(CLINIC['doctor']) ?></strong>,
        <?= e(CLINIC['doctor_qual']) ?> — a dental surgeon with special interests in root canal treatment
        and oral &amp; maxillofacial surgery.
        <span class="hero__quote">&ldquo;<?= e(CLINIC['tagline']) ?>&rdquo;</span>
      </p>

      <div class="btn-row mt-2">
        <a class="btn btn--primary btn--lg" href="/book"><?= icon('calendar') ?> Book an appointment</a>
        <a class="btn btn--on-dark btn--lg" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">
          <?= icon('whatsapp') ?> WhatsApp us
        </a>
      </div>

      <ul class="hero__points">
        <li><?= icon('check-c') ?> Specialist-led RCT &amp; oral surgery</li>
        <li><?= icon('check-c') ?> Autoclaved instruments</li>
        <li><?= icon('check-c') ?> Slots booked online</li>
        <li><?= icon('check-c') ?> All ages welcome</li>
      </ul>
    </div>

    <div class="hero__media">
      <picture>
        <source type="image/webp" srcset="/assets/img/clinic-operatory-800.webp 800w, /assets/img/clinic-operatory-1200.webp 1200w"
                sizes="(min-width: 940px) 46vw, 92vw">
        <img src="/assets/img/clinic-operatory-1200.jpg" width="1200" height="900"
             alt="Treatment room at ORADENT Dental Care in Sonarpur, Kolkata, with a dental chair, overhead operating light and sterilised instrument tray"
             fetchpriority="high" decoding="async">
      </picture>
      <div class="hero__badge">
        <?= icon('star', '') ?>
        <span><b>5.0</b><span>Rated by patients on Google</span></span>
      </div>
    </div>
  </div>
</section>

<section class="trust">
  <div class="wrap trust__in">
    <div class="trust__item"><?= icon('shield') ?><span><b>Specialist care</b>RCT &amp; oral surgery</span></div>
    <div class="trust__item"><?= icon('sparkle') ?><span><b>Sterile &amp; safe</b>Autoclaved every time</span></div>
    <div class="trust__item"><?= icon('clock') ?><span><b>On-time slots</b>Booked online, 30 days ahead</span></div>
    <div class="trust__item"><?= icon('family') ?><span><b>All ages</b>Children to grandparents</span></div>
  </div>
</section>

<!-- ------------------------------------------------------------- slider -->
<section class="section" id="clinic-photos">
  <div class="wrap">
    <div class="section-head section-head--center">
      <span class="eyebrow">A look around</span>
      <h2>The clinic you will actually walk into</h2>
      <p>The treatment room, the equipment and the people &mdash; photographed here,
         not bought from a stock library.</p>
    </div>
    <?php showcase(slider_images(), 'Inside ORADENT Dental Care'); ?>
  </div>
</section>

<!-- ------------------------------------------------------------ services -->
<section class="section" id="services">
  <div class="wrap">
    <div class="slider__head">
      <div class="section-head">
        <span class="eyebrow">Our treatments</span>
        <h2>Complete dental care, under one roof</h2>
        <p>From a routine scaling to a surgical extraction — the treatments most families need,
           delivered by a dentist who does the complex work herself rather than referring it on.</p>
      </div>
      <div class="slider__nav" data-slider-nav>
        <button class="slider__btn" type="button" data-slider-prev aria-label="Previous treatments"><?= icon('left') ?></button>
        <button class="slider__btn" type="button" data-slider-next aria-label="More treatments"><?= icon('right') ?></button>
      </div>
    </div>

    <div class="slider" data-slider>
      <div class="slider__track">
        <?php foreach (SERVICES as $slug => $s): ?>
        <div class="slider__slide">
          <a class="card card--link service-card" href="/services/<?= e($slug) ?>">
            <span class="icon-badge"><?= icon($s['icon']) ?></span>
            <h3><?= e($s['name']) ?></h3>
            <p><?= e($s['short']) ?></p>
            <span class="service-card__more">Learn more <?= icon('arrow') ?></span>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="btn-row mt-3">
      <a class="btn btn--ghost" href="/services">See all treatments <?= icon('arrow') ?></a>
    </div>
  </div>
</section>

<!-- --------------------------------------------------------------- about -->
<section class="section section--tint">
  <div class="wrap doctor">
    <div class="doctor__photo" data-reveal>
      <picture>
        <source type="image/webp" srcset="/assets/img/dr-nabanita-chaklader-800.webp">
        <img src="/assets/img/dr-nabanita-chaklader-800.jpg" width="800" height="1000" loading="lazy" decoding="async"
             alt="Dr. Nabanita Chaklader, BDS (WBUHS), dental surgeon at ORADENT Dental Care, at her consulting desk in the Sonarpur clinic">
      </picture>
    </div>
    <div>
      <span class="eyebrow">About the doctor</span>
      <h2><?= e(CLINIC['doctor']) ?></h2>
      <p class="lede">
        Dental surgeon, <?= e(CLINIC['doctor_qual']) ?> — with declared special interests in
        Root Canal Treatment and in Oral &amp; Maxillofacial Surgery.
      </p>
      <p>
        Dr. Chaklader founded ORADENT to bring careful, unhurried dentistry to
        <?= e(CLINIC['locality']) ?>. Her approach is straightforward: explain what is actually
        wrong, lay out the real options with their costs, and let the patient decide — then do the
        treatment gently and well.
      </p>

      <ul class="doctor__creds">
        <li><?= icon('check-c') ?><span><strong>BDS, The West Bengal University of Health Sciences.</strong> Registered dental surgeon.</span></li>
        <li><?= icon('check-c') ?><span><strong>Endodontics.</strong> Root canal treatment, including molars and re-treatment cases.</span></li>
        <li><?= icon('check-c') ?><span><strong>Oral &amp; maxillofacial surgery.</strong> Impacted wisdom teeth, minor oral surgery, cysts and biopsies.</span></li>
        <li><?= icon('check-c') ?><span><strong>Gentle with anxious patients.</strong> Nothing is done without explaining it first.</span></li>
      </ul>

      <div class="btn-row">
        <a class="btn btn--navy" href="/about">Read her full profile <?= icon('arrow') ?></a>
        <a class="btn btn--ghost" href="/book">Book a consultation</a>
      </div>
    </div>
  </div>
</section>

<!-- ------------------------------------------------------------- pillars -->
<section class="section">
  <div class="wrap">
    <div class="section-head section-head--center">
      <span class="eyebrow">Why patients choose us</span>
      <h2>A dental experience built on trust</h2>
      <p>Clinical care is the easy part to promise. These are the things we actually hold ourselves to.</p>
    </div>
    <div class="grid grid--3">
      <?php foreach (PILLARS as $p): ?>
      <div class="card" data-reveal>
        <span class="icon-badge icon-badge--soft"><?= icon($p['icon']) ?></span>
        <h3><?= e($p['title']) ?></h3>
        <p><?= e($p['text']) ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- -------------------------------------------------------- the clinic -->
<section class="section section--tint" id="clinic">
  <div class="wrap">
    <div class="section-head section-head--center">
      <span class="eyebrow">Our standards</span>
      <h2>Built around hygiene and doing things properly</h2>
      <p>Sterilisation, equipment and how treatment decisions get made &mdash; the parts
         of a clinic that matter most and are hardest to see.</p>
    </div>

    <div class="grid grid--3 mt-3">
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

    <div class="btn-row mt-3" style="justify-content:center">
      <a class="btn btn--ghost" href="/gallery">More about the clinic <?= icon('arrow') ?></a>
    </div>
  </div>
</section>

<!-- ------------------------------------------------------------ reviews -->
<section class="section">
  <div class="wrap">
    <div class="grid grid--2" style="align-items:center">
      <div>
        <span class="eyebrow">Patient feedback</span>
        <h2>Rated 5.0 out of 5 on Google</h2>
        <p class="lede">
          Every review on our Google Business Profile is written by a real patient, unedited and
          unincentivised. We would rather point you there than print selected quotes here.
        </p>
        <div class="btn-row mt-2">
          <a class="btn btn--navy" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">
            <?= icon('star') ?> Read reviews on Google
          </a>
          <a class="btn btn--ghost" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">
            Leave a review
          </a>
        </div>
      </div>
      <div class="card" style="text-align:center;padding:2.4rem">
        <div style="font-size:3.6rem;font-weight:800;color:var(--teal-600);line-height:1">5.0</div>
        <div class="review__stars" style="font-size:1.35rem">★★★★★</div>
        <p style="margin-top:.6rem">Average rating from verified Google reviews of<br>
          <strong>ORADENT Dental Care, <?= e(CLINIC['locality']) ?></strong></p>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------------------------- booking CTA -->
<section class="section" style="padding-top:0">
  <div class="wrap">
    <div class="cta-band">
      <div>
        <h2>Book in under a minute</h2>
        <p>Pick a date, pick a free slot, and your appointment is confirmed on the spot —
           any time in the next <?= BOOKING_WINDOW_DAYS ?> days. Prefer to talk? Call or WhatsApp us.</p>
      </div>
      <div class="btn-row">
        <a class="btn btn--primary btn--lg" href="/book"><?= icon('calendar') ?> Book online</a>
        <a class="btn btn--on-dark btn--lg" href="<?= e(tel_link()) ?>"><?= icon('phone') ?> Call now</a>
      </div>
    </div>
  </div>
</section>

<!-- ---------------------------------------------------------------- faq -->
<section class="section section--tint">
  <div class="wrap">
    <div class="grid grid--2" style="align-items:start;gap:clamp(2rem,5vw,4rem)">
      <div class="section-head" style="margin-bottom:0">
        <span class="eyebrow">Common questions</span>
        <h2>Everything patients ask before their first visit</h2>
        <p>Cannot see your question? Call us on <a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a>
           — we would rather answer it than have you worry about it.</p>
        <div class="btn-row mt-2"><a class="btn btn--ghost" href="/faq">All FAQs <?= icon('arrow') ?></a></div>
      </div>
      <div class="faq">
        <?php foreach (array_slice(GENERAL_FAQS, 0, 6) as $i => [$q, $a]): ?>
        <details class="faq__item"<?= $i === 0 ? ' open' : '' ?>>
          <summary><?= e($q) ?></summary>
          <div class="faq__answer"><?= e($a) ?></div>
        </details>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- ----------------------------------------------------------- location -->
<section class="section">
  <div class="wrap">
    <div class="grid grid--2" style="gap:clamp(2rem,5vw,3.5rem);align-items:start">
      <div>
        <span class="eyebrow">Find us</span>
        <h2>In <?= e(CLINIC['locality']) ?>, right by the auto stand</h2>
        <ul class="contact-list mt-2">
          <li>
            <span class="icon-badge"><?= icon('pin') ?></span>
            <span><b>Clinic address</b>
              <?= e(CLINIC['street']) ?><br>
              <?= e(CLINIC['landmark']) ?><br>
              <?= e(CLINIC['locality'] . ', ' . CLINIC['city'] . ' ' . CLINIC['postal']) ?>
            </span>
          </li>
          <li>
            <span class="icon-badge"><?= icon('clock') ?></span>
            <span><b>Opening hours</b>
              Monday – Saturday<br>10:00 AM – 1:00 PM &amp; 5:00 PM – 9:00 PM<br>
              <span style="color:var(--ink-mute)">Sunday closed</span>
            </span>
          </li>
          <li>
            <span class="icon-badge"><?= icon('phone') ?></span>
            <span><b>Phone &amp; WhatsApp</b>
              <a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a>
            </span>
          </li>
        </ul>
        <div class="btn-row mt-3">
          <a class="btn btn--navy" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">
            <?= icon('directions') ?> Get directions
          </a>
          <a class="btn btn--ghost" href="/contact">Contact page</a>
        </div>
      </div>
      <div class="map-frame">
        <iframe
          src="https://www.google.com/maps?q=<?= e(CLINIC['maps_embed_q']) ?>&output=embed"
          title="Map showing ORADENT Dental Care in <?= e(CLINIC['locality']) ?>, <?= e(CLINIC['city']) ?>"
          loading="lazy" referrerpolicy="no-referrer-when-downgrade"
          allowfullscreen></iframe>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
