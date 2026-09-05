<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/seo.php';
Page::$path = '/privacy';
Page::$title = 'Privacy Policy | ORADENT Dental Care';
Page::$description = 'How ORADENT Dental Care collects, uses and protects the personal information you give us through this website.';
Page::$breadcrumbs = [['name'=>'Home','url'=>url()],['name'=>'Privacy','url'=>url('privacy')]];
require __DIR__ . '/includes/header.php';
?>
<div class="page-head"><div class="wrap">
  <nav class="crumbs" aria-label="Breadcrumb"><ol><li><a href="/">Home</a></li><li>Privacy</li></ol></nav>
  <h1>Privacy policy</h1><p>Last updated <?= date('j F Y') ?></p>
</div></div>
<section class="section"><div class="wrap wrap-narrow prose">
  <h2>What we collect</h2>
  <p>When you book an appointment or send a message through this website, we collect your name,
     mobile number, optional email address, the treatment you selected, and anything you choose to
     write in the notes. We also record the date and time of the request and the IP address it came
     from, which we use only to prevent abuse of the booking system.</p>
  <h2>Why we collect it</h2>
  <p>To reserve your appointment, to contact you if it needs to be changed, and to have your details
     ready when you arrive. We do not use your contact details for marketing, and we do not sell,
     rent or share them with third parties for their own purposes.</p>
  <h2>Clinical records</h2>
  <p>Information you give through this website is separate from your clinical record. Clinical notes
     are made and kept at the clinic in the ordinary course of dental practice, and are held
     confidentially in line with professional obligations.</p>
  <h2>How long we keep it</h2>
  <p>Appointment records are kept for as long as needed to run the practice and to meet record-keeping
     obligations. Website enquiry messages are deleted once they have been dealt with and are no
     longer needed.</p>
  <h2>Cookies</h2>
  <p>This site sets one strictly necessary cookie, a PHP session cookie, which protects the booking
     and contact forms against cross-site request forgery. It contains no personal information and
     is discarded when you close your browser. We do not use advertising or tracking cookies.</p>
  <h2>Third parties</h2>
  <p>Our pages embed a Google Maps frame so you can find the clinic; loading it means Google may
     receive your IP address and set its own cookies, under Google's privacy policy. WhatsApp and
     telephone links hand off to those apps on your own device.</p>
  <h2>Your choices</h2>
  <p>You can ask us what information we hold about you, ask for it to be corrected, or ask us to
     delete an appointment enquiry. Contact us on <a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a>
     or <a href="mailto:<?= e(CLINIC['email']) ?>"><?= e(CLINIC['email']) ?></a>.</p>
  <h2>Security</h2>
  <p>The site is served over HTTPS and booking data is stored in an access-controlled database.
     No system is perfectly secure, so please do not send sensitive medical details through the
     website forms — bring them to your appointment instead.</p>
</div></section>
<?php require __DIR__ . '/includes/footer.php'; ?>
