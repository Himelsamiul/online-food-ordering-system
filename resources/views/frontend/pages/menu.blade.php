@extends('frontend.master')

@section('title', 'Menu')

@push('styles')
@include('frontend.partials.food-card-styles')
@include('frontend.partials.category-card-styles')
<style>
    /* ============ PAGE HEAD ============ */
    .sf-page-head { margin-bottom: 26px; }

    .sf-page-head h2 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .sf-page-head p {
        color: var(--sf-muted);
        margin-bottom: 0;
    }

    /* ============ FILTER BAR ============ */
    .filter-bar {
        padding: 18px 20px;
        margin-bottom: 22px;
    }

    .filter-top {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 12px;
    }

    .filter-top .menu-search { flex: 1 1 220px; }

    /* Under lg the whole control set folds behind this button instead of
       spilling out of the bar. */
    .filter-toggle {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        height: 46px;
        padding: 0 18px;
        border-radius: 30px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-ink);
        font-size: 13.5px;
        font-weight: 700;
        transition: border-color .18s, color .18s, background-color .18s;
    }

    .filter-toggle:hover { background: rgba(255, 255, 255, .16); }

    .filter-toggle:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .2);
    }

    .filter-toggle.has-filters {
        border-color: var(--sf-accent);
        color: var(--sf-accent);
    }

    .filter-toggle .fa-angle-down { transition: transform .2s ease; }
    .filter-toggle[aria-expanded="true"] .fa-angle-down { transform: rotate(180deg); }

    #menuFilterBody { padding-top: 18px; }

    .filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
        gap: 18px 26px;
    }

    .filter-label {
        display: flex;
        align-items: center;
        gap: 7px;
        font-size: 11.5px;
        letter-spacing: .1em;
        text-transform: uppercase;
        color: var(--sf-faint);
        font-weight: 700;
        margin-bottom: 9px;
    }

    .filter-label i { color: var(--sf-accent); font-size: 12px; }

    .filter-divider {
        height: 1px;
        background: var(--sf-glass-line);
        margin: 20px 0 16px;
    }

    /* ============ SEARCH ============ */
    .menu-search { position: relative; }

    .menu-search i {
        position: absolute;
        left: 18px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--sf-faint);
        pointer-events: none;
    }

    .menu-search input {
        width: 100%;
        height: 46px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        border-radius: 30px;
        color: var(--sf-ink);
        padding: 0 18px 0 46px;
        outline: none;
        transition: border-color .18s, background-color .18s, box-shadow .18s;
    }

    .menu-search input:focus {
        border-color: var(--sf-accent);
        background: rgba(255, 255, 255, .14);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .12);
    }

    .menu-search input::placeholder { color: var(--sf-faint); }

    /* ============ SORT ============ */
    .sort-select {
        width: 100%;
        height: 46px;
        line-height: 44px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid var(--sf-glass-line);
        border-radius: 30px;
        color: var(--sf-ink);
        padding: 0 42px 0 18px;
        font-size: 14px;
        outline: none;
        cursor: pointer;
    }

    select.sort-select option { background: var(--sf-panel); color: var(--sf-ink); }

    /*
     * The template's custom.js runs $('select').niceSelect() on every page,
     * which hides the native control and inserts a div that copies the
     * select's classes. Both paths therefore need dark styling.
     */
    .filter-bar .nice-select {
        float: none;
        display: block;
        height: 46px;
        line-height: 44px;
        padding: 0 42px 0 18px;
    }

    .filter-bar .nice-select::after {
        border-bottom-color: var(--sf-accent);
        border-right-color: var(--sf-accent);
        right: 20px;
    }

    .filter-bar .nice-select .current { color: var(--sf-ink); font-size: 14px; }

    .filter-bar .nice-select .list {
        width: 100%;
        background: var(--sf-panel);
        border: 1px solid var(--sf-glass-line);
        border-radius: var(--sf-radius-sm);
        box-shadow: var(--sf-shadow-lg);
        padding: 6px;
        margin-top: 8px;
    }

    .filter-bar .nice-select .option {
        color: rgba(255, 255, 255, .82);
        border-radius: 9px;
        min-height: 38px;
        line-height: 38px;
        padding: 0 12px;
        font-size: 13.5px;
    }

    .filter-bar .nice-select .option:hover,
    .filter-bar .nice-select .option.focus,
    .filter-bar .nice-select .option.selected.focus {
        background: rgba(255, 255, 255, .1);
        color: var(--sf-accent);
    }

    .filter-bar .nice-select .option.selected {
        font-weight: 700;
        color: var(--sf-accent);
    }

    /* ============ CHIPS ============ */
    .chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .chip {
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        color: rgba(255, 255, 255, .8);
        border-radius: 30px;
        padding: 7px 16px;
        font-size: 13.5px;
        font-weight: 600;
        line-height: 1.4;
        cursor: pointer;
        transition: .18s;
        user-select: none;
    }

    .chip:hover {
        background: rgba(255, 255, 255, .16);
        color: var(--sf-ink);
    }

    .chip.active {
        background: var(--sf-accent);
        border-color: var(--sf-accent);
        color: rgba(0, 0, 0, .85);
    }

    .chip-sub.active {
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        border-color: transparent;
        color: var(--sf-ink);
    }

    /* ============ DUAL PRICE SLIDER ============ */
    .price-slider {
        position: relative;
        height: 34px;
        margin-top: 4px;
    }

    .price-slider .track,
    .price-slider .fill {
        position: absolute;
        top: 15px;
        height: 4px;
        border-radius: 4px;
    }

    .price-slider .track {
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, .2);
    }

    .price-slider .fill { background: var(--sf-accent); }

    /* Two range inputs stacked on top of each other; only the thumbs
       stay clickable so both handles remain grabbable. */
    .price-slider input[type=range] {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 34px;
        margin: 0;
        background: none;
        appearance: none;
        -webkit-appearance: none;
        pointer-events: none;
    }

    .price-slider input[type=range]::-webkit-slider-thumb {
        -webkit-appearance: none;
        pointer-events: auto;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--sf-ink);
        border: 3px solid var(--sf-accent);
        cursor: grab;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .5);
    }

    .price-slider input[type=range]::-moz-range-thumb {
        pointer-events: auto;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: var(--sf-ink);
        border: 3px solid var(--sf-accent);
        cursor: grab;
        box-shadow: 0 2px 6px rgba(0, 0, 0, .5);
    }

    .price-slider input[type=range]:focus { outline: none; }

    .price-readout {
        display: flex;
        justify-content: space-between;
        color: var(--sf-accent);
        font-weight: 700;
        font-size: 14px;
        margin-top: 2px;
    }

    /* ============ RESULT BAR ============ */
    .result-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 18px;
        color: var(--sf-muted);
    }

    .result-left {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
    }

    .result-left strong { color: var(--sf-ink); }

    .filter-count {
        display: inline-block;
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-accent);
        border-radius: 20px;
        padding: 3px 12px;
        font-size: 12px;
        font-weight: 700;
    }

    .reset-link {
        background: none;
        border: none;
        color: var(--sf-accent);
        font-weight: 700;
        font-size: 14px;
        cursor: pointer;
        padding: 0;
    }

    .reset-link:hover { text-decoration: underline; }
    .reset-link:focus { outline: none; text-decoration: underline; }

    #foodGrid.is-loading {
        opacity: .35;
        pointer-events: none;
        transition: opacity .15s;
    }

    .load-more-btn {
        background: transparent;
        border: 2px solid var(--sf-accent);
        color: var(--sf-accent);
        font-weight: 700;
        border-radius: 30px;
        padding: 12px 40px;
        transition: .2s;
    }

    .load-more-btn:hover {
        background: var(--sf-accent);
        color: rgba(0, 0, 0, .85);
    }

    .load-more-btn:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 255, 255, .2);
    }

    @media (max-width: 575.98px) {
        .filter-bar { margin-bottom: 18px; }
        #menuFilterBody { padding-top: 14px; }
        .result-bar { font-size: 14px; }
    }
</style>
@endpush

@section('content')

<section class="menu-section layout_padding">
    <div class="container">

        <div class="sf-page-head text-center">
            <h2>Our Menu</h2>
            <p>Everything we cook, on one page — filter it down to exactly what you're craving.</p>
        </div>

        {{-- ================= FILTERS ================= --}}
        <div class="filter-bar glass-card">

            <div class="filter-top">
                <div class="menu-search">
                    <i class="fa fa-search" aria-hidden="true"></i>
                    <input type="text" id="menuSearch" placeholder="Search for a dish…"
                           value="{{ request('q') }}" autocomplete="off"
                           aria-label="Search the menu">
                </div>

                <button type="button" class="filter-toggle d-lg-none" id="filterToggle"
                        data-toggle="collapse" data-target="#menuFilterBody"
                        aria-expanded="false" aria-controls="menuFilterBody">
                    <i class="fa fa-sliders" aria-hidden="true"></i>
                    Filters
                    <i class="fa fa-angle-down" aria-hidden="true"></i>
                </button>
            </div>

            {{-- Collapsed on phones, always open from lg up (.d-lg-block is
                 !important, so it beats .collapse:not(.show)). --}}
            <div class="collapse d-lg-block" id="menuFilterBody">

                <div class="filter-grid">
                    <div>
                        <label class="filter-label" for="menuSort">
                            <i class="fa fa-sort-amount-asc" aria-hidden="true"></i> Sort by
                        </label>
                        <select id="menuSort" class="sort-select">
                            <option value="">Newest first</option>
                            <option value="price_low"  @selected(request('sort') === 'price_low')>Price: low to high</option>
                            <option value="price_high" @selected(request('sort') === 'price_high')>Price: high to low</option>
                            <option value="discount"   @selected(request('sort') === 'discount')>Biggest discount</option>
                            <option value="name"       @selected(request('sort') === 'name')>Name (A–Z)</option>
                        </select>
                    </div>

                    <div>
                        <label class="filter-label">
                            <i class="fa fa-money" aria-hidden="true"></i> Price range
                        </label>

                        <div class="price-slider">
                            <div class="track"></div>
                            <div class="fill" id="priceFill"></div>
                            <input type="range" id="priceMin" aria-label="Lowest price"
                                   min="{{ $minPrice }}" max="{{ $maxPrice }}" step="1"
                                   value="{{ request('min_price', $minPrice) }}">
                            <input type="range" id="priceMax" aria-label="Highest price"
                                   min="{{ $minPrice }}" max="{{ $maxPrice }}" step="1"
                                   value="{{ request('max_price', $maxPrice) }}">
                        </div>

                        <div class="price-readout">
                            <span>৳<span id="priceMinLabel">{{ request('min_price', $minPrice) }}</span></span>
                            <span>৳<span id="priceMaxLabel">{{ request('max_price', $maxPrice) }}</span></span>
                        </div>
                    </div>
                </div>

                <div class="filter-divider"></div>

                {{-- CATEGORY --}}
                <label class="filter-label">
                    <i class="fa fa-th-large" aria-hidden="true"></i> Category
                </label>
                <div class="mb-3" id="categoryChips">
                    @include('frontend.partials.category-cards', [
                        'categories'     => $categories,
                        'compact'        => true,
                        'activeCategory' => request('category'),
                    ])
                </div>

                {{-- SUBCATEGORY (rendered by JS for the active category) --}}
                <div id="subcategoryWrap" style="display:none;">
                    <label class="filter-label">
                        <i class="fa fa-tags" aria-hidden="true"></i> Subcategory
                    </label>
                    <div class="chip-row" id="subcategoryChips"></div>
                </div>
            </div>
        </div>

        {{-- ================= RESULTS ================= --}}
        <div class="result-bar">
            <div class="result-left">
                <span><strong id="resultCount">{{ $foods->total() }}</strong> dish(es) found</span>
                <span class="filter-count" id="filterCount" style="display:none;"></span>
            </div>

            <button type="button" class="reset-link" id="resetFilters">
                <i class="fa fa-times-circle" aria-hidden="true"></i> Reset filters
            </button>
        </div>

        <div class="row" id="foodGrid">
            @include('frontend.partials.food-grid', ['foods' => $foods])
        </div>

        <div class="text-center mt-3">
            <button type="button" class="load-more-btn" id="loadMore"
                    style="{{ $foods->hasMorePages() ? '' : 'display:none;' }}">
                Load more
            </button>
        </div>

    </div>
</section>

@endsection

@push('scripts')
<script>
$(function () {

    const SUBCATEGORIES = @json(
        $categories->mapWithKeys(fn ($c) => [
            $c->id => $c->subcategories->map(fn ($s) => ['id' => $s->id, 'name' => $s->name])
        ])
    );

    const MENU_URL   = "{{ route('menu.index') }}";
    const PRICE_MIN  = {{ $minPrice }};
    const PRICE_MAX  = {{ $maxPrice }};

    const state = {
        q:           @json(request('q', '')),
        sort:        @json(request('sort', '')),
        category:    @json((string) request('category', '')),
        subcategory: @json((string) request('subcategory', '')),
        min_price:   {{ (int) request('min_price', $minPrice) }},
        max_price:   {{ (int) request('max_price', $maxPrice) }},
        page:        1,
    };

    const $grid = $('#foodGrid');

    /* ---------- price slider ---------- */
    const $min = $('#priceMin'), $max = $('#priceMax');

    function paintSlider() {
        const span = PRICE_MAX - PRICE_MIN || 1;
        const left  = ((state.min_price - PRICE_MIN) / span) * 100;
        const right = ((state.max_price - PRICE_MIN) / span) * 100;

        $('#priceFill').css({ left: left + '%', width: (right - left) + '%' });
        $('#priceMinLabel').text(state.min_price);
        $('#priceMaxLabel').text(state.max_price);
    }

    function readSlider() {
        let lo = parseInt($min.val(), 10);
        let hi = parseInt($max.val(), 10);

        // Stop the handles from crossing over each other
        if (lo > hi) { [lo, hi] = [hi, lo]; }

        state.min_price = lo;
        state.max_price = hi;
        paintSlider();
    }

    $min.add($max).on('input', readSlider);
    $min.add($max).on('change', () => reload());   // fires when the handle is released

    paintSlider();

    /* ---------- subcategory chips ---------- */
    function renderSubcategories() {
        const subs = SUBCATEGORIES[state.category] || [];

        if (!state.category || subs.length === 0) {
            $('#subcategoryWrap').hide();
            $('#subcategoryChips').empty();
            return;
        }

        const html = subs.map(s =>
            `<span class="chip chip-sub ${String(s.id) === state.subcategory ? 'active' : ''}" data-id="${s.id}">${$('<div>').text(s.name).html()}</span>`
        ).join('');

        $('#subcategoryChips').html(html);
        $('#subcategoryWrap').show();
    }

    renderSubcategories();

    /* ---------- query string ---------- */
    function queryParams(extra = {}) {
        const p = {};

        if (state.q)           p.q           = state.q;
        if (state.sort)        p.sort        = state.sort;
        if (state.category)    p.category    = state.category;
        if (state.subcategory) p.subcategory = state.subcategory;
        if (state.min_price > PRICE_MIN) p.min_price = state.min_price;
        if (state.max_price < PRICE_MAX) p.max_price = state.max_price;

        return Object.assign(p, extra);
    }

    /* ---------- "3 filters on" badge, so a narrowed list never looks empty
                  for no visible reason ---------- */
    function paintFilterCount() {
        const n = Object.keys(queryParams()).length;

        $('#filterCount')
            .text(n + (n === 1 ? ' filter on' : ' filters on'))
            .toggle(n > 0);

        $('#filterToggle').toggleClass('has-filters', n > 0);
    }

    paintFilterCount();

    /* ---------- fetch ---------- */
    let pending = null;

    function reload(append = false) {
        if (!append) { state.page = 1; }

        if (pending) { pending.abort(); }

        $grid.addClass('is-loading');
        paintFilterCount();

        pending = $.getJSON(MENU_URL, queryParams({ page: state.page }))
            .done(function (res) {
                if (append) {
                    $grid.append(res.html);
                } else {
                    $grid.html(res.html);
                }

                $('#resultCount').text(res.total);
                $('#loadMore').toggle(res.has_more);

                // Keep the address bar shareable / back-button friendly
                const qs = $.param(queryParams());
                window.history.replaceState({}, '', qs ? MENU_URL + '?' + qs : MENU_URL);
            })
            .always(function () {
                pending = null;
                $grid.removeClass('is-loading');
            });
    }

    /* ---------- events ---------- */
    let typing;
    $('#menuSearch').on('input', function () {
        state.q = this.value;
        clearTimeout(typing);
        typing = setTimeout(reload, 350);
    });

    $('#menuSort').on('change', function () {
        state.sort = this.value;
        reload();
    });

    $('#categoryChips').on('click', '.cat-card', function (e) {
        e.preventDefault();

        state.category    = String($(this).data('id') || '');
        state.subcategory = '';       // old subcategory belongs to another category

        $('#categoryChips .cat-card').removeClass('active').removeAttr('aria-current');
        $(this).addClass('active').attr('aria-current', 'true');

        renderSubcategories();
        reload();
    });

    $('#subcategoryChips').on('click', '.chip-sub', function () {
        const id = String($(this).data('id'));

        // Clicking the active chip clears it
        state.subcategory = (state.subcategory === id) ? '' : id;

        $('.chip-sub').removeClass('active');
        if (state.subcategory) { $(this).addClass('active'); }

        reload();
    });

    $('#loadMore').on('click', function () {
        state.page += 1;
        reload(true);
    });

    function resetFilters() {
        Object.assign(state, {
            q: '', sort: '', category: '', subcategory: '',
            min_price: PRICE_MIN, max_price: PRICE_MAX, page: 1,
        });

        $('#menuSearch').val('');
        $('#menuSort').val('');

        // custom.js swaps the select for a nice-select widget, which keeps
        // its own label — tell it to re-read the value it now has.
        if ($.fn.niceSelect) { $('#menuSort').niceSelect('update'); }

        $min.val(PRICE_MIN);
        $max.val(PRICE_MAX);
        $('#categoryChips .cat-card').removeClass('active').removeAttr('aria-current')
            .filter('[data-id=""]').addClass('active').attr('aria-current', 'true');

        paintSlider();
        renderSubcategories();
        reload();
    }

    $('#resetFilters').on('click', resetFilters);

    // The empty state offers the same escape hatch; it is re-rendered on
    // every fetch, so the handler is delegated from the grid.
    $grid.on('click', '.js-reset-filters', function (e) {
        e.preventDefault();
        resetFilters();
    });

    /* ---------- add to cart without losing the filters ---------- */
    $grid.on('submit', '.add-cart-form', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $btn  = $form.find('button');

        $.post($form.attr('action'), $form.serialize())
            .done(function (res) {
                $btn.prop('disabled', true)
                    .addClass('disabled-btn')
                    .attr('title', 'Already in cart')
                    .find('i').attr('class', 'fa fa-check');

                $btn.find('.add-cart-label').text('In cart');

                if (res.cart_count !== undefined) {
                    $('#cartCount').text(res.cart_count).show();
                }

                Swal.fire({
                    icon: res.status === 'success' ? 'success' : 'info',
                    title: res.status === 'success' ? 'Added to cart' : 'Notice',
                    text: res.message,
                    timer: 1400,
                    showConfirmButton: false,
                });
            })
            .fail(function () {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Could not add this item.' });
            });
    });
});
</script>
@endpush
