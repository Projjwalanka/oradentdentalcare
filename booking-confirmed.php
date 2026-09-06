<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/booking.php';
require_once __DIR__ . '/includes/seo.php';

$ref     = (string) ($_GET['ref'] ?? '');
$booking = find_booking($ref);

Page::$path        = '/booking-confirmed';
Page::$title       = 'Appointment confirmed | ORADENT Dental Care';
Page::$description = 'Your appointment at ORADENT Dental Care is confirmed.';
Page::$noindex     = true;   // never index a page keyed to a patient's reference

require __DIR__ . '/includes/header.php';
?>

<section class="section">
  <div class="wrap wrap-narrow">

    <?php if ($booking && $booking['status'] !== 'cancelled'):
        $when = new DateTimeImmutable($booking['appt_date'] . ' ' . $booking['appt_time']);
        $svc  = SERVICES[$booking['service_slug']]['name'] ?? $booking['service_slug'];
    ?>
      <div class="confirm-card">
        <div class="confirm-card__tick"><?= icon('check') ?></div>
        <h1 style="font-size:clamp(1.7rem,1.4rem+1.4vw,2.3rem)">Appointment confirmed</h1>
        <p class="lede">
          Thank you, <?= e($booking['patient_name']) ?>. Your slot is reserved —
          there is nothing else to do. Please arrive about five minutes early.
        </p>

        <?php /* The only place we know a booking actually completed — main.js
                  reports it once on load. See api/track.php. */ ?>
        <div class="ref-badge" data-booking-complete
             data-service="<?= e((string) ($booking['service_slug'] ?? '')) ?>"><?= e($booking['reference']) ?></div>

        <div class="summary-box" style="text-align:left">
          <dl>
            <dt>Treatment</dt><dd><?= e($svc) ?></dd>
            <dt>Date</dt><dd><?= e($when->format('l, j F Y')) ?></dd>
            <dt>Time</dt><dd><?= e($when->format('g:i A')) ?></dd>
            <dt>Patient</dt><dd><?= e($booking['patient_name']) ?></dd>
            <dt>Mobile</dt><dd><?= e($booking['phone']) ?></dd>
          </dl>
        </div>

        <div class="summary-box" style="text-align:left;margin-top:1rem;background:#fff">
          <p style="margin:0;font-size:.94rem;color:var(--ink-soft)">
            <strong style="display:block;margin-bottom:.3rem">Where to come</strong>
            <?= e(CLINIC['street']) ?><br>
            <?= e(CLINIC['landmark']) ?><br>
            <?= e(CLINIC['locality'] . ', ' . CLINIC['city'] . ' ' . CLINIC['postal']) ?>
          </p>
        </div>

        <div class="btn-row mt-3" style="justify-content:center">
          <a class="btn btn--navy" href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">
            <?= icon('directions') ?> Get directions
          </a>
          <a class="btn btn--wa" target="_blank" rel="noopener"
             href="<?= e(whatsapp_link('Hello ORADENT, this is about my appointment ' . $booking['reference'] . '.')) ?>">
            <?= icon('whatsapp') ?> Message us
          </a>
          <a class="btn btn--ghost" href="<?= e(tel_link()) ?>"><?= icon('phone') ?> Call</a>
        </div>

        <p class="field__hint" style="margin-top:1.6rem">
          Need to change or cancel? Call or WhatsApp us on
          <a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a> quoting
          <strong><?= e($booking['reference']) ?></strong>. Please give us as much notice as you can.
        </p>
      </div>

    <?php else: ?>
      <div class="confirm-card">
        <h1>We could not find that booking</h1>
        <p class="lede">
          The reference <strong><?= e($ref !== '' ? $ref : '—') ?></strong> does not match a live
          appointment. It may have been cancelled, or the link may be incomplete.
        </p>
        <div class="btn-row mt-2" style="justify-content:center">
          <a class="btn btn--primary" href="/book">Book an appointment</a>
          <a class="btn btn--ghost" href="<?= e(tel_link()) ?>"><?= icon('phone') ?> Call the clinic</a>
        </div>
      </div>
    <?php endif; ?>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
