<?php
/**
 * ORADENT Dental Care — central configuration.
 *
 * Every clinic-specific fact lives here (single source of truth). Change it once
 * and it updates the pages, the schema.org markup, the sitemap and the booking
 * engine together, which is what keeps NAP data consistent for local SEO.
 *
 * Secrets are NOT stored here. Copy .env.example to .env and fill it in; .env is
 * gitignored so credentials never reach the repository.
 */

declare(strict_types=1);

date_default_timezone_set('Asia/Kolkata');

/* ------------------------------------------------------------------ env ---- */

function env(string $key, ?string $default = null): ?string
{
    static $vars = null;
    if ($vars === null) {
        $vars = [];
        $file = dirname(__DIR__) . '/.env';
        // is_readable() alone is true for a *directory* named .env, and file()
        // then warns and returns false. Insist on a real file so a mistaken
        // .env/ folder is reported by db_diagnosis() instead of half-failing.
        if (is_file($file) && is_readable($file)) {
            foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $vars[trim($k)] = trim(trim($v), "\"'");
            }
        }
    }
    return $vars[$key] ?? (getenv($key) !== false ? getenv($key) : $default);
}

/* ------------------------------------------------------------- database ---- */

define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_NAME', env('DB_NAME', 'oradent'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_PORT', (int) env('DB_PORT', '3306'));

/* ------------------------------------------------------------------ site --- */

define('SITE_URL',   rtrim(env('SITE_URL', 'https://oradentdentalcare.com'), '/'));
define('SITE_NAME',  'ORADENT Dental Care');
define('SITE_TAGLINE', 'Your Smile, Our Commitment.');
define('DEBUG',      env('APP_DEBUG', 'false') === 'true');

/* ---------------------------------------------------------------- clinic --- */

/**
 * NAP (Name / Address / Phone). Keep this byte-identical to the Google Business
 * Profile and every directory listing — inconsistencies dilute local rankings.
 */
const CLINIC = [
    'name'            => 'ORADENT Dental Care',
    'legal_name'      => 'ORADENT Dental Care',
    'doctor'          => 'Dr. Nabanita Chaklader',
    'doctor_qual'     => 'BDS (WBUHS)',
    'doctor_role'     => 'Dental Surgeon — Root Canal Treatment & Oral and Maxillofacial Surgery',
    'tagline'         => 'Your Smile, Our Commitment.',

    // Taken from the verified Google Business Profile, so the site and the
    // listing say the same thing — which is what local ranking rewards.
    'street'          => '48, Maharani Indira Devi Road',
    'landmark'        => 'Near Sakshi Baban, opposite Gurunan Math',
    // One area label, used everywhere: page titles, the hero badge, the top bar,
    // the schema.org address and the service pages. Keeping it single-valued is
    // what stops the site saying three different things about where it is.
    'locality'        => 'Parnasree, Behala',
    'region'          => 'West Bengal',
    'region_code'     => 'WB',
    'postal'          => '700060',
    'country'         => 'IN',
    'city'            => 'Kolkata',

    // Coordinates of the Google Business Profile pin.
    'lat'             => 22.5043803,
    'lng'             => 88.3031698,

    'phone'           => '+919830169230',   // E.164, used for tel: and schema
    'phone_display'   => '+91 98301 69230',
    'whatsapp'        => '919830169230',    // wa.me format, no plus
    'email'           => 'doctornabanita@gmail.com',

    'maps_url'        => 'https://maps.app.goo.gl/uoUen21Z47qxKw5d7',
    'maps_embed_q'    => 'ORADENT+DENTAL+CARE+(Alap+Apartment)',
    'price_range'     => '₹₹',
    'founded'         => '2023',
];

/** Scalar mirrors of the most-used CLINIC values, usable inside const expressions. */
define('CLINIC_PHONE',         CLINIC['phone']);
define('CLINIC_PHONE_DISPLAY', CLINIC['phone_display']);
define('CLINIC_LOCALITY',      CLINIC['locality']);
define('CLINIC_CITY',          CLINIC['city']);

/* ------------------------------------------------------- opening hours ----- */

/**
 * Open every day, two sessions a day.
 * Times are 24-hour "H:i". Used by the booking engine AND by the
 * openingHoursSpecification in the schema markup, so they can never drift apart.
 * Every line of copy that quotes the hours is generated from this array too —
 * see hours_summary() and hours_inline() in includes/functions.php.
 */
const OPENING_HOURS = [
    1 => [['09:30', '14:00'], ['17:30', '21:00']], // Monday
    2 => [['09:30', '14:00'], ['17:30', '21:00']],
    3 => [['09:30', '14:00'], ['17:30', '21:00']],
    4 => [['09:30', '14:00'], ['17:30', '21:00']],
    5 => [['09:30', '14:00'], ['17:30', '21:00']],
    6 => [['09:30', '14:00'], ['17:30', '21:00']], // Saturday
    7 => [['09:30', '14:00'], ['17:30', '21:00']], // Sunday
];

/* ------------------------------------------------------------- booking ----- */

define('SLOT_MINUTES',        15);  // length of one appointment slot
define('BOOKING_WINDOW_DAYS', 30);  // how far ahead patients may book
define('BOOKING_LEAD_MINUTES', 60); // minimum notice for a same-day booking
define('BOOKING_MAX_PER_PHONE_PER_DAY', 3); // light abuse guard

/* ----------------------------------------------------------- watermark ----- */

/**
 * The domain name stamped onto photos and videos, so a picture lifted from the
 * site still says where it came from.
 *
 *   images  Burned into the resized copies the site serves (top-right). The
 *           files in assets/images/ are never modified, so switching this off
 *           or changing the text simply produces fresh copies.
 *   videos  A domain label is overlaid on the player. To burn it into the file
 *           itself, run tools/watermark-video.sh before adding the clip.
 *
 * Only the placeholders listed in 'placeholders' are marked — the hero and the
 * doctor's portraits are left clean on purpose.
 */
const WATERMARK = [
    'enabled'      => true,
    'text'         => '',                       // '' = the domain from SITE_URL
    'placeholders' => ['slider', 'gallery'],
    'opacity'      => 0.6,                      // 0 (invisible) … 1 (solid)
];

/* --------------------------------------------------------------- admin ----- */

define('ADMIN_SESSION_NAME',    'oradent_admin');
define('ADMIN_SESSION_LIFETIME', 60 * 60 * 8);
define('ADMIN_MAX_LOGIN_TRIES',  6);
define('ADMIN_LOCKOUT_MINUTES',  15);
