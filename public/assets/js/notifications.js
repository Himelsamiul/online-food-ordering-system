/* ==========================================================================
   Storefront notification bell

   Polls for the unread count and the newest few items. Slower than chat on
   purpose — an order status change is not a per-second event. Stops entirely
   while the tab is hidden.
   ========================================================================== */
(function () {
    'use strict';

    var wrap = document.getElementById('sfBell');
    if (!wrap) return;                          // signed out — no bell rendered

    var toggle  = wrap.querySelector('.sf-bell-toggle');
    var panel   = wrap.querySelector('.sf-bell-panel');
    var list    = wrap.querySelector('.sf-bell-list');
    var badge   = wrap.querySelector('.sf-bell-badge');
    var readAll = wrap.querySelector('.sf-bell-readall');

    var cfg = {
        pollUrl:  wrap.dataset.pollUrl,
        readUrl:  wrap.dataset.readUrl,
        interval: parseInt(wrap.dataset.interval, 10) || 25000
    };

    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    var state = { timer: null, inFlight: false, loaded: false, unread: 0, failures: 0 };

    /* ------------------------------------------------------- open / close */

    function isOpen() { return wrap.classList.contains('open'); }

    function open() {
        panel.hidden = false;
        requestAnimationFrame(function () { wrap.classList.add('open'); });
        toggle.setAttribute('aria-expanded', 'true');

        // Always refresh on open — the list may be up to a poll interval stale.
        fetchFeed();
    }

    function close() {
        wrap.classList.remove('open');
        toggle.setAttribute('aria-expanded', 'false');
        setTimeout(function () { if (!isOpen()) panel.hidden = true; }, 180);
    }

    toggle.addEventListener('click', function (e) {
        e.stopPropagation();
        isOpen() ? close() : open();
    });

    // Click-away and Escape, the two ways every dropdown is expected to shut.
    document.addEventListener('click', function (e) {
        if (isOpen() && !wrap.contains(e.target)) close();
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen()) close();
    });

    /* -------------------------------------------------------------- polling */

    function schedule() {
        if (state.timer) clearInterval(state.timer);
        if (document.hidden) return;

        var every = cfg.interval;
        if (state.failures > 2) every = Math.min(every * Math.pow(2, state.failures - 2), 300000);

        state.timer = setInterval(fetchFeed, every);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            if (state.timer) clearInterval(state.timer);
            state.timer = null;
        } else {
            fetchFeed();
            schedule();
        }
    });

    function fetchFeed() {
        if (state.inFlight) return;
        state.inFlight = true;

        fetch(cfg.pollUrl, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (res) {
                if (res.status === 401 || res.status === 419) {
                    // Session gone; stop rather than spinning on a dead endpoint.
                    if (state.timer) clearInterval(state.timer);
                    state.timer = null;
                    return null;
                }
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (!data) return;

                state.failures = 0;
                setUnread(data.unread);
                render(data.items || []);
                state.loaded = true;
            })
            .catch(function () {
                state.failures += 1;
                schedule();

                if (!state.loaded) {
                    list.innerHTML = '';
                    list.appendChild(empty('fa-exclamation-circle', 'Could not load notifications.'));
                }
            })
            .finally(function () { state.inFlight = false; });
    }

    /* ------------------------------------------------------------ rendering */

    function render(items) {
        list.innerHTML = '';

        if (!items.length) {
            list.appendChild(empty('fa-bell-slash-o',
                'No notifications yet. Order updates and replies from support will appear here.'));
            return;
        }

        items.forEach(function (item) {
            var a = document.createElement('a');
            a.className = 'sf-bell-item' + (item.unread ? ' unread' : '');
            a.href = item.url || '#';

            var icon = document.createElement('span');
            icon.className = 'sf-bell-icon tone-' + (item.tone || 'info');
            var i = document.createElement('i');
            i.className = 'fa ' + (item.icon || 'fa-bell');
            icon.appendChild(i);
            a.appendChild(icon);

            var text = document.createElement('span');
            text.className = 'sf-bell-item-text';

            // textContent throughout: titles carry customer and agent names.
            var title = document.createElement('span');
            title.className = 'sf-bell-item-title';
            title.textContent = item.title;
            text.appendChild(title);

            if (item.body) {
                var body = document.createElement('span');
                body.className = 'sf-bell-item-body';
                body.textContent = item.body;
                text.appendChild(body);
            }

            var time = document.createElement('span');
            time.className = 'sf-bell-item-time';
            time.textContent = item.ago ? item.ago + ' ago' : '';
            text.appendChild(time);

            a.appendChild(text);

            if (item.unread) {
                var dot = document.createElement('span');
                dot.className = 'sf-bell-dot';
                a.appendChild(dot);
            }

            list.appendChild(a);
        });
    }

    function empty(iconClass, message) {
        var box = document.createElement('div');
        box.className = 'sf-bell-empty';

        var i = document.createElement('i');
        i.className = 'fa ' + iconClass;
        box.appendChild(i);

        box.appendChild(document.createTextNode(message));

        return box;
    }

    function setUnread(count) {
        count = parseInt(count, 10) || 0;

        // Only re-trigger the ring animation when the number actually grows.
        var grew = count > state.unread;
        state.unread = count;

        if (count > 0) {
            badge.textContent = count > 9 ? '9+' : String(count);
            badge.hidden = false;
            if (readAll) readAll.hidden = false;

            if (grew) {
                wrap.classList.remove('has-unread');
                void wrap.offsetWidth;             // restart the CSS animation
                wrap.classList.add('has-unread');
            }
        } else {
            badge.hidden = true;
            wrap.classList.remove('has-unread');
            if (readAll) readAll.hidden = true;
        }
    }

    /* -------------------------------------------------------- mark all read */

    if (readAll) {
        readAll.addEventListener('click', function (e) {
            e.stopPropagation();

            fetch(cfg.readUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function () {
                    setUnread(0);
                    list.querySelectorAll('.sf-bell-item.unread').forEach(function (el) {
                        el.classList.remove('unread');
                        var dot = el.querySelector('.sf-bell-dot');
                        if (dot) dot.remove();
                    });
                })
                .catch(function () { /* the next poll will correct the badge */ });
        });
    }

    /* --------------------------------------------------------------- startup */

    fetchFeed();
    schedule();
})();
