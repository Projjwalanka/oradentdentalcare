<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/icons.php';

/**
 * Render the showcase slider.
 *
 * Accepts either shape of item:
 *   folder-driven (includes/slider.php)  'src', 'srcset', 'w', 'h'
 *   manifest      (includes/media.php)   'file' basename, or 'video'
 *
 * @param array<int, array<string,mixed>> $items
 * @param string $label     accessible name for the carousel
 * @param bool   $autoplay  advance on a timer (pauses on hover, focus, or when
 *                          a video is playing)
 */
function showcase(array $items, string $label = 'Clinic photos', bool $autoplay = true): void
{
    $items = array_values($items);
    if (!$items) {
        return;
    }
    $id    = 'sc-' . substr(md5($label), 0, 6);
    $total = count($items);
    ?>
    <div class="showcase" data-showcase<?= $autoplay && $total > 1 ? ' data-autoplay="6000"' : '' ?>>
      <div class="showcase__viewport" id="<?= e($id) ?>" tabindex="0"
           role="region" aria-roledescription="carousel" aria-label="<?= e($label) ?>">
        <?php foreach ($items as $i => $m): ?>
          <div class="showcase__slide" role="group" aria-roledescription="slide"
               aria-label="<?= $i + 1 ?> of <?= $total ?>">
            <?php if (($m['type'] ?? 'image') === 'video'): ?>
              <?php /* Folder-driven: src and poster come from assets/media/videos/
                        via media_videos(). preload="none" so a gallery of clips
                        costs nothing until one is actually played. */ ?>
              <video controls preload="none" playsinline
                     <?= !empty($m['poster']) ? 'poster="' . e($m['poster']) . '"' : '' ?>>
                <source src="<?= e($m['src'] ?? ('/assets/video/' . ($m['video'] ?? ''))) ?>" type="video/mp4">
                <?= e($m['alt']) ?>
              </video>

            <?php elseif (!empty($m['src'])): /* folder-driven */ ?>
              <img src="<?= e($m['src']) ?>"
                   <?= !empty($m['srcset']) ? 'srcset="' . e($m['srcset']) . '" sizes="(min-width: 1160px) 1100px, 96vw"' : '' ?>
                   <?= !empty($m['w']) ? 'width="' . (int) $m['w'] . '" height="' . (int) $m['h'] . '"' : '' ?>
                   alt="<?= e($m['alt']) ?>"
                   loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                   <?= $i === 0 ? 'fetchpriority="high"' : '' ?> decoding="async">

            <?php else: /* legacy manifest basename */ ?>
              <picture>
                <source type="image/webp"
                        srcset="/assets/img/<?= e($m['file']) ?>-800.webp 800w, /assets/img/<?= e($m['file']) ?>-1200.webp 1200w"
                        sizes="(min-width: 1160px) 1100px, 96vw">
                <img src="/assets/img/<?= e($m['file']) ?>-1200.jpg" width="1200" height="750"
                     alt="<?= e($m['alt']) ?>"
                     loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" decoding="async">
              </picture>
            <?php endif; ?>

            <?php if (!empty($m['title']) || !empty($m['caption'])): ?>
              <div class="showcase__cap">
                <?php if (!empty($m['title'])): ?><b><?= e($m['title']) ?></b><?php endif; ?>
                <?php if (!empty($m['caption'])): ?><span><?= e($m['caption']) ?></span><?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if ($total > 1): ?>
      <button class="showcase__arrow showcase__arrow--prev" type="button"
              data-sc-prev aria-controls="<?= e($id) ?>" aria-label="Previous slide"><?= icon('left') ?></button>
      <button class="showcase__arrow showcase__arrow--next" type="button"
              data-sc-next aria-controls="<?= e($id) ?>" aria-label="Next slide"><?= icon('right') ?></button>

      <div class="showcase__bar">
        <?php if ($autoplay): ?>
        <button class="showcase__toggle" type="button" data-sc-toggle
                aria-label="Pause the slideshow" aria-pressed="false">
          <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/></svg>
        </button>
        <?php endif; ?>
        <div class="showcase__dots" role="tablist" aria-label="Choose a slide">
          <?php foreach ($items as $i => $m): ?>
            <button class="showcase__dot" type="button" role="tab" data-sc-dot="<?= $i ?>"
                    aria-current="<?= $i === 0 ? 'true' : 'false' ?>"
                    aria-label="Slide <?= $i + 1 ?><?= !empty($m['title']) ? ': ' . e($m['title']) : '' ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php
}
