<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/* --------------------------------------------------------------- output ---- */

function e(?string $s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    return SITE_URL . '/' . ltrim($path, '/');
}

/** Cache-busted asset URL so CSS/JS changes go live immediately after a deploy. */
function asset(string $path): string
{
    $file = dirname(__DIR__) . '/' . ltrim($path, '/');
    $v    = is_file($file) ? (string) filemtime($file) : '1';
    return url($path) . '?v=' . $v;
}

function current_path(): string
{
    return '/' . trim(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '', '/');
}

function is_active(string $path): bool
{
    $cur = current_path();
    return $cur === $path || ($path !== '/' && str_starts_with($cur, $path));
}

/* ----------------------------------------------------------------- csrf ---- */

function session_start_once(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'secure'   => !empty($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

function csrf_token(): string
{
    session_start_once();
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_check(?string $token): bool
{
    session_start_once();
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

/* ------------------------------------------------------------ contact ------ */

function tel_link(): string
{
    return 'tel:' . CLINIC['phone'];
}

function whatsapp_link(string $message = ''): string
{
    $message = $message !== '' ? $message
        : 'Hello ORADENT Dental Care, I would like to book an appointment with Dr. Nabanita Chaklader.';
    return 'https://wa.me/' . CLINIC['whatsapp'] . '?text=' . rawurlencode($message);
}

function full_address(string $sep = ', '): string
{
    return implode($sep, [
        CLINIC['street'],
        CLINIC['locality'],
        CLINIC['city'] . ' ' . CLINIC['postal'],
        CLINIC['region'],
    ]);
}

/* ------------------------------------------------------ opening hours ------ */

/** Human-readable hours grouped into consecutive runs, e.g. "Mon – Sat". */
function hours_summary(): array
{
    $names = [1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    $rows  = [];
    $run   = null;

    for ($d = 1; $d <= 7; $d++) {
        $key = json_encode(OPENING_HOURS[$d]);
        if ($run && $run['key'] === $key) {
            $run['to'] = $d;
        } else {
            if ($run) { $rows[] = $run; }
            $run = ['key' => $key, 'from' => $d, 'to' => $d, 'sessions' => OPENING_HOURS[$d]];
        }
    }
    $rows[] = $run;

    return array_map(static function (array $r) use ($names): array {
        $full  = $r['from'] === $r['to']
            ? $names[$r['from']]
            : $names[$r['from']] . ' – ' . $names[$r['to']];
        $label = $r['from'] === $r['to']
            ? $names[$r['from']]
            : substr($names[$r['from']], 0, 3) . ' – ' . substr($names[$r['to']], 0, 3);
        $list  = array_map(
            static fn(array $s) => fmt_time($s[0]) . ' – ' . fmt_time($s[1]),
            $r['sessions']
        );
        return [
            'days'      => $label,
            'days_full' => $full,
            'times'     => $list ? implode(', ', $list) : 'Closed',
            'sessions'  => $list,
            'open'      => (bool) $r['sessions'],
        ];
    }, $rows);
}

/**
 * Opening hours on one line, e.g. "Mon – Sun, 9:30 AM – 2:00 PM & 5:30 PM – 9:00 PM".
 *
 * Every piece of running copy that quotes the hours calls this instead of
 * spelling them out. Hard-coded hours are how a site ends up advertising times
 * the booking engine will not actually offer.
 */
function hours_inline(): string
{
    $parts = [];
    foreach (hours_summary() as $row) {
        $parts[] = $row['open']
            ? $row['days'] . ', ' . implode(' & ', $row['sessions'])
            : $row['days'] . ' closed';
    }
    return implode(' · ', $parts);
}

/** "The clinic is closed on Sundays." — empty string when it is open all week. */
function closed_days_sentence(): string
{
    $shut = [];
    foreach (hours_summary() as $row) {
        if (!$row['open']) {
            $shut[] = $row['days_full'];
        }
    }
    if (!$shut) {
        return '';
    }
    return 'The clinic is closed on ' . implode(' and ', $shut) . '.';
}

function fmt_time(string $hhmm): string
{
    return (new DateTimeImmutable($hhmm))->format('g:i A');
}

function is_open_now(?DateTimeImmutable $now = null): bool
{
    $now      = $now ?? new DateTimeImmutable('now');
    $sessions = OPENING_HOURS[(int) $now->format('N')] ?? [];
    $minutes  = (int) $now->format('G') * 60 + (int) $now->format('i');

    foreach ($sessions as [$from, $to]) {
        if ($minutes >= hhmm_to_min($from) && $minutes < hhmm_to_min($to)) {
            return true;
        }
    }
    return false;
}

/**
 * Live status with the next thing that actually happens.
 *
 * "Currently closed" on its own reads as "do not bother" — but at 3 PM on a
 * working day the clinic reopens in two and a half hours, and at 9 PM it reopens
 * tomorrow morning. Telling the patient *when* is the difference between them
 * waiting and them going elsewhere, and it is the only hours information small
 * screens have room for.
 *
 * @return array{open:bool, label:string, detail:string}
 */
function open_status(?DateTimeImmutable $now = null): array
{
    $now     = $now ?? new DateTimeImmutable('now');
    $minutes = (int) $now->format('G') * 60 + (int) $now->format('i');

    // Open right now — say when this session ends.
    foreach (OPENING_HOURS[(int) $now->format('N')] ?? [] as [$from, $to]) {
        if ($minutes >= hhmm_to_min($from) && $minutes < hhmm_to_min($to)) {
            return ['open' => true, 'label' => 'Open now', 'detail' => 'until ' . fmt_time($to)];
        }
    }

    // Otherwise walk forward to the next session that starts. Eight days covers
    // any schedule, including one with only a single open day in the week.
    for ($i = 0; $i <= 7; $i++) {
        $day = $now->modify("+$i days");
        foreach (OPENING_HOURS[(int) $day->format('N')] ?? [] as [$from, $to]) {
            if ($i === 0 && $minutes >= hhmm_to_min($from)) {
                continue;   // that session has already ended today
            }
            $when = match (true) {
                $i === 0 => 'today',
                $i === 1 => 'tomorrow',
                default  => $day->format('l'),
            };
            return ['open' => false, 'label' => 'Opens ' . fmt_time($from) . ' ' . $when, 'detail' => ''];
        }
    }

    return ['open' => false, 'label' => 'Currently closed', 'detail' => ''];
}

function hhmm_to_min(string $hhmm): int
{
    [$h, $m] = array_map('intval', explode(':', $hhmm));
    return $h * 60 + $m;
}

function min_to_hhmm(int $min): string
{
    return sprintf('%02d:%02d', intdiv($min, 60), $min % 60);
}

/* ---------------------------------------------------------- slot engine ---- */

/** Every slot the clinic *could* offer on a given date, ignoring bookings. */
function slots_for_date(string $date): array
{
    $day      = (int) (new DateTimeImmutable($date))->format('N');
    $sessions = OPENING_HOURS[$day] ?? [];
    $slots    = [];

    foreach ($sessions as $i => [$from, $to]) {
        $start = hhmm_to_min($from);
        $end   = hhmm_to_min($to);
        for ($m = $start; $m + SLOT_MINUTES <= $end; $m += SLOT_MINUTES) {
            $slots[] = ['time' => min_to_hhmm($m), 'session' => $i === 0 ? 'Morning' : 'Evening'];
        }
    }
    return $slots;
}

function booking_window(): array
{
    $today = new DateTimeImmutable('today');
    return [$today, $today->modify('+' . BOOKING_WINDOW_DAYS . ' days')];
}

function date_is_bookable(string $date): bool
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return false;
    }
    $d = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
    if (!$d) {
        return false;
    }
    [$from, $to] = booking_window();
    return $d >= $from && $d <= $to && !empty(OPENING_HOURS[(int) $d->format('N')]);
}

/**
 * Slots actually offerable to a patient: opening hours, minus booked slots,
 * minus admin blocks, minus anything too soon (or in the past) today.
 *
 * @return array<int, array{time:string, session:string, available:bool, label:string}>
 */
function available_slots(string $date): array
{
    $slots = slots_for_date($date);
    if (!$slots || !date_is_bookable($date)) {
        return [];
    }

    $taken   = [];
    $blocked = [];
    $dayOff  = false;

    if (db_available()) {
        $st = db()->prepare(
            "SELECT appt_time FROM appointments WHERE appt_date = ? AND status <> 'cancelled'"
        );
        $st->execute([$date]);
        foreach ($st->fetchAll() as $r) {
            $taken[substr((string) $r['appt_time'], 0, 5)] = true;
        }

        $st = db()->prepare('SELECT block_time FROM blocked_slots WHERE block_date = ?');
        $st->execute([$date]);
        foreach ($st->fetchAll() as $r) {
            if ($r['block_time'] === null) {
                $dayOff = true;
            } else {
                $blocked[substr((string) $r['block_time'], 0, 5)] = true;
            }
        }
    }

    $cutoff = (new DateTimeImmutable('now'))->modify('+' . BOOKING_LEAD_MINUTES . ' minutes');

    return array_map(static function (array $s) use ($date, $taken, $blocked, $dayOff, $cutoff): array {
        $when = new DateTimeImmutable($date . ' ' . $s['time']);
        $free = !$dayOff
            && !isset($taken[$s['time']])
            && !isset($blocked[$s['time']])
            && $when > $cutoff;

        return [
            'time'      => $s['time'],
            'session'   => $s['session'],
            'available' => $free,
            'label'     => fmt_time($s['time']),
        ];
    }, $slots);
}

/* -------------------------------------------------------------- helpers ---- */

function booking_reference(): string
{
    return 'ORD-' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
}

/** Accepts 10-digit Indian mobiles with or without +91 / 0 prefixes. */
function normalise_phone(string $raw): ?string
{
    $digits = preg_replace('/\D+/', '', $raw) ?? '';
    if (str_starts_with($digits, '91') && strlen($digits) === 12) {
        $digits = substr($digits, 2);
    } elseif (str_starts_with($digits, '0') && strlen($digits) === 11) {
        $digits = substr($digits, 1);
    }
    return preg_match('/^[6-9]\d{9}$/', $digits) ? $digits : null;
}

function json_out(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function client_ip(): string
{
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
}
