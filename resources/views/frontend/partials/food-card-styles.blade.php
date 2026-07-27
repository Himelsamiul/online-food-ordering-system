{{-- Shared styling for frontend.partials.food-card (menu page + home page) --}}
<style>
    /* ---------------------------------------------------------------
       Card shell.
       .food-box is also the reveal-animation target in
       storefront-refresh.js, so the class name has to stay.
       --------------------------------------------------------------- */
    .food-box {
        position: relative;
        display: flex;
        flex-direction: column;
        height: 100%;
        background: var(--sf-glass);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid var(--sf-glass-line);
        border-radius: var(--sf-radius);
        overflow: hidden;
        box-shadow: var(--sf-shadow);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
    }

    .food-box:hover {
        transform: translateY(-6px);
        border-color: var(--sf-accent);
        box-shadow: var(--sf-shadow-lg);
    }

    /* ---------------------------------------------------------------
       Media
       --------------------------------------------------------------- */
    .food-media { position: relative; }

    .food-img {
        height: 190px;
        background: rgba(0, 0, 0, .45);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .food-img i { color: rgba(255, 255, 255, .3); }

    .food-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .45s ease;
    }

    .food-box:hover .food-img img { transform: scale(1.06); }

    .discount-badge {
        position: absolute;
        top: 12px;
        right: 12px;
        z-index: 3;
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        color: var(--sf-ink);
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 800;
        line-height: 1;
        box-shadow: 0 6px 16px rgba(0, 0, 0, .45);
    }

    /* Stock state, top-left so it never collides with the discount badge. */
    .stock-flag {
        position: absolute;
        top: 12px;
        left: 12px;
        z-index: 3;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        border-radius: 20px;
        font-size: 11.5px;
        font-weight: 700;
        line-height: 1;
        background: rgba(0, 0, 0, .68);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-accent);
        backdrop-filter: blur(6px);
        -webkit-backdrop-filter: blur(6px);
    }

    .stock-flag.is-out {
        background: var(--sf-danger);
        border-color: transparent;
        color: var(--sf-ink);
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    /* ---------------------------------------------------------------
       Body
       The page already sits on a dark scrim, so this panel only needs a
       light knock-back rather than the near-black plate it used to have.
       --------------------------------------------------------------- */
    .food-details {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 14px;
        padding: 15px 16px 16px;
        background: rgba(0, 0, 0, .34);
        color: var(--sf-ink);
    }

    .food-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 6px;
        margin-bottom: 9px;
    }

    .food-tag {
        display: inline-block;
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-accent);
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
        padding: 3px 10px;
        border-radius: 20px;
        line-height: 1.5;
    }

    .food-tag.is-parent {
        color: var(--sf-muted);
        background: rgba(255, 255, 255, .06);
    }

    .food-name {
        font-weight: 700;
        font-size: 16px;
        line-height: 1.3;
        margin: 0 0 5px;
        color: var(--sf-ink);
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 2.6em;
    }

    .food-box .details-link:hover .food-name { color: var(--sf-accent); }

    .food-desc {
        color: var(--sf-muted);
        font-size: 13px;
        line-height: 1.5;
        margin-bottom: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* ---------------------------------------------------------------
       Price + add to cart
       --------------------------------------------------------------- */
    .food-foot {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 10px;
    }

    .price-block { line-height: 1.2; }

    .price-now {
        display: block;
        font-size: 19px;
        font-weight: 800;
        color: var(--sf-ink);
    }

    .price-was {
        font-size: 12.5px;
        font-weight: 600;
        color: var(--sf-faint);
        text-decoration: line-through;
    }

    .food-box .add-cart-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 16px;
        border: none;
        border-radius: 30px;
        font-size: 13.5px;
        font-weight: 700;
        white-space: nowrap;
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        color: var(--sf-ink);
        transition: transform .18s, box-shadow .18s, background-color .18s;
    }

    .food-box .add-cart-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 10px 22px rgba(0, 0, 0, .45);
        color: var(--sf-ink);
    }

    .food-box .add-cart-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .25);
    }

    .food-box .add-cart-btn.disabled-btn,
    .food-box .add-cart-btn:disabled {
        background: rgba(255, 255, 255, .12);
        color: var(--sf-muted);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    /* ---------------------------------------------------------------
       Sold out — the whole card reads as unavailable, not just the button.
       --------------------------------------------------------------- */
    .food-box.is-out,
    .food-box.is-out:hover {
        border-color: rgba(255, 255, 255, .1);
        transform: none;
        box-shadow: var(--sf-shadow);
    }

    .food-box.is-out .food-img img,
    .food-box.is-out:hover .food-img img {
        filter: grayscale(.9);
        opacity: .45;
        transform: none;
    }

    .food-box.is-out .food-name,
    .food-box.is-out .price-now { color: var(--sf-muted); }

    .food-box.is-out .add-cart-btn {
        background: rgba(255, 255, 255, .08);
        color: var(--sf-faint);
    }

    .food-box .details-link,
    .food-box .details-link:hover,
    .food-box .details-link:focus {
        text-decoration: none;
        color: inherit;
    }

    /* ---------------------------------------------------------------
       Empty state for the food grid
       --------------------------------------------------------------- */
    .menu-empty {
        text-align: center;
        color: var(--sf-muted);
        padding: 54px 22px;
        background: var(--sf-glass);
        border: 1px dashed var(--sf-glass-line);
        border-radius: var(--sf-radius);
    }

    .menu-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: rgba(0, 0, 0, .85);
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        box-shadow: 0 12px 26px rgba(0, 0, 0, .45);
    }

    .menu-empty h5 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .menu-empty p {
        color: var(--sf-muted);
        margin-bottom: 20px;
    }

    /* ---------------------------------------------------------------
       Responsive.
       These re-state the small-screen sizes from storefront-refresh.css:
       that file loads before this block, and a media query adds no
       specificity, so the rules above would otherwise win everywhere.
       --------------------------------------------------------------- */
    @media (max-width: 575.98px) {
        .food-img { height: 150px; }
        .price-now { font-size: 16px; }

        .food-details { padding: 13px; gap: 12px; }
        .food-name { font-size: 14.5px; }
        .food-desc { display: none; }

        .food-foot {
            flex-direction: column;
            align-items: stretch;
            gap: 9px;
        }

        .food-box .add-cart-btn { width: 100%; }
        .menu-empty { padding: 40px 16px; }
    }
</style>
