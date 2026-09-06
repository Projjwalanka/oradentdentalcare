<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/seo.php';

Page::$path        = '/contact';
Page::$title       = 'Contact & Directions | ORADENT Dental Care, Parnasree, Behala, Kolkata';
Page::$description = 'Contact ORADENT Dental Care, ' . CLINIC['locality'] . ', ' . CLINIC['city'] . ' ' . CLINIC['postal']
                   . ' — phone ' . CLINIC['phone_display'] . ', WhatsApp, directions to ' . CLINIC['street']
                   . ', and opening hours.';
Page::$breadcrumbs = [
    ['name' => 'Home',    'url' => url()],
    ['name' => 'Contact', 'url' => url('contact')],
];

/* -------------------------------------------------------- contact form ---- */
$sent = false; $cErr = null; $cErrors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $cErr = 'Your session expired. Please try again.';
    } elseif (trim((string) ($_POST['website'] ?? '')) !== '') {
        $sent = true;                                   // honeypot: pretend success
    } else {
        $name  = trim((string) ($_POST['name'] ?? ''));
        $phone = normalise_phone((string) ($_POST['phone'] ?? ''));
        $email = trim((string) ($_POST['email'] ?? ''));
        $msg   = trim((string) ($_POST['message'] ?? ''));
        $subj  = mb_substr(trim((string) ($_POST['subject'] ?? '')), 0, 190);

        if (mb_strlen($name) < 2)  { $cErrors['name']    = 'Please enter your name.'; }
        if ($phone === null)       { $cErrors['phone']   = 'Enter a valid 10-digit mobile number.'; }
        if (mb_strlen($msg) < 5)   { $cErrors['message'] = 'Please tell us how we can help.'; }
        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $cErrors['email'] = 'That email address does not look right.';
        }

        if (!$cErrors) {
            if (!db_available()) {
                $cErr = 'Our message system is temporarily down. Please call or WhatsApp us instead.';
            } else {
                $st = db()->prepare(
                    'INSERT INTO contact_messages (name, phone, email, subject, message, ip)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $st->execute([$name, $phone, $email !== '' ? $email : null,
                              $subj !== '' ? $subj : null, mb_substr($msg, 0, 3000), client_ip()]);
                $sent = true;
            }
        } else {
            $cErr = 'Please check the highlighted fields.';
        }
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol><li><a href="/">Home</a></li><li>Contact</li></ol>
    </nav>
    <h1>Contact &amp; directions</h1>
    <p>We are at <?= e(CLINIC['street']) ?> in <?= e(CLINIC['locality']) ?>,
       <?= e(lcfirst(CLINIC['landmark'])) ?>.</p>
  </div>
</div>

<section class="section">
  <div class="wrap">
    <div class="grid grid--2" style="gap:clamp(2rem,5vw,3.5rem);align-items:start">

      <div>
        <span class="eyebrow">Reach us</span>
        <h2>The fastest ways to get an answer</h2>

        <ul class="contact-list mt-2">
          <li>
            <span class="icon-badge"><?= icon('phone') ?></span>
            <span><b>Call the clinic</b>
              <a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a><br>
              <span style="color:var(--ink-mute);font-size:.87rem">Best during consulting hours</span>
            </span>
          </li>
          <li>
            <span class="icon-badge" style="background:linear-gradient(140deg,#25d366,#128c4a)"><?= icon('whatsapp') ?></span>
            <span><b>WhatsApp</b>
              <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">Message <?= e(CLINIC['phone_display']) ?></a><br>
              <span style="color:var(--ink-mute);font-size:.87rem">Send a photo of the problem tooth if it helps</span>
            </span>
          </li>
          <li>
            <span class="icon-badge"><?= icon('mail') ?></span>
            <span><b>Email</b>
              <a href="mailto:<?= e(CLINIC['email']) ?>"><?= e(CLINIC['email']) ?></a>
            </span>
          </li>
          <li>
            <span class="icon-badge"><?= icon('pin') ?></span>
            <span><b>Clinic address</b>
              <?= e(CLINIC['street']) ?><br>
              <?= e(CLINIC['landmark']) ?><br>
              <?= e(CLINIC['locality'] . ', ' . CLINIC['city'] . ' ' . CLINIC['postal']) ?><br>
              <a href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">Open in Google Maps →</a>
            </span>
          </li>
        </ul>

        <h3 style="margin-top:2.4rem">Opening hours</h3>
        <table class="hours-table">
          <tbody>
            <?php
            $names = [1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            for ($d = 1; $d <= 7; $d++):
                $sessions = OPENING_HOURS[$d];
            ?>
            <tr data-day="<?= $d % 7 ?>" class="<?= $sessions ? '' : 'is-shut' ?>">
              <th scope="row"><?= e($names[$d]) ?></th>
              <td>
                <?php if ($sessions): ?>
                  <?= e(fmt_time($sessions[0][0]) . ' – ' . fmt_time($sessions[0][1])) ?> &nbsp;·&nbsp;
                  <?= e(fmt_time($sessions[1][0]) . ' – ' . fmt_time($sessions[1][1])) ?>
                <?php else: ?>Closed<?php endif; ?>
              </td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>

        <div class="alert alert--warn" style="margin-top:1.6rem">
          <strong>In severe pain or swelling?</strong> Please call rather than emailing —
          we keep slots free each session for emergencies.
        </div>
      </div>

      <div>
        <div class="booking__panel">
          <h2 style="font-size:1.42rem;margin-top:0">Send us a message</h2>
          <p style="color:var(--ink-soft);font-size:.95rem">
            For appointments, the <a href="/book">booking page</a> is faster — it confirms your slot
            instantly. Use this form for questions.
          </p>

          <?php if ($sent): ?>
            <div class="alert alert--ok" role="status">
              <strong>Message sent.</strong> We will get back to you on the number you gave, usually the same day.
            </div>
          <?php elseif ($cErr): ?>
            <div class="alert alert--bad" role="alert"><?= e($cErr) ?></div>
          <?php endif; ?>

          <?php if (!$sent): ?>
          <form method="post" action="/contact" novalidate>
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <div style="position:absolute;left:-9999px" aria-hidden="true">
              <label>Leave empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <div class="form-grid form-grid--2">
              <label class="field">
                <span class="field__label">Your name <span class="req">*</span></span>
                <input class="input" type="text" name="name" required autocomplete="name"
                       value="<?= e((string) ($_POST['name'] ?? '')) ?>"
                       <?= isset($cErrors['name']) ? 'aria-invalid="true"' : '' ?>>
                <?php if (isset($cErrors['name'])): ?><p class="field__error"><?= e($cErrors['name']) ?></p><?php endif; ?>
              </label>
              <label class="field">
                <span class="field__label">Mobile <span class="req">*</span></span>
                <input class="input" type="tel" name="phone" required inputmode="numeric" autocomplete="tel"
                       value="<?= e((string) ($_POST['phone'] ?? '')) ?>"
                       <?= isset($cErrors['phone']) ? 'aria-invalid="true"' : '' ?>>
                <?php if (isset($cErrors['phone'])): ?><p class="field__error"><?= e($cErrors['phone']) ?></p><?php endif; ?>
              </label>
            </div>

            <label class="field">
              <span class="field__label">Email <span style="font-weight:500;color:var(--ink-mute)">(optional)</span></span>
              <input class="input" type="email" name="email" autocomplete="email"
                     value="<?= e((string) ($_POST['email'] ?? '')) ?>">
            </label>

            <label class="field">
              <span class="field__label">Subject</span>
              <input class="input" type="text" name="subject" maxlength="190"
                     placeholder="e.g. Cost of a root canal"
                     value="<?= e((string) ($_POST['subject'] ?? '')) ?>">
            </label>

            <label class="field">
              <span class="field__label">Message <span class="req">*</span></span>
              <textarea class="textarea" name="message" required maxlength="3000"
                        <?= isset($cErrors['message']) ? 'aria-invalid="true"' : '' ?>><?= e((string) ($_POST['message'] ?? '')) ?></textarea>
              <?php if (isset($cErrors['message'])): ?><p class="field__error"><?= e($cErrors['message']) ?></p><?php endif; ?>
            </label>

            <button class="btn btn--primary btn--block btn--lg" type="submit">Send message</button>
            <p class="field__hint text-center" style="margin-top:.8rem">
              We use your details only to reply to you — see our <a href="/privacy">privacy policy</a>.
            </p>
          </form>
          <?php endif; ?>
        </div>
      </div>

    </div>

    <div class="map-frame mt-3">
      <iframe src="https://www.google.com/maps?q=<?= e(CLINIC['maps_embed_q']) ?>&output=embed"
              title="Map to ORADENT Dental Care, <?= e(CLINIC['locality']) ?>, <?= e(CLINIC['city']) ?>"
              loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen></iframe>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
