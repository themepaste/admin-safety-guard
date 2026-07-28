/**
 * Settings field behaviour.
 *
 * Keeps the colour swatch and its hex text input in sync, in both directions,
 * without pulling in the heavy wp-color-picker dependency.
 */
(function () {
  function byId(id) {
    return document.getElementById(id);
  }

  document.addEventListener('input', function (e) {
    // Swatch -> text
    if (e.target.classList && e.target.classList.contains('tp-color-swatch')) {
      var text = byId(e.target.dataset.target);
      if (text) text.value = e.target.value;
      return;
    }

    // Text -> swatch (only once it is a complete, valid hex value)
    if (e.target.classList && e.target.classList.contains('tp-color-text')) {
      var value = e.target.value.trim();
      if (/^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test(value)) {
        var swatch = e.target.parentNode.querySelector('.tp-color-swatch');
        if (swatch) swatch.value = value;
      }
    }
  });

  document.addEventListener('click', function (e) {
    if (!e.target.classList || !e.target.classList.contains('tp-color-clear')) return;

    var text = byId(e.target.dataset.target);
    if (text) text.value = '';

    var swatch = e.target.parentNode.querySelector('.tp-color-swatch');
    if (swatch) swatch.value = '#ffffff';
  });
})();

/**
 * Security-score issue dialog.
 *
 * Opened from the sidebar card. Kept as plain DOM code because the settings
 * sidebar is server-rendered PHP, not one of the React panels.
 */
(function () {
  var openBtn = document.getElementById('asg-ss_view');
  var dialog = document.getElementById('asg-issues');

  if (!openBtn || !dialog) return;

  var closeBtn = document.getElementById('asg-issues_close');
  var lastFocused = null;

  function open() {
    lastFocused = document.activeElement;
    dialog.hidden = false;
    if (closeBtn) closeBtn.focus();
  }

  function close() {
    dialog.hidden = true;
    if (lastFocused) lastFocused.focus();
  }

  openBtn.addEventListener('click', open);

  // Arriving from the admin notice's "Review and fix" link.
  if (window.location.hash === '#security-issues') {
    open();
  }
  if (closeBtn) closeBtn.addEventListener('click', close);

  // Click the backdrop, but not the panel itself.
  dialog.addEventListener('click', function (e) {
    if (e.target === dialog) close();
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && !dialog.hidden) close();
  });
})();

/**
 * Support form: inline validation, character count and a submit state.
 *
 * The form posts normally (no AJAX), so without a pending state the page just
 * sits there after the click and invites a second submit.
 */
(function () {
  var form = document.getElementById('tpsa-support-form');
  if (!form) return;

  var button = document.getElementById('tpsa-support-send');
  var message = document.getElementById('tpsa_support_message');
  var counter = document.getElementById('tpsa-support-count');
  var MIN = 20;

  function setError(field, text) {
    var wrap = field.closest('.tpsa-support__field');
    if (!wrap) return;

    wrap.classList.toggle('has-error', Boolean(text));
    field.setAttribute('aria-invalid', text ? 'true' : 'false');

    var existing = wrap.querySelector('.tpsa-support__err');
    if (text) {
      if (!existing) {
        existing = document.createElement('p');
        existing.className = 'tpsa-support__err';
        wrap.appendChild(existing);
      }
      existing.textContent = text;
    } else if (existing) {
      existing.remove();
    }
  }

  function updateCount() {
    if (!message || !counter) return;
    var left = MIN - message.value.trim().length;
    counter.textContent =
      left > 0
        ? left + ' more characters needed'
        : message.value.trim().length + ' characters';
  }

  if (message) {
    message.addEventListener('input', function () {
      updateCount();
      if (message.value.trim().length >= MIN) setError(message, '');
    });
    updateCount();
  }

  // Clear a field's error as soon as the user starts fixing it.
  form.querySelectorAll('input[required]').forEach(function (input) {
    input.addEventListener('input', function () {
      if (input.value.trim() !== '') setError(input, '');
    });
  });

  form.addEventListener('submit', function (e) {
    var firstBad = null;

    form.querySelectorAll('[required]').forEach(function (field) {
      var value = field.value.trim();
      var error = '';

      if (value === '') {
        error = 'This field is required.';
      } else if (field.type === 'email' && !/^\S+@\S+\.\S+$/.test(value)) {
        error = 'That email address does not look right.';
      } else if (field === message && value.length < MIN) {
        error = 'Please add a little more detail.';
      }

      setError(field, error);
      if (error && !firstBad) firstBad = field;
    });

    if (firstBad) {
      e.preventDefault();
      firstBad.focus();
      firstBad.scrollIntoView({ behavior: 'smooth', block: 'center' });
      return;
    }

    // Passed validation: show progress and block a double submit.
    if (button) {
      button.classList.add('is-sending');
      button.disabled = true;
      var label = button.querySelector('.tpsa-support__btnText');
      if (label) label.textContent = 'Sending…';
    }
  });
})();
