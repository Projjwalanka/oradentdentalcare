<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$user = require_admin();
$pdo  = db();

/* ------------------------------------------------------ status changes ----- */
$notice = null;
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        $notice = ['bad', 'Session expired — please try again.'];
    } else {
        $id     = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? '');
        if ($id > 0 && in_array($status, ['confirmed', 'completed', 'cancelled', 'no_show'], true)) {
            $st = $pdo->prepare('UPDATE appointments SET status = ? WHERE id = ?');
            $st->execute([$status, $id]);
            $notice = ['ok', 'Appointment updated.'];
        } else {
            $notice = ['bad', 'Could not update that appointment.'];
        }
    }
}

/* ------------------------------------------------------------- filters ----- */
$q      = trim((string) ($_GET['q'] ?? ''));
$status = (string) ($_GET['status'] ?? '');
$from   = (string) ($_GET['from'] ?? date('Y-m-d'));
$to     = (string) ($_GET['to'] ?? '');

$where = [];
$args  = [];

if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $where[] = 'appt_date >= ?'; $args[] = $from; }
if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $where[] = 'appt_date <= ?'; $args[] = $to; }
if (in_array($status, ['confirmed', 'completed', 'cancelled', 'no_show'], true)) {
    $where[] = 'status = ?'; $args[] = $status;
}
if ($q !== '') {
    $where[] = '(reference LIKE ? OR patient_name LIKE ? OR phone LIKE ?)';
    $like    = '%' . $q . '%';
    array_push($args, $like, $like, $like);
}
$sql = 'FROM appointments' . ($where ? ' WHERE ' . implode(' AND ', $where) : '');

/* ---------------------------------------------------------- CSV export ----- */
if (($_GET['export'] ?? '') === 'csv') {
    $st = $pdo->prepare('SELECT * ' . $sql . ' ORDER BY appt_date ASC, appt_time ASC');
    $st->execute($args);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="oradent-appointments-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF");                       // BOM so Excel reads UTF-8
    fputcsv($out, ['Reference', 'Date', 'Time', 'Patient', 'Phone', 'Email',
                   'Treatment', 'First visit', 'Status', 'Notes', 'Booked at']);
    foreach ($st->fetchAll() as $r) {
        fputcsv($out, [
            $r['reference'], $r['appt_date'], substr((string) $r['appt_time'], 0, 5),
            $r['patient_name'], "'" . $r['phone'], $r['email'],
            SERVICES[$r['service_slug']]['name'] ?? $r['service_slug'],
            $r['is_first_visit'] ? 'Yes' : 'No', $r['status'], $r['notes'], $r['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

/* ------------------------------------------------------------ paging ------- */
$perPage = 50;
$page    = max(1, (int) ($_GET['p'] ?? 1));

$cst = $pdo->prepare('SELECT COUNT(*) ' . $sql);
$cst->execute($args);
$total = (int) $cst->fetchColumn();
$pages = max(1, (int) ceil($total / $perPage));
$page  = min($page, $pages);

$st = $pdo->prepare('SELECT * ' . $sql . ' ORDER BY appt_date ASC, appt_time ASC LIMIT ' . $perPage
                    . ' OFFSET ' . (($page - 1) * $perPage));
$st->execute($args);
$rows = $st->fetchAll();

$qs = static function (array $over) : string {
    return '?' . http_build_query(array_merge($_GET, $over));
};

admin_head('Appointments', $user);
?>
<div class="adm-head">
  <div>
    <h1>Appointments</h1>
    <p><?= number_format($total) ?> matching <?= $total === 1 ? 'appointment' : 'appointments' ?></p>
  </div>
  <div class="btn-row">
    <a class="btn btn--ghost btn--sm" href="<?= e($qs(['export' => 'csv'])) ?>">Export these to CSV</a>
  </div>
</div>

<?php if ($notice): ?>
  <div class="alert alert--<?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div>
<?php endif; ?>

<div class="panel">
  <form class="filters" method="get">
    <label class="field">
      <span class="field__label">Search</span>
      <input class="input" type="search" name="q" value="<?= e($q) ?>" placeholder="Name, phone or ORD-…">
    </label>
    <label class="field">
      <span class="field__label">From</span>
      <input class="input" type="date" name="from" value="<?= e($from) ?>">
    </label>
    <label class="field">
      <span class="field__label">To</span>
      <input class="input" type="date" name="to" value="<?= e($to) ?>">
    </label>
    <label class="field">
      <span class="field__label">Status</span>
      <select class="select" name="status">
        <option value="">All</option>
        <?php foreach (['confirmed' => 'Confirmed', 'completed' => 'Completed',
                        'cancelled' => 'Cancelled', 'no_show' => 'No-show'] as $k => $v): ?>
          <option value="<?= e($k) ?>"<?= $status === $k ? ' selected' : '' ?>><?= e($v) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <button class="btn btn--primary btn--sm" type="submit">Apply</button>
    <a class="btn btn--ghost btn--sm" href="/admin/appointments.php">Reset</a>
  </form>
</div>

<div class="panel panel--flush">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('calendar') ?><p>No appointments match these filters.</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data">
      <thead>
        <tr><th>Date &amp; time</th><th>Patient</th><th>Treatment</th><th>Contact</th>
            <th>Notes</th><th>Status</th><th>Change status</th></tr>
      </thead>
      <tbody>
      <?php foreach ($rows as $r):
          $when = new DateTimeImmutable($r['appt_date'] . ' ' . $r['appt_time']);
          $svc  = SERVICES[$r['service_slug']]['name'] ?? $r['service_slug']; ?>
        <tr class="<?= $r['status'] === 'cancelled' ? 'row-cancelled' : '' ?>">
          <td class="num">
            <strong><?= e($when->format('D, j M')) ?></strong>
            <div class="muted"><?= e($when->format('g:i A')) ?></div>
          </td>
          <td class="patient">
            <strong><?= e($r['patient_name']) ?></strong>
            <div class="muted ref"><?= e($r['reference']) ?><?= $r['is_first_visit'] ? ' · new' : '' ?></div>
          </td>
          <td><?= e($svc) ?></td>
          <td class="num">
            <a href="tel:+91<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a>
            <div class="muted">
              <a href="https://wa.me/91<?= e($r['phone']) ?>" target="_blank" rel="noopener">WhatsApp</a>
              <?php if ($r['email']): ?> · <a href="mailto:<?= e($r['email']) ?>">Email</a><?php endif; ?>
            </div>
          </td>
          <td class="muted" style="max-width:24ch"><?= e((string) $r['notes']) ?></td>
          <td><?= status_chip($r['status']) ?></td>
          <td>
            <form method="post" class="inline-form" style="display:flex;gap:.35rem">
              <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <select class="select" name="status" style="min-height:36px;padding:.3rem 2rem .3rem .6rem;font-size:.85rem">
                <?php foreach (['confirmed' => 'Confirmed', 'completed' => 'Completed',
                                'cancelled' => 'Cancelled', 'no_show' => 'No-show'] as $k => $v): ?>
                  <option value="<?= e($k) ?>"<?= $r['status'] === $k ? ' selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn--navy btn--sm" type="submit">Save</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>

<?php if ($pages > 1): ?>
<div class="btn-row" style="justify-content:center">
  <?php if ($page > 1): ?><a class="btn btn--ghost btn--sm" href="<?= e($qs(['p' => $page - 1])) ?>">← Previous</a><?php endif; ?>
  <span style="align-self:center;color:var(--ink-mute);font-size:.88rem">Page <?= $page ?> of <?= $pages ?></span>
  <?php if ($page < $pages): ?><a class="btn btn--ghost btn--sm" href="<?= e($qs(['p' => $page + 1])) ?>">Next →</a><?php endif; ?>
</div>
<?php endif; ?>

<?php admin_foot(); ?>
