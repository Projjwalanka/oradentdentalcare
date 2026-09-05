<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/seo.php';

/* Every service FAQ is folded in so this page answers the long tail too. */
$all = GENERAL_FAQS;
foreach (SERVICES as $slug => $s) {
    foreach ($s['faqs'] as [$q, $a]) {
        $all[] = [$s['name'] . ': ' . $q, $a];
    }
}

Page::$path        = '/faq';
Page::$title       = 'Patient FAQs | ORADENT Dental Care, Sonarpur, Kolkata';
Page::$description = 'Answers to the questions patients ask most — opening hours, booking, costs, root canals, extractions, implants, children\'s dentistry and sterilisation at ORADENT Dental Care, Sonarpur.';
Page::$faqs        = array_slice($all, 0, 30);
Page::$breadcrumbs = [
    ['name' => 'Home', 'url' => url()],
    ['name' => 'FAQ',  'url' => url('faq')],
];

require __DIR__ . '/includes/header.php';
?>
<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol><li><a href="/">Home</a></li><li>FAQ</li></ol>
    </nav>
    <h1>Patient questions, answered</h1>
    <p>The things people ask us on the phone every week — about visiting, about costs, and about
       what each treatment actually involves.</p>
  </div>
</div>

<section class="section">
  <div class="wrap" style="display:grid;gap:clamp(2rem,5vw,3.5rem)">
    <div style="display:grid;gap:clamp(2rem,5vw,3rem)" class="faq-layout">
      <style>@media (min-width:1000px){.faq-layout{grid-template-columns:1fr 320px}}</style>

      <div>
        <h2 style="margin-top:0">About visiting the clinic</h2>
        <div class="faq">
          <?php foreach (GENERAL_FAQS as $i => [$q, $a]): ?>
          <details class="faq__item"<?= $i === 0 ? ' open' : '' ?>>
            <summary><?= e($q) ?></summary>
            <div class="faq__answer"><?= e($a) ?></div>
          </details>
          <?php endforeach; ?>
        </div>

        <?php foreach (SERVICES as $slug => $s): ?>
          <h2 id="<?= e($slug) ?>"><?= e($s['name']) ?></h2>
          <div class="faq">
            <?php foreach ($s['faqs'] as [$q, $a]): ?>
            <details class="faq__item">
              <summary><?= e($q) ?></summary>
              <div class="faq__answer"><?= e($a) ?>
                <p style="margin-top:.9rem"><a href="/services/<?= e($slug) ?>">Read more about <?= e($s['name']) ?> →</a></p>
              </div>
            </details>
            <?php endforeach; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <aside>
        <div class="aside-card" style="position:sticky;top:calc(var(--header-h) + 16px)">
          <h3>Still unsure?</h3>
          <p style="font-size:.94rem">Ask us directly — a two-minute conversation usually settles it.</p>
          <div class="btn-row" style="margin-top:1.1rem;flex-direction:column">
            <a class="btn btn--primary btn--block" href="/book">Book an appointment</a>
            <a class="btn btn--wa btn--block" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp</a>
            <a class="btn btn--on-dark btn--block" href="<?= e(tel_link()) ?>"><?= icon('phone') ?> Call</a>
          </div>
        </div>
      </aside>
    </div>
  </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
