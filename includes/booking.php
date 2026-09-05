<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/data.php';

/**
 * Validate and persist one appointment.
 *
 * Shared by the JSON endpoint (api/book.php) and the no-JavaScript form POST in
 * book.php, so both paths enforce exactly the same rules.
 *
 * @param array<string,mixed> $in
 * @return array{ok:bool, status:int, message?:string, errors?:array<string,string>,
 *                field?:string, taken?:bool, data?:array<string,string>}
 */
function create_booking(array $in): array
{
    /* Honeypot: bots fill every field, patients cannot see this one. */
    if (trim((string) ($in['website'] ?? '')) !== '') {
        return ['ok' => true, 'status' => 200,
                'data' => ['reference' => 'ORD-000000', 'service' => '', 'date' => '', 'time' => '']];
    }

    $errors = [];

    $name = trim((string) ($in['name'] ?? ''));
    if (mb_strlen($name) < 2 || mb_strlen($name) > 120) {
        $errors['name'] = 'Please enter your full name.';
    }

    $phone = normalise_phone((string) ($in['phone'] ?? ''));
    if ($phone === null) {
        $errors['phone'] = 'Enter a valid 10-digit Indian mobile number.';
    }

    $email = trim((string) ($in['email'] ?? ''));
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'That email address does not look right.';
    }

    $service = (string) ($in['service'] ?? '');
    if (!isset(SERVICES[$service])) {
        $errors['service'] = 'Please choose a treatment.';
    }

    $date = (string) ($in['date'] ?? '');
    if (!date_is_bookable($date)) {
        $errors['date'] = 'Choose a date in the next ' . BOOKING_WINDOW_DAYS . ' days when the clinic is open.';
    }

    $time = (string) ($in['time'] ?? '');
    if (!preg_match('/^\d{2}:\d{2}$/', $time)) {
        $errors['time'] = 'Please pick a time slot.';
    }

    if ($errors) {
        return ['ok' => false, 'status' => 422, 'errors' => $errors,
                'message' => 'Please check the highlighted fields.'];
    }

    $notes = mb_substr(trim((string) ($in['notes'] ?? '')), 0, 1000);
    $first = !empty($in['first_visit']) ? 1 : 0;

    /* Never trust the slot the browser sends — re-derive availability here. */
    $slot = null;
    foreach (available_slots($date) as $s) {
        if ($s['time'] === $time) {
            $slot = $s;
            break;
        }
    }
    if ($slot === null) {
        return ['ok' => false, 'status' => 422, 'field' => 'time',
                'message' => 'That is not a valid time slot. Please pick another.'];
    }
    if (!$slot['available']) {
        return ['ok' => false, 'status' => 409, 'field' => 'time', 'taken' => true,
                'message' => 'Sorry — that slot has just been taken. Please choose another time.'];
    }

    $pdo = db();

    $st = $pdo->prepare(
        "SELECT COUNT(*) FROM appointments WHERE phone = ? AND appt_date = ? AND status <> 'cancelled'"
    );
    $st->execute([$phone, $date]);
    if ((int) $st->fetchColumn() >= BOOKING_MAX_PER_PHONE_PER_DAY) {
        return ['ok' => false, 'status' => 429,
                'message' => 'You already have appointments booked that day. Please call us on '
                             . CLINIC_PHONE_DISPLAY . ' to add another.'];
    }

    /*
     * The uniq_live_slot index is what actually prevents two people taking the
     * same slot in the same instant; this loop only re-rolls a duplicate
     * reference code.
     */
    for ($attempt = 0; $attempt < 5; $attempt++) {
        $reference = booking_reference();
        try {
            $st = $pdo->prepare(
                'INSERT INTO appointments
                    (reference, patient_name, phone, email, service_slug, appt_date, appt_time,
                     notes, is_first_visit, source, ip, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $st->execute([
                $reference, $name, $phone, $email !== '' ? $email : null, $service,
                $date, $time . ':00', $notes !== '' ? $notes : null, $first,
                (string) ($in['source'] ?? 'website'),
                client_ip(), mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 255),
            ]);

            $when = new DateTimeImmutable($date . ' ' . $time);
            return ['ok' => true, 'status' => 200, 'data' => [
                'reference' => $reference,
                'service'   => SERVICES[$service]['name'],
                'date'      => $when->format('l, j F Y'),
                'time'      => $when->format('g:i A'),
            ]];
        } catch (PDOException $e) {
            if ($e->getCode() !== '23000') {
                error_log('[oradent] booking insert failed: ' . $e->getMessage());
                return ['ok' => false, 'status' => 500,
                        'message' => 'Something went wrong saving your booking. Please call us on '
                                     . CLINIC_PHONE_DISPLAY . '.'];
            }
            if (str_contains($e->getMessage(), 'uniq_live_slot')) {
                return ['ok' => false, 'status' => 409, 'field' => 'time', 'taken' => true,
                        'message' => 'Sorry — that slot was booked a moment ago. Please choose another time.'];
            }
            // duplicate reference: fall through and try a new code
        }
    }

    return ['ok' => false, 'status' => 500,
            'message' => 'Could not complete your booking. Please call us on ' . CLINIC_PHONE_DISPLAY . '.'];
}

/** Look up a confirmed booking by its public reference code. */
function find_booking(string $reference): ?array
{
    if (!preg_match('/^ORD-[A-Z0-9]{6}$/', $reference) || !db_available()) {
        return null;
    }
    $st = db()->prepare('SELECT * FROM appointments WHERE reference = ? LIMIT 1');
    $st->execute([$reference]);
    return $st->fetch() ?: null;
}
