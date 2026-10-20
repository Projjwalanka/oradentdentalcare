<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/seo.php';

Page::$path        = '/about';
Page::$title       = 'Dr. Nabanita Chaklader, BDS (WBUHS) | Dentist in Parnasree, Behala, Kolkata — ORADENT';
Page::$description = 'Meet Dr. Nabanita Chaklader, BDS (WBUHS) — dental surgeon at ORADENT Dental Care, Parnasree, Behala, Kolkata, with special interests in root canal treatment and oral & maxillofacial surgery.';
/* The portrait is the first file in assets/images/about/ — see assets/images/README.md. */
$portrait = media_first('about');
if ($portrait) {
    Page::$image = strtok($portrait['src'], '?');
}
Page::$type        = 'profile';
Page::$breadcrumbs = [
    ['name' => 'Home',  'url' => url()],
    ['name' => 'About', 'url' => url('about')],
];

require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol><li><a href="/">Home</a></li><li>About</li></ol>
    </nav>
    <h1>About <?= e(CLINIC['doctor']) ?></h1>
    <p>Dental surgeon · <?= e(CLINIC['doctor_qual']) ?> · Special interests in Root Canal Treatment
       and Oral &amp; Maxillofacial Surgery</p>
  </div>
</div>

<section class="section">
  <div class="wrap doctor">
    <?php if ($portrait): ?>
    <div class="doctor__photo">
      <img src="<?= e($portrait['src']) ?>"
           <?= $portrait['srcset'] !== '' ? 'srcset="' . e($portrait['srcset']) . '" sizes="(min-width: 900px) 38vw, 92vw"' : '' ?>
           width="<?= (int) $portrait['w'] ?>" height="<?= (int) $portrait['h'] ?>"
           alt="<?= e($portrait['alt']) ?>" fetchpriority="high" decoding="async">
    </div>
    <?php endif; ?>

    <div class="prose">
      <span class="eyebrow">The dentist who will treat you</span>
      <h2 style="margin-top:0">Careful dentistry, explained plainly</h2>
      <p class="lede">
        Dr. Nabanita Chaklader holds a Bachelor of Dental Surgery from The West Bengal University of
        Health Sciences and practises as a dental surgeon in <?= e(CLINIC['locality']) ?>,
        <?= e(CLINIC['city']) ?>. She has two declared special interests: root canal treatment, and
        oral &amp; maxillofacial surgery.
      </p>
      <p>
        Those two interests shape what ORADENT can offer. A great many general practices refer both
        difficult molar root canals and impacted wisdom teeth elsewhere. Here they are managed
        in-house, by the same dentist who examined you — which usually means fewer appointments,
        no second consultation fee, and continuity of care from diagnosis through to review.
      </p>

      <h2>How she works</h2>
      <p>
        Patients tend to describe two things about a visit here. The first is that they are told
        what is actually wrong, in ordinary language, with the X-ray on the screen in front of them.
        The second is that they are given the real options — including doing nothing, when that is
        reasonable — with the cost of each, before any decision is made.
      </p>
      <p>
        That matters most in dentistry, where the person recommending the treatment is also the
        person selling it. The standard we hold to is simple: recommend the treatment we would
        choose for our own family, and say plainly when a cheaper option would do the same job.
      </p>

      <h2>Nervous patients</h2>
      <p>
        A large share of adults avoid the dentist because of a bad experience years ago, often as a
        child. If that is you, say so when you book — it changes how the appointment is run, not how
        you are judged. First visits for anxious patients are examination and conversation only;
        nothing is done that has not been explained and agreed.
      </p>

      <h2>Sterilisation and safety</h2>
      <p>
        Every instrument that enters a patient's mouth is scrubbed, packed and autoclaved between
        uses. Needles, blades, gloves, suction tips and cups are single-use and discarded after one
        patient. Work surfaces, the chair and the light handles are disinfected between
        appointments. Sharps go to authorised bio-medical waste disposal.
      </p>

      <div class="factbar" style="margin-top:2.2rem">
        <div><b>BDS (WBUHS)</b>Qualification</div>
        <div><b>RCT &amp; Oral Surgery</b>Special interests</div>
        <div><b>All ages</b>Patients treated</div>
        <div><b>Mon – Sat</b>Consulting days</div>
      </div>

      <div class="btn-row mt-3">
        <a class="btn btn--primary" href="/book"><?= icon('calendar') ?> Book a consultation</a>
        <a class="btn btn--ghost" href="/services">Browse treatments</a>
      </div>
    </div>
  </div>
</section>

<section class="section section--tint">
  <div class="wrap">
    <div class="section-head section-head--center">
      <span class="eyebrow">What we hold ourselves to</span>
      <h2>The standards behind every appointment</h2>
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

<section class="section">
  <div class="wrap">
    <div class="cta-band">
      <div>
        <h2>Ready when you are</h2>
        <p>Book a slot online, or just call — we are happy to answer a question without an appointment.</p>
      </div>
      <div class="btn-row">
        <a class="btn btn--primary btn--lg" href="/book"><?= icon('calendar') ?> Book online</a>
        <a class="btn btn--on-dark btn--lg" href="<?= e(tel_link()) ?>"><?= icon('phone') ?> <?= e(CLINIC['phone_display']) ?></a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
