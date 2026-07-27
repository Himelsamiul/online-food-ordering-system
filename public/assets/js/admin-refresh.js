/* =====================================================================
   Admin panel progressive enhancements.
   Everything here degrades safely — no page depends on it to function.
   ===================================================================== */
(function () {
    'use strict';

    var SKIN_KEY  = 'app-skin';          // shared with the vendor customizer
    var DARK_SKIN = 'app-skin-dark';

    /* -------------------------------------------------------------------
       Theme
       The class is applied before paint by an inline snippet in the layout
       head; this half only handles switching and remembering.
       ------------------------------------------------------------------- */
    function isDark() {
        return document.documentElement.classList.contains(DARK_SKIN);
    }

    function applyTheme(dark) {
        document.documentElement.classList.toggle(DARK_SKIN, dark);

        try {
            localStorage.setItem(SKIN_KEY, dark ? DARK_SKIN : 'app-skin-light');
        } catch (e) {
            /* private mode — the toggle still works for this page */
        }

        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.setAttribute('aria-pressed', dark ? 'true' : 'false');
            btn.setAttribute('title', dark ? 'Switch to light mode' : 'Switch to dark mode');
        });

        document.dispatchEvent(new CustomEvent('ar:themechange', { detail: { dark: dark } }));
    }

    function initTheme() {
        document.querySelectorAll('[data-theme-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                applyTheme(!isDark());
            });
        });

        // Keep the vendor customizer's own radio buttons in step, if present.
        document.querySelectorAll('[data-app-skin]').forEach(function (input) {
            input.addEventListener('change', function () {
                applyTheme(input.getAttribute('data-app-skin') === DARK_SKIN);
            });
        });

        applyTheme(isDark());
    }

    /* -------------------------------------------------------------------
       Responsive tables.
       Copy each header's text onto the matching cell so the CSS can render
       one row as a labelled card on small screens.
       ------------------------------------------------------------------- */
    function initStackedTables() {
        document.querySelectorAll('.nxl-content table.table').forEach(function (table) {
            if (table.hasAttribute('data-ar-no-stack')) {
                return;
            }

            var heads = table.querySelectorAll('thead th');

            if (!heads.length) {
                return;
            }

            var labels = Array.prototype.map.call(heads, function (th) {
                return th.textContent.trim();
            });

            table.querySelectorAll('tbody tr').forEach(function (row) {
                Array.prototype.forEach.call(row.cells, function (cell, i) {
                    if (labels[i] && !cell.hasAttribute('data-label')) {
                        cell.setAttribute('data-label', labels[i]);
                    }
                });
            });

            table.classList.add('ar-stack');
        });
    }

    /* -------------------------------------------------------------------
       Image inputs get a live preview instead of just a filename.
       ------------------------------------------------------------------- */
    function initImagePreviews() {
        document.querySelectorAll('input[type="file"][accept*="image"], input[type="file"][name="image"]')
            .forEach(function (input) {
                input.addEventListener('change', function () {
                    var file = input.files && input.files[0];

                    if (!file || !file.type.startsWith('image/')) {
                        return;
                    }

                    var preview = input.parentNode.querySelector('.ar-preview');

                    if (!preview) {
                        preview = document.createElement('img');
                        preview.className = 'ar-preview';
                        preview.style.cssText =
                            'display:block;margin-top:10px;width:120px;height:120px;' +
                            'object-fit:cover;border-radius:10px;border:1px solid var(--ar-line);';
                        input.parentNode.appendChild(preview);
                    }

                    preview.src = URL.createObjectURL(file);
                });
            });
    }

    /* -------------------------------------------------------------------
       Stop double submits — a second click on a slow form used to create
       duplicate records.
       ------------------------------------------------------------------- */
    function initSubmitGuards() {
        document.querySelectorAll('.nxl-content form').forEach(function (form) {
            // Delete forms are intercepted by the SweetAlert confirm and
            // re-submitted, which would otherwise trip the guard on the way in.
            if (form.classList.contains('delete-form')) {
                return;
            }

            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"]');

                if (!btn || btn.dataset.arBusy) {
                    return;
                }

                // Let the browser finish submitting before disabling, or the
                // button's own name/value would be dropped from the payload.
                setTimeout(function () {
                    btn.dataset.arBusy = '1';
                    btn.dataset.arLabel = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Working…';
                }, 0);
            });
        });
    }

    /* -------------------------------------------------------------------
       Live row filter for any table that opts in with
       data-ar-filter="#someInput".
       ------------------------------------------------------------------- */
    function initLiveFilters() {
        document.querySelectorAll('[data-ar-filter]').forEach(function (table) {
            var input = document.querySelector(table.getAttribute('data-ar-filter'));

            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                var needle = input.value.toLowerCase().trim();
                var shown  = 0;

                table.querySelectorAll('tbody tr').forEach(function (row) {
                    var hit = !needle || row.textContent.toLowerCase().indexOf(needle) > -1;
                    row.style.display = hit ? '' : 'none';
                    if (hit) { shown++; }
                });

                var counter = document.querySelector('[data-ar-filter-count]');

                if (counter) {
                    counter.textContent = shown;
                }
            });
        });
    }

    /* -------------------------------------------------------------------
       Bulk selection.
       A master checkbox marked data-ar-check-all="<scope selector>" drives
       every [data-ar-check] inside that scope, and toggles the visibility
       of anything marked data-ar-bulk-bar.
       ------------------------------------------------------------------- */
    function initBulkSelect() {
        document.querySelectorAll('[data-ar-check-all]').forEach(function (master) {
            var scope = document.querySelector(master.getAttribute('data-ar-check-all'));

            if (!scope) {
                return;
            }

            var boxes = scope.querySelectorAll('[data-ar-check]');
            var bar   = document.querySelector('[data-ar-bulk-bar]');
            var count = document.querySelector('[data-ar-bulk-count]');

            function sync() {
                var checked = scope.querySelectorAll('[data-ar-check]:checked').length;

                master.checked      = checked > 0 && checked === boxes.length;
                master.indeterminate = checked > 0 && checked < boxes.length;

                if (bar)   { bar.style.display = checked ? '' : 'none'; }
                if (count) { count.textContent = checked; }
            }

            master.addEventListener('change', function () {
                boxes.forEach(function (box) { box.checked = master.checked; });
                sync();
            });

            boxes.forEach(function (box) { box.addEventListener('change', sync); });

            sync();
        });
    }

    /* -------------------------------------------------------------------
       Permission matrix.
       Each action is a clickable tile; "view" is implied by every other
       action, because an admin who can edit but cannot open the page would
       only ever meet a 403.
       ------------------------------------------------------------------- */
    function initPermissionMatrix() {
        var matrix = document.querySelector('[data-perm-matrix]');

        if (!matrix) {
            return;
        }

        function paint(label) {
            var input = label.querySelector('input[type="checkbox"]');
            label.classList.toggle('checked', !!(input && input.checked));
        }

        function moduleOf(name) {
            return name.split('.')[0];
        }

        function viewBoxFor(module) {
            return matrix.querySelector('input[value="' + module + '.view"]');
        }

        matrix.querySelectorAll('.perm-check').forEach(function (label) {
            paint(label);

            var input = label.querySelector('input[type="checkbox"]');

            if (!input) {
                return;
            }

            input.addEventListener('change', function () {
                var name   = input.value;
                var module = moduleOf(name);
                var view   = viewBoxFor(module);

                if (input.checked && view && view !== input) {
                    // granting anything grants view
                    view.checked = true;
                    paint(view.closest('.perm-check'));
                }

                if (!input.checked && view === input) {
                    // removing view removes the rest of the module
                    matrix.querySelectorAll('input[type="checkbox"][value^="' + module + '."]')
                        .forEach(function (other) {
                            other.checked = false;
                            paint(other.closest('.perm-check'));
                        });
                }

                paint(label);
                syncGroupToggles();
            });
        });

        // "select all" per group
        function syncGroupToggles() {
            matrix.querySelectorAll('[data-perm-group-toggle]').forEach(function (toggle) {
                var group = toggle.closest('.perm-group');

                if (!group) {
                    return;
                }

                var boxes   = group.querySelectorAll('.perm-check input[type="checkbox"]');
                var checked = group.querySelectorAll('.perm-check input[type="checkbox"]:checked');

                toggle.checked       = boxes.length > 0 && checked.length === boxes.length;
                toggle.indeterminate = checked.length > 0 && checked.length < boxes.length;
            });
        }

        matrix.querySelectorAll('[data-perm-group-toggle]').forEach(function (toggle) {
            toggle.addEventListener('change', function () {
                var group = toggle.closest('.perm-group');

                group.querySelectorAll('.perm-check input[type="checkbox"]').forEach(function (box) {
                    box.checked = toggle.checked;
                    paint(box.closest('.perm-check'));
                });

                syncGroupToggles();
            });
        });

        // grant / revoke everything
        var all = document.querySelector('[data-perm-all]');
        var none = document.querySelector('[data-perm-none]');

        function setAll(state) {
            matrix.querySelectorAll('.perm-check input[type="checkbox"]').forEach(function (box) {
                box.checked = state;
                paint(box.closest('.perm-check'));
            });
            syncGroupToggles();
        }

        if (all)  { all.addEventListener('click',  function (e) { e.preventDefault(); setAll(true);  }); }
        if (none) { none.addEventListener('click', function (e) { e.preventDefault(); setAll(false); }); }

        /* A superadmin holds everything implicitly, so the matrix is locked
           off rather than left showing a selection that is never read. */
        var roleInputs = document.querySelectorAll('input[name="role"], select[name="role"]');

        function syncRole() {
            var role = null;

            roleInputs.forEach(function (input) {
                if (input.tagName === 'SELECT') {
                    role = input.value;
                } else if (input.checked) {
                    role = input.value;
                }
            });

            var locked = role === 'superadmin';

            matrix.classList.toggle('perm-locked', locked);

            var notice = document.querySelector('[data-perm-superadmin-notice]');

            if (notice) {
                notice.style.display = locked ? '' : 'none';
            }
        }

        roleInputs.forEach(function (input) { input.addEventListener('change', syncRole); });

        syncRole();
        syncGroupToggles();
    }

    /* -------------------------------------------------------------------
       Notification bell.
       Marking read is a background POST so the dropdown does not jump, and
       the badge is refreshed on a slow poll so a long-lived tab does not go
       stale.
       ------------------------------------------------------------------- */
    function initNotifications() {
        var root = document.querySelector('[data-notif-root]');

        if (!root) {
            return;
        }

        var token   = document.querySelector('meta[name="csrf-token"]');
        var readUrl = root.getAttribute('data-notif-read-url');
        var feedUrl = root.getAttribute('data-notif-feed-url');
        var badge   = root.querySelector('[data-notif-badge]');
        var clear   = root.querySelector('[data-notif-clear]');

        function post(url) {
            return fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': token ? token.getAttribute('content') : '',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                credentials: 'same-origin'
            });
        }

        if (clear && readUrl) {
            clear.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                post(readUrl).then(function () {
                    root.querySelectorAll('.notif-item.is-new').forEach(function (item) {
                        item.classList.remove('is-new');
                    });

                    if (badge) {
                        badge.style.display = 'none';
                    }
                }).catch(function () { /* offline — the page reload will catch up */ });
            });
        }

        if (!feedUrl || !badge) {
            return;
        }

        // Two minutes: often enough to be useful, rare enough to be cheap.
        setInterval(function () {
            fetch(feedUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (res) { return res.ok ? res.json() : null; })
                .then(function (data) {
                    if (!data) {
                        return;
                    }

                    badge.textContent   = data.unread > 99 ? '99+' : data.unread;
                    badge.style.display = data.unread > 0 ? '' : 'none';
                })
                .catch(function () { /* ignore transient failures */ });
        }, 120000);
    }

    /* -------------------------------------------------------------------
       Reveal panels — a button marked data-ar-toggle="<selector>" shows or
       hides its target. Used by the inline "reject with a reason" forms.
       ------------------------------------------------------------------- */
    function initToggles() {
        document.querySelectorAll('[data-ar-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();

                var target = document.querySelector(btn.getAttribute('data-ar-toggle'));

                if (!target) {
                    return;
                }

                var hidden = target.hasAttribute('hidden');

                if (hidden) {
                    target.removeAttribute('hidden');
                    var field = target.querySelector('input, textarea, select');
                    if (field) { field.focus(); }
                } else {
                    target.setAttribute('hidden', '');
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        initTheme();
        initStackedTables();
        initImagePreviews();
        initSubmitGuards();
        initLiveFilters();
        initBulkSelect();
        initPermissionMatrix();
        initNotifications();
        initToggles();
    });
})();
