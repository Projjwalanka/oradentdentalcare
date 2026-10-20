<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';
require_once __DIR__ . '/includes/showcase.php';
require_once __DIR__ . '/includes/media_lib.php';
require_once __DIR__ . '/includes/media.php';   // CLINIC_FEATURES

Page::$path        = '/';
Page::$title       = 'Dentist in ' . CLINIC['locality'] . ', ' . CLINIC['city'] . ' | ORADENT Dental Care — Dr. Nabanita Chaklader';
Page::$description = 'Calm, clear dental care for your family in ' . CLINIC['locality'] . ', ' . CLINIC['city']
                   . '. Dr. Nabanita Chaklader, BDS (WBUHS) — root canal treatment, implants, oral surgery '
                   . 'and children\'s dentistry. Open every day. Book online in under a minute.';
Page::$faqs        = array_slice(general_faqs(), 0, 6);

/* Every picture on this page comes from its own placeholder folder — drop a
   file in to change it, no code edit. See assets/images/README.md. */
$portrait    = media_first('hero');
$portraitAlt = media_first('dentist') ?? $portrait;   // "Meet your dentist"
$slides      = media_items('slider');                 // photos and videos, by filename

/**
 * The four paths a visitor actually arrives with. Deliberately phrased as the
 * problem the patient has, not the treatment we sell: someone in pain searches
 * "tooth pain", never "endodontics".
 */
$paths = [
    [
        'slug'  => 'root-canal-treatment',
        'icon'  => 'tooth-rct',
        'title' => 'Tooth pain or a root canal',
        'text'  => 'Sharp pain, throbbing at night, or a tooth you have been told needs an RCT.',
        'cta'   => 'Get it looked at',
    ],
    [
        'slug'  => 'scaling-and-polishing',
        'icon'  => 'clean',
        'title' => 'Cleaning and bleeding gums',
        'text'  => 'Tartar, stains, bad breath, or gums that bleed when you brush.',
        'cta'   => 'Book a cleaning',
    ],
    [
        'slug'  => 'dental-implants',
        'icon'  => 'implant',
        'title' => 'A missing tooth',
        'text'  => 'Replacing one or several teeth with an implant, bridge or denture.',
        'cta'   => 'See the options',
    ],
    [
        'slug'  => 'pediatric-dental-care',
        'icon'  => 'child',
        'title' => 'A dentist for my child',
        'text'  => 'First visits, decay in milk teeth, and getting a nervous child comfortable.',
        'cta'   => 'Bring them in',
    ],
];

$pageScripts = [];
require __DIR__ . '/includes/header.php';
?>

<!-- ================================================================== hero -->
<section class="hero hero--intent">
  <div class="wrap hero__in">
    <div class="hero__copy">
      <span class="pill"><?= icon('pin') ?> <?= e(CLINIC['locality']) ?></span>

      <h1>Calm, clear dental care for your family in <span><?= e(CLINIC['locality']) ?></span>.</h1>

      <p class="hero__lede">
        <strong style="color:#fff"><?= e(CLINIC['doctor']) ?></strong>, <?= e(CLINIC['doctor_qual']) ?> —
        root canal treatment, oral surgery and everyday family dentistry, explained before anything is done.
      </p>

      <div class="btn-row hero__cta">
        <a class="btn btn--primary btn--lg" href="/book"
           data-track="book_click" data-track-source="hero">
          <?= icon('calendar') ?> Book an appointment
        </a>
        <a class="btn btn--on-dark btn--lg" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"
           data-track="whatsapp_click" data-track-source="hero">
          <?= icon('whatsapp') ?> Ask on WhatsApp
        </a>
      </div>

      <ul class="proof">
        <li>
          <span class="proof__mark"><?= icon('star', '') ?></span>
          <span><b>5.0 on Google</b>Rated by our own patients</span>
        </li>
        <li>
          <span class="proof__mark"><?= icon('shield') ?></span>
          <span><b>Sterile, every time</b>Pouches opened at the chair</span>
        </li>
        <li>
          <span class="proof__mark"><?= icon('check-c') ?></span>
          <span><b>Costs explained first</b>Before treatment begins</span>
        </li>
      </ul>
    </div>

    <?php if ($portrait): ?>
    <div class="hero__portrait">
      <figure>
        <img src="<?= e($portrait['src']) ?>"
             <?= $portrait['srcset'] !== '' ? 'srcset="' . e($portrait['srcset']) . '" sizes="(min-width:940px) 34vw, 86vw"' : '' ?>
             width="<?= (int) $portrait['w'] ?>" height="<?= (int) $portrait['h'] ?>"
             alt="<?= e($portrait['alt']) ?>" fetchpriority="high" decoding="async">
        <figcaption>
          <b><?= e(CLINIC['doctor']) ?></b>
          <span><?= e(CLINIC['doctor_qual']) ?> · Dental Surgeon</span>
        </figcaption>
      </figure>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ====================================================== 1. what do you need -->
<section class="section" id="help">
  <div class="wrap">
    <div class="section-head section-head--center">
      <span class="eyebrow">Start here</span>
      <h2>How can we help?</h2>
      <p>Pick what brought you here and we will take you straight to it.</p>
    </div>

    <div class="paths">
      <?php foreach ($paths as $p): ?>
      <a class="path" href="/services/<?= e($p['slug']) ?>"
         data-track="service_select" data-track-source="home_chooser" data-track-detail="<?= e($p['slug']) ?>">
        <span class="path__icon"><?= icon($p['icon']) ?></span>
        <h3><?= e($p['title']) ?></h3>
        <p><?= e($p['text']) ?></p>
        <span class="path__cta"><?= e($p['cta']) ?> <?= icon('arrow') ?></span>
      </a>
      <?php endforeach; ?>
    </div>

    <p class="paths__all">
      Something else? <a href="/services">See all eight treatments <?= icon('arrow') ?></a>
    </p>
  </div>
</section>

<!-- ========================================================= 2. trust proof -->
<section class="section section--tint" id="reviews">
  <div class="wrap">
    <div class="rating">
      <div class="rating__score">
        <div class="rating__number">5.0</div>
        <div class="rating__stars" aria-hidden="true">★★★★★</div>
        <p class="rating__meta">Average rating on Google</p>
      </div>
      <div class="rating__body">
        <h2>Rated 5.0 out of 5 by our patients</h2>
        <p>
          Every review on our Google Business Profile is written by a real patient — unedited and
          never incentivised. Rather than print selected quotes here, we would rather you read all
          of them at the source and judge for yourself.
        </p>
        <div class="btn-row mt-2">
          <a class="btn btn--navy" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">
            <?= icon('star') ?> Read every review on Google
          </a>
          <a class="btn btn--ghost" href="/book" data-track="book_click" data-track-source="reviews">
            <?= icon('calendar') ?> Book an appointment
          </a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ====================================================== 3. meet the dentist -->
<section class="section" id="dentist">
  <div class="wrap doctor">
    <?php if ($portraitAlt): ?>
    <div class="doctor__photo" data-reveal>
      <picture>
        <img src="<?= e($portraitAlt['src']) ?>"
             <?= $portraitAlt['srcset'] !== '' ? 'srcset="' . e($portraitAlt['srcset']) . '" sizes="(min-width:940px) 34vw, 92vw"' : '' ?>
             width="<?= (int) $portraitAlt['w'] ?>" height="<?= (int) $portraitAlt['h'] ?>"
             loading="lazy" decoding="async" alt="<?= e($portraitAlt['alt']) ?>">
      </picture>
    </div>
    <?php endif; ?>
    <div>
      <span class="eyebrow">Meet your dentist</span>
      <h2><?= e(CLINIC['doctor']) ?></h2>
      <p class="lede"><?= e(CLINIC['doctor_qual']) ?> — Dental Surgeon</p>
      <p>
        Dr. Chaklader founded ORADENT to bring careful, unhurried dentistry to
        <?= e(CLINIC['locality']) ?>. She does the complex work herself rather than referring it
        on, so the dentist who examines you is the one who treats you.
      </p>
      <ul class="doctor__creds">
        <li><?= icon('check-c') ?><span><strong>BDS</strong>, The West Bengal University of Health Sciences — registered dental surgeon.</span></li>
        <li><?= icon('check-c') ?><span><strong>Clinical focus:</strong> root canal treatment, and oral &amp; maxillofacial surgery.</span></li>
        <li><?= icon('check-c') ?><span><strong>Gentle with anxious patients.</strong> Nothing is done without explaining it first.</span></li>
      </ul>
      <div class="btn-row">
        <a class="btn btn--navy" href="/about">Read her full profile <?= icon('arrow') ?></a>
        <a class="btn btn--ghost" href="/book" data-track="book_click" data-track-source="dentist">
          Book a consultation
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ===================================================== 4. what to expect -->
<section class="section section--tint" id="what-to-expect">
  <div class="wrap">
    <div class="section-head section-head--center">
      <span class="eyebrow">What to expect</span>
      <h2>You will know the cost before treatment starts</h2>
      <p>Nobody enjoys the uncertainty more than the dentistry. Here is exactly how a first
         visit runs, so there are no surprises in the chair.</p>
    </div>

    <ol class="steps">
      <li class="steps__item">
        <span class="steps__n">1</span>
        <h3>Diagnosis first</h3>
        <p>We examine the tooth, take an X-ray if it is needed, and tell you plainly what is
           actually wrong — before any treatment is suggested.</p>
      </li>
      <li class="steps__item">
        <span class="steps__n">2</span>
        <h3>Options and an estimated cost</h3>
        <p>Usually there is more than one way forward. You get each option, what it involves,
           how long it lasts and what it will cost — then you decide, not us.</p>
      </li>
      <li class="steps__item">
        <span class="steps__n">3</span>
        <h3>Treatment, or a booking for it</h3>
        <p>Simple work is often done the same visit. Anything longer is booked for a time that
           suits you, with the plan written down before you leave.</p>
      </li>
    </ol>

    <div class="btn-row" style="justify-content:center;margin-top:2rem">
      <a class="btn btn--primary btn--lg" href="/book" data-track="book_click" data-track-source="what_to_expect">
        <?= icon('calendar') ?> Book an appointment
      </a>
    </div>
  </div>
</section>

<?php if ($slides): ?>
<!-- ============================================ 5. inside the clinic slider -->
<section class="section clinic-tour" id="inside">
  <div class="wrap">
    <div class="section-head section-head--center">
      <span class="eyebrow">Inside the clinic</span>
      <h2>See where you will be treated</h2>
      <p>Real photographs and video of the clinic &mdash; not stock images.</p>
    </div>

    <?php showcase($slides, 'Inside ORADENT Dental Care'); ?>

    <div class="btn-row mt-3" style="justify-content:center">
      <a class="btn btn--ghost" href="/gallery">Open the full gallery <?= icon('arrow') ?></a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ============================================== 6. the clinic (lower down) -->
<section class="section" id="clinic"<?= $slides ? ' style="padding-top:0"' : '' ?>>
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

    <?php if (!$slides): /* otherwise the slider above already links there */ ?>
    <div class="btn-row mt-3" style="justify-content:center">
      <a class="btn btn--ghost" href="/gallery">See the clinic and videos <?= icon('arrow') ?></a>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- ------------------------------------------------------------ location -->
<section class="section section--tint">
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
              <?php foreach (hours_summary() as $row): ?>
                <?= e($row['days_full']) ?><br>
                <?php if ($row['open']): ?>
                  <?= e(implode(' & ', $row['sessions'])) ?><br>
                <?php else: ?>
                  <span style="color:var(--ink-mute)">Closed</span><br>
                <?php endif; ?>
              <?php endforeach; ?>
            </span>
          </li>
          <li>
            <span class="icon-badge"><?= icon('phone') ?></span>
            <span><b>Phone &amp; WhatsApp</b>
              <a href="<?= e(tel_link()) ?>" data-track="call_click" data-track-source="location"><?= e(CLINIC['phone_display']) ?></a>
            </span>
          </li>
        </ul>
        <div class="btn-row mt-3">
          <a class="btn btn--navy" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener"
             data-track="directions_click" data-track-source="location">
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

<!-- ---------------------------------------------------------------- faq -->
<section class="section">
  <div class="wrap">
    <div class="grid grid--2" style="align-items:start;gap:clamp(2rem,5vw,4rem)">
      <div class="section-head" style="margin-bottom:0">
        <span class="eyebrow">Common questions</span>
        <h2>Everything patients ask before their first visit</h2>
        <p>Cannot see your question? Call us on
           <a href="<?= e(tel_link()) ?>" data-track="call_click" data-track-source="faq"><?= e(CLINIC['phone_display']) ?></a>
           — we would rather answer it than have you worry about it.</p>
        <div class="btn-row mt-2"><a class="btn btn--ghost" href="/faq">All FAQs <?= icon('arrow') ?></a></div>
      </div>
      <div class="faq">
        <?php foreach (array_slice(general_faqs(), 0, 6) as $i => [$q, $a]): ?>
        <details class="faq__item"<?= $i === 0 ? ' open' : '' ?>>
          <summary><?= e($q) ?></summary>
          <div class="faq__answer"><?= e($a) ?></div>
        </details>
        <?php endforeach; ?>
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
        <a class="btn btn--primary btn--lg" href="/book" data-track="book_click" data-track-source="footer_cta">
          <?= icon('calendar') ?> Book online
        </a>
        <a class="btn btn--on-dark btn--lg" href="<?= e(tel_link()) ?>" data-track="call_click" data-track-source="footer_cta">
          <?= icon('phone') ?> Call now
        </a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
