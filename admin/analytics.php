<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$user = require_admin();

/**
 * What the website is actually producing.
 *
 * Deliberately answers three questions and no more: how many people tried to
 * book, where on the page they were when they decided, and which problem
 * brought them in. Everything else is noise for a single clinic.
 */

$days = (int) ($_GET['days'] ?? 30);
$days = max(7, min(365, $days));

$tableMissing = false;
$totals = $bySource = $byService = $daily = [];

try {
    $pdo = db();

    $st = $pdo->prepare(
        "SELECT event, COUNT(*) AS n FROM ui_events
          WHERE created_at >= (NOW() - INTERVAL ? DAY)
          GROUP BY event ORDER BY n DESC"
    );
    $st->execute([$days]);
    foreach ($st->fetchAll() as $r) {
        $totals[$r['event']] = (int) $r['n'];
    }

    $st = $pdo->prepare(
        "SELECT source, COUNT(*) AS n FROM ui_events
          WHERE event = 'book_click' AND source IS NOT NULL
            AND created_at >= (NOW() - INTERVAL ? DAY)
          GROUP BY source ORDER BY n DESC"
    );
    $st->execute([$days]);
    $bySource = $st->fetchAll();

    $st = $pdo->prepare(
        "SELECT detail, COUNT(*) AS n FROM ui_events
          WHERE event = 'service_select' AND detail IS NOT NULL
            AND created_at >= (NOW() - INTERVAL ? DAY)
          GROUP BY detail ORDER BY n DESC"
    );
    $st->execute([$days]);
    $byService = $st->fetchAll();

    $st = $pdo->prepare(
        "SELECT `day`, COUNT(*) AS n,
                SUM(event = 'book_click')       AS books,
                SUM(event = 'booking_complete') AS completed
           FROM ui_events
          WHERE created_at >= (NOW() - INTERVAL ? DAY)
          GROUP BY `day` ORDER BY `day` DESC LIMIT 14"
    );
    $st->execute([$days]);
    $daily = $st->fetchAll();
} catch (Throwable $e) {
    $tableMissing = true;
}

$labels = [
    'book_click'       => 'Booking button clicked',
    'booking_complete' => 'Booking completed',
    'whatsapp_click'   => 'WhatsApp opened',
    'call_click'       => 'Phone number tapped',
    'directions_click' => 'Directions opened',
    'service_select'   => 'Service card chosen',
];

admin_head('What the site is doing', $user);
?>

<div class="adm-head">
  <div>
    <h1>What the site is doing</h1>
    <p>Clicks on the actions that matter, over the last <?= $days ?> days. First-party only —
       no cookies, no third-party script, nothing that identifies a visitor.</p>
  </div>
  <div class="btn-row">
    <?php foreach ([7, 30, 90] as $d): ?>
      <a class="btn btn--<?= $d === $days ? 'primary' : 'ghost' ?> btn--sm" href="?days=<?= $d ?>"><?= $d ?> days</a>
    <?php endforeach; ?>
  </div>
</div>

<?php if ($tableMissing): ?>
  <div class="alert alert--warn" role="alert">
    <strong>The <code>ui_events</code> table does not exist yet.</strong><br>
    Import <code>sql/schema.sql</code> again in phpMyAdmin — it uses
    <code>CREATE TABLE IF NOT EXISTS</code>, so re-importing adds this table and leaves your
    appointments untouched.
  </div>
<?php else: ?>

  <div class="stats">
    <?php foreach ($labels as $key => $label): ?>
    <div class="stat-card<?= $key === 'booking_complete' ? ' stat-card--accent' : '' ?>">
      <b><?= number_format($totals[$key] ?? 0) ?></b><span><?= e($label) ?></span>
    </div>
    <?php endforeach; ?>
  </div>

  <?php if (($totals['book_click'] ?? 0) > 0): ?>
  <p class="muted">
    <?= number_format($totals['booking_complete'] ?? 0) ?> of
    <?= number_format($totals['book_click']) ?> booking clicks finished as a confirmed
    appointment
    (<?= round(100 * ($totals['booking_complete'] ?? 0) / max(1, $totals['book_click'])) ?>%).
    A low figure usually means the booking form is losing people, not that the button is wrong.
  </p>
  <?php endif; ?>

  <div class="grid grid--2" style="gap:1.6rem;margin-top:1.8rem">
    <div>
      <h2 style="font-size:1.1rem">Where they clicked Book</h2>
      <?php if (!$bySource): ?>
        <p class="empty">Nothing recorded yet.</p>
      <?php else: ?>
      <div class="table-wrap"><table class="data">
        <thead><tr><th>Place on the page</th><th style="text-align:right">Clicks</th></tr></thead>
        <tbody>
          <?php foreach ($bySource as $r): ?>
          <tr>
            <td><?= e(ucfirst(str_replace('_', ' ', (string) $r['source']))) ?></td>
            <td style="text-align:right"><?= number_format((int) $r['n']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>

    <div>
      <h2 style="font-size:1.1rem">Which problem brought them in</h2>
      <?php if (!$byService): ?>
        <p class="empty">Nothing recorded yet.</p>
      <?php else: ?>
      <div class="table-wrap"><table class="data">
        <thead><tr><th>Service card</th><th style="text-align:right">Chosen</th></tr></thead>
        <tbody>
          <?php foreach ($byService as $r): ?>
          <tr>
            <td><?= e(SERVICES[$r['detail']]['name'] ?? ucfirst(str_replace('-', ' ', (string) $r['detail']))) ?></td>
            <td style="text-align:right"><?= number_format((int) $r['n']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table></div>
      <?php endif; ?>
    </div>
  </div>

  <h2 style="font-size:1.1rem;margin-top:2.2rem">Day by day</h2>
  <?php if (!$daily): ?>
    <p class="empty">Nothing recorded yet. Events appear here as soon as visitors start
       clicking — give it a day or two of real traffic.</p>
  <?php else: ?>
  <div class="table-wrap"><table class="data">
    <thead>
      <tr><th>Date</th><th style="text-align:right">All actions</th>
          <th style="text-align:right">Book clicked</th><th style="text-align:right">Booked</th></tr>
    </thead>
    <tbody>
      <?php foreach ($daily as $r): ?>
      <tr>
        <td><?= e((new DateTimeImmutable((string) $r['day']))->format('D, j M Y')) ?></td>
        <td style="text-align:right"><?= number_format((int) $r['n']) ?></td>
        <td style="text-align:right"><?= number_format((int) $r['books']) ?></td>
        <td style="text-align:right"><?= number_format((int) $r['completed']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table></div>
  <?php endif; ?>

<?php endif; ?>

<?php admin_foot(); ?>
