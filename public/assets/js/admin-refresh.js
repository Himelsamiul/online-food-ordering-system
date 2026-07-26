/* =====================================================================
   Admin panel progressive enhancements.
   Everything here degrades safely — no page depends on it to function.
   ===================================================================== */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* ---------------------------------------------------------------
           Responsive tables.
           Copy each header's text onto the matching cell so the CSS can
           render one row as a labelled card on small screens.
           --------------------------------------------------------------- */
        document.querySelectorAll('.nxl-content table.table').forEach(function (table) {
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

        /* ---------------------------------------------------------------
           Image inputs get a live preview instead of just a filename.
           --------------------------------------------------------------- */
        document.querySelectorAll('input[type="file"][name="image"]').forEach(function (input) {
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
                        'object-fit:cover;border-radius:10px;border:1px solid #e5e7eb;';
                    input.parentNode.appendChild(preview);
                }

                preview.src = URL.createObjectURL(file);
            });
        });

        /* ---------------------------------------------------------------
           Stop double submits — a second click on a slow form used to
           create duplicate records.
           --------------------------------------------------------------- */
        document.querySelectorAll('.nxl-content form').forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"]');

                if (!btn || btn.dataset.arBusy) {
                    return;
                }

                // Let the browser finish submitting before disabling, or the
                // button's own name/value would be dropped from the payload.
                setTimeout(function () {
                    btn.dataset.arBusy = '1';
                    btn.disabled = true;
                    btn.dataset.arLabel = btn.innerHTML;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Working…';
                }, 0);
            });
        });

        /* ---------------------------------------------------------------
           Live row filter for any table that opts in with
           data-ar-filter="#someInput".
           --------------------------------------------------------------- */
        document.querySelectorAll('[data-ar-filter]').forEach(function (table) {
            var input = document.querySelector(table.getAttribute('data-ar-filter'));

            if (!input) {
                return;
            }

            input.addEventListener('input', function () {
                var needle = input.value.toLowerCase().trim();

                table.querySelectorAll('tbody tr').forEach(function (row) {
                    row.style.display =
                        !needle || row.textContent.toLowerCase().indexOf(needle) > -1 ? '' : 'none';
                });
            });
        });
    });
})();
