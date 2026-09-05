<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/data.php';

header('Content-Type: application/xml; charset=utf-8');
header('Cache-Control: public, max-age=3600');

$today = date('Y-m-d');
$pages = [
    ['',          '1.0', 'weekly'],
    ['about',     '0.9', 'monthly'],
    ['services',  '0.9', 'monthly'],
    ['book',      '0.9', 'weekly'],
    ['contact',   '0.8', 'monthly'],
    ['gallery',   '0.6', 'monthly'],
    ['faq',       '0.7', 'monthly'],
    ['privacy',   '0.2', 'yearly'],
    ['terms',     '0.2', 'yearly'],
];
foreach (array_keys(SERVICES) as $slug) {
    $pages[] = ['services/' . $slug, '0.85', 'monthly'];
}

echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($pages as [$path, $prio, $freq]): ?>
  <url>
    <loc><?= e(url($path)) ?></loc>
    <lastmod><?= $today ?></lastmod>
    <changefreq><?= $freq ?></changefreq>
    <priority><?= $prio ?></priority>
  </url>
<?php endforeach; ?>
</urlset>
