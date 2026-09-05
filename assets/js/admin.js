/* Small conveniences for the admin panel. */
(function () {
  'use strict';
  // Submit filter forms when a select changes, so filtering feels immediate.
  document.querySelectorAll('form.filters select[name="status"]').forEach(function (sel) {
    sel.addEventListener('change', function () { sel.form.submit(); });
  });
  // Warn before leaving a half-typed block reason.
  var dirty = false;
  document.querySelectorAll('form input[type="text"]').forEach(function (i) {
    i.addEventListener('input', function () { dirty = true; });
  });
  document.querySelectorAll('form').forEach(function (f) {
    f.addEventListener('submit', function () { dirty = false; });
  });
  window.addEventListener('beforeunload', function (e) {
    if (dirty) { e.preventDefault(); e.returnValue = ''; }
  });
})();
