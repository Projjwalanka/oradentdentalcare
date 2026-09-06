<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/booking.php';
require_once __DIR__ . '/includes/seo.php';

Page::$path        = '/book';
Page::$title       = 'Book a Dental Appointment in Sonarpur, Kolkata | ORADENT Dental Care';
Page::$description = 'Book your dental appointment at ORADENT Dental Care, Sonarpur, Kolkata. Pick a free slot up to 30 days ahead and get instant confirmation. Mon–Sat, 10 AM–1 PM and 5–9 PM.';
Page::$breadcrumbs = [
    ['name' => 'Home', 'url' => url()],
    ['name' => 'Book an appointment', 'url' => url('book')],
];

/* --------------------------------------------- no-JavaScript form handling -- */
$formError = null;
$formErrors = [];

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_check($_POST['csrf'] ?? null)) {
        $formError = 'Your session expired. Please try again.';
    } else {
        $res = create_booking($_POST);
        if ($res['ok']) {
            header('Location: /booking-confirmed?ref=' . rawurlencode($res['data']['reference']), true, 303);
            exit;
        }
        $formError  = $res['message'] ?? 'Could not complete the booking.';
        $formErrors = $res['errors'] ?? [];
        if (isset($res['field'])) {
            $formErrors[$res['field']] = $res['message'];
        }
    }
}

[$winStart, $winEnd] = booking_window();
$preselect = (string) ($_GET['service'] ?? ($_POST['service'] ?? ''));
$dbUp      = db_available();

$pageScripts = ['assets/js/booking.js'];
require __DIR__ . '/includes/header.php';
?>

<div class="page-head">
  <div class="wrap">
    <nav class="crumbs" aria-label="Breadcrumb">
      <ol><li><a href="/">Home</a></li><li>Book an appointment</li></ol>
    </nav>
    <h1>Book an appointment</h1>
    <p>Pick a date and a free slot. Your appointment is confirmed straight away and you
       get a reference number — no waiting for a call back.</p>
  </div>
</div>

<section class="section">
  <div class="wrap">

    <?php if (!$dbUp): ?>
      <div class="alert alert--warn" role="alert">
        <strong>Online booking is temporarily unavailable.</strong><br>
        Please call or WhatsApp us on <a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a>
        and we will book you in right away.
      </div>
    <?php endif; ?>

    <div class="booking" id="booking"
         data-phone="<?= e(CLINIC['phone_display']) ?>"
         data-initial-date="<?= e((string) ($_POST['date'] ?? '')) ?>">

      <div class="booking__panel">
        <div class="alert" data-alert hidden></div>
        <?php if ($formError): ?>
          <div class="alert alert--bad" role="alert"><?= e($formError) ?></div>
        <?php endif; ?>

        <form method="post" action="/book" novalidate>
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="date" value="<?= e((string) ($_POST['date'] ?? '')) ?>">
          <input type="hidden" name="time" value="<?= e((string) ($_POST['time'] ?? '')) ?>">
          <div style="position:absolute;left:-9999px" aria-hidden="true">
            <label>Leave this empty <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
          </div>

          <!-- ---------------------------------------------------- step 1 -->
          <div class="step">
            <div class="step__head">
              <span class="step__num">1</span>
              <span class="step__title">Choose a date</span>
            </div>

            <div class="datestrip" data-datestrip>
              <div class="datestrip__track">
                <?php
                for ($i = 0; $i <= BOOKING_WINDOW_DAYS; $i++) {
                    $d      = $winStart->modify("+$i days");
                    $iso    = $d->format('Y-m-d');
                    $closed = empty(OPENING_HOURS[(int) $d->format('N')]);
                    ?>
                    <button type="button" class="datechip" data-date="<?= e($iso) ?>"
                            aria-pressed="false" <?= $closed ? 'disabled' : '' ?>
                            aria-label="<?= e($d->format('l, j F Y')) . ($closed ? ' — closed' : '') ?>">
                      <span class="datechip__dow"><?= e($d->format('D')) ?></span>
                      <span class="datechip__day"><?= e($d->format('j')) ?></span>
                      <span class="datechip__mon"><?= e($d->format('M')) ?></span>
                    </button>
                <?php } ?>
              </div>
              <p class="field__hint">
                Bookings open <?= BOOKING_WINDOW_DAYS ?> days ahead
                (until <?= e($winEnd->format('j M Y')) ?>). The clinic is closed on Sundays.
              </p>
              <?php if (isset($formErrors['date'])): ?>
                <p class="field__error"><?= e($formErrors['date']) ?></p>
              <?php endif; ?>
            </div>
          </div>

          <!-- ---------------------------------------------------- step 2 -->
          <div class="step">
            <div class="step__head">
              <span class="step__num">2</span>
              <span class="step__title">Pick a time <span data-day-label style="font-weight:500;color:var(--ink-mute)"></span></span>
            </div>

            <div data-slots aria-live="polite">
              <noscript>
                <p class="field__hint">
                  JavaScript is off, so live availability cannot be shown. Choose a date and time
                  below and we will confirm it — or call <?= e(CLINIC['phone_display']) ?>.
                </p>
                <label class="field">
                  <span class="field__label">Date</span>
                  <input class="input" type="date" name="date"
                         min="<?= e($winStart->format('Y-m-d')) ?>" max="<?= e($winEnd->format('Y-m-d')) ?>" required>
                </label>
                <label class="field">
                  <span class="field__label">Time</span>
                  <select class="select" name="time" required>
                    <option value="">Select a time…</option>
                    <?php foreach (slots_for_date($winStart->modify('+1 day')->format('Y-m-d')) as $s): ?>
                      <option value="<?= e($s['time']) ?>"><?= e($s['label'] ?? fmt_time($s['time'])) ?> (<?= e($s['session']) ?>)</option>
                    <?php endforeach; ?>
                  </select>
                </label>
              </noscript>
              <div class="slots-empty">Choose a date above to see available times.</div>
            </div>
            <?php if (isset($formErrors['time'])): ?>
              <p class="field__error"><?= e($formErrors['time']) ?></p>
            <?php endif; ?>
          </div>

          <!-- ---------------------------------------------------- step 3 -->
          <div class="step">
            <div class="step__head">
              <span class="step__num">3</span>
              <span class="step__title">Your details</span>
            </div>

            <label class="field">
              <span class="field__label">Treatment needed <span class="req">*</span></span>
              <select class="select" name="service" required
                      <?= isset($formErrors['service']) ? 'aria-invalid="true"' : '' ?>>
                <option value="">Select a treatment…</option>
                <?php foreach (SERVICES as $slug => $s): ?>
                  <option value="<?= e($slug) ?>"<?= $preselect === $slug ? ' selected' : '' ?>><?= e($s['name']) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($formErrors['service'])): ?><p class="field__error"><?= e($formErrors['service']) ?></p><?php endif; ?>
            </label>

            <div class="form-grid form-grid--2">
              <label class="field">
                <span class="field__label">Full name <span class="req">*</span></span>
                <input class="input" type="text" name="name" required autocomplete="name"
                       value="<?= e((string) ($_POST['name'] ?? '')) ?>"
                       <?= isset($formErrors['name']) ? 'aria-invalid="true"' : '' ?>>
                <?php if (isset($formErrors['name'])): ?><p class="field__error"><?= e($formErrors['name']) ?></p><?php endif; ?>
              </label>

              <label class="field">
                <span class="field__label">Mobile number <span class="req">*</span></span>
                <input class="input" type="tel" name="phone" required autocomplete="tel"
                       inputmode="numeric" placeholder="98301 69230"
                       value="<?= e((string) ($_POST['phone'] ?? '')) ?>"
                       <?= isset($formErrors['phone']) ? 'aria-invalid="true"' : '' ?>>
                <span class="field__hint">We will call or WhatsApp this number if anything changes.</span>
                <?php if (isset($formErrors['phone'])): ?><p class="field__error"><?= e($formErrors['phone']) ?></p><?php endif; ?>
              </label>
            </div>

            <label class="field">
              <span class="field__label">Email <span style="font-weight:500;color:var(--ink-mute)">(optional)</span></span>
              <input class="input" type="email" name="email" autocomplete="email"
                     value="<?= e((string) ($_POST['email'] ?? '')) ?>">
            </label>

            <label class="field">
              <span class="field__label">Anything we should know? <span style="font-weight:500;color:var(--ink-mute)">(optional)</span></span>
              <textarea class="textarea" name="notes" maxlength="1000"
                        placeholder="Pain, swelling, medical conditions, medicines you take, or if you are nervous about the visit."><?= e((string) ($_POST['notes'] ?? '')) ?></textarea>
            </label>

            <label class="field" style="display:flex;gap:.6rem;align-items:flex-start">
              <input type="checkbox" name="first_visit" value="1" style="margin-top:.35rem;width:18px;height:18px"
                     <?= !empty($_POST['first_visit']) ? 'checked' : '' ?>>
              <span style="font-size:.93rem;color:var(--ink-soft)">This is my first visit to ORADENT</span>
            </label>
          </div>

          <div class="summary-box" style="margin-bottom:1.3rem">
            <dl>
              <dt>Date</dt><dd data-sum-date>—</dd>
              <dt>Time</dt><dd data-sum-time>—</dd>
              <dt>Treatment</dt><dd data-sum-service>—</dd>
              <dt>Clinic</dt><dd><?= e(CLINIC['locality']) ?>, <?= e(CLINIC['city']) ?></dd>
            </dl>
          </div>

          <button class="btn btn--primary btn--lg btn--block" type="submit" data-submit
                  <?= $dbUp ? '' : 'disabled' ?>>
            <?= icon('check') ?> Confirm appointment
          </button>
          <p class="field__hint text-center" style="margin-top:.8rem">
            By booking you agree to our <a href="/terms">terms</a> and <a href="/privacy">privacy policy</a>.
            No payment is taken online.
          </p>
        </form>
      </div>

      <!-- ------------------------------------------------------- aside -->
      <aside>
        <div class="aside-card">
          <h3>How booking works</h3>
          <ul>
            <li><?= icon('check-c') ?><span>Pick any free slot, up to <?= BOOKING_WINDOW_DAYS ?> days ahead.</span></li>
            <li><?= icon('check-c') ?><span>Confirmed instantly — you get a reference number on screen.</span></li>
            <li><?= icon('check-c') ?><span>No payment online. You pay at the clinic after treatment.</span></li>
            <li><?= icon('check-c') ?><span>Need to change it? Call or WhatsApp with your reference.</span></li>
          </ul>
        </div>

        <div class="card" style="margin-top:1.2rem">
          <h3 style="font-size:1.02rem">In pain right now?</h3>
          <p>Do not wait for an online slot. Call us and we will fit you in the same session
             wherever we can.</p>
          <div class="btn-row" style="margin-top:1rem;flex-direction:column">
            <a class="btn btn--navy btn--block" href="<?= e(tel_link()) ?>">
              <?= icon('phone') ?> <?= e(CLINIC['phone_display']) ?>
            </a>
            <a class="btn btn--wa btn--block" target="_blank" rel="noopener"
               href="<?= e(whatsapp_link('Hello ORADENT, I have dental pain and need an urgent appointment.')) ?>">
              <?= icon('whatsapp') ?> WhatsApp
            </a>
          </div>
        </div>

        <div class="card" style="margin-top:1.2rem">
          <h3 style="font-size:1.02rem">Opening hours</h3>
          <table class="hours-table" style="margin-top:.6rem">
            <tbody>
              <?php
              $names = [1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
              for ($d = 1; $d <= 7; $d++):
                  $sessions = OPENING_HOURS[$d];
                  $jsDay    = $d % 7; // JS getDay(): Sunday = 0
              ?>
              <tr data-day="<?= $jsDay ?>" class="<?= $sessions ? '' : 'is-shut' ?>">
                <th scope="row"><?= e(substr($names[$d], 0, 3)) ?></th>
                <td>
                  <?php if ($sessions): ?>
                    <?= e(fmt_time($sessions[0][0]) . '–' . fmt_time($sessions[0][1])) ?><br>
                    <?= e(fmt_time($sessions[1][0]) . '–' . fmt_time($sessions[1][1])) ?>
                  <?php else: ?>Closed<?php endif; ?>
                </td>
              </tr>
              <?php endfor; ?>
            </tbody>
          </table>
        </div>
      </aside>

    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
