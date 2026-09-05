/* ORADENT — appointment booking widget. */
(function () {
  'use strict';

  var root = document.getElementById('booking');
  if (!root) return;

  var strip    = root.querySelector('[data-datestrip]');
  var slotWrap = root.querySelector('[data-slots]');
  var dayLabel = root.querySelector('[data-day-label]');
  var form     = root.querySelector('form');
  var dateIn   = form.querySelector('input[name="date"]');
  var timeIn   = form.querySelector('input[name="time"]');
  var submit   = form.querySelector('[data-submit]');
  var alertBox = root.querySelector('[data-alert]');
  var sumDate  = root.querySelector('[data-sum-date]');
  var sumTime  = root.querySelector('[data-sum-time]');
  var sumSvc   = root.querySelector('[data-sum-service]');
  var svcSel   = form.querySelector('select[name="service"]');

  var cache = {};

  /* ------------------------------------------------------------ helpers -- */
  function show(type, msg) {
    if (!msg) { alertBox.hidden = true; return; }
    alertBox.hidden = false;
    alertBox.className = 'alert alert--' + type;
    alertBox.textContent = msg;
    alertBox.setAttribute('role', type === 'bad' ? 'alert' : 'status');
  }

  function clearErrors() {
    form.querySelectorAll('.field__error').forEach(function (n) { n.remove(); });
    form.querySelectorAll('[aria-invalid]').forEach(function (n) { n.removeAttribute('aria-invalid'); });
  }

  function fieldError(name, msg) {
    var el = form.querySelector('[name="' + name + '"]');
    if (!el) return;
    el.setAttribute('aria-invalid', 'true');
    var p = document.createElement('p');
    p.className = 'field__error';
    p.textContent = msg;
    (el.closest('.field') || el.parentNode).appendChild(p);
  }

  function updateSummary() {
    var d = dateIn.value, t = timeIn.value;
    if (sumDate) sumDate.textContent = d ? cache[d] && cache[d].label ? cache[d].label : d : '—';
    if (sumTime) sumTime.textContent = t ? formatTime(t) : '—';
    if (sumSvc)  sumSvc.textContent  = svcSel.value ? svcSel.options[svcSel.selectedIndex].text : '—';
    submit.disabled = !(d && t && svcSel.value);
  }

  function formatTime(hhmm) {
    var p = hhmm.split(':'), h = Number(p[0]);
    var ap = h >= 12 ? 'PM' : 'AM';
    h = h % 12; if (h === 0) h = 12;
    return h + ':' + p[1] + ' ' + ap;
  }

  /* ------------------------------------------------------- slot loading -- */
  function loadSlots(date) {
    timeIn.value = '';
    updateSummary();
    slotWrap.classList.add('is-loading');
    slotWrap.setAttribute('aria-busy', 'true');

    var done = function (data) {
      cache[date] = data;
      slotWrap.classList.remove('is-loading');
      slotWrap.removeAttribute('aria-busy');
      renderSlots(data);
      if (dayLabel) dayLabel.textContent = data.label || '';
      updateSummary();
    };

    if (cache[date]) { done(cache[date]); return; }

    fetch('/api/slots.php?date=' + encodeURIComponent(date), {
      headers: { 'Accept': 'application/json' }
    })
      .then(function (r) { return r.json(); })
      .then(done)
      .catch(function () {
        slotWrap.classList.remove('is-loading');
        slotWrap.removeAttribute('aria-busy');
        slotWrap.innerHTML = '<div class="slots-empty">Could not load times. Please check your connection, ' +
          'or call us on ' + (root.dataset.phone || '') + '.</div>';
      });
  }

  function renderSlots(data) {
    if (!data.ok) {
      slotWrap.innerHTML = '<div class="slots-empty">' + escapeHtml(data.message || 'No times available.') + '</div>';
      return;
    }
    if (!data.available) {
      slotWrap.innerHTML = '<div class="slots-empty"><strong>Fully booked on this day.</strong><br>' +
        'Please choose another date, or call us on ' + escapeHtml(root.dataset.phone || '') +
        ' and we will fit you in.</div>';
      return;
    }

    var html = '';
    ['Morning', 'Evening'].forEach(function (session) {
      var list = data.sessions[session] || [];
      if (!list.length) return;
      var free = list.filter(function (s) { return s.available; }).length;
      html += '<div class="session">' +
        '<div class="session__label">' + (session === 'Morning' ? sunSvg() : moonSvg()) +
        session + ' &middot; ' + free + ' free</div><div class="slots">';
      list.forEach(function (s) {
        html += '<button type="button" class="slot" data-time="' + s.time + '"' +
          ' aria-pressed="false"' + (s.available ? '' : ' disabled aria-label="' + s.label + ' — already booked"') +
          '>' + s.label + '</button>';
      });
      html += '</div></div>';
    });
    slotWrap.innerHTML = html;
  }

  function escapeHtml(s) {
    return String(s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function sunSvg() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">' +
      '<circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2.6v2.2M12 19.2v2.2M4.3 4.3l1.6 1.6' +
      'M18.1 18.1l1.6 1.6M2.6 12h2.2M19.2 12h2.2M4.3 19.7l1.6-1.6M18.1 5.9l1.6-1.6"/></svg>';
  }
  function moonSvg() {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" aria-hidden="true">' +
      '<path stroke-linejoin="round" d="M20 14.4A8.2 8.2 0 0 1 9.6 4 8.4 8.4 0 1 0 20 14.4Z"/></svg>';
  }

  /* -------------------------------------------------------------- events -- */
  strip.addEventListener('click', function (ev) {
    var chip = ev.target.closest('.datechip');
    if (!chip || chip.disabled) return;
    strip.querySelectorAll('.datechip').forEach(function (c) { c.setAttribute('aria-pressed', 'false'); });
    chip.setAttribute('aria-pressed', 'true');
    dateIn.value = chip.dataset.date;
    show(null);
    loadSlots(chip.dataset.date);
  });

  slotWrap.addEventListener('click', function (ev) {
    var btn = ev.target.closest('.slot');
    if (!btn || btn.disabled) return;
    slotWrap.querySelectorAll('.slot').forEach(function (b) { b.setAttribute('aria-pressed', 'false'); });
    btn.setAttribute('aria-pressed', 'true');
    timeIn.value = btn.dataset.time;
    show(null);
    updateSummary();
  });

  svcSel.addEventListener('change', updateSummary);

  form.addEventListener('submit', function (ev) {
    ev.preventDefault();
    clearErrors();
    show(null);

    submit.disabled = true;
    var original = submit.textContent;
    submit.textContent = 'Confirming…';

    fetch('/api/book.php', {
      method: 'POST',
      headers: { 'Accept': 'application/json' },
      body: new FormData(form)
    })
      .then(function (r) { return r.json().then(function (j) { return { status: r.status, body: j }; }); })
      .then(function (res) {
        var data = res.body;
        if (data.ok) {
          window.location.href = data.redirect;
          return;
        }
        submit.disabled = false;
        submit.textContent = original;

        if (data.errors) {
          Object.keys(data.errors).forEach(function (k) { fieldError(k, data.errors[k]); });
          var firstBad = form.querySelector('[aria-invalid]');
          if (firstBad) firstBad.focus();
        } else if (data.field) {
          fieldError(data.field, data.message);
        }
        show('bad', data.message || 'Could not complete the booking.');

        // The slot went while the patient was typing — refresh availability.
        if (data.taken) {
          delete cache[dateIn.value];
          timeIn.value = '';
          loadSlots(dateIn.value);
        }
      })
      .catch(function () {
        submit.disabled = false;
        submit.textContent = original;
        show('bad', 'Network problem — please try again, or call us on ' + (root.dataset.phone || '') + '.');
      });
  });

  /* ------------------------------------------------------------- startup -- */
  var preferred = root.dataset.initialDate;
  var target = preferred && strip.querySelector('.datechip[data-date="' + preferred + '"]:not(:disabled)');
  var firstOpen = target || strip.querySelector('.datechip:not(:disabled)');
  if (firstOpen) {
    firstOpen.setAttribute('aria-pressed', 'true');
    dateIn.value = firstOpen.dataset.date;
    loadSlots(firstOpen.dataset.date);
  }
  updateSummary();
})();
