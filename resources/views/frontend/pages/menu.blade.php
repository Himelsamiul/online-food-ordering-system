@extends('frontend.master')

@push('styles')
@include('frontend.partials.food-card-styles')
@include('frontend.partials.category-card-styles')
<style>
    /* ============ FILTER BAR ============ */
    .menu-head {
        text-align: center;
        margin-bottom: 28px;
    }

    .menu-head h2 {
        font-weight: 700;
        color: #fff;
    }

    .menu-head p {
        color: rgba(255,255,255,0.7);
        margin-bottom: 0;
    }

    .filter-bar {
        background: rgba(255,255,255,0.08);
        backdrop-filter: blur(14px);
        -webkit-backdrop-filter: blur(14px);
        border: 1px solid rgba(255,255,255,0.15);
        border-radius: 18px;
        padding: 20px 22px;
        margin-bottom: 28px;
    }

    .filter-label {
        font-size: 12px;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: rgba(255,255,255,0.55);
        font-weight: 700;
        margin-bottom: 8px;
        display: block;
    }

    .menu-search {
        position: relative;
    }

    .menu-search i {
        position: absolute;
        left: 16px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255,255,255,0.45);
    }

    .menu-search input {
        width: 100%;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 30px;
        color: #fff;
        padding: 12px 18px 12px 44px;
        outline: none;
        transition: .2s;
    }

    .menu-search input:focus {
        border-color: #f1b816;
        background: rgba(255,255,255,0.14);
    }

    .menu-search input::placeholder {
        color: rgba(255,255,255,0.4);
    }

    .sort-select {
        width: 100%;
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.2);
        border-radius: 30px;
        color: #fff;
        padding: 12px 16px;
        outline: none;
        cursor: pointer;
    }

    .sort-select option {
        background: #1c1c1c;
        color: #fff;
    }

    /* ============ CHIPS ============ */
    .chip-row {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
    }

    .chip {
        background: rgba(255,255,255,0.08);
        border: 1px solid rgba(255,255,255,0.18);
        color: rgba(255,255,255,0.8);
        border-radius: 30px;
        padding: 7px 18px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: .18s;
        user-select: none;
    }

    .chip:hover {
        background: rgba(255,255,255,0.16);
        color: #fff;
    }

    .chip.active {
        background: #f1b816;
        border-color: #f1b816;
        color: #1c1c1c;
    }

    .chip-sub.active {
        background: #198754;
        border-color: #198754;
        color: #fff;
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
        background: rgba(255,255,255,0.2);
    }

    .price-slider .fill {
        background: #f1b816;
    }

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
        background: #fff;
        border: 3px solid #f1b816;
        cursor: grab;
        box-shadow: 0 2px 6px rgba(0,0,0,.4);
    }

    .price-slider input[type=range]::-moz-range-thumb {
        pointer-events: auto;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        background: #fff;
        border: 3px solid #f1b816;
        cursor: grab;
        box-shadow: 0 2px 6px rgba(0,0,0,.4);
    }

    .price-readout {
        display: flex;
        justify-content: space-between;
        color: #f1b816;
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
        color: rgba(255,255,255,0.75);
    }

    .reset-link {
        background: none;
        border: none;
        color: #f1b816;
        font-weight: 700;
        cursor: pointer;
        padding: 0;
    }

    .reset-link:hover {
        text-decoration: underline;
    }

    #foodGrid.is-loading {
        opacity: .35;
        pointer-events: none;
        transition: opacity .15s;
    }

    .load-more-btn {
        background: transparent;
        border: 2px solid #f1b816;
        color: #f1b816;
        font-weight: 700;
        border-radius: 30px;
        padding: 12px 40px;
        transition: .2s;
    }

    .load-more-btn:hover {
        background: #f1b816;
        color: #1c1c1c;
    }
</style>
@endpush

@section('content')

<div class="container py-5">

    <div class="menu-head">
        <h2>Our Menu</h2>
        <p>Everything we cook, on one page — filter it down to exactly what you're craving.</p>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="filter-bar">

        <div class="row g-3 align-items-end">

            <div class="col-lg-5 mb-3 mb-lg-0">
                <label class="filter-label" for="menuSearch">Search</label>
                <div class="menu-search">
                    <i class="fa fa-search"></i>
                    <input type="text" id="menuSearch" placeholder="Search for a dish..."
                           value="{{ request('q') }}" autocomplete="off">
                </div>
            </div>

            <div class="col-lg-3 mb-3 mb-lg-0">
                <label class="filter-label" for="menuSort">Sort by</label>
                <select id="menuSort" class="sort-select">
                    <option value="">Newest first</option>
                    <option value="price_low"  @selected(request('sort') === 'price_low')>Price: low to high</option>
                    <option value="price_high" @selected(request('sort') === 'price_high')>Price: high to low</option>
                    <option value="discount"   @selected(request('sort') === 'discount')>Biggest discount</option>
                    <option value="name"       @selected(request('sort') === 'name')>Name (A–Z)</option>
                </select>
            </div>

            <div class="col-lg-4">
                <label class="filter-label">Price range</label>

                <div class="price-slider">
                    <div class="track"></div>
                    <div class="fill" id="priceFill"></div>
                    <input type="range" id="priceMin"
                           min="{{ $minPrice }}" max="{{ $maxPrice }}" step="1"
                           value="{{ request('min_price', $minPrice) }}">
                    <input type="range" id="priceMax"
                           min="{{ $minPrice }}" max="{{ $maxPrice }}" step="1"
                           value="{{ request('max_price', $maxPrice) }}">
                </div>

                <div class="price-readout">
                    <span>৳<span id="priceMinLabel">{{ request('min_price', $minPrice) }}</span></span>
                    <span>৳<span id="priceMaxLabel">{{ request('max_price', $maxPrice) }}</span></span>
                </div>
            </div>
        </div>

        <hr style="border-color: rgba(255,255,255,.15); margin: 20px 0 16px;">

        {{-- CATEGORY --}}
        <label class="filter-label">Category</label>
        <div class="mb-3" id="categoryChips">
            @include('frontend.partials.category-cards', [
                'categories'     => $categories,
                'compact'        => true,
                'activeCategory' => request('category'),
            ])
        </div>

        {{-- SUBCATEGORY (rendered by JS for the active category) --}}
        <div id="subcategoryWrap" style="display:none;">
            <label class="filter-label">Subcategory</label>
            <div class="chip-row" id="subcategoryChips"></div>
        </div>
    </div>

    {{-- ================= RESULTS ================= --}}
    <div class="result-bar">
        <span><strong id="resultCount">{{ $foods->total() }}</strong> dish(es) found</span>
        <button type="button" class="reset-link" id="resetFilters">
            <i class="fa fa-times-circle"></i> Reset filters
        </button>
    </div>

    <div class="row" id="foodGrid">
        @include('frontend.partials.food-grid', ['foods' => $foods])
    </div>

    <div class="text-center mt-3 mb-5">
        <button type="button" class="load-more-btn" id="loadMore"
                style="{{ $foods->hasMorePages() ? '' : 'display:none;' }}">
            Load more
        </button>
    </div>

</div>

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

    /* ---------- fetch ---------- */
    let pending = null;

    function reload(append = false) {
        if (!append) { state.page = 1; }

        if (pending) { pending.abort(); }

        $grid.addClass('is-loading');

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

        $('#categoryChips .cat-card').removeClass('active');
        $(this).addClass('active');

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

    $('#resetFilters').on('click', function () {
        Object.assign(state, {
            q: '', sort: '', category: '', subcategory: '',
            min_price: PRICE_MIN, max_price: PRICE_MAX, page: 1,
        });

        $('#menuSearch').val('');
        $('#menuSort').val('');
        $min.val(PRICE_MIN);
        $max.val(PRICE_MAX);
        $('#categoryChips .cat-card').removeClass('active')
            .filter('[data-id=""]').addClass('active');

        paintSlider();
        renderSubcategories();
        reload();
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
