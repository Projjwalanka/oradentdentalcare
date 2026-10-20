<?php
declare(strict_types=1);

require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/icons.php';

/**
 * Render the showcase slider — photos and videos, one slide at a time.
 *
 * Items come from media_items() / media_images() / media_videos() in
 * includes/media_lib.php, so the slides are whatever is in the placeholder's
 * folders. A video slide plays muted when it comes into view and the slideshow
 * waits for it to finish before moving on.
 *
 * @param array<int, array<string,mixed>> $items
 * @param string $label     accessible name for the carousel
 * @param bool   $autoplay  advance on a timer (pauses on hover, focus, or while
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
              <?php media_video_tag($m, 'data-sc-video muted'); ?>
            <?php else: ?>
              <img src="<?= e($m['src']) ?>"
                   <?= !empty($m['srcset']) ? 'srcset="' . e($m['srcset']) . '" sizes="(min-width: 1160px) 1100px, 96vw"' : '' ?>
                   <?= !empty($m['w']) ? 'width="' . (int) $m['w'] . '" height="' . (int) $m['h'] . '"' : '' ?>
                   alt="<?= e($m['alt']) ?>"
                   loading="<?= $i === 0 ? 'eager' : 'lazy' ?>" decoding="async">
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
            <button class="showcase__dot<?= ($m['type'] ?? '') === 'video' ? ' showcase__dot--video' : '' ?>"
                    type="button" role="tab" data-sc-dot="<?= $i ?>"
                    aria-current="<?= $i === 0 ? 'true' : 'false' ?>"
                    aria-label="Slide <?= $i + 1 ?><?= ($m['type'] ?? '') === 'video' ? ' (video)' : '' ?><?= !empty($m['title']) ? ': ' . e($m['title']) : '' ?>"></button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </div>
    <?php
}

/**
 * A <video> with its watermark overlay, shared by the slider and the gallery.
 * preload="none" so a page of clips costs nothing until one is played.
 *
 * @param array<string,mixed> $v      an item from media_videos()
 * @param string              $attrs  extra attributes for the <video> tag
 */
function media_video_tag(array $v, string $attrs = ''): void
{
    ?>
    <div class="media-video">
      <video controls playsinline preload="none" <?= $attrs ?>
             <?= !empty($v['poster']) ? 'poster="' . e($v['poster']) . '"' : '' ?>
             aria-label="<?= e($v['alt']) ?>">
        <source src="<?= e($v['src']) ?>" type="<?= e($v['mime'] ?? 'video/mp4') ?>">
        <?= e($v['alt']) ?>
      </video>
      <?php if (!empty($v['watermark'])): ?>
        <span class="media-video__wm" aria-hidden="true"><?= e($v['watermark']) ?></span>
      <?php endif; ?>
    </div>
    <?php
}
