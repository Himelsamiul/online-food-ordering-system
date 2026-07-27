{{-- Shared styling for frontend.partials.category-cards --}}
<style>
    .cat-card-row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 18px;
    }

    /* Menu page variant: one scrollable row instead of a wrapping grid */
    .cat-card-row.is-compact {
        display: flex;
        gap: 12px;
        overflow-x: auto;
        overflow-y: hidden;
        padding: 4px 2px 12px;
        scrollbar-width: thin;
        scrollbar-color: rgba(255, 255, 255, .28) rgba(255, 255, 255, .06);
        scroll-snap-type: x proximity;
        -webkit-overflow-scrolling: touch;
    }

    .cat-card-row.is-compact::-webkit-scrollbar { height: 6px; }

    .cat-card-row.is-compact::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, .06);
        border-radius: 10px;
    }

    .cat-card-row.is-compact::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, .28);
        border-radius: 10px;
    }

    .cat-card-row.is-compact .cat-card {
        flex: 0 0 142px;
        scroll-snap-align: start;
    }

    .cat-card-row.is-compact .cat-card-img { height: 78px; }
    .cat-card-row.is-compact .cat-card-body { padding: 9px 10px; }

    /* ---------------------------------------------------------------
       The card itself. .cat-card is also the reveal-animation target in
       storefront-refresh.js and the click target of the menu filters, so
       the class name has to stay.
       --------------------------------------------------------------- */
    .cat-card {
        position: relative;
        display: block;
        background: var(--sf-glass);
        border: 1px solid var(--sf-glass-line);
        border-radius: var(--sf-radius);
        overflow: hidden;
        text-decoration: none;
        color: var(--sf-ink);
        box-shadow: var(--sf-shadow);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        cursor: pointer;
    }

    .cat-card:hover,
    .cat-card:focus {
        transform: translateY(-6px);
        border-color: var(--sf-accent);
        box-shadow: var(--sf-shadow-lg);
        text-decoration: none;
        color: var(--sf-ink);
        outline: none;
    }

    .cat-card-img {
        height: 130px;
        background: linear-gradient(135deg, rgba(255, 255, 255, .14), rgba(0, 0, 0, .4));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        overflow: hidden;
    }

    .cat-card-img i { color: var(--sf-accent); opacity: .7; }

    .cat-card-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform .45s ease;
    }

    .cat-card:hover .cat-card-img img { transform: scale(1.07); }

    .cat-card-body {
        padding: 12px 14px;
        text-align: center;
        background: rgba(0, 0, 0, .42);
        transition: background-color .2s ease;
    }

    .cat-card-body h6 {
        font-weight: 700;
        margin: 0 0 2px;
        color: var(--sf-ink);
        font-size: 15px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .cat-card-body small {
        color: var(--sf-accent);
        font-weight: 600;
        font-size: 12px;
    }

    /* ---------------------------------------------------------------
       Selected state — used on /menu, where exactly one card is the
       live filter. It has to be readable at a glance across a scrolling
       row, so it gets the border, the fill and a tick.
       --------------------------------------------------------------- */
    .cat-card.active {
        border-color: var(--sf-accent);
        box-shadow: 0 0 0 2px var(--sf-accent), var(--sf-shadow);
        transform: translateY(-3px);
    }

    .cat-card.active .cat-card-body { background: var(--sf-accent); }
    .cat-card.active .cat-card-body h6 { color: rgba(0, 0, 0, .88); }
    .cat-card.active .cat-card-body small { color: rgba(0, 0, 0, .6); }

    .cat-card::after {
        content: '\f00c';                 /* fa-check, Font Awesome 4 */
        font-family: FontAwesome;
        font-weight: normal;
        font-style: normal;
        -webkit-font-smoothing: antialiased;
        position: absolute;
        top: 8px;
        right: 8px;
        z-index: 3;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        background: var(--sf-accent);
        color: rgba(0, 0, 0, .85);
        box-shadow: 0 4px 12px rgba(0, 0, 0, .5);
        opacity: 0;
        transform: scale(.5);
        transition: opacity .18s ease, transform .18s ease;
        pointer-events: none;
    }

    .cat-card.active::after { opacity: 1; transform: none; }

    @media (max-width: 575.98px) {
        .cat-card-row { grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 12px; }
        .cat-card-body h6 { font-size: 13px; }
        .cat-card-img { height: 104px; }
    }
</style>
