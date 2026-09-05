/* ORADENT — site behaviour. No dependencies, ~2 KB gzipped. */
(function () {
  'use strict';

  /* ------------------------------------------------------- mobile drawer -- */
  var drawer = document.getElementById('drawer');
  var toggle = document.querySelector('.nav-toggle[aria-controls="drawer"]');

  function setDrawer(open) {
    if (!drawer || !toggle) return;
    if (open) drawer.hidden = false;
    // let the browser paint before transitioning in
    requestAnimationFrame(function () {
      drawer.dataset.open = open ? 'true' : 'false';
    });
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    document.body.classList.toggle('nav-open', open);
    if (open) {
      var first = drawer.querySelector('a, button');
      if (first) first.focus();
    } else {
      window.setTimeout(function () {
        if (drawer.dataset.open === 'false') drawer.hidden = true;
      }, 280);
    }
  }

  if (toggle && drawer) {
    toggle.addEventListener('click', function () {
      setDrawer(toggle.getAttribute('aria-expanded') !== 'true');
    });
    drawer.addEventListener('click', function (ev) {
      if (ev.target === drawer || ev.target.closest('[data-drawer-close]')) setDrawer(false);
    });
    drawer.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape') { setDrawer(false); toggle.focus(); }
    });
    // Close when a link is followed (also covers same-page anchors)
    drawer.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', function () { setDrawer(false); });
    });
  }

  /* -------------------------------------------------------------- sliders -- */
  document.querySelectorAll('[data-slider]').forEach(function (slider) {
    var track = slider.querySelector('.slider__track');
    var prev  = slider.querySelector('[data-slider-prev]');
    var next  = slider.querySelector('[data-slider-next]');
    if (!track) return;

    function step() {
      var slide = track.querySelector('.slider__slide');
      if (!slide) return track.clientWidth * 0.8;
      var gap = parseFloat(getComputedStyle(track).columnGap || '0') || 0;
      return slide.getBoundingClientRect().width + gap;
    }

    function sync() {
      var max = track.scrollWidth - track.clientWidth - 2;
      if (prev) prev.disabled = track.scrollLeft <= 2;
      if (next) next.disabled = track.scrollLeft >= max;
    }

    if (prev) prev.addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
    if (next) next.addEventListener('click', function () { track.scrollBy({ left:  step(), behavior: 'smooth' }); });

    track.addEventListener('scroll', function () {
      window.clearTimeout(track._t);
      track._t = window.setTimeout(sync, 90);
    }, { passive: true });
    window.addEventListener('resize', sync, { passive: true });
    sync();
  });

  /* ------------------------------------------------ reveal-on-scroll -------
     Content is only ever hidden by this script, and a hard timeout reveals
     everything regardless — so a stalled observer can never leave a section
     permanently invisible.                                                    */
  if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches &&
      'IntersectionObserver' in window) {

    var revealed = [];

    function reveal(el) {
      el.style.transition = 'opacity .55s ease, transform .55s ease';
      el.style.opacity = '1';
      el.style.transform = 'none';
    }

    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { reveal(en.target); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -6% 0px', threshold: 0.02 });

    document.querySelectorAll('[data-reveal]').forEach(function (el, i) {
      el.style.opacity = '0';
      el.style.transform = 'translateY(16px)';
      el.style.transitionDelay = Math.min(i % 4, 3) * 60 + 'ms';
      revealed.push(el);
      io.observe(el);
    });

    window.setTimeout(function () {
      revealed.forEach(function (el) {
        if (el.style.opacity !== '1') { reveal(el); io.unobserve(el); }
      });
    }, 2500);
  }

  /* ------------------------------------------- highlight today's hours ----- */
  var today = new Date().getDay(); // 0 = Sunday
  document.querySelectorAll('[data-day]').forEach(function (row) {
    if (Number(row.dataset.day) === today) row.classList.add('is-today');
  });
})();
