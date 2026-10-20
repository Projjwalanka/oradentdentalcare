<?php
declare(strict_types=1);

/**
 * First-party click tracking.
 *
 * Records that an action happened — nothing about who took it. No cookie, no
 * identifier, no IP, no third-party request, so there is nothing to disclose in
 * a consent banner and nothing that leaves the clinic's own server.
 *
 * Deliberately silent: tracking must never break the page or delay a booking,
 * so every failure path returns 204 and the browser sends this with
 * navigator.sendBeacon, which does not wait for a response.
 */

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/includes/db.php';

/** Nothing to say, ever. The browser is not waiting. */
function track_done(): never
{
    if (!headers_sent()) {
        http_response_code(204);
        header('Cache-Control: no-store');
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    track_done();
}

/* Only the events the site actually emits. An unknown name is dropped rather
   than stored, so a stray script cannot fill the table with junk. */
const TRACKED_EVENTS = [
    'book_click',       // any "Book an appointment" control
    'whatsapp_click',
    'call_click',
    'directions_click',
    'service_select',   // one of the "How can we help?" cards
    'booking_complete',
];

$raw  = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
    $data = $_POST;
}

$event = (string) ($data['event'] ?? '');
if (!in_array($event, TRACKED_EVENTS, true)) {
    track_done();
}

$clean = static fn(mixed $v, int $max): ?string => is_string($v) && $v !== ''
    ? mb_substr(preg_replace('/[^\w\/\-.]/u', '', $v) ?? '', 0, $max)
    : null;

$source = $clean($data['source'] ?? null, 40);
$detail = $clean($data['detail'] ?? null, 60);
$path   = $clean($data['path'] ?? ($_SERVER['HTTP_REFERER'] ?? null), 190);

/* A phone and a desktop behave very differently on a clinic site, and this is
   the one distinction worth keeping. It is not a fingerprint: one bit, derived,
   never stored alongside anything identifying. */
$isMobile = (int) (bool) preg_match('/Mobi|Android|iPhone|iPad/i', (string) ($_SERVER['HTTP_USER_AGENT'] ?? ''));

if (!db_available()) {
    track_done();
}

try {
    $st = db()->prepare(
        'INSERT INTO ui_events (event, source, detail, path, is_mobile) VALUES (?, ?, ?, ?, ?)'
    );
    $st->execute([$event, $source, $detail, $path, $isMobile]);
} catch (Throwable $e) {
    error_log('[oradent] track failed: ' . $e->getMessage());
}

track_done();
