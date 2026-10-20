<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Folder-driven media library.
 *
 * THE RULE: one folder per placeholder on the site, photos and videos kept
 * apart. Drop a file in and it appears on the next page load; delete it and it
 * goes. No code changes, ever.
 *
 *   assets/images/hero/       home page hero portrait        (first file)
 *   assets/images/dentist/    home page "Meet your dentist"  (first file)
 *   assets/images/about/      About page portrait            (first file)
 *   assets/images/slider/     home page slider     ┐ photos and videos are
 *   assets/videos/slider/     home page slider     ┘ mixed, ordered by filename
 *   assets/images/gallery/    Gallery page, Photos tab
 *   assets/videos/gallery/    Gallery page, Videos tab
 *   assets/images/brand/      logo, icons, social-share card (fixed names)
 *
 * Because each placeholder reads only its own folder, adding a gallery photo
 * can never disturb the hero, and replacing the portrait can never reorder the
 * gallery.
 *
 * ORDER      Natural filename sort, so a numeric prefix sets the sequence:
 *            01-…, 02-…, 10-… order correctly. In the slider, photos and videos
 *            share one sequence, so 02-tour.mp4 plays between 01-… and 03-….
 *
 * WORDS      The filename becomes the title: "02-treatment-room.jpg" ->
 *            "Treatment room". For better wording and alt text, add an entry to
 *            that folder's captions.php.
 *
 * SIZE       A photo straight off a phone is fine — it is turned upright from
 *            its EXIF orientation and resized once into the folder's .cache/,
 *            then served as a static file. If .cache/ cannot be written
 *            (read-only deploy) the original is served and the page still works.
 *
 * WATERMARK  Placeholders listed in WATERMARK (config/config.php) get the domain
 *            name stamped on every photo copy and video poster, and overlaid on
 *            the video player. The originals in assets/ are never modified.
 *
 * VIDEO      assets/videos/<placeholder>/posters/<same-name>.jpg is the poster
 *            frame. Without one the clip still plays, showing its first frame.
 */

const MEDIA_SIZES = [800, 1400];
const MEDIA_IMG   = ['jpg', 'jpeg', 'png', 'webp'];
const MEDIA_VIDEO = ['mp4', 'webm'];
const MEDIA_FONT  = __DIR__ . '/fonts/DejaVuSans-Bold.ttf';

/* ------------------------------------------------------------------ paths -- */

/** Site-relative folder of one placeholder: media_dir('images', 'hero'). */
function media_dir(string $kind, string $placeholder): string
{
    return 'assets/' . $kind . '/' . trim($placeholder, '/');
}

/** Absolute filesystem path of a site-relative path. */
function media_path(string $rel): string
{
    return dirname(__DIR__) . '/' . $rel;
}

/**
 * Web URL of a site-relative file, cache-busted by its modification time.
 * .htaccess serves media as immutable for a year, so without the ?v= a photo
 * replaced under the same filename would stay stale in returning visitors'
 * browsers.
 */
function media_url(string $rel): string
{
    $mtime = @filemtime(media_path($rel));
    return '/' . implode('/', array_map('rawurlencode', explode('/', $rel)))
         . ($mtime ? '?v=' . $mtime : '');
}

/* ------------------------------------------------------------------ words -- */

/** "02-treatment-room.jpg" -> "Treatment room". */
function media_label(string $file): string
{
    $name = pathinfo($file, PATHINFO_FILENAME);
    $name = preg_replace('/^\d+[\s._-]+/', '', $name) ?? $name;   // strip ordering prefix
    $name = str_replace(['-', '_'], ' ', $name);
    $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    return $name === '' ? 'Clinic photo' : ucfirst($name);
}

/** Optional per-file overrides from <dir>/captions.php. */
function media_captions(string $dir): array
{
    static $cache = [];
    if (!array_key_exists($dir, $cache)) {
        $file = media_path($dir) . '/captions.php';
        $caps = is_readable($file) ? (require $file) : [];
        $cache[$dir] = is_array($caps) ? $caps : [];
    }
    return $cache[$dir];
}

/** Filenames of the given types in a folder, naturally sorted. */
function media_files(string $dir, array $extensions): array
{
    $abs = media_path($dir);
    if (!is_dir($abs)) {
        return [];
    }
    $files = [];
    foreach (scandir($abs) ?: [] as $f) {
        if ($f === '' || $f[0] === '.') {
            continue;                                   // skips .cache and dotfiles
        }
        if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $extensions, true)
            && is_file("$abs/$f")) {
            $files[] = $f;
        }
    }
    natcasesort($files);
    return array_values($files);
}

/* -------------------------------------------------------------- watermark -- */

/** The text stamped on media: WATERMARK['text'], or the bare domain. */
function watermark_text(): string
{
    $text = trim((string) (WATERMARK['text'] ?? ''));
    if ($text !== '') {
        return $text;
    }
    $host = (string) parse_url(SITE_URL, PHP_URL_HOST);
    return (string) preg_replace('/^www\./i', '', $host);
}

/** Whether a placeholder's media should carry the watermark. */
function watermark_on(string $placeholder): bool
{
    return !empty(WATERMARK['enabled'])
        && in_array($placeholder, WATERMARK['placeholders'] ?? [], true)
        && watermark_text() !== '';
}

/**
 * Stamp the watermark top-right — clear of the caption the slider lays over
 * the bottom of each slide, and where the video overlay sits.
 *
 * The slider (16:10) and gallery (4:3) crop photos to fill their frames, centred,
 * so a mark in the true corner of a portrait or panoramic photo would be cut
 * off. It goes in the corner of the area that both crops keep instead — which
 * for an ordinary landscape photo is simply the corner.
 */
function watermark_stamp(\GdImage $im): void
{
    $text    = watermark_text();
    $iw      = imagesx($im);
    $ih      = imagesy($im);
    $w       = (int) round(min($iw, $ih * 4 / 3));     // width every crop keeps
    $h       = (int) round(min($ih, $iw * 10 / 16));   // height every crop keeps
    $right   = (int) round(($iw + $w) / 2);
    $top     = (int) round(($ih - $h) / 2);
    $opacity = max(0.0, min(1.0, (float) (WATERMARK['opacity'] ?? 0.6)));
    $alpha   = static fn (float $o): int => (int) round(127 * (1 - $o));   // GD: 0 opaque … 127 clear
    $pad     = (int) max(8, round(min($w, $h) * 0.03));

    imagealphablending($im, true);
    $ink    = imagecolorallocatealpha($im, 255, 255, 255, $alpha($opacity));
    $shadow = imagecolorallocatealpha($im, 0, 0, 0, $alpha($opacity * 0.6));

    if (function_exists('imagettftext') && is_file(MEDIA_FONT)) {
        $size = max(8.0, $w * 0.0125);                 // ~18pt on a 1400px copy
        $box  = imagettfbbox($size, 0, MEDIA_FONT, $text);
        if ($box !== false) {
            $x   = $right - $pad - $box[2];
            $y   = $top + $pad - $box[5];               // y is the baseline; box[5] the cap top
            $off = (int) max(1, round($size / 12));
            imagettftext($im, $size, 0, $x + $off, $y + $off, $shadow, MEDIA_FONT, $text);
            imagettftext($im, $size, 0, $x, $y, $ink, MEDIA_FONT, $text);
            return;
        }
    }

    /* No FreeType: draw GD's built-in bitmap font on a small transparent
       canvas and scale that up. Blockier, but still clearly legible. */
    $font  = 5;
    $tw    = imagefontwidth($font) * strlen($text) + 1;
    $th    = imagefontheight($font) + 1;
    $label = imagecreatetruecolor($tw, $th);
    imagealphablending($label, false);
    imagesavealpha($label, true);
    imagefill($label, 0, 0, imagecolorallocatealpha($label, 0, 0, 0, 127));
    imagestring($label, $font, 1, 1, $text, imagecolorallocatealpha($label, 0, 0, 0, $alpha($opacity * 0.6)));
    imagestring($label, $font, 0, 0, $text, imagecolorallocatealpha($label, 255, 255, 255, $alpha($opacity)));

    $scale = max(1.0, ($w * 0.017) / $th);
    $dw    = (int) round($tw * $scale);
    $dh    = (int) round($th * $scale);
    imagecopyresampled($im, $label, $right - $pad - $dw, $top + $pad, 0, 0, $dw, $dh, $tw, $th);
}

/* ----------------------------------------------------------------- images -- */

/** EXIF orientation of a JPEG (1 = upright), when the exif extension exists. */
function media_orientation(string $abs): int
{
    if (!function_exists('exif_read_data')) {
        return 1;
    }
    $exif = @exif_read_data($abs);
    return (int) ($exif['Orientation'] ?? 1);
}

/** Display dimensions [w, h] — swapped for photos the camera stored sideways. */
function media_dims(string $abs): ?array
{
    $info = @getimagesize($abs);
    if (!$info) {
        return null;
    }
    [$w, $h] = $info;
    if ($info[2] === IMAGETYPE_JPEG && in_array(media_orientation($abs), [5, 6, 7, 8], true)) {
        [$w, $h] = [$h, $w];
    }
    return [(int) $w, (int) $h];
}

/** Decode an image, turned upright. */
function media_load(string $abs): ?\GdImage
{
    $info = @getimagesize($abs);
    if (!$info) {
        return null;
    }
    $im = match ($info[2]) {
        IMAGETYPE_JPEG => @imagecreatefromjpeg($abs),
        IMAGETYPE_PNG  => @imagecreatefrompng($abs),
        IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($abs) : false,
        default        => false,
    };
    if (!$im) {
        return null;
    }
    if ($info[2] === IMAGETYPE_JPEG) {
        // imagerotate() turns counter-clockwise
        $turn = [3 => 180, 6 => 270, 8 => 90][media_orientation($abs)] ?? 0;
        if ($turn && ($upright = imagerotate($im, $turn, 0))) {
            $im = $upright;
        }
    }
    return $im;
}

/** Write one resized, optionally watermarked copy. Atomic: never half-written. */
function media_render(string $src, string $out, int $width, bool $mark): bool
{
    $dir = dirname($out);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
        return false;                                   // read-only deploy
    }
    $im = media_load($src);
    if (!$im) {
        return false;
    }

    $png = str_ends_with($out, '.png');
    $sw  = imagesx($im);
    $sh  = imagesy($im);
    if ($width < $sw) {
        $dst = imagecreatetruecolor($width, max(1, (int) round($sh * $width / $sw)));
        if ($png) {
            imagealphablending($dst, false);
            imagesavealpha($dst, true);
        }
        imagecopyresampled($dst, $im, 0, 0, 0, 0, imagesx($dst), imagesy($dst), $sw, $sh);
        $im = $dst;
    }

    if ($mark) {
        watermark_stamp($im);
    }

    $tmp = $out . '.' . getmypid() . '.tmp';
    if ($png) {
        imagesavealpha($im, true);
        $ok = imagepng($im, $tmp, 7);
    } else {
        imageinterlace($im, true);                      // progressive JPEG
        $ok = imagejpeg($im, $tmp, 82);
    }
    if (!$ok || !@rename($tmp, $out)) {
        @unlink($tmp);
        return false;
    }
    return true;
}

/**
 * A resized (and, where configured, watermarked) copy in <dir>/.cache/, built
 * on first request. Returns [url, width], or null when the original should be
 * served as-is.
 */
function media_variant(string $dir, string $file, int $width, bool $mark): ?array
{
    $src  = media_path("$dir/$file");
    $dims = is_file($src) ? media_dims($src) : null;
    if (!$dims || !function_exists('imagecreatetruecolor')) {
        return null;
    }
    $width = min($width, $dims[0]);                     // never upscale
    if ($width === $dims[0] && !$mark && media_orientation($src) === 1) {
        return null;                                    // the original is already right
    }

    /* The settings are part of the name, so changing the watermark produces
       fresh copies instead of serving ones stamped with the old text. */
    $tag  = $mark ? '-wm' . substr(md5('top-right|' . watermark_text() . '|' . (WATERMARK['opacity'] ?? '')), 0, 6) : '';
    $ext  = strtolower(pathinfo($file, PATHINFO_EXTENSION)) === 'png' ? 'png' : 'jpg';
    $rel  = "$dir/.cache/" . pathinfo($file, PATHINFO_FILENAME) . "-$width$tag.$ext";
    $out  = media_path($rel);

    if (!(is_file($out) && filemtime($out) >= filemtime($src))
        && !media_render($src, $out, $width, $mark)) {
        return null;
    }
    return [media_url($rel), $width];
}

/** One photo, ready to render: src, srcset, display size and words. */
function media_image(string $dir, string $file, bool $mark, array $meta = []): array
{
    $abs          = media_path("$dir/$file");
    [$sw, $sh]    = media_dims($abs) ?? [1600, 1000];
    $label        = $meta['title'] ?? media_label($file);

    $srcset = [];
    $src    = null;
    foreach (array_unique(array_map(static fn (int $w) => min($w, $sw), MEDIA_SIZES)) as $w) {
        $v = media_variant($dir, $file, $w, $mark)
          ?? ($w === $sw ? [media_url("$dir/$file"), $sw] : null);
        if ($v !== null) {
            $srcset[] = $v[0] . ' ' . $v[1] . 'w';
            $src      = $v[0];                          // widest wins
        }
    }

    return [
        'type'    => 'image',
        'file'    => $file,
        'src'     => $src ?? media_url("$dir/$file"),
        'srcset'  => count($srcset) > 1 ? implode(', ', $srcset) : '',
        'w'       => $sw,
        'h'       => $sh,
        'alt'     => $meta['alt'] ?? ($label . ' at ' . CLINIC['name'] . ', '
                                      . CLINIC['locality'] . ', ' . CLINIC['city']),
        'title'   => $label,
        'caption' => $meta['caption'] ?? '',
    ];
}

/**
 * Every photo in assets/images/<placeholder>/.
 *
 * @return array<int, array{type:string, file:string, src:string, srcset:string,
 *                          w:int, h:int, alt:string, title:string, caption:string}>
 */
function media_images(string $placeholder): array
{
    $dir  = media_dir('images', $placeholder);
    $mark = watermark_on($placeholder);
    $caps = media_captions($dir);

    return array_map(
        static fn (string $f) => media_image($dir, $f, $mark, $caps[$f] ?? []),
        media_files($dir, MEDIA_IMG)
    );
}

/** The first photo of a placeholder — where a section shows exactly one. */
function media_first(string $placeholder): ?array
{
    $dir   = media_dir('images', $placeholder);
    $files = media_files($dir, MEDIA_IMG);
    if (!$files) {
        return null;
    }
    return media_image($dir, $files[0], watermark_on($placeholder), media_captions($dir)[$files[0]] ?? []);
}

/* ----------------------------------------------------------------- videos -- */

/**
 * Every clip in assets/videos/<placeholder>/, with its poster if one exists.
 *
 * @return array<int, array{type:string, file:string, src:string, mime:string,
 *                          poster:string, title:string, caption:string,
 *                          alt:string, watermark:string}>
 */
function media_videos(string $placeholder): array
{
    $dir     = media_dir('videos', $placeholder);
    $mark    = watermark_on($placeholder);
    $caps    = media_captions($dir);
    $posters = media_files("$dir/posters", MEDIA_IMG);
    $items   = [];

    foreach (media_files($dir, MEDIA_VIDEO) as $f) {
        $meta  = $caps[$f] ?? [];
        $label = $meta['title'] ?? media_label($f);
        $base  = pathinfo($f, PATHINFO_FILENAME);

        $poster = '';
        foreach ($posters as $p) {
            if (pathinfo($p, PATHINFO_FILENAME) === $base) {
                $poster = media_image("$dir/posters", $p, $mark)['src'];
                break;
            }
        }

        $items[] = [
            'type'      => 'video',
            'file'      => $f,
            'src'       => media_url("$dir/$f"),
            'mime'      => strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'webm' ? 'video/webm' : 'video/mp4',
            'poster'    => $poster,
            'title'     => $label,
            'caption'   => $meta['caption'] ?? '',
            'alt'       => $meta['alt'] ?? ($label . ' at ' . CLINIC['name']),
            'watermark' => $mark ? watermark_text() : '',
        ];
    }

    return $items;
}

/* ------------------------------------------------------------------ mixed -- */

/**
 * Photos and videos of one placeholder as a single sequence, ordered by
 * filename across both folders — what the slider shows.
 */
function media_items(string $placeholder): array
{
    $all = array_merge(media_images($placeholder), media_videos($placeholder));
    usort($all, static fn (array $a, array $b) => strnatcasecmp($a['file'], $b['file']));
    return $all;
}
