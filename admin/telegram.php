<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/layout.php';
require_once dirname(__DIR__) . '/includes/notify.php';

/*
 * Booking alerts: shows whether Telegram is set up, finds the group's chat id
 * (the one value nobody can be expected to know), and sends a test message.
 * The token itself is never shown or accepted here — it lives only in .env.
 */

$user   = require_admin();
$notice = null;
$chats  = null;
$bot    = null;

if (telegram_token() !== '') {
    $me  = telegram_api('getMe', [], 5);
    $bot = !empty($me['ok']) ? $me['result'] : null;
    if (!$bot) {
        $notice = ['bad', 'Telegram rejected the bot token: ' . ($me['description'] ?? 'unknown error')
                        . '. Check TELEGRAM_BOT_TOKEN in .env.'];
    }
}

if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST' && $bot) {
    if (!admin_csrf_check($_POST['csrf'] ?? null)) {
        $notice = ['bad', 'Session expired — please try again.'];
    } elseif (($_POST['action'] ?? '') === 'find') {
        // Every chat the bot has seen in the last 24 hours: being added to a
        // group counts, and so does a /start command sent in it.
        $res   = telegram_api('getUpdates', ['allowed_updates' => ['message', 'my_chat_member', 'channel_post']], 8);
        $chats = [];
        foreach (($res['ok'] ?? false) ? $res['result'] : [] as $u) {
            $c = $u['message']['chat'] ?? $u['my_chat_member']['chat'] ?? $u['channel_post']['chat'] ?? null;
            if ($c) {
                $chats[(string) $c['id']] = [
                    'title' => $c['title'] ?? trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')),
                    'type'  => $c['type'],
                ];
            }
            // A group that became a supergroup keeps working only under its new id.
            if (isset($u['message']['migrate_to_chat_id'])) {
                unset($chats[(string) $u['message']['chat']['id']]);
            }
        }
        if (empty($res['ok'])) {
            $notice = ['bad', 'Could not ask Telegram: ' . ($res['description'] ?? 'unknown error')];
        }
    } elseif (($_POST['action'] ?? '') === 'test') {
        $err = telegram_send(
            "✅ <b>ORADENT booking alerts are working.</b>\n\nEvery new booking on "
            . htmlspecialchars(parse_url(SITE_URL, PHP_URL_HOST) ?: SITE_URL) . ' will appear here.'
        );
        $notice = $err === ''
            ? ['ok', 'Test message sent — check the Telegram group.']
            : ['bad', 'Telegram did not accept the message: ' . $err];
    }
}

admin_head('Booking alerts', $user);
$step = !$bot ? 1 : (telegram_chat_id() === '' ? 2 : 3);   // a step is done once Telegram accepts it
?>
<div class="adm-head">
  <div><h1>Booking alerts</h1><p>A Telegram message to the clinic's group for every new booking.</p></div>
</div>
<?php if ($notice): ?><div class="alert alert--<?= e($notice[0]) ?>" role="status"><?= e($notice[1]) ?></div><?php endif; ?>

<div class="panel">
  <h2 style="margin-top:0">
    <?php if (telegram_configured() && $bot): ?>
      <span class="chip chip--ok">On</span> Alerts are switched on
    <?php else: ?>
      <span class="chip">Off</span> Alerts are not switched on yet
    <?php endif; ?>
  </h2>

  <ol style="line-height:1.7">
    <li<?= $step > 1 ? ' style="opacity:.6"' : '' ?>>
      <strong>Bot token</strong> —
      <?php if ($bot): ?>
        set ✓ (bot <strong>@<?= e($bot['username']) ?></strong>)
      <?php else: ?>
        add <code>TELEGRAM_BOT_TOKEN=…</code> (from @BotFather) to <code>.env</code> in
        <code>public_html</code>, then reload this page.
      <?php endif; ?>
    </li>
    <li<?= $step > 2 ? ' style="opacity:.6"' : '' ?>>
      <strong>Group</strong> —
      <?php if (telegram_chat_id() !== ''): ?>
        set ✓ (<code>TELEGRAM_CHAT_ID=<?= e(telegram_chat_id()) ?></code>)
      <?php else: ?>
        add the bot to your group, then in the group send
        <code>/start<?= $bot ? '@' . e($bot['username']) : '' ?></code>. Press <em>Find my group</em>
        and copy its id into <code>.env</code> as <code>TELEGRAM_CHAT_ID=…</code>.
      <?php endif; ?>
    </li>
    <li><strong>Test</strong> — send a test message to make sure it arrives.</li>
  </ol>

  <?php if ($bot): ?>
  <div class="btn-row">
    <form method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
      <input type="hidden" name="action" value="find">
      <button class="btn btn--ghost" type="submit">Find my group</button>
    </form>
    <?php if (telegram_chat_id() !== ''): ?>
    <form method="post" class="inline-form">
      <input type="hidden" name="csrf" value="<?= e(admin_csrf()) ?>">
      <input type="hidden" name="action" value="test">
      <button class="btn btn--primary" type="submit">Send a test message</button>
    </form>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<?php if ($chats !== null): ?>
<div class="panel panel--flush">
  <?php if (!$chats): ?>
    <div class="empty">
      <p>The bot has not seen any chat in the last 24 hours.<br>
         In the group, send <code>/start<?= $bot ? '@' . e($bot['username']) : '' ?></code>, then press
         <em>Find my group</em> again.</p>
    </div>
  <?php else: ?>
  <div class="table-wrap">
    <table class="data">
      <thead><tr><th>Chat</th><th>Type</th><th>Put this in .env</th></tr></thead>
      <tbody>
      <?php foreach ($chats as $id => $c): ?>
        <tr>
          <td><strong><?= e($c['title'] !== '' ? $c['title'] : '(no name)') ?></strong></td>
          <td><?= e($c['type'] === 'private' ? 'direct chat' : $c['type']) ?></td>
          <td><code>TELEGRAM_CHAT_ID=<?= e((string) $id) ?></code></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php admin_foot(); ?>
