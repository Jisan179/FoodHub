(function () {
    'use strict';

    // Mobile navigation
    var toggle = document.querySelector('[data-nav-toggle]');
    var menu = document.querySelector('[data-nav-menu]');
    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            var open = menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(open));
        });
    }

    // Show or hide the password on the account forms
    document.querySelectorAll('.auth input[type="password"]').forEach(function (input) {
        var wrap = document.createElement('span');
        wrap.className = 'password-wrap';
        input.parentNode.insertBefore(wrap, input);
        wrap.appendChild(input);
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'password-toggle';
        button.textContent = 'Show';
        button.setAttribute('aria-label', 'Show password');
        button.addEventListener('click', function () {
            var hidden = input.type === 'password';
            input.type = hidden ? 'text' : 'password';
            button.textContent = hidden ? 'Hide' : 'Show';
            button.setAttribute('aria-label', hidden ? 'Hide password' : 'Show password');
        });
        wrap.appendChild(button);
    });

    // Keep live figures fresh while the page is visible and nothing is being edited
    var live = document.querySelector('[data-auto-refresh]');
    if (live) {
        window.setInterval(function () {
            var busy = document.querySelector('details[open]') || (document.activeElement && document.activeElement !== document.body);
            if (document.visibilityState === 'visible' && !busy) {
                window.location.reload();
            }
        }, Math.max(10, Number(live.getAttribute('data-auto-refresh')) || 30) * 1000);
    }

    // Fade out success notices after a few seconds, then remove them
    document.querySelectorAll('[data-auto-dismiss]').forEach(function (notice) {
        window.setTimeout(function () {
            notice.classList.add('is-leaving');
            window.setTimeout(function () {
                notice.remove();
            }, 700);
        }, Number(notice.getAttribute('data-auto-dismiss')) || 5000);
    });

    // Confirmation before destructive actions; also stops accidental double submits
    document.addEventListener('submit', function (event) {
        var form = event.target;
        var message = form.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
            event.preventDefault();
            return;
        }
        if (form.dataset.submitted) {
            event.preventDefault();
            return;
        }
        form.dataset.submitted = '1';
    });

    // Pages restored from the back button can be submitted again
    window.addEventListener('pageshow', function () {
        document.querySelectorAll('form[data-submitted]').forEach(function (form) {
            delete form.dataset.submitted;
        });
    });
})();
