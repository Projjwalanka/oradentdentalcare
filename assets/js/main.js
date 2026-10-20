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

/* ==========================================================================
   Showcase slider — one slide at a time.
   Swipe is the browser's own scroll-snap; this only wires arrows, dots,
   keyboard and autoplay on top of it.
   ========================================================================== */
(function () {
  'use strict';

  document.querySelectorAll('[data-showcase]').forEach(function (root) {
    var view   = root.querySelector('.showcase__viewport');
    var slides = Array.prototype.slice.call(root.querySelectorAll('.showcase__slide'));
    var prev   = root.querySelector('[data-sc-prev]');
    var next   = root.querySelector('[data-sc-next]');
    var dots   = Array.prototype.slice.call(root.querySelectorAll('[data-sc-dot]'));
    var toggle = root.querySelector('[data-sc-toggle]');
    if (!view || slides.length < 2) {
      if (prev) prev.hidden = true;
      if (next) next.hidden = true;
      return;
    }

    var reduced  = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var interval = Number(root.dataset.autoplay || 0);
    var timer    = null;
    var paused   = false;
    var index    = 0;

    function go(i, smooth) {
      index = Math.max(0, Math.min(slides.length - 1, i));
      view.scrollTo({ left: slides[index].offsetLeft - view.offsetLeft,
                      behavior: smooth === false || reduced ? 'auto' : 'smooth' });
      sync();
    }

    function current() {
      // Whichever slide's centre is nearest the viewport centre
      var mid = view.scrollLeft + view.clientWidth / 2, best = 0, bestD = Infinity;
      slides.forEach(function (s, i) {
        var c = s.offsetLeft - view.offsetLeft + s.offsetWidth / 2;
        var d = Math.abs(c - mid);
        if (d < bestD) { bestD = d; best = i; }
      });
      return best;
    }

    function sync() {
      var i = current();
      index = i;
      dots.forEach(function (d, n) { d.setAttribute('aria-current', n === i ? 'true' : 'false'); });
      if (prev) prev.disabled = i === 0;
      if (next) next.disabled = i === slides.length - 1;
      syncVideos();
    }

    /* ---- video slides ----
       A clip only ever plays while its slide is the one showing. When the
       slideshow is running it starts the clip itself (muted, which is what
       browsers allow without a click) and waits for it to end before moving
       on; a visitor can always unmute or scrub with the player's controls. */
    var videos = slides.map(function (s) { return s.querySelector('video[data-sc-video]'); });
    var inView = false;

    function syncVideos() {
      videos.forEach(function (v, n) {
        if (!v) return;
        if (n !== index) {
          if (!v.paused) v.pause();
          return;
        }
        if (interval && !reduced && inView && !userPaused() && v.paused && !v.ended) {
          var p = v.play();
          if (p && p.catch) p.catch(function () { /* autoplay refused: controls still work */ });
        }
      });
    }

    function videoPlaying() {
      var v = videos[index];
      return !!(v && !v.paused && !v.ended);
    }

    function userPaused() {
      return !!(toggle && toggle.getAttribute('aria-pressed') === 'true');
    }

    /* ---- autoplay ---- */
    function tick() {
      if (paused || videoPlaying()) return;
      go(index >= slides.length - 1 ? 0 : index + 1);
    }
    function start() {
      if (!interval || reduced) return;
      stop();
      timer = window.setInterval(tick, interval);
    }
    function stop() { if (timer) { window.clearInterval(timer); timer = null; } }

    function setPaused(state) {
      paused = state;
      if (toggle) {
        toggle.setAttribute('aria-pressed', state ? 'true' : 'false');
        toggle.setAttribute('aria-label', state ? 'Play the slideshow' : 'Pause the slideshow');
        toggle.innerHTML = state
          ? '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.5v15l13-7.5z"/></svg>'
          : '<svg viewBox="0 0 24 24" aria-hidden="true"><rect x="6" y="4" width="4" height="16" rx="1"/>'
            + '<rect x="14" y="4" width="4" height="16" rx="1"/></svg>';
      }
    }

    if (prev) prev.addEventListener('click', function () { go(index - 1); });
    if (next) next.addEventListener('click', function () { go(index + 1); });
    dots.forEach(function (d) {
      d.addEventListener('click', function () { go(Number(d.dataset.scDot)); });
    });
    if (toggle) toggle.addEventListener('click', function () {
      setPaused(!userPaused());
      var v = videos[index];
      if (v && userPaused() && !v.paused) v.pause();
      syncVideos();
    });

    view.addEventListener('scroll', function () {
      window.clearTimeout(view._t);
      view._t = window.setTimeout(sync, 90);
    }, { passive: true });

    view.addEventListener('keydown', function (ev) {
      if (ev.key === 'ArrowRight') { ev.preventDefault(); go(index + 1); }
      if (ev.key === 'ArrowLeft')  { ev.preventDefault(); go(index - 1); }
    });

    // Don't advance while someone is looking at, or interacting with, a slide
    ['mouseenter', 'focusin', 'touchstart', 'pointerdown'].forEach(function (ev) {
      root.addEventListener(ev, function () { paused = true; }, { passive: true });
    });
    ['mouseleave', 'focusout'].forEach(function (ev) {
      root.addEventListener(ev, function () {
        if (toggle && toggle.getAttribute('aria-pressed') === 'true') return;
        paused = false;
      }, { passive: true });
    });

    // A clip that finishes hands control straight back to the slideshow
    videos.forEach(function (v) {
      if (!v) return;
      v.addEventListener('ended', function () {
        if (!userPaused() && interval && !reduced) {
          v.currentTime = 0;
          go(index >= slides.length - 1 ? 0 : index + 1);
        }
      });
    });

    // Pause entirely when the slider is off-screen or the tab is hidden
    var onScreen = !('IntersectionObserver' in window);
    function visibility() {
      inView = onScreen && !document.hidden;
      if (inView) { start(); syncVideos(); }
      else {
        stop();
        videos.forEach(function (v) { if (v && !v.paused) v.pause(); });
      }
    }
    if (!onScreen) {
      new IntersectionObserver(function (es) {
        es.forEach(function (en) { onScreen = en.isIntersecting; visibility(); });
      }, { threshold: 0.25 }).observe(root);
    }
    document.addEventListener('visibilitychange', visibility);
    visibility();

    sync();
  });
})();

/* ==========================================================================
   Click tracking — first-party, no cookies, no third-party script.

   Any element carrying data-track="<event>" reports itself when clicked, with
   an optional data-track-source (where on the page) and data-track-detail (the
   service slug). sendBeacon so nothing is delayed: the browser hands the
   request to the OS and lets the navigation proceed immediately.
   ========================================================================== */
(function () {
  'use strict';

  var ENDPOINT = '/api/track.php';

  function send(payload) {
    payload.path = location.pathname;
    try {
      var body = JSON.stringify(payload);
      if (navigator.sendBeacon) {
        navigator.sendBeacon(ENDPOINT, new Blob([body], { type: 'application/json' }));
        return;
      }
      fetch(ENDPOINT, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: body,
        keepalive: true
      }).catch(function () {});
    } catch (e) { /* tracking must never break the page */ }
  }

  document.addEventListener('click', function (e) {
    var el = e.target.closest('[data-track]');
    if (!el) return;
    send({
      event:  el.getAttribute('data-track'),
      source: el.getAttribute('data-track-source') || '',
      detail: el.getAttribute('data-track-detail') || ''
    });
  }, true);

  /* A completed booking is worth more than a click on the button, and the
     confirmation page is the only place we know it actually happened. */
  var done = document.querySelector('[data-booking-complete]');
  if (done) {
    send({ event: 'booking_complete', source: 'confirmation', detail: done.getAttribute('data-service') || '' });
  }
})();

/* ==========================================================================
   Gallery tabs. Progressive: without JS every panel is visible and the page
   still reads correctly, so nothing is hidden behind a script.
   ========================================================================== */
(function () {
  'use strict';

  document.querySelectorAll('[data-tabs]').forEach(function (root) {
    var tabs = Array.prototype.slice.call(root.querySelectorAll('[role="tab"]'));
    if (tabs.length < 2) return;

    function panelOf(tab) {
      return document.getElementById(tab.getAttribute('aria-controls'));
    }

    function select(tab) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', on ? 'true' : 'false');
        t.setAttribute('tabindex', on ? '0' : '-1');
        var p = panelOf(t);
        if (p) p.hidden = !on;
      });
    }

    tabs.forEach(function (tab, i) {
      tab.addEventListener('click', function () { select(tab); });
      tab.addEventListener('keydown', function (e) {
        var step = e.key === 'ArrowRight' ? 1 : e.key === 'ArrowLeft' ? -1 : 0;
        if (!step) return;
        e.preventDefault();
        var next = tabs[(i + step + tabs.length) % tabs.length];
        next.focus();
        select(next);
      });
    });

    select(tabs.filter(function (t) { return t.getAttribute('aria-selected') === 'true'; })[0] || tabs[0]);
  });
})();
