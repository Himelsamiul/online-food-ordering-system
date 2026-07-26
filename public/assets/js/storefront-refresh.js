/* =====================================================================
   Storefront progressive enhancements.
   Pure additions — no page relies on this file to work.
   ===================================================================== */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {

        /* ---------------------------------------------------------------
           Reveal cards as they scroll into view.
           Skipped entirely when the visitor asked for reduced motion.
           --------------------------------------------------------------- */
        var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        if (!reducedMotion && 'IntersectionObserver' in window) {
            var observer = new IntersectionObserver(function (entries) {
                entries.forEach(function (entry) {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, { rootMargin: '0px 0px -40px 0px', threshold: 0.05 });

            document.querySelectorAll('.food-box, .cat-card, .promo-card').forEach(function (el, i) {
                el.classList.add('sf-reveal');
                el.style.transitionDelay = Math.min(i, 8) * 40 + 'ms';
                observer.observe(el);
            });
        }

        /* ---------------------------------------------------------------
           Back to top.
           --------------------------------------------------------------- */
        var top = document.createElement('button');
        top.type = 'button';
        top.className = 'sf-top';
        top.setAttribute('aria-label', 'Back to top');
        top.innerHTML = '&uarr;';
        document.body.appendChild(top);

        top.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: reducedMotion ? 'auto' : 'smooth' });
        });

        window.addEventListener('scroll', function () {
            top.classList.toggle('is-visible', window.scrollY > 420);
        }, { passive: true });

        /* ---------------------------------------------------------------
           Confirm destructive cart actions instead of firing on one click.
           --------------------------------------------------------------- */
        document.querySelectorAll('form[action*="cart/clear"]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                if (form.dataset.sfConfirmed) {
                    return;
                }

                e.preventDefault();

                Swal.fire({
                    icon: 'warning',
                    title: 'Clear the whole cart?',
                    text: 'Every item will be removed.',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, clear it',
                    confirmButtonColor: '#dc3545',
                    cancelButtonText: 'Keep my cart',
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.dataset.sfConfirmed = '1';
                        form.submit();
                    }
                });
            });
        });

        /* ---------------------------------------------------------------
           Stop double submits on forms that really navigate away.

           AJAX forms are excluded on purpose: they cancel the submit, so
           locking their button here would disable it permanently.
           --------------------------------------------------------------- */
        document.querySelectorAll('form:not(.add-cart-form):not([data-sf-no-lock])')
            .forEach(function (form) {
                form.addEventListener('submit', function () {
                    var btn = form.querySelector('button[type="submit"]');

                    if (!btn || btn.dataset.sfBusy) {
                        return;
                    }

                    setTimeout(function () {
                        btn.dataset.sfBusy = '1';
                        btn.disabled = true;
                    }, 0);
                });
            });
    });
})();
