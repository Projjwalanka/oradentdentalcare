<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/booking.php';

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    json_out(['ok' => false, 'message' => 'Method not allowed.'], 405);
}

$in = $_POST;
if (!$in) {                       // accept a JSON body as well
    $in = json_decode(file_get_contents('php://input') ?: '', true) ?: [];
}

if (!csrf_check($in['csrf'] ?? null)) {
    json_out(['ok' => false,
              'message' => 'Your session expired. Please refresh the page and try again.'], 419);
}

$res = create_booking($in);

if ($res['ok']) {
    json_out([
        'ok'        => true,
        'reference' => $res['data']['reference'],
        'service'   => $res['data']['service'],
        'date'      => $res['data']['date'],
        'time'      => $res['data']['time'],
        'redirect'  => '/booking-confirmed?ref=' . rawurlencode($res['data']['reference']),
    ]);
}

json_out(array_filter([
    'ok'      => false,
    'message' => $res['message'] ?? 'Could not complete the booking.',
    'errors'  => $res['errors'] ?? null,
    'field'   => $res['field'] ?? null,
    'taken'   => $res['taken'] ?? null,
], static fn($v) => $v !== null), $res['status']);
