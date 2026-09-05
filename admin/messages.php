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
    } else {
        $id = (int) ($_POST['id'] ?? 0);
        if (($_POST['action'] ?? '') === 'delete') {
            $pdo->prepare('DELETE FROM contact_messages WHERE id = ?')->execute([$id]);
            $notice = ['ok', 'Message deleted.'];
        } else {
            $pdo->prepare('UPDATE contact_messages SET is_read = 1 - is_read WHERE id = ?')->execute([$id]);
            $notice = ['ok', 'Message updated.'];
        }
    }
}

$rows = $pdo->query('SELECT * FROM contact_messages ORDER BY is_read ASC, created_at DESC LIMIT 200')->fetchAll();

admin_head('Messages', $user);
?>
<div class="adm-head">
  <div><h1>Patient messages</h1><p>Enquiries sent through the contact form.</p></div>
</div>
<?php if ($notice): ?><div class="alert alert--<?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div><?php endif; ?>

<div class="panel panel--flush">
  <?php if (!$rows): ?>
    <div class="empty"><?= icon('mail') ?><p>No messages yet.</p></div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Received</th><th>From</th><th>Subject</th><th>Message</th><th></th></tr></thead>
      <tbody>
      <?php foreach ($rows as $r): ?>
        <tr style="<?= $r['is_read'] ? 'opacity:.62' : '' ?>">
          <td class="num"><?= e((new DateTimeImmutable($r['created_at']))->format('j M, g:i A')) ?>
            <?php if (!$r['is_read']): ?><div><span class="chip chip--ok">New</span></div><?php endif; ?>
          </td>
          <td><strong><?= e($r['name']) ?></strong>
            <div class="muted"><a href="tel:+91<?= e($r['phone']) ?>"><?= e($r['phone']) ?></a>
              · <a href="https://wa.me/91<?= e($r['phone']) ?>" target="_blank" rel="noopener">WhatsApp</a></div>
            <?php if ($r['email']): ?><div class="muted"><a href="mailto:<?= e($r['email']) ?>"><?= e($r['email']) ?></a></div><?php endif; ?>
          </td>
          <td><?= e((string) $r['subject']) ?></td>
          <td style="max-width:44ch;white-space:pre-wrap"><?= e($r['message']) ?></td>
          <td>
            <form method="post" class="inline-form">
              <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--ghost btn--sm" type="submit"><?= $r['is_read'] ? 'Mark unread' : 'Mark read' ?></button>
            </form>
            <form method="post" class="inline-form" onsubmit="return confirm('Delete this message permanently?')">
              <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
              <input type="hidden" name="action" value="delete">
              <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn--ghost btn--sm" type="submit" style="color:var(--bad)">Delete</button>
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
