<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';

$user = require_admin();
$pdo  = db();
$notice = null;

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        $notice = ['bad', 'Session expired — please try again.'];
    } elseif (($_POST['action'] ?? '') === 'delete') {
        $pdo->prepare('DELETE FROM blocked_slots WHERE id = ?')->execute([(int) $_POST['id']]);
        $notice = ['ok', 'Block removed — those times are bookable again.'];
    } else {
        $date   = (string) ($_POST['block_date'] ?? '');
        $scope  = (string) ($_POST['scope'] ?? 'day');
        $time   = (string) ($_POST['block_time'] ?? '');
        $reason = mb_substr(trim((string) ($_POST['reason'] ?? '')), 0, 190);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $notice = ['bad', 'Please choose a valid date.'];
        } else {
            $booked = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE appt_date = ? AND status <> 'cancelled'"
                                    . ($scope === 'slot' ? ' AND appt_time = ?' : ''));
            $booked->execute($scope === 'slot' ? [$date, $time . ':00'] : [$date]);
            $n = (int) $booked->fetchColumn();

            try {
                $st = $pdo->prepare('INSERT INTO blocked_slots (block_date, block_time, reason) VALUES (?, ?, ?)');
                $st->execute([$date, $scope === 'slot' && $time !== '' ? $time . ':00' : null,
                              $reason !== '' ? $reason : null]);
                $notice = ['ok', $n > 0
                    ? "Blocked. Note: $n appointment(s) are already booked in that period — they are unaffected, so contact those patients if you need to move them."
                    : 'Blocked. Those times are no longer offered to patients.'];
            } catch (PDOException $e) {
                $notice = ['warn', 'That date or slot is already blocked.'];
            }
        }
    }
}

$rows = $pdo->query(
    'SELECT b.*, (SELECT COUNT(*) FROM appointments a
                   WHERE a.appt_date = b.block_date AND a.status <> "cancelled"
                     AND (b.block_time IS NULL OR a.appt_time = b.block_time)) AS booked
       FROM blocked_slots b WHERE b.block_date >= CURDATE()
      ORDER BY b.block_date ASC, b.block_time ASC'
)->fetchAll();

admin_head('Block time', $user);
?>
<div class="adm-head">
  <div><h1>Block time off</h1>
  <p>Close a whole day (holiday, leave) or a single slot so patients cannot book it.</p></div>
</div>

<?php if ($notice): ?><div class="alert alert--<?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div><?php endif; ?>

<div class="panel">
  <h2>Add a block</h2>
  <form method="post" class="filters">
    <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
    <label class="field">
      <span class="field__label">Date</span>
      <input class="input" type="date" name="block_date" required min="<?= e(date('Y-m-d')) ?>" value="<?= e(date('Y-m-d')) ?>">
    </label>
    <label class="field">
      <span class="field__label">Scope</span>
      <select class="select" name="scope" id="scope">
        <option value="day">Whole day</option>
        <option value="slot">One slot</option>
      </select>
    </label>
    <label class="field" id="slotField" hidden>
      <span class="field__label">Time</span>
      <select class="select" name="block_time">
        <?php foreach (slots_for_date(date('Y-m-d', strtotime('next monday'))) as $s): ?>
          <option value="<?= e($s['time']) ?>"><?= e(fmt_time($s['time'])) ?> (<?= e($s['session']) ?>)</option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="field" style="flex:1;min-width:200px">
      <span class="field__label">Reason (optional)</span>
      <input class="input" type="text" name="reason" maxlength="190" placeholder="Puja holiday, conference, personal leave…">
    </label>
    <button class="btn btn--primary btn--sm" type="submit">Block it</button>
  </form>
  <script>
    document.getElementById('scope').addEventListener('change', function () {
      document.getElementById('slotField').hidden = this.value !== 'slot';
    });
  </script>
</div>

<div class="panel panel--flush">
  <h2 style="padding:1.3rem 1.4rem 0">Upcoming blocks</h2>
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('calendar') ?><p>Nothing blocked. The clinic is open on all its normal days.</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data" style="min-width:640px">
      <thead><tr><th>Date</th><th>Scope</th><th>Reason</th><th>Already booked</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr>
          <td class="num"><strong><?= e((new DateTimeImmutable($r['block_date']))->format('D, j M Y')) ?></strong></td>
          <td><?= $r['block_time'] === null ? '<span class="chip chip--bad">Whole day</span>'
                : '<span class="chip chip--warn">' . e(fmt_time(substr((string) $r['block_time'], 0, 5))) . '</span>' ?></td>
          <td class="muted"><?= e((string) $r['reason']) ?></td>
          <td class="num"><?= (int) $r['booked'] ? '<strong style="color:var(--warn)">' . (int) $r['booked'] . '</strong>' : '—' ?></td>
          <td>
            <form method="post" class="inline-form" onsubmit="return confirm('Remove this block?')">
              <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--ghost btn--sm" type="submit">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php admin_foot(); ?>
