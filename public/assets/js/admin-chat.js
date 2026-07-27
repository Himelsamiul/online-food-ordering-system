/* ==========================================================================
   Support inbox (admin)

   Polls for new lines in the open thread and refreshes the sidebar list in the
   same request. Sending is AJAX so the transcript does not reload and lose the
   admin's scroll position mid-conversation.
   ========================================================================== */
(function () {
    'use strict';

    var shell = document.querySelector('.chat-shell');
    if (!shell) return;

    var transcript = document.getElementById('chat-transcript');
    var threads    = document.getElementById('chat-threads');
    var composer   = document.getElementById('chat-composer');
    var input      = document.getElementById('chat-input');
    var alertBox   = document.querySelector('.chat-composer-alert');
    var statusPill = document.getElementById('chat-status-pill');

    var cfg = {
        pollUrl:  shell.dataset.pollUrl,
        activeId: parseInt(shell.dataset.activeId, 10) || 0,
        pollMs:   parseInt(shell.dataset.pollMs, 10) || 3000
    };

    var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';

    var state = {
        lastId:   parseInt(shell.dataset.lastId, 10) || 0,
        lastDay:  lastRenderedDay(),
        inFlight: false,
        sending:  false,
        timer:    null,
        failures: 0
    };

    if (transcript) scrollToBottom();

    /* ------------------------------------------------------------ polling */

    function schedule() {
        if (state.timer) clearInterval(state.timer);
        if (document.hidden) return;

        var every = cfg.pollMs;
        if (state.failures > 2) every = Math.min(every * Math.pow(2, state.failures - 2), 120000);

        state.timer = setInterval(poll, every);
    }

    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            if (state.timer) clearInterval(state.timer);
            state.timer = null;
        } else {
            poll();
            schedule();
        }
    });

    function poll() {
        if (state.inFlight || state.sending) return;
        state.inFlight = true;

        // Carry the current filters so the polled list matches what the admin
        // is actually looking at, instead of snapping back to "All".
        var params = new URLSearchParams(window.location.search);
        params.set('after', state.lastId);
        if (cfg.activeId) params.set('conversation', cfg.activeId);
        params.delete('page');

        fetch(cfg.pollUrl + '?' + params.toString(), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (res) {
                if (res.status === 401 || res.status === 419) {
                    showAlert('Your admin session expired. Reload the page to sign in again.');
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
                clearAlert();

                if (data.messages && data.messages.length && transcript) {
                    var stick = isNearBottom();
                    clearTranscriptEmpty();
                    data.messages.forEach(function (m) { appendMessage(m); });
                    if (stick) scrollToBottom();
                }

                if (data.conversation) paintStatus(data.conversation);
                if (data.threads) paintThreads(data.threads);

                paintSidebarBadge(data.total_unread);
            })
            .catch(function () {
                state.failures += 1;
                schedule();
            })
            .finally(function () { state.inFlight = false; });
    }

    /* ------------------------------------------------------------ sending */

    if (composer) {
        composer.addEventListener('submit', function (e) {
            e.preventDefault();
            send();
        });

        input.addEventListener('keydown', function (e) {
            // Shift+Enter for a newline; a composing IME must never be cut off.
            if (e.key === 'Enter' && !e.shiftKey && !e.isComposing) {
                e.preventDefault();
                send();
            }
        });

        input.addEventListener('input', autoGrow);
        input.focus();
    }

    function autoGrow() {
        input.style.height = 'auto';
        input.style.height = Math.min(input.scrollHeight, 130) + 'px';
    }

    function send() {
        var text = input.value.trim();
        if (!text) return;

        clearAlert();
        clearTranscriptEmpty();

        var bubble = appendMessage({
            id: null, from: 'admin', name: null, body: text, time: formatNow(), date: null
        });
        if (bubble) bubble.classList.add('is-pending');
        scrollToBottom();

        input.value = '';
        autoGrow();
        state.sending = true;

        var btn = composer.querySelector('button[type="submit"]');
        if (btn) btn.disabled = true;

        fetch(composer.dataset.url, {
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
                if (res.status === 429) throw new Error('Sending too quickly — wait a moment and try again.');
                if (res.status === 403) throw new Error('You do not have permission to reply.');
                if (res.status === 401 || res.status === 419) throw new Error('Your session expired. Reload the page.');

                return res.json().then(function (data) {
                    if (!res.ok) throw new Error(data.message || 'Could not send that reply.');
                    return data;
                });
            })
            .then(function (data) {
                if (bubble) bubble.classList.remove('is-pending');

                if (data.message && data.message.id) {
                    if (bubble) bubble.dataset.id = data.message.id;
                    state.lastId = Math.max(state.lastId, data.message.id);
                }

                paintStatus({ open: true });
                markThreadRead(cfg.activeId);
            })
            .catch(function (err) {
                if (bubble) {
                    bubble.classList.remove('is-pending');
                    bubble.classList.add('is-failed');
                    bubble.title = 'Not delivered';
                }
                showAlert(err.message || 'Could not send that reply.');
                if (!input.value) { input.value = text; autoGrow(); }
            })
            .finally(function () {
                state.sending = false;
                if (btn) btn.disabled = false;
                input.focus();
            });
    }

    /* ---------------------------------------------------------- rendering */

    function appendMessage(msg) {
        if (!transcript) return null;

        if (msg.id) {
            if (msg.id <= state.lastId) return null;
            if (transcript.querySelector('[data-id="' + msg.id + '"]')) return null;

            // A poll can return the reply we just sent before its own response
            // landed; adopt the optimistic bubble instead of duplicating it.
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
            day.className = 'chat-day';
            day.textContent = msg.date;
            transcript.appendChild(day);
        }

        var el = document.createElement('div');
        el.className = 'chat-bubble ' + bubbleClass(msg.from);
        if (msg.id) el.dataset.id = msg.id;

        if (msg.from === 'admin' && msg.name) {
            var who = document.createElement('span');
            who.className = 'chat-bubble-name';
            who.textContent = msg.name;
            el.appendChild(who);
        }

        // textContent only — message bodies are stored raw and are entirely
        // customer-controlled.
        el.appendChild(document.createTextNode(msg.body));

        if (msg.time) {
            var t = document.createElement('span');
            t.className = 'chat-bubble-time';
            t.textContent = msg.time;
            el.appendChild(t);
        }

        transcript.appendChild(el);

        return el;
    }

    function adoptPending(msg) {
        if (msg.from !== 'admin') return null;

        var candidates = transcript.querySelectorAll('.chat-bubble.from-admin:not([data-id])');

        for (var i = 0; i < candidates.length; i++) {
            var el = candidates[i];

            // Skip the name span if one was rendered.
            var node = el.childNodes[0];
            if (node && node.nodeType === 1 && node.classList.contains('chat-bubble-name')) {
                node = el.childNodes[1];
            }

            var text = node && node.nodeType === 3 ? node.nodeValue : '';

            if (text === msg.body) {
                el.dataset.id = msg.id;
                el.classList.remove('is-pending');
                return el;
            }
        }

        return null;
    }

    function bubbleClass(from) {
        if (from === 'admin')  return 'from-admin';
        if (from === 'system') return 'from-system';
        return 'from-customer';
    }

    function paintStatus(conversation) {
        if (!statusPill) return;

        if (conversation.open) {
            statusPill.textContent = 'Open';
            statusPill.classList.add('on');
            statusPill.classList.remove('off');
        } else {
            statusPill.textContent = 'Resolved';
            statusPill.classList.add('off');
            statusPill.classList.remove('on');
        }
    }

    /**
     * Refresh the list in place: only the preview, timestamp and unread badge
     * are rewritten. Replacing the markup would kill hover state and, on a
     * touch device, cancel a scroll in progress every few seconds.
     */
    function paintThreads(list) {
        if (!threads) return;

        list.forEach(function (thread) {
            var row = threads.querySelector('[data-thread="' + thread.id + '"]');
            if (!row) return;

            var preview = row.querySelector('.chat-thread-preview');
            if (preview && thread.preview) preview.textContent = thread.preview;

            var time = row.querySelector('.chat-thread-top small');
            if (time && thread.time) time.textContent = thread.time;

            var meta  = row.querySelector('.chat-thread-meta');
            var badge = row.querySelector('.chat-thread-badge');

            if (thread.unread > 0) {
                row.classList.add('unread');

                if (!badge && meta) {
                    badge = document.createElement('span');
                    badge.className = 'chat-thread-badge';
                    meta.insertBefore(badge, meta.firstChild);
                }
                if (badge) badge.textContent = thread.unread;
            } else {
                row.classList.remove('unread');
                if (badge) badge.remove();
            }
        });
    }

    function markThreadRead(id) {
        if (!threads || !id) return;

        var row = threads.querySelector('[data-thread="' + id + '"]');
        if (!row) return;

        row.classList.remove('unread');

        var badge = row.querySelector('.chat-thread-badge');
        if (badge) badge.remove();
    }

    /** Keep the sidebar's Live Chat badge in step without a page reload. */
    function paintSidebarBadge(total) {
        var link = document.querySelector('.nxl-navbar a[href*="/admin/chat"]');
        if (!link) return;

        var badge = link.querySelector('.nxl-badge');

        if (total > 0) {
            if (!badge) {
                badge = document.createElement('span');
                badge.className = 'nxl-badge';
                link.appendChild(badge);
            }
            badge.textContent = total;
        } else if (badge) {
            badge.remove();
        }
    }

    /* ------------------------------------------------------------ helpers */

    function lastRenderedDay() {
        var days = document.querySelectorAll('.chat-day');
        return days.length ? days[days.length - 1].textContent.trim() : null;
    }

    function clearTranscriptEmpty() {
        var empty = transcript && transcript.querySelector('.chat-transcript-empty');
        if (empty) empty.remove();
    }

    function isNearBottom() {
        return transcript.scrollHeight - transcript.scrollTop - transcript.clientHeight < 120;
    }

    function scrollToBottom() {
        if (transcript) transcript.scrollTop = transcript.scrollHeight;
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

    /* ------------------------------------------------------------ startup */

    schedule();
})();
