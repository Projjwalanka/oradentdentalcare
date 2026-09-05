<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$user = require_admin();
$pdo  = db();

$today    = date('Y-m-d');
$tomorrow = date('Y-m-d', strtotime('+1 day'));

$stat = static function (string $sql, array $args = []) use ($pdo): int {
    $st = $pdo->prepare($sql);
    $st->execute($args);
    return (int) $st->fetchColumn();
};

$todayCount   = $stat("SELECT COUNT(*) FROM appointments WHERE appt_date = ? AND status <> 'cancelled'", [$today]);
$tomorrowCnt  = $stat("SELECT COUNT(*) FROM appointments WHERE appt_date = ? AND status <> 'cancelled'", [$tomorrow]);
$weekCount    = $stat("SELECT COUNT(*) FROM appointments WHERE appt_date BETWEEN ? AND ? AND status <> 'cancelled'",
                      [$today, date('Y-m-d', strtotime('+6 days'))]);
$newMessages  = $stat('SELECT COUNT(*) FROM contact_messages WHERE is_read = 0');
$totalUpcoming = $stat("SELECT COUNT(*) FROM appointments WHERE appt_date >= ? AND status = 'confirmed'", [$today]);

$st = $pdo->prepare(
    "SELECT * FROM appointments WHERE appt_date = ? ORDER BY appt_time ASC"
);
$st->execute([$today]);
$todayList = $st->fetchAll();

$st = $pdo->prepare(
    "SELECT * FROM appointments WHERE appt_date = ? AND status <> 'cancelled' ORDER BY appt_time ASC"
);
$st->execute([$tomorrow]);
$tomorrowList = $st->fetchAll();

$slotsToday = available_slots($today);
$freeToday  = count(array_filter($slotsToday, static fn(array $s) => $s['available']));

admin_head('Dashboard', $user);
?>
<div class="adm-head">
  <div>
    <?php
    // "Dr. Nabanita Chaklader" -> "Dr. Nabanita"; "Priya Roy" -> "Priya"
    $parts = preg_split('/\s+/', trim($user['name'])) ?: [$user['name']];
    $greet = (str_starts_with($parts[0], 'Dr') && isset($parts[1]))
        ? $parts[0] . ' ' . $parts[1]
        : $parts[0];
    ?>
    <h1>Good <?= date('G') < 12 ? 'morning' : (date('G') < 17 ? 'afternoon' : 'evening') ?>, <?= e($greet) ?></h1>
    <p><?= e(date('l, j F Y')) ?> · The clinic is <?= is_open_now() ? '<strong style="color:var(--ok)">open now</strong>' : 'closed right now' ?>.</p>
  </div>
  <div class="btn-row">
    <a class="btn btn--ghost btn--sm" href="/admin/appointments.php">All appointments</a>
    <a class="btn btn--primary btn--sm" href="/admin/appointments.php?export=csv&amp;from=<?= e($today) ?>">Export CSV</a>
  </div>
</div>

<div class="stats">
  <div class="stat-card stat-card--accent"><b><?= $todayCount ?></b><span>Today</span></div>
  <div class="stat-card"><b><?= $tomorrowCnt ?></b><span>Tomorrow</span></div>
  <div class="stat-card"><b><?= $weekCount ?></b><span>Next 7 days</span></div>
  <div class="stat-card"><b><?= $freeToday ?></b><span>Slots free today</span></div>
  <div class="stat-card"><b><?= $totalUpcoming ?></b><span>Upcoming total</span></div>
  <div class="stat-card"><b><?= $newMessages ?></b><span>Unread messages</span></div>
</div>

<?php
/** Render one appointment table. */
$table = static function (array $rows, string $emptyText): void {
    if (!$rows) {
        echo '<div class="empty">' . icon('calendar') . '<p>' . e($emptyText) . '</p></div>';
        return;
    }
    ?>
    <div class="table-wrap">
      <table class="data">
        <thead>
          <tr><th>Time</th><th>Patient</th><th>Treatment</th><th>Contact</th><th>Notes</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
        <?php foreach ($rows as $r):
            $svc = SERVICES[$r['service_slug']]['name'] ?? $r['service_slug']; ?>
          <tr class="<?= $r['status'] === 'cancelled' ? 'row-cancelled' : '' ?>">
            <td class="num"><strong><?= e((new DateTimeImmutable($r['appt_time']))->format('g:i A')) ?></strong></td>
            <td class="patient">
              <strong><?= e($r['patient_name']) ?></strong>
              <div class="muted ref"><?= e($r['reference']) ?><?= $r['is_first_visit'] ? ' · new patient' : '' ?></div>
            </td>
            <td><?= e($svc) ?></td>
            <td class="num">
              <a href="tel:+91<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a>
              <div class="muted"><a href="https://wa.me/91<?= e($r['phone']) ?>" target="_blank" rel="noopener">WhatsApp</a></div>
            </td>
            <td class="muted" style="max-width:22ch"><?= e((string) $r['notes']) ?></td>
            <td><?= status_chip($r['status']) ?></td>
            <td><a class="btn btn--ghost btn--sm" href="/admin/appointments.php?q=<?= e($r['reference']) ?>">Manage</a></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php
};
?>

<div class="panel panel--flush">
  <h2 style="padding:1.3rem 1.4rem 0">Today · <?= e(date('j M')) ?></h2>
  <?php $table($todayList, 'No appointments booked for today yet.'); ?>
</div>

<div class="panel panel--flush">
  <h2 style="padding:1.3rem 1.4rem 0">Tomorrow · <?= e(date('j M', strtotime('+1 day'))) ?></h2>
  <?php $table($tomorrowList, 'Nothing booked for tomorrow yet.'); ?>
</div>

<?php admin_foot(); ?>
