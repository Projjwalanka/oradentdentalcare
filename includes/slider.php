<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Folder-driven image slider.
 *
 * Drop any image into  assets/img/slider/  and it appears in the slider on the
 * next page load. Nothing else to edit. Remove it and it disappears. The number
 * of slides, the dots and the arrows all follow the folder.
 *
 * ORDER      Files are sorted naturally by filename, so a numeric prefix
 *            controls the sequence: 01-…, 02-…, 10-… sorts correctly.
 *
 * SIZE       Phone photos are far too heavy to serve as-is, so the first time
 *            an image is seen it is resized once into assets/img/slider/.cache/
 *            at 800w and 1400w. After that the browser gets plain static files —
 *            no PHP in the request path. If the cache folder cannot be written
 *            (read-only deploy), the original is served instead and the page
 *            still works.
 *
 * WORDS      Titles and alt text are derived from the filename, so
 *            "02-treatment-room.jpg" becomes "Treatment room". To write better
 *            text, add assets/img/slider/captions.php — see the sample there.
 */

const SLIDER_DIR   = 'assets/img/slider';
const SLIDER_CACHE = 'assets/img/slider/.cache';
const SLIDER_SIZES = [800, 1400];
const SLIDER_EXT   = ['jpg', 'jpeg', 'png', 'webp'];

function slider_root(): string
{
    return dirname(__DIR__) . '/' . SLIDER_DIR;
}

/** Human-readable label from a filename: "02-treatment-room.jpg" -> "Treatment room". */
function slider_label_from_filename(string $file): string
{
    $name = pathinfo($file, PATHINFO_FILENAME);
    $name = preg_replace('/^[\d]+[\s._-]+/', '', $name) ?? $name;  // strip an ordering prefix
    $name = str_replace(['-', '_'], ' ', $name);
    $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    return $name === '' ? 'Clinic photo' : ucfirst($name);
}

/** Optional per-file captions: assets/img/slider/captions.php returning an array. */
function slider_captions(): array
{
    static $caps = null;
    if ($caps === null) {
        $file = slider_root() . '/captions.php';
        $caps = is_readable($file) ? (require $file) : [];
        if (!is_array($caps)) {
            $caps = [];
        }
    }
    return $caps;
}

/**
 * Make (once) a resized copy in the cache folder.
 * Returns a web path, or null when resizing is unavailable or pointless.
 */
function slider_variant(string $file, int $width): ?string
{
    $src = slider_root() . '/' . $file;
    if (!is_file($src) || !function_exists('imagecreatetruecolor')) {
        return null;
    }

    $cacheDir = dirname(__DIR__) . '/' . SLIDER_CACHE;
    if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
        return null;                                   // read-only deploy: serve originals
    }

    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $out  = $cacheDir . '/' . pathinfo($file, PATHINFO_FILENAME) . "-$width." . ($ext === 'png' ? 'png' : 'jpg');
    $web  = '/' . SLIDER_CACHE . '/' . basename($out);

    // Rebuild only if missing or older than the source
    if (is_file($out) && filemtime($out) >= filemtime($src)) {
        return $web;
    }

    $info = @getimagesize($src);
    if (!$info) {
        return null;
    }
    [$sw, $sh] = $info;
    if ($sw <= $width) {
        return null;                                   // already small enough — use the original
    }

    $im = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($src),
        IMAGETYPE_PNG  => @imagecreatefrompng($src),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($src) : false,
        default        => false,
    };
    if (!$im) {
        return null;
    }

    $nh  = (int) round($sh * $width / $sw);
    $dst = imagecreatetruecolor($width, $nh);
    if ($info[2] === IMAGETYPE_PNG) {
        imagealphablending($dst, false);
        imagesavealpha($dst, true);
    }
    imagecopyresampled($dst, $im, 0, 0, 0, 0, $width, $nh, $sw, $sh);

    $ok = $info[2] === IMAGETYPE_PNG
        ? imagepng($dst, $out, 7)
        : imagejpeg($dst, $out, 82);

    imagedestroy($im);
    imagedestroy($dst);

    return $ok ? $web : null;
}

/**
 * Every image in the slider folder, ready for the showcase renderer.
 *
 * @return array<int, array{type:string, src:string, srcset:string, w:int, h:int,
 *                          alt:string, title:string, caption:string}>
 */
function slider_images(): array
{
    $dir = slider_root();
    if (!is_dir($dir)) {
        return [];
    }

    $files = [];
    foreach (scandir($dir) ?: [] as $f) {
        if ($f[0] === '.' ) {
            continue;                                  // skips .cache and dotfiles
        }
        if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), SLIDER_EXT, true)
            && is_file("$dir/$f")) {
            $files[] = $f;
        }
    }
    natcasesort($files);

    $caps  = slider_captions();
    $items = [];

    foreach ($files as $f) {
        $meta = $caps[$f] ?? [];
        $label = $meta['title'] ?? slider_label_from_filename($f);

        $original = '/' . SLIDER_DIR . '/' . rawurlencode($f);
        $size     = @getimagesize("$dir/$f") ?: [1600, 1000];

        $srcset = [];
        foreach (SLIDER_SIZES as $w) {
            $v = slider_variant($f, $w);
            if ($v !== null) {
                $srcset[] = $v . ' ' . $w . 'w';
            }
        }
        $largest = SLIDER_SIZES[count(SLIDER_SIZES) - 1];
        $src     = slider_variant($f, $largest) ?? $original;

        $items[] = [
            'type'    => 'image',
            'src'     => $src,
            'srcset'  => $srcset ? implode(', ', $srcset) : '',
            'w'       => (int) $size[0],
            'h'       => (int) $size[1],
            'alt'     => $meta['alt'] ?? ($label . ' at ' . CLINIC['name'] . ', '
                                          . CLINIC['locality'] . ', ' . CLINIC['city']),
            'title'   => $label,
            'caption' => $meta['caption'] ?? '',
        ];
    }

    return $items;
}
