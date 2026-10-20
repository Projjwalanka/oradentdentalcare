<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/data.php';
require_once __DIR__ . '/media_lib.php';

/**
 * Per-page SEO state. Pages set these before including header.php.
 */
final class Page
{
    public static string $title       = '';
    public static string $description = '';
    public static string $path        = '/';
    public static string $image       = '/assets/images/brand/social-share.jpg';
    public static string $type        = 'website';
    public static bool   $noindex     = false;
    /** @var array<int, array{name:string, url:string}> */
    public static array  $breadcrumbs = [];
    /** @var array<int, array{0:string,1:string}> */
    public static array  $faqs        = [];
    /** @var array<int, array<string,mixed>> extra JSON-LD graph nodes */
    public static array  $schema      = [];

    public static function canonical(): string
    {
        return url(ltrim(self::$path, '/'));
    }
}

/* ------------------------------------------------------------ schema.org --- */

/**
 * The @id anchors let the graph nodes reference one another, which is what
 * search engines use to understand that the Dentist, the Person and the
 * WebSite all describe one business.
 */
function schema_organization(): array
{
    $openingSpec = [];
    $dayNames    = [1 => 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    // One specification per session so morning and evening are both explicit.
    $bySession = [];
    for ($d = 1; $d <= 7; $d++) {
        foreach (OPENING_HOURS[$d] as $i => [$from, $to]) {
            $bySession[$i . '|' . $from . '|' . $to][] = $dayNames[$d];
        }
    }
    foreach ($bySession as $key => $days) {
        [, $from, $to] = explode('|', $key);
        $openingSpec[] = [
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => array_map(static fn($d) => 'https://schema.org/' . $d, $days),
            'opens'     => $from,
            'closes'    => $to,
        ];
    }

    return [
        '@type'  => ['Dentist', 'MedicalBusiness', 'LocalBusiness'],
        '@id'    => url('#clinic'),
        'name'   => CLINIC['name'],
        'legalName' => CLINIC['legal_name'],
        'alternateName' => 'ORADENT Dental Clinic',
        'url'    => url(),
        'logo'   => ['@type' => 'ImageObject', '@id' => url('#logo'), 'url' => url(brand_asset('logo'))],
        'image'  => array_map(static fn (array $p) => url($p['src']), array_slice(media_images('gallery'), 0, 2)),
        'description' => 'ORADENT Dental Care is a dental and oral surgery clinic in ' . CLINIC['locality']
            . ', ' . CLINIC['city'] . ', led by ' . CLINIC['doctor'] . ', ' . CLINIC['doctor_qual']
            . ' — a dental surgeon with special interests in root canal treatment and oral & maxillofacial surgery.',
        'slogan'   => CLINIC['tagline'],
        'telephone' => CLINIC['phone'],
        'email'    => CLINIC['email'],
        'priceRange' => CLINIC['price_range'],
        'currenciesAccepted' => 'INR',
        'paymentAccepted'    => 'Cash, UPI',
        'foundingDate'       => CLINIC['founded'],
        'address' => [
            '@type'           => 'PostalAddress',
            'streetAddress'   => CLINIC['street'],
            'addressLocality' => CLINIC['locality'],
            'addressRegion'   => CLINIC['region'],
            'postalCode'      => CLINIC['postal'],
            'addressCountry'  => CLINIC['country'],
        ],
        'geo' => [
            '@type'     => 'GeoCoordinates',
            'latitude'  => CLINIC['lat'],
            'longitude' => CLINIC['lng'],
        ],
        'hasMap'    => CLINIC['maps_url'],
        'openingHoursSpecification' => $openingSpec,
        'areaServed' => array_map(
            static fn(string $a) => ['@type' => 'Place', 'name' => $a],
            ['Parnasree', 'Behala', 'Barisha', 'Sakher Bazar', 'Thakurpukur', 'Joka', 'Kolkata']
        ),
        'availableService' => array_values(array_map(
            static fn(string $slug, array $s) => [
                '@type'       => 'MedicalProcedure',
                'name'        => $s['name'],
                'description' => $s['short'],
                'url'         => url('services/' . $slug),
            ],
            array_keys(SERVICES),
            SERVICES
        )),
        'founder'  => ['@id' => url('#dentist')],
        'employee' => ['@id' => url('#dentist')],
        'potentialAction' => [
            '@type'  => 'ReserveAction',
            'name'   => 'Book an appointment',
            'target' => [
                '@type'       => 'EntryPoint',
                'urlTemplate' => url('book'),
                'inLanguage'  => 'en-IN',
                'actionPlatform' => [
                    'https://schema.org/DesktopWebPlatform',
                    'https://schema.org/MobileWebPlatform',
                ],
            ],
            'result' => ['@type' => 'Reservation', 'name' => 'Dental appointment'],
        ],
    ];
}

function schema_dentist_person(): array
{
    return [
        '@type'      => ['Person', 'Physician'],
        '@id'        => url('#dentist'),
        'name'       => CLINIC['doctor'],
        'honorificPrefix' => 'Dr.',
        'jobTitle'   => 'Dental Surgeon',
        'description' => CLINIC['doctor'] . ' is a dental surgeon practising in ' . CLINIC['locality']
            . ', ' . CLINIC['city'] . ', with special interests in root canal treatment (endodontics) '
            . 'and oral & maxillofacial surgery.',
        'url'        => url('about'),
        'image'      => url((media_first('about') ?? media_first('hero'))['src'] ?? 'assets/images/brand/social-share.jpg'),
        'medicalSpecialty' => ['https://schema.org/Dentistry'],
        'knowsAbout' => ['Root Canal Treatment', 'Oral and Maxillofacial Surgery', 'Dental Implants',
                         'Cosmetic Dentistry', 'Pediatric Dentistry'],
        'hasCredential' => [
            '@type'                => 'EducationalOccupationalCredential',
            'credentialCategory'   => 'degree',
            'educationalLevel'     => 'Bachelor of Dental Surgery',
            'name'                 => 'BDS',
            'recognizedBy'         => [
                '@type' => 'CollegeOrUniversity',
                'name'  => 'The West Bengal University of Health Sciences (WBUHS)',
            ],
        ],
        'worksFor'  => ['@id' => url('#clinic')],
        'workLocation' => ['@id' => url('#clinic')],
    ];
}

function schema_website(): array
{
    return [
        '@type'    => 'WebSite',
        '@id'      => url('#website'),
        'url'      => url(),
        'name'     => CLINIC['name'],
        'inLanguage' => 'en-IN',
        'publisher'  => ['@id' => url('#clinic')],
    ];
}

function schema_breadcrumbs(): ?array
{
    if (!Page::$breadcrumbs) {
        return null;
    }
    $items = [];
    foreach (Page::$breadcrumbs as $i => $c) {
        $items[] = [
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $c['name'],
            'item'     => $c['url'],
        ];
    }
    return ['@type' => 'BreadcrumbList', '@id' => Page::canonical() . '#breadcrumbs', 'itemListElement' => $items];
}

function schema_faqs(): ?array
{
    if (!Page::$faqs) {
        return null;
    }
    return [
        '@type'      => 'FAQPage',
        '@id'        => Page::canonical() . '#faq',
        'mainEntity' => array_map(static fn(array $f) => [
            '@type'          => 'Question',
            'name'           => $f[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
        ], Page::$faqs),
    ];
}

function schema_graph(): string
{
    $graph = [schema_organization(), schema_dentist_person(), schema_website()];

    foreach ([schema_breadcrumbs(), schema_faqs()] as $node) {
        if ($node) {
            $graph[] = $node;
        }
    }
    foreach (Page::$schema as $node) {
        $graph[] = $node;
    }

    return json_encode(
        ['@context' => 'https://schema.org', '@graph' => $graph],
        JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
    );
}
