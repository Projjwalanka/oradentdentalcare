</main>

<footer class="footer">
  <div class="wrap">
    <div class="footer__grid">

      <div class="footer__brand">
        <a class="brand" href="/" aria-label="<?= e(CLINIC['name']) ?> — home">
          <svg class="brand__mark" viewBox="0 0 48 48" aria-hidden="true">
            <circle cx="24" cy="24" r="23" fill="none" stroke="#5f7fb5" stroke-width="2"/>
            <path d="M24 12.5c-2.6 0-3.7 1.1-6.4 1.1-3.5 0-6 2.7-6 6.9 0 3.5 1.1 5.3 2.1 8.5.8 2.7 1 6.4 1.8 9 .5 1.6 1.3 2.7 2.6 2.7 1.9 0 2.2-2.2 2.7-4.8.5-2.4 1-4.2 3.2-4.2s2.7 1.8 3.2 4.2c.5 2.6.8 4.8 2.7 4.8 1.3 0 2.1-1.1 2.6-2.7.8-2.6 1-6.3 1.8-9 1-3.2 2.1-5 2.1-8.5 0-4.2-2.5-6.9-6-6.9-2.7 0-3.8-1.1-6.4-1.1Z"
                  fill="none" stroke="#29a8c9" stroke-width="2.1" stroke-linejoin="round"/>
            <path d="M20.6 19.4 24 28.6l3.4-9.2" fill="none" stroke="#8fb0dd" stroke-width="1.8"
                  stroke-linecap="round" stroke-linejoin="round"/>
          </svg>
          <span class="brand__text">
            <span class="brand__name">ORA<em>DENT</em></span>
            <span class="brand__sub">Dental Care</span>
          </span>
        </a>
        <p class="footer__about">
          Dental care &amp; oral surgery in <?= e(CLINIC['locality']) ?>, <?= e(CLINIC['city']) ?>, led by
          <?= e(CLINIC['doctor']) ?>, <?= e(CLINIC['doctor_qual']) ?>.
          <em>&ldquo;<?= e(CLINIC['tagline']) ?>&rdquo;</em>
        </p>
        <div class="btn-row" style="margin-top:1.2rem">
          <a class="btn btn--wa btn--sm" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener">
            <?= icon('whatsapp') ?> WhatsApp
          </a>
          <a class="btn btn--on-dark btn--sm" href="<?= e(tel_link()) ?>"><?= icon('phone') ?> Call</a>
        </div>
      </div>

      <div>
        <h4>Treatments</h4>
        <ul>
          <?php foreach (array_slice(SERVICES, 0, 6, true) as $slug => $s): ?>
            <li><a href="/services/<?= e($slug) ?>"><?= e($s['name']) ?></a></li>
          <?php endforeach; ?>
          <li><a href="/services"><strong>All treatments →</strong></a></li>
        </ul>
      </div>

      <div>
        <h4>Clinic</h4>
        <ul>
          <li><a href="/about">About Dr. Chaklader</a></li>
          <li><a href="/gallery">Inside the clinic</a></li>
          <li><a href="/faq">Patient FAQs</a></li>
          <li><a href="/book">Book an appointment</a></li>
          <li><a href="/contact">Contact &amp; directions</a></li>
        </ul>
      </div>

      <div>
        <h4>Visit us</h4>
        <ul>
          <li>
            <?= e(CLINIC['street']) ?><br>
            <span style="color:#7f93b4"><?= e(CLINIC['landmark']) ?></span><br>
            <?= e(CLINIC['locality'] . ', ' . CLINIC['city'] . ' ' . CLINIC['postal']) ?>
          </li>
          <li><a href="<?= e(CLINIC['maps_url']) ?>" target="_blank" rel="noopener">Get directions →</a></li>
          <li><a href="<?= e(tel_link()) ?>"><?= e(CLINIC['phone_display']) ?></a></li>
          <li><a href="mailto:<?= e(CLINIC['email']) ?>"><?= e(CLINIC['email']) ?></a></li>
          <li style="margin-top:.4rem">
            <?php foreach (hours_summary() as $row): ?>
              <strong style="color:#fff"><?= e($row['days']) ?></strong><br>
              <?php if ($row['open']): ?>
                <?php foreach ($row['sessions'] as $s): ?><?= e($s) ?><br><?php endforeach; ?>
              <?php else: ?>
                <span style="color:#7f93b4">Closed</span><br>
              <?php endif; ?>
            <?php endforeach; ?>
          </li>
        </ul>
      </div>

    </div>

    <div class="footer__bottom">
      <span>&copy; <?= date('Y') ?> <?= e(CLINIC['name']) ?>. All rights reserved.</span>
      <span>
        <a href="/privacy">Privacy</a> &nbsp;·&nbsp;
        <a href="/terms">Terms</a> &nbsp;·&nbsp;
        <a href="/sitemap.xml">Sitemap</a>
      </span>
    </div>
  </div>
</footer>

<nav class="mobile-bar" aria-label="Quick actions">
  <a href="<?= e(tel_link()) ?>"><?= icon('phone') ?> Call</a>
  <a class="is-wa" href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener"><?= icon('whatsapp') ?> WhatsApp</a>
  <a class="is-book" href="/book"><?= icon('calendar') ?> Book</a>
</nav>

<script src="<?= e(asset('assets/js/main.js')) ?>" defer></script>
<?php foreach (($pageScripts ?? []) as $src): ?>
<script src="<?= e(asset($src)) ?>" defer></script>
<?php endforeach; ?>
</body>
</html>
