<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Booking alerts to the clinic's Telegram group.
 *
 * Off until both values are in .env:
 *
 *   TELEGRAM_BOT_TOKEN=7123456789:AAH…     from @BotFather
 *   TELEGRAM_CHAT_ID=-1001234567890        found for you at /admin/telegram.php
 *
 * An alert is sent only AFTER the patient's page has been delivered, and a
 * failure is logged, never shown, so a slow or unreachable Telegram can
 * neither delay nor break a booking.
 *
 * Telegram is a third party: an alert carries the patient's name, phone,
 * treatment and time to the clinic's group, and nothing else.
 */

function telegram_token(): string
{
    return trim((string) env('TELEGRAM_BOT_TOKEN', ''));
}

function telegram_chat_id(): string
{
    return trim((string) env('TELEGRAM_CHAT_ID', ''));
}

function telegram_configured(): bool
{
    return telegram_token() !== '' && telegram_chat_id() !== '';
}

/**
 * Call one Bot API method.
 *
 * @return array{ok:bool, result?:mixed, description?:string, parameters?:array}
 */
function telegram_api(string $method, array $params = [], int $timeout = 5): array
{
    $token = telegram_token();
    if ($token === '') {
        return ['ok' => false, 'description' => 'TELEGRAM_BOT_TOKEN is not set in .env'];
    }
    $url  = 'https://api.telegram.org/bot' . $token . '/' . $method;
    $body = json_encode($params, JSON_UNESCAPED_UNICODE);

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 3,
            CURLOPT_TIMEOUT        => $timeout,
        ]);
        $raw = curl_exec($ch);
        $err = curl_error($ch);
    } else {
        $raw = @file_get_contents($url, false, stream_context_create(['http' => [
            'method'        => 'POST',
            'header'        => "Content-Type: application/json\r\n",
            'content'       => $body,
            'timeout'       => $timeout,
            'ignore_errors' => true,             // read Telegram's JSON error, not just "400"
        ]]));
        $err = $raw === false ? 'request failed' : '';
    }

    $res = is_string($raw) ? json_decode($raw, true) : null;
    if (!is_array($res)) {
        // Never echo the URL: it contains the token.
        return ['ok' => false, 'description' => 'Could not reach Telegram' . ($err !== '' ? " ($err)" : '')];
    }
    return $res;
}

/** Send a message (Telegram HTML) to the clinic's chat. Returns an error, or '' on success. */
function telegram_send(string $html, ?string $chatId = null): string
{
    $chat = $chatId ?? telegram_chat_id();
    if ($chat === '') {
        return 'TELEGRAM_CHAT_ID is not set in .env';
    }
    $res = telegram_api('sendMessage', [
        'chat_id'                  => $chat,
        'text'                     => $html,
        'parse_mode'               => 'HTML',
        'disable_web_page_preview' => true,
    ]);
    if (!empty($res['ok'])) {
        return '';
    }
    $why = (string) ($res['description'] ?? 'unknown error');
    // A group upgraded to a "supergroup" gets a new id; say which one to use.
    if (isset($res['parameters']['migrate_to_chat_id'])) {
        $why .= ' — set TELEGRAM_CHAT_ID=' . $res['parameters']['migrate_to_chat_id'];
    }
    return $why;
}

/** The alert text for one booking. */
function booking_alert_text(array $b): string
{
    $h     = static fn (string $s): string => htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    $when  = new DateTimeImmutable($b['date'] . ' ' . $b['time']);
    $phone = preg_replace('/^(\d{5})(\d{5})$/', '$1 $2', (string) $b['phone']) ?? $b['phone'];

    $lines = [
        '🦷 <b>New booking</b>' . (!empty($b['first_visit']) ? ' · first visit' : ''),
        '',
        '<b>' . $h($b['name']) . '</b>',
        '📞 +91 ' . $h($phone),
        '🩺 ' . $h($b['service']),
        '📅 ' . $h($when->format('D j M Y, g:i A')),
    ];
    if (!empty($b['notes'])) {
        $notes   = mb_strlen($b['notes']) > 300 ? mb_substr($b['notes'], 0, 300) . '…' : $b['notes'];
        $lines[] = '📝 ' . $h($notes);
    }
    $lines[] = '';
    $lines[] = '<a href="' . $h(url('admin/appointments.php?q=' . rawurlencode($b['reference']))) . '">'
             . $h($b['reference']) . '</a>';

    return implode("\n", $lines);
}

/**
 * Queue the alert for a booking that has just been saved. It goes out once
 * the response is on its way to the patient.
 */
function notify_new_booking(array $b): void
{
    if (!telegram_configured()) {
        return;
    }
    register_shutdown_function(static function () use ($b): void {
        // Hand the finished page to the patient first, then talk to Telegram.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (function_exists('litespeed_finish_request')) {
            litespeed_finish_request();                // Hostinger runs LiteSpeed
        }
        try {
            $err = telegram_send(booking_alert_text($b));
            if ($err !== '') {
                error_log('[oradent] telegram alert failed for ' . $b['reference'] . ': ' . $err);
            }
        } catch (Throwable $e) {
            error_log('[oradent] telegram alert crashed: ' . $e->getMessage());
        }
    });
}
