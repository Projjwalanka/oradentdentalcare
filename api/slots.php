<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';

header('Cache-Control: no-store, max-age=0');

$date = (string) ($_GET['date'] ?? '');

if (!date_is_bookable($date)) {
    // Distinguish "closed that day" from "outside the booking window" so the UI
    // can say something useful instead of a generic error.
    $valid = (bool) preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)
        && DateTimeImmutable::createFromFormat('!Y-m-d', $date) !== false;

    $reason = 'invalid';
    if ($valid) {
        $d      = new DateTimeImmutable($date);
        $closed = empty(OPENING_HOURS[(int) $d->format('N')]);
        $reason = $closed ? 'closed' : 'out_of_window';
    }

    json_out([
        'ok'      => false,
        'reason'  => $reason,
        'message' => $reason === 'closed'
            ? 'The clinic is closed on this day.'
            : 'Please choose a date within the next ' . BOOKING_WINDOW_DAYS . ' days.',
        'slots'   => [],
    ]);
}

$slots     = available_slots($date);
$bySession = ['Morning' => [], 'Evening' => []];
foreach ($slots as $s) {
    $bySession[$s['session']][] = $s;
}

json_out([
    'ok'        => true,
    'date'      => $date,
    'label'     => (new DateTimeImmutable($date))->format('l, j F Y'),
    'sessions'  => $bySession,
    'available' => count(array_filter($slots, static fn(array $s) => $s['available'])),
    'total'     => count($slots),
]);
