<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/seo.php';
Page::$path = '/terms';
Page::$title = 'Terms of Use | ORADENT Dental Care';
Page::$description = 'Terms governing the use of the ORADENT Dental Care website and its online appointment booking.';
Page::$breadcrumbs = [['name'=>'Home','url'=>url()],['name'=>'Terms','url'=>url('terms')]];
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div class="wrap">
  <nav class="crumbs" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li>Terms</li></ol></nav>
  <h1>Terms of use</h1><p>Last updated <?= date('j F Y') ?></p>
</div></div>
<section class="section"><div class="wrap wrap-narrow prose">
  <h2>Information, not diagnosis</h2>
  <p>The treatment descriptions on this website are general information about dental procedures.
     They are not a diagnosis and not a substitute for examination by a dentist. What is right for
     your tooth can only be decided after an examination and, usually, a radiograph.</p>
  <h2>Appointments</h2>
  <p>A slot booked here is confirmed immediately, subject to availability at the moment of booking.
     Please arrive about five minutes early. If you cannot attend, call or WhatsApp us on
     <a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a> with your reference number
     so the slot can be offered to another patient.</p>
  <p>We may need to reschedule an appointment because of an emergency case or unforeseen closure. If
     that happens we will contact you on the mobile number you gave.</p>
  <h2>Emergencies</h2>
  <p>Do not use the online booking system for a dental emergency. If you have severe pain, facial
     swelling, uncontrolled bleeding or a knocked-out tooth, call the clinic directly. If you have
     difficulty breathing or swallowing, seek immediate hospital care.</p>
  <h2>Fees</h2>
  <p>No payment is taken through this website. Fees are explained and agreed at the clinic before
     treatment begins. Estimates given before an examination are indicative only.</p>
  <h2>Acceptable use</h2>
  <p>Please do not make speculative or duplicate bookings, or attempt to disrupt the booking system.
     We may cancel bookings that appear to be made in bad faith.</p>
  <h2>Content</h2>
  <p>The text, photographs and design of this site belong to <?= e(CLINIC['name']) ?>. The clinic
     photographs show our actual premises and may not be reused without permission.</p>
  <h2>Contact</h2>
  <p><?= e(CLINIC['name']) ?>, <?= e(full_address()) ?>. Telephone
     <a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a>.</p>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
