/* ==========================================================================
   Live support chat widget (storefront) — Messenger-shaped

   Polling, not sockets. Two intervals: a fast one while the panel is open and
   the tab is focused, a slow one when the panel is shut and we only care about
   keeping the unread badge honest. Polling stops entirely on a hidden tab — a
   background tab left open for a day would otherwise be thousands of pointless
   queries.

   Rendering is row-based so consecutive messages from one side can be grouped
   the way a real messenger does: tightened corners, one avatar per run, and a
   timestamp only on the last of the run.
   ========================================================================== */
(function () {
    'use strict';

    var root = document.getElementById('sf-chat');
    if (!root) return;

    var launcher = root.querySelector('.sf-chat-launcher');
    var panel    = root.querySelector('.sf-chat-panel');
    var closeBtn = root.querySelector('.sf-chat-close');
    var dot      = root.querySelector('.sf-chat-dot');

    /* ------------------------------------------------------ open / close */

    var STORE_KEY = 'sf-chat-open';
    var chat = null;

    function isOpen() {
        return root.classList.contains('is-open');
    }

    function open() {
        panel.hidden = false;
        // Next frame, so the transition has a "from" state to animate out of.
        requestAnimationFrame(function () { root.classList.add('is-open'); });
        launcher.setAttribute('aria-expanded', 'true');

        try { sessionStorage.setItem(STORE_KEY, '1'); } catch (e) {}

        if (chat) chat.onOpen();
    }

    function close() {
        root.classList.remove('is-open');
        launcher.setAttribute('aria-expanded', 'false');

        try { sessionStorage.removeItem(STORE_KEY); } catch (e) {}

        // Wait out the transition before pulling it from the layout.
        setTimeout(function () { if (!isOpen()) panel.hidden = true; }, 240);

        if (chat) chat.onClose();
    }

    launcher.addEventListener('click', function () { isOpen() ? close() : open(); });
    if (closeBtn) closeBtn.addEventListener('click', close);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && isOpen()) close();
    });

    function restoreOpenState() {
        var wasOpen = false;
        try { wasOpen = sessionStorage.getItem(STORE_KEY) === '1'; } catch (e) {}
        if (wasOpen) open();
    }

    /* --------------------------------------------------------- guest stop */

    // Signed out: the panel is a static "please log in" card. No endpoints, no
    // polling, nothing below this point applies.
    if (!root.classList.contains('is-auth')) {
        restoreOpenState();
        return;
    }

    /* ---------------------------------------------------------- signed in */

    var body     = root.querySelector('.sf-chat-body');
    var form     = root.querySelector('.sf-chat-composer');
    var input    = root.querySelector('#sf-chat-input');
    var sendBtn  = root.querySelector('.sf-chat-send');
    var alertBox = root.querySelector('.sf-chat-alert');
    var status   = root.querySelector('.sf-chat-status');
    var jumpBtn  = root.querySelector('.sf-chat-jump');

    var cfg = {
        pollUrl:   root.dataset.pollUrl,
        sendUrl:   root.dataset.sendUrl,
        activeMs:  parseInt(root.dataset.activeMs, 10) || 3000,
        idleMs:    parseInt(root.dataset.idleMs, 10) || 20000,
        maxLength: parseInt(root.dataset.maxLength, 10) || 2000,
        initial:   root.dataset.initial || '?'
    };

    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    // Two messages from the same side inside this window read as one thought.
    var GROUP_WINDOW_MS = 5 * 60 * 1000;

    var state = {
        lastId:   0,
        loaded:   false,
        timer:    null,
        inFlight: false,
        sending:  false,
        lastDay:  null,
        failures: 0,
        lastRow:  null,       // previous row element
        lastFrom: null,       // its sender type
        lastTime: 0           // its timestamp, for the grouping window
    };

    chat = {
        onOpen: function () {
            schedule(true);
            // The user is looking at it, so clear the badge immediately rather
            // than waiting for the round trip to come back.
            setUnread(0);
            fetchMessages(true);
            focusInput();
            scrollToBottom();
        },
        onClose: function () {
            schedule(false);
            hideJump();
        }
    };

    /* ------------------------------------------------------------ polling */

    function schedule(fast) {
        if (state.timer) clearInterval(state.timer);

        if (document.hidden) return;   // resumed by the visibility handler

        var every = fast ? cfg.activeMs : cfg.idleMs;

        // Back off after repeated failures so a server that is down does not
        // get hammered every 3 seconds by every open tab.
        if (state.failures > 2) every = Math.min(every * Math.pow(2, state.failures - 2), 120000);

        state.timer = setInterval(function () { fetchMessages(false); }, every);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            if (state.timer) clearInterval(state.timer);
            state.timer = null;
        } else {
            schedule(isOpen());
            fetchMessages(false);
        }
    });

    function fetchMessages(markRead) {
        // A poll that overlaps a send would race the optimistic bubble.
        // appendMessage() also reconciles, this just avoids the flicker.
        if (state.inFlight || state.sending) return;
        state.inFlight = true;

        var url = cfg.pollUrl
            + '?after=' + encodeURIComponent(state.lastId)
            + (markRead ? '&read=1' : '');

        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (res) {
                if (res.status === 401 || res.status === 419) {
                    // Session gone — a reload lands them on the login page.
                    showAlert('Your session expired. Please reload the page and sign in again.');
                    stopPolling();
                    return null;
                }
                if (!res.ok) throw new Error('HTTP ' + res.status);
                return res.json();
            })
            .then(function (data) {
                if (!data) return;

                state.failures = 0;
                clearAlert();

                if (!state.loaded) {
                    state.loaded = true;
                    clearPlaceholder();
                    if (!data.messages.length) showEmpty();
                }

                if (data.messages.length) {
                    var stick = isNearBottom();
                    clearEmpty();

                    var gotIncoming = false;
                    data.messages.forEach(function (m) {
                        if (appendMessage(m) && m.from !== 'customer') gotIncoming = true;
                    });

                    paintReceipt(data.messages);

                    if (stick) {
                        scrollToBottom();
                    } else if (gotIncoming) {
                        // They are reading history; do not yank them away —
                        // offer the jump button instead.
                        showJump();
                    }
                }

                if (data.conversation) paintStatus(data.conversation);

                if (!isOpen()) setUnread(data.unread || 0);
            })
            .catch(function () {
                state.failures += 1;
                if (state.failures === 3) showAlert('Connection lost — retrying…');
                schedule(isOpen());
            })
            .finally(function () { state.inFlight = false; });
    }

    function stopPolling() {
        if (state.timer) clearInterval(state.timer);
        state.timer = null;
    }

    /* ------------------------------------------------------------ sending */

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        submit();
    });

    input.addEventListener('keydown', function (e) {
        // Enter sends; Shift+Enter is a newline. IME composition must never be
        // interrupted, or typing Bengali/Japanese sends half a word.
        if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
            e.preventDefault();
            submit();
        }
    });

    input.addEventListener('input', function () {
        autoGrow();
        sendBtn.disabled = input.value.trim() === '';
    });

    function autoGrow() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 96) + 'px';
    }

    function submit() {
        var text = input.value.trim();

        if (!text) return;

        if (text.length > cfg.maxLength) {
            showAlert('Messages are limited to ' + cfg.maxLength + ' characters.');
            return;
        }

        clearAlert();
        clearEmpty();

        // Optimistic bubble: the message shows the instant they hit send, and
        // is reconciled (or marked failed) when the server answers.
        var row = appendMessage({
            id: null, from: 'customer', name: null, body: text,
            time: formatNow(), date: null
        });
        if (row) row.querySelector('.sf-chat-msg').classList.add('is-pending');
        scrollToBottom();
        hideJump();

        input.value = '';
        autoGrow();
        sendBtn.disabled = true;
        state.sending = true;

        fetch(cfg.sendUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ body: text })
        })
            .then(function (res) {
                if (res.status === 429) {
                    throw new Error('You are sending messages too quickly. Wait a moment and try again.');
                }
                if (res.status === 401 || res.status === 419) {
                    throw new Error('Your session expired. Please reload the page and sign in again.');
                }
                return res.json().then(function (data) {
                    if (!res.ok) throw new Error(data.message || 'Could not send that message.');
                    return data;
                });
            })
            .then(function (data) {
                var bubble = row && row.querySelector('.sf-chat-msg');
                if (bubble) bubble.classList.remove('is-pending');

                // The poll must not hand us this line back as "new".
                if (data.message && data.message.id) {
                    if (row) row.dataset.id = data.message.id;
                    state.lastId = Math.max(state.lastId, data.message.id);
                }

                setReceipt('Sent');

                if (data.conversation) paintStatus(data.conversation);
            })
            .catch(function (err) {
                var bubble = row && row.querySelector('.sf-chat-msg');
                if (bubble) {
                    bubble.classList.remove('is-pending');
                    bubble.classList.add('is-failed');
                    bubble.title = 'Not delivered';
                }
                showAlert(err.message || 'Could not send that message.');
                // Give the text back so it is not lost.
                if (!input.value) { input.value = text; autoGrow(); sendBtn.disabled = false; }
            })
            .finally(function () {
                state.sending = false;
                focusInput();
            });
    }

    /* ---------------------------------------------------------- rendering */

    /**
     * Append one message as a row, grouped against the previous one.
     * Returns the row element, or null when the message was already drawn.
     */
    function appendMessage(msg) {
        if (msg.id) {
            if (msg.id <= state.lastId) return null;              // already drawn
            if (body.querySelector('[data-id="' + msg.id + '"]')) return null;

            // A poll can hand back a line the customer just sent, before the
            // send response arrived to stamp an id on the optimistic bubble.
            // Adopt the id rather than drawing the same message twice.
            var pending = adoptPending(msg);
            if (pending) {
                state.lastId = Math.max(state.lastId, msg.id);
                return pending;
            }

            state.lastId = Math.max(state.lastId, msg.id);
        }

        if (msg.date && msg.date !== state.lastDay) {
            state.lastDay = msg.date;
            var day = document.createElement('div');
            day.className = 'sf-chat-day';
            day.textContent = msg.date;
            body.appendChild(day);
            state.lastRow = null;                                  // a date breaks the run
        }

        var side = msg.from === 'customer' ? 'from-me'
                 : msg.from === 'system'   ? 'from-system'
                 : 'from-them';

        var when = msg.iso ? Date.parse(msg.iso) : Date.now();

        // System notes never group; everything else groups with the same side
        // inside the window.
        var continues = side !== 'from-system'
            && state.lastRow
            && state.lastFrom === side
            && (when - state.lastTime) < GROUP_WINDOW_MS;

        var row = document.createElement('div');
        row.className = 'sf-chat-row ' + side + ' group-end' + (continues ? '' : ' group-start');
        if (msg.id) row.dataset.id = msg.id;

        if (continues) {
            // The previous row is no longer the end of its run.
            state.lastRow.classList.remove('group-end');
            var prevAvatar = state.lastRow.querySelector('.sf-chat-row-avatar');
            if (prevAvatar) prevAvatar.classList.add('is-spacer');
        }

        if (side === 'from-them') {
            var avatar = document.createElement('span');
            avatar.className = 'sf-chat-row-avatar';
            avatar.textContent = (msg.name || 'S').charAt(0).toUpperCase();
            row.appendChild(avatar);
        }

        var bubble = document.createElement('div');
        bubble.className = 'sf-chat-msg';

        if (side === 'from-them' && msg.name && !continues) {
            var who = document.createElement('span');
            who.className = 'sf-chat-msg-name';
            who.textContent = msg.name;
            bubble.appendChild(who);
        }

        // textContent, never innerHTML — message bodies are stored raw and are
        // fully attacker-controlled.
        bubble.appendChild(document.createTextNode(msg.body));

        if (msg.time) {
            var t = document.createElement('span');
            t.className = 'sf-chat-msg-time';
            t.textContent = msg.time;
            bubble.appendChild(t);
        }

        row.appendChild(bubble);
        body.appendChild(row);

        state.lastRow  = row;
        state.lastFrom = side;
        state.lastTime = when;

        return row;
    }

    /**
     * Match an incoming customer line against an un-stamped optimistic row.
     * Only rows still awaiting an id are candidates, so an identical message
     * sent twice on purpose still renders twice.
     */
    function adoptPending(msg) {
        if (msg.from !== 'customer') return null;

        var candidates = body.querySelectorAll('.sf-chat-row.from-me:not([data-id])');

        for (var i = 0; i < candidates.length; i++) {
            var row = candidates[i];
            var bubble = row.querySelector('.sf-chat-msg');
            if (!bubble) continue;

            var node = bubble.childNodes[0];
            var text = node && node.nodeType === 3 ? node.nodeValue : '';

            if (text === msg.body) {
                row.dataset.id = msg.id;
                bubble.classList.remove('is-pending');
                return row;
            }
        }

        return null;
    }

    /** "Sent" / "Seen" under the newest outgoing message. */
    function paintReceipt(messages) {
        for (var i = messages.length - 1; i >= 0; i--) {
            if (messages[i].from === 'customer') {
                setReceipt(messages[i].read ? 'Seen' : 'Sent');
                return;
            }
        }
    }

    function setReceipt(label) {
        var existing = body.querySelector('.sf-chat-receipt');
        if (existing) existing.remove();

        /*
         * Only meaningful when our own message is the last thing in the
         * thread. lastElementChild, not `:last-of-type` — every node here is a
         * div, so `:last-of-type` matches on the tag and would miss.
         */
        var lastRow = body.lastElementChild;
        if (!lastRow || !lastRow.classList.contains('from-me')) return;

        var tag = document.createElement('div');
        tag.className = 'sf-chat-receipt';
        tag.textContent = label;
        body.appendChild(tag);
    }

    function paintStatus(conversation) {
        if (!status) return;

        if (conversation.open) {
            status.textContent = 'Typically replies within a few minutes';
            status.classList.remove('is-closed');
        } else {
            status.textContent = 'Resolved — send a message to reopen';
            status.classList.add('is-closed');
        }
    }

    function setUnread(count) {
        if (!dot) return;

        if (count > 0) {
            dot.textContent = count > 9 ? '9+' : String(count);
            dot.hidden = false;
            launcher.classList.add('has-unread');
        } else {
            dot.hidden = true;
            launcher.classList.remove('has-unread');
        }
    }

    function clearPlaceholder() {
        var loading = body.querySelector('.sf-chat-loading');
        if (loading) loading.remove();
    }

    function showEmpty() {
        if (body.querySelector('.sf-chat-empty')) return;

        var el = document.createElement('div');
        el.className = 'sf-chat-empty';

        var icon = document.createElement('div');
        icon.className = 'sf-chat-empty-icon';
        icon.innerHTML = '<i class="fa fa-comments-o" aria-hidden="true"></i>';
        el.appendChild(icon);

        var head = document.createElement('strong');
        head.textContent = 'Say hello!';
        el.appendChild(head);

        el.appendChild(document.createTextNode(
            'Ask us anything about your order, delivery or payment. We usually reply within a few minutes.'
        ));

        body.appendChild(el);
    }

    function clearEmpty() {
        var empty = body.querySelector('.sf-chat-empty');
        if (empty) empty.remove();
    }

    /* -------------------------------------------------------- scroll state */

    function isNearBottom() {
        return body.scrollHeight - body.scrollTop - body.clientHeight < 90;
    }

    function scrollToBottom() {
        body.scrollTop = body.scrollHeight;
        hideJump();
    }

    function showJump() { if (jumpBtn) jumpBtn.hidden = false; }
    function hideJump() { if (jumpBtn) jumpBtn.hidden = true; }

    if (jumpBtn) jumpBtn.addEventListener('click', scrollToBottom);

    body.addEventListener('scroll', function () {
        if (isNearBottom()) hideJump();
    });

    function focusInput() {
        // Not on touch — focusing pops the keyboard and covers the transcript.
        if (window.matchMedia('(hover: hover)').matches) input.focus();
    }

    function showAlert(text) {
        if (!alertBox) return;
        alertBox.textContent = text;
        alertBox.hidden = false;
    }

    function clearAlert() {
        if (!alertBox) return;
        alertBox.hidden = true;
        alertBox.textContent = '';
    }

    function formatNow() {
        var d = new Date();
        var h = d.getHours();
        var m = String(d.getMinutes()).padStart(2, '0');
        var ap = h >= 12 ? 'PM' : 'AM';
        h = h % 12 || 12;
        return h + ':' + m + ' ' + ap;
    }

    /* ------------------------------------------------------------- startup */

    restoreOpenState();

    // Even shut, poll once on load and then slowly, so the badge is right when
    // an admin replied while the customer was reading another page.
    if (!isOpen()) {
        fetchMessages(false);
        schedule(false);
    }
})();
