@extends('backend.master')

@section('title', 'New Sale')

@section('content')

<style>
    /* ---------------------------------------------------------------
       POS terminal. Colours come from the design-system variables so
       the till reads correctly in both skins.
       --------------------------------------------------------------- */

    .pos-toolbar { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px; }

    .pos-search { position: relative; flex: 1 1 220px; }
    .pos-search > i {
        position: absolute;
        left: 13px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--ar-faint);
        font-size: 14px;
        pointer-events: none;
    }
    .pos-search .form-control { padding-left: 36px; }
    .pos-cat { flex: 0 1 220px; }

    .pos-grid {
        max-height: calc(100vh - 340px);
        min-height: 240px;
        overflow-y: auto;
        padding-right: 4px;
    }

    #posFoodGrid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(142px, 1fr));
        gap: 10px;
    }

    .pos-food-card {
        cursor: pointer;
        height: 100%;
        display: flex;
        flex-direction: column;
        gap: 3px;
        padding: 12px 13px;
        background: var(--ar-surface);
        border: 1px solid var(--ar-line);
        border-radius: var(--ar-radius-sm);
        transition: border-color .16s var(--ar-ease), box-shadow .16s var(--ar-ease), transform .16s var(--ar-ease);
        user-select: none;
    }

    .pos-food-card:hover {
        border-color: var(--ar-primary);
        box-shadow: 0 8px 20px var(--ar-primary-glow);
        transform: translateY(-2px);
    }

    .pos-food-card:active { transform: translateY(0); }

    .pos-food-card .name {
        font-size: 13px;
        font-weight: 650;
        color: var(--ar-ink);
        line-height: 1.3;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .pos-food-card .price {
        font-size: 15px;
        font-weight: 800;
        color: var(--ar-primary);
        font-variant-numeric: tabular-nums;
    }

    .pos-food-card .was {
        font-size: 11px;
        font-weight: 600;
        color: var(--ar-faint);
        text-decoration: line-through;
        margin-left: 5px;
    }

    .pos-food-card .stock { font-size: 11px; font-weight: 650; color: var(--ar-muted); }
    .pos-food-card .stock.low { color: var(--ar-warning); }

    /* Order type switch */
    .pos-typeswitch { display: flex; gap: 6px; margin-bottom: 14px; }

    .nxl-content .order-type-btn {
        flex: 1 1 0;
        padding: 8px 6px;
        font-size: 13px;
        font-weight: 650;
        background: var(--ar-surface-3);
        border: 1px solid var(--ar-line);
        color: var(--ar-muted);
    }

    .nxl-content .order-type-btn:hover { color: var(--ar-primary); border-color: var(--ar-primary); }

    .nxl-content .order-type-btn.active {
        background: linear-gradient(135deg, var(--ar-primary), var(--ar-primary-600));
        border-color: transparent;
        color: #fff;
        box-shadow: 0 6px 16px var(--ar-primary-glow);
    }

    /* Bill panel */
    .pos-bill { position: sticky; top: 88px; }

    .pos-bill-items {
        max-height: 36vh;
        overflow-y: auto;
        border: 1px solid var(--ar-line);
        border-radius: var(--ar-radius-sm);
        margin-bottom: 14px;
    }

    .pos-bill-items .table thead th { padding: 9px 10px; }
    .pos-bill-items .table tbody td { padding: 9px 10px; font-size: 13px; }

    .nxl-content .qty-btn {
        width: 26px;
        height: 26px;
        min-width: 26px;
        padding: 0 !important;
        font-size: 14px;
        line-height: 1;
        border-radius: var(--ar-radius-xs) !important;
    }

    .pos-empty { color: var(--ar-faint); font-size: 13px; }

    .pos-sum {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        font-size: 13px;
        padding: 3px 0;
        color: var(--ar-ink-2);
    }

    .pos-sum > span:last-child { font-variant-numeric: tabular-nums; font-weight: 650; color: var(--ar-ink); }
    .pos-sum.is-discount > span:last-child { color: var(--ar-danger); }

    .pos-total {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        margin-top: 9px;
        padding-top: 10px;
        border-top: 1px dashed var(--ar-line);
        font-size: 19px;
        font-weight: 800;
        color: var(--ar-ink);
    }

    .pos-total > span:last-child { font-variant-numeric: tabular-nums; }

    .pos-change {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        font-size: 13px;
        font-weight: 650;
        padding-top: 4px;
    }

    @media (max-width: 991.98px) {
        .pos-bill { position: static; }
        .pos-grid { max-height: none; }
    }
</style>

<div class="container-fluid">

    <x-page-header
        title="New Sale"
        subtitle="Ring up a counter order, take payment, and print the receipt."
        icon="feather-shopping-cart"
        :breadcrumb="['POS Billing' => null, 'New Sale' => null]">
        @can('pos.view')
            <a href="{{ route('admin.pos.sales') }}" class="btn btn-soft-primary">
                <i class="feather-list"></i> Sales History
            </a>
        @endcan
    </x-page-header>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">

        {{-- ================= LEFT : FOOD PICKER ================= --}}
        <div class="col-xl-7 col-lg-7">
            <div class="card">
                <div class="card-header">
                    <h5>Menu</h5>
                    <span class="text-muted fs-13">{{ $foods->count() }} items in stock</span>
                </div>
                <div class="card-body">

                    {{-- Search + category filter --}}
                    <div class="pos-toolbar">
                        <div class="pos-search">
                            <i class="feather-search"></i>
                            <input type="text" id="posSearch" class="form-control"
                                   placeholder="Search food by name…" autocomplete="off">
                        </div>
                        <div class="pos-cat">
                            <select id="posCategory" class="form-select">
                                <option value="">All Categories</option>
                                @foreach ($categories as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- Food grid --}}
                    <div class="pos-grid">
                        <div id="posFoodGrid">
                            @forelse ($foods as $food)
                                @php
                                    $pct   = $food->discount ?? 0;
                                    $final = $pct > 0 ? round($food->price - ($food->price * $pct / 100), 2) : (float) $food->price;
                                @endphp
                                <div class="pos-food-col"
                                     data-name="{{ strtolower($food->name) }}"
                                     data-category="{{ $food->subcategory?->category?->id }}">
                                    <div class="pos-food-card"
                                         data-id="{{ $food->id }}"
                                         data-food-name="{{ $food->name }}"
                                         data-price="{{ $final }}"
                                         data-stock="{{ $food->quantity }}"
                                         title="{{ $food->name }}"
                                         onclick="posAdd(this)">
                                        <div class="name">{{ $food->name }}</div>
                                        <div class="price">
                                            ৳{{ number_format($final, 2) }}
                                            @if ($pct > 0)
                                                <span class="was">৳{{ number_format($food->price, 2) }}</span>
                                            @endif
                                        </div>
                                        <div class="stock {{ $food->quantity <= 5 ? 'low' : '' }}">
                                            Stock: {{ $food->quantity }}
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="grid-column: 1 / -1">
                                    <x-empty-state icon="feather-shopping-bag"
                                                   title="Nothing is in stock"
                                                   message="Every active item is out of stock, so there is nothing to sell right now." />
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ================= RIGHT : BILL ================= --}}
        <div class="col-xl-5 col-lg-5">
            <form method="POST" action="{{ route('admin.pos.store') }}" id="posForm">
                @csrf
                <div class="card pos-bill">
                    <div class="card-header">
                        <h5>Current Bill</h5>
                        <span class="text-muted fs-13">{{ auth()->user()->name ?? 'Counter' }}</span>
                    </div>
                    <div class="card-body">

                        {{-- Order type --}}
                        <div class="pos-typeswitch">
                            <button type="button" class="btn order-type-btn active"
                                    data-type="dine_in" onclick="posSetType(this)">Dine-in</button>
                            <button type="button" class="btn order-type-btn"
                                    data-type="takeaway" onclick="posSetType(this)">Takeaway</button>
                            <button type="button" class="btn order-type-btn"
                                    data-type="delivery" onclick="posSetType(this)">Delivery</button>
                        </div>
                        <input type="hidden" name="order_type" id="orderType" value="dine_in">

                        {{-- Contextual fields --}}
                        <div class="row g-2 mb-3">
                            <div class="col-6" id="tableWrap">
                                <input type="text" name="table_no" class="form-control form-control-sm"
                                       placeholder="Table No" value="{{ old('table_no') }}">
                            </div>
                            <div class="col-6">
                                <input type="text" name="customer_name" class="form-control form-control-sm"
                                       placeholder="Customer (optional)" value="{{ old('customer_name') }}">
                            </div>
                            <div class="col-6">
                                <input type="text" name="phone" class="form-control form-control-sm"
                                       placeholder="Phone (optional)" value="{{ old('phone') }}">
                            </div>
                            <div class="col-12 d-none" id="addressWrap">
                                <textarea name="address" rows="2" class="form-control form-control-sm"
                                          placeholder="Delivery address">{{ old('address') }}</textarea>
                            </div>
                        </div>

                        {{-- Items --}}
                        <div class="pos-bill-items">
                            <table class="table table-sm mb-0 align-middle" data-ar-no-stack>
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Qty</th>
                                        <th class="text-end">Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody id="posBillBody">
                                    <tr id="posEmptyRow">
                                        <td colspan="4" class="text-center pos-empty py-4">
                                            Click a food item to add it to the bill
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Charges --}}
                        <p class="section-title">Charges</p>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="filter-label" for="discountValue">Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" name="discount_value"
                                           id="discountValue" class="form-control" value="0" oninput="posCalc()">
                                    <select name="discount_type" id="discountType" class="form-select"
                                            onchange="posCalc()" style="max-width:72px">
                                        <option value="flat">৳</option>
                                        <option value="percent">%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <label class="filter-label" for="taxRate">VAT %</label>
                                <input type="number" step="0.01" min="0" max="100" name="tax_rate"
                                       id="taxRate" class="form-control form-control-sm" value="5" oninput="posCalc()">
                            </div>
                            <div class="col-3">
                                <label class="filter-label" for="serviceRate">Service %</label>
                                <input type="number" step="0.01" min="0" max="100" name="service_charge_rate"
                                       id="serviceRate" class="form-control form-control-sm" value="10" oninput="posCalc()">
                            </div>
                        </div>

                        {{-- Totals --}}
                        <div class="pos-sum"><span>Subtotal</span><span id="sumSubtotal">৳0.00</span></div>
                        <div class="pos-sum is-discount"><span>Discount</span><span id="sumDiscount">-৳0.00</span></div>
                        <div class="pos-sum"><span>VAT</span><span id="sumTax">৳0.00</span></div>
                        <div class="pos-sum"><span>Service charge</span><span id="sumService">৳0.00</span></div>
                        <div class="pos-total"><span>Total</span><span id="sumTotal">৳0.00</span></div>

                        <hr class="hr-soft">

                        {{-- Payment --}}
                        <p class="section-title">Payment</p>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="filter-label" for="paymentMethod">Method</label>
                                <select name="payment_method" id="paymentMethod" class="form-select form-select-sm"
                                        onchange="posCalc()">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>
                            <div class="col-6" id="paidWrap">
                                <label class="filter-label" for="paidAmount">Cash received</label>
                                <input type="number" step="0.01" min="0" name="paid_amount"
                                       id="paidAmount" class="form-control form-control-sm" value="0" oninput="posCalc()">
                            </div>
                            <div class="col-12">
                                <div class="pos-change fw-semibold text-success" id="changeWrap">
                                    <span>Change</span><span id="sumChange">৳0.00</span>
                                </div>
                            </div>
                        </div>

                        <textarea name="note" rows="1" class="form-control form-control-sm mt-3"
                                  placeholder="Note (optional)">{{ old('note') }}</textarea>

                        {{-- Hidden item inputs injected here on submit --}}
                        <div id="posHiddenItems"></div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary btn-lg" id="posSubmitBtn" disabled>
                                <i class="feather-check-circle"></i> Complete Sale
                            </button>
                            <button type="button" class="btn btn-soft-danger btn-sm" onclick="posClear()">
                                <i class="feather-trash-2"></i> Clear Bill
                            </button>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // ================= POS cart state =================
    const cart = {}; // { foodId: {id, name, price, stock, qty} }

    function money(n) { return '৳' + Number(n).toFixed(2); }

    function posAdd(el) {
        const id    = el.dataset.id;
        const stock = parseInt(el.dataset.stock, 10);
        if (cart[id]) {
            if (cart[id].qty >= stock) { return posToast('Only ' + stock + ' in stock'); }
            cart[id].qty++;
        } else {
            cart[id] = {
                id: id,
                name: el.dataset.foodName,
                price: parseFloat(el.dataset.price),
                stock: stock,
                qty: 1
            };
        }
        posRender();
    }

    function posChange(id, delta) {
        if (!cart[id]) return;
        const next = cart[id].qty + delta;
        if (next <= 0) { delete cart[id]; }
        else if (next > cart[id].stock) { return posToast('Only ' + cart[id].stock + ' in stock'); }
        else { cart[id].qty = next; }
        posRender();
    }

    function posRemove(id) { delete cart[id]; posRender(); }

    function posRender() {
        const body = document.getElementById('posBillBody');
        const ids  = Object.keys(cart);
        body.innerHTML = '';

        if (ids.length === 0) {
            body.innerHTML = '<tr id="posEmptyRow"><td colspan="4" class="text-center pos-empty py-4">Click a food item to add it to the bill</td></tr>';
        } else {
            ids.forEach(id => {
                const it = cart[id];
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>
                        <div class="fw-semibold small text-truncate" style="max-width:120px">${it.name}</div>
                        <div class="text-muted" style="font-size:11px">${money(it.price)}</div>
                    </td>
                    <td class="text-nowrap">
                        <button type="button" class="btn btn-outline-secondary qty-btn" onclick="posChange('${id}',-1)">−</button>
                        <span class="mx-1">${it.qty}</span>
                        <button type="button" class="btn btn-outline-secondary qty-btn" onclick="posChange('${id}',1)">+</button>
                    </td>
                    <td class="text-end small fw-semibold">${money(it.price * it.qty)}</td>
                    <td class="text-end">
                        <button type="button" class="btn btn-sm text-danger p-0" onclick="posRemove('${id}')">✕</button>
                    </td>`;
                body.appendChild(row);
            });
        }
        posCalc();
    }

    function posCalc() {
        let subtotal = 0;
        Object.values(cart).forEach(it => subtotal += it.price * it.qty);

        const dType = document.getElementById('discountType').value;
        const dVal  = parseFloat(document.getElementById('discountValue').value) || 0;
        let discount = dType === 'percent' ? subtotal * Math.min(dVal, 100) / 100 : Math.min(dVal, subtotal);
        discount = Math.max(0, discount);

        const taxable = Math.max(0, subtotal - discount);
        const taxRate = parseFloat(document.getElementById('taxRate').value) || 0;
        const scRate  = parseFloat(document.getElementById('serviceRate').value) || 0;
        const tax     = taxable * taxRate / 100;
        const service = taxable * scRate / 100;
        const total   = taxable + tax + service;

        document.getElementById('sumSubtotal').textContent = money(subtotal);
        document.getElementById('sumDiscount').textContent = '-' + money(discount);
        document.getElementById('sumTax').textContent      = money(tax);
        document.getElementById('sumService').textContent  = money(service);
        document.getElementById('sumTotal').textContent    = money(total);

        // Change (cash only)
        const method = document.getElementById('paymentMethod').value;
        const paidWrap = document.getElementById('paidWrap');
        const changeWrap = document.getElementById('changeWrap');
        if (method === 'cash') {
            paidWrap.classList.remove('d-none');
            changeWrap.classList.remove('d-none');
            const paid = parseFloat(document.getElementById('paidAmount').value) || 0;
            const change = paid - total;
            document.getElementById('sumChange').textContent = money(change >= 0 ? change : 0);
            changeWrap.classList.toggle('text-danger', change < 0);
            changeWrap.classList.toggle('text-success', change >= 0);
        } else {
            paidWrap.classList.add('d-none');
            changeWrap.classList.add('d-none');
        }

        document.getElementById('posSubmitBtn').disabled = Object.keys(cart).length === 0;
    }

    function posClear() {
        Object.keys(cart).forEach(k => delete cart[k]);
        posRender();
    }

    // Order type switching
    function posSetType(btn) {
        document.querySelectorAll('.order-type-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        const type = btn.dataset.type;
        document.getElementById('orderType').value = type;
        document.getElementById('tableWrap').classList.toggle('d-none', type !== 'dine_in');
        document.getElementById('addressWrap').classList.toggle('d-none', type !== 'delivery');
    }

    // Search + category filter
    document.getElementById('posSearch').addEventListener('input', posFilter);
    document.getElementById('posCategory').addEventListener('change', posFilter);
    function posFilter() {
        const q   = document.getElementById('posSearch').value.toLowerCase().trim();
        const cat = document.getElementById('posCategory').value;
        document.querySelectorAll('.pos-food-col').forEach(col => {
            const matchName = col.dataset.name.includes(q);
            const matchCat  = !cat || col.dataset.category === cat;
            col.style.display = (matchName && matchCat) ? '' : 'none';
        });
    }

    // Build hidden inputs right before submit
    document.getElementById('posForm').addEventListener('submit', function (e) {
        const wrap = document.getElementById('posHiddenItems');
        wrap.innerHTML = '';
        let i = 0;
        Object.values(cart).forEach(it => {
            wrap.insertAdjacentHTML('beforeend',
                `<input type="hidden" name="items[${i}][food_id]" value="${it.id}">
                 <input type="hidden" name="items[${i}][quantity]" value="${it.qty}">`);
            i++;
        });
        if (i === 0) { e.preventDefault(); }
    });

    function posToast(msg) {
        if (window.Swal) {
            Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: msg, showConfirmButton: false, timer: 1500 });
        }
    }

    posCalc();
</script>

@endsection
