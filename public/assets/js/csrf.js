/**
 * Attaches the CSRF token to every same-origin state-changing request.
 *
 * Covers both paths the app uses:
 *   - fetch()  -> X-CSRF-Token header
 *   - <form method="post"> -> hidden csrf_token input
 *
 * Loaded early (before page scripts) so the fetch wrapper is in place before
 * any code captures a reference to window.fetch.
 */
(function () {
    'use strict';

    function token() {
        var m = document.querySelector('meta[name="csrf-token"]');
        return m ? m.getAttribute('content') : '';
    }

    function sameOrigin(url) {
        try {
            return new URL(url, window.location.href).origin === window.location.origin;
        } catch (e) {
            return true; // relative URL
        }
    }

    // ── fetch ────────────────────────────────────────────────────────────────
    if (window.fetch) {
        var nativeFetch = window.fetch.bind(window);
        window.fetch = function (input, init) {
            init = init || {};
            var url    = (typeof input === 'string') ? input : (input && input.url) || '';
            var method = (init.method || (input && input.method) || 'GET').toUpperCase();

            if (method !== 'GET' && method !== 'HEAD' && sameOrigin(url)) {
                var t = token();
                if (t) {
                    var h = new Headers(init.headers || (input && input.headers) || {});
                    if (!h.has('X-CSRF-Token')) h.set('X-CSRF-Token', t);
                    init.headers = h;
                }
            }
            return nativeFetch(input, init);
        };
    }

    // ── XMLHttpRequest (in case anything still uses it) ───────────────────────
    if (window.XMLHttpRequest) {
        var open = XMLHttpRequest.prototype.open;
        var send = XMLHttpRequest.prototype.send;
        XMLHttpRequest.prototype.open = function (method, url) {
            this.__csrfUnsafe = String(method).toUpperCase() !== 'GET'
                             && String(method).toUpperCase() !== 'HEAD'
                             && sameOrigin(url);
            return open.apply(this, arguments);
        };
        XMLHttpRequest.prototype.send = function () {
            if (this.__csrfUnsafe) {
                var t = token();
                if (t) { try { this.setRequestHeader('X-CSRF-Token', t); } catch (e) {} }
            }
            return send.apply(this, arguments);
        };
    }

    // ── forms ────────────────────────────────────────────────────────────────
    function stampForm(form) {
        if (!form || String(form.method || 'get').toLowerCase() !== 'post') return;
        if (form.querySelector('input[name="csrf_token"]')) return;
        var t = token();
        if (!t) return;
        var input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'csrf_token';
        input.value = t;
        form.appendChild(input);
    }

    function stampAll() {
        var forms = document.querySelectorAll('form');
        for (var i = 0; i < forms.length; i++) stampForm(forms[i]);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', stampAll);
    } else {
        stampAll();
    }

    // forms injected later (modals, rendered rows) are stamped on submit
    document.addEventListener('submit', function (e) { stampForm(e.target); }, true);
})();
