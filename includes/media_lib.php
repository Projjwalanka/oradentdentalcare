<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';

/**
 * Folder-driven media library.
 *
 * THE RULE: one folder = one section of the site. Drop a file in and it appears
 * on the next page load; delete it and it goes. No code changes, ever.
 *
 * Because each section reads its OWN folder, adding a gallery photo can never
 * disturb the hero, and replacing the doctor's portrait can never reorder the
 * gallery. That isolation is the whole point of the layout:
 *
 *   assets/media/doctor/    portraits of the dentist   -> hero, profile
 *   assets/media/clinic/    clinic photographs         -> gallery, home page
 *   assets/media/videos/    .mp4 clips                 -> gallery
 *
 * ORDER   Natural filename sort, so a numeric prefix sets the sequence:
 *         01-…, 02-…, 10-… order correctly. The FIRST file in a folder is the
 *         one used where a section shows only one image (e.g. the hero).
 *
 * WORDS   The filename becomes the title: "02-treatment-room.jpg" -> "Treatment
 *         room". For better wording and proper alt text, add a captions.php to
 *         that folder returning ['02-treatment-room.jpg' => ['title' => …,
 *         'alt' => …, 'caption' => …]].
 *
 * SIZE    A photo straight off a phone is fine. The first time it is seen it is
 *         resized once into that folder's .cache/ and served as a static file
 *         after that. If .cache/ cannot be written (read-only deploy) the
 *         original is served and the page still works.
 *
 * VIDEO   assets/media/videos/posters/<same-basename>.jpg is used as the poster
 *         frame. Without one the video still plays, showing its own first frame.
 */

const MEDIA_BASE   = 'assets/media';
const MEDIA_SIZES  = [800, 1400];
const MEDIA_IMG    = ['jpg', 'jpeg', 'png', 'webp'];
const MEDIA_VIDEO  = ['mp4', 'webm'];

/** Absolute path of one media folder. */
function media_root(string $folder): string
{
    return dirname(__DIR__) . '/' . MEDIA_BASE . '/' . trim($folder, '/');
}

/** Web path of one media folder. */
function media_web(string $folder): string
{
    return '/' . MEDIA_BASE . '/' . trim($folder, '/');
}

/** "02-treatment-room.jpg" -> "Treatment room". */
function media_label(string $file): string
{
    $name = pathinfo($file, PATHINFO_FILENAME);
    $name = preg_replace('/^\d+[\s._-]+/', '', $name) ?? $name;   // strip ordering prefix
    $name = str_replace(['-', '_'], ' ', $name);
    $name = trim(preg_replace('/\s+/', ' ', $name) ?? $name);
    return $name === '' ? 'Clinic photo' : ucfirst($name);
}

/** Optional per-file overrides from <folder>/captions.php. */
function media_captions(string $folder): array
{
    static $cache = [];
    if (!array_key_exists($folder, $cache)) {
        $file = media_root($folder) . '/captions.php';
        $caps = is_readable($file) ? (require $file) : [];
        $cache[$folder] = is_array($caps) ? $caps : [];
    }
    return $cache[$folder];
}

/** Filenames of one type in a folder, naturally sorted. */
function media_files(string $folder, array $extensions): array
{
    $dir = media_root($folder);
    if (!is_dir($dir)) {
        return [];
    }
    $files = [];
    foreach (scandir($dir) ?: [] as $f) {
        if ($f === '' || $f[0] === '.') {
            continue;                                   // skips .cache and dotfiles
        }
        if (in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), $extensions, true)
            && is_file("$dir/$f")) {
            $files[] = $f;
        }
    }
    natcasesort($files);
    return array_values($files);
}

/**
 * Resize once into <folder>/.cache/ and return a web path, or null when the
 * original should be used as-is.
 */
function media_variant(string $folder, string $file, int $width): ?string
{
    $src = media_root($folder) . '/' . $file;
    if (!is_file($src) || !function_exists('imagecreatetruecolor')) {
        return null;
    }

    $cacheDir = media_root($folder) . '/.cache';
    if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0775, true) && !is_dir($cacheDir)) {
        return null;                                    // read-only deploy
    }

    $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
    $out = $cacheDir . '/' . pathinfo($file, PATHINFO_FILENAME) . "-$width." . ($ext === 'png' ? 'png' : 'jpg');
    $web = media_web($folder) . '/.cache/' . rawurlencode(basename($out));

    if (is_file($out) && filemtime($out) >= filemtime($src)) {
        return $web;
    }

    $info = @getimagesize($src);
    if (!$info) {
        return null;
    }
    [$sw, $sh] = $info;
    if ($sw <= $width) {
        return null;                                    // already small enough
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

    $ok = $info[2] === IMAGETYPE_PNG ? imagepng($dst, $out, 7) : imagejpeg($dst, $out, 82);

    imagedestroy($im);
    imagedestroy($dst);

    return $ok ? $web : null;
}

/**
 * Every photo in a folder, ready for the showcase renderer.
 *
 * @return array<int, array{type:string, src:string, srcset:string, w:int, h:int,
 *                          alt:string, title:string, caption:string, file:string}>
 */
function media_photos(string $folder): array
{
    $caps  = media_captions($folder);
    $items = [];

    foreach (media_files($folder, MEDIA_IMG) as $f) {
        $meta  = $caps[$f] ?? [];
        $label = $meta['title'] ?? media_label($f);
        $size  = @getimagesize(media_root($folder) . '/' . $f) ?: [1600, 1000];

        $srcset = [];
        foreach (MEDIA_SIZES as $w) {
            $v = media_variant($folder, $f, $w);
            if ($v !== null) {
                $srcset[] = $v . ' ' . $w . 'w';
            }
        }
        $original = media_web($folder) . '/' . rawurlencode($f);

        $items[] = [
            'type'    => 'image',
            'file'    => $f,
            'src'     => media_variant($folder, $f, MEDIA_SIZES[count(MEDIA_SIZES) - 1]) ?? $original,
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

/** The first photo in a folder — used where a section shows exactly one image. */
function media_first(string $folder): ?array
{
    $all = media_photos($folder);
    return $all[0] ?? null;
}

/**
 * Every video in assets/media/videos/, with its poster if one exists.
 *
 * @return array<int, array{type:string, src:string, poster:string, title:string,
 *                          caption:string, alt:string}>
 */
function media_videos(string $folder = 'videos'): array
{
    $caps  = media_captions($folder);
    $items = [];

    foreach (media_files($folder, MEDIA_VIDEO) as $f) {
        $meta   = $caps[$f] ?? [];
        $label  = $meta['title'] ?? media_label($f);
        $base   = pathinfo($f, PATHINFO_FILENAME);

        $poster = '';
        foreach (['jpg', 'jpeg', 'png', 'webp'] as $ext) {
            if (is_file(media_root($folder) . "/posters/$base.$ext")) {
                $poster = media_web($folder) . '/posters/' . rawurlencode("$base.$ext");
                break;
            }
        }

        $items[] = [
            'type'    => 'video',
            'file'    => $f,
            'src'     => media_web($folder) . '/' . rawurlencode($f),
            'poster'  => $poster,
            'title'   => $label,
            'caption' => $meta['caption'] ?? '',
            'alt'     => $meta['alt'] ?? ($label . ' at ' . CLINIC['name']),
        ];
    }

    return $items;
}

/** True when a folder currently holds nothing renderable. */
function media_empty(string $folder): bool
{
    return media_files($folder, array_merge(MEDIA_IMG, MEDIA_VIDEO)) === [];
}
