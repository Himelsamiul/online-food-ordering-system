@extends('backend.master')

@section('content')

<style>
    .pos-food-card {
        cursor: pointer;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        transition: all .15s ease;
        height: 100%;
    }
    .pos-food-card:hover { border-color: #3454d1; box-shadow: 0 4px 12px rgba(52,84,209,.15); transform: translateY(-2px); }
    .pos-food-card .price { color: #3454d1; font-weight: 700; }
    .pos-food-card .stock { font-size: 11px; }
    .pos-grid { max-height: calc(100vh - 250px); overflow-y: auto; }
    .pos-bill { position: sticky; top: 90px; }
    .pos-bill-items { max-height: 40vh; overflow-y: auto; }
    .qty-btn { width: 26px; height: 26px; line-height: 1; padding: 0; }
    .order-type-btn.active { background:#3454d1; color:#fff; border-color:#3454d1; }
    .pos-empty { color:#9ca3af; }
</style>

<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">🧾 POS — New Sale</h4>
        <a href="{{ route('admin.pos.sales') }}" class="btn btn-outline-primary btn-sm">
            <i class="feather-list"></i> Sales History
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-3">

        {{-- ================= LEFT : FOOD PICKER ================= --}}
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">

                    {{-- Search + category filter --}}
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <input type="text" id="posSearch" class="form-control"
                                   placeholder="🔍 Search food by name...">
                        </div>
                        <div class="col-md-6">
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
                        <div class="row g-2" id="posFoodGrid">
                            @forelse ($foods as $food)
                                @php
                                    $pct = $food->discount ?? 0;
                                    $final = $pct > 0 ? round($food->price - ($food->price * $pct / 100), 2) : (float) $food->price;
                                @endphp
                                <div class="col-6 col-md-4 pos-food-col"
                                     data-name="{{ strtolower($food->name) }}"
                                     data-category="{{ optional($food->subcategory->category)->id }}">
                                    <div class="pos-food-card p-2 text-center"
                                         data-id="{{ $food->id }}"
                                         data-food-name="{{ $food->name }}"
                                         data-price="{{ $final }}"
                                         data-stock="{{ $food->quantity }}"
                                         onclick="posAdd(this)">
                                        <div class="fw-semibold text-truncate small">{{ $food->name }}</div>
                                        <div class="price">৳{{ number_format($final, 2) }}</div>
                                        <div class="stock text-muted">Stock: {{ $food->quantity }}</div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12 text-center text-muted py-5">
                                    No in-stock foods available.
                                </div>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- ================= RIGHT : BILL ================= --}}
        <div class="col-lg-5">
            <form method="POST" action="{{ route('admin.pos.store') }}" id="posForm">
                @csrf
                <div class="card pos-bill">
                    <div class="card-body">

                        {{-- Order type --}}
                        <div class="btn-group w-100 mb-3" role="group">
                            <button type="button" class="btn btn-outline-primary order-type-btn active"
                                    data-type="dine_in" onclick="posSetType(this)">Dine-in</button>
                            <button type="button" class="btn btn-outline-primary order-type-btn"
                                    data-type="takeaway" onclick="posSetType(this)">Takeaway</button>
                            <button type="button" class="btn btn-outline-primary order-type-btn"
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
                        <div class="pos-bill-items border-top border-bottom py-2 mb-2">
                            <table class="table table-sm mb-0 align-middle">
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
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Discount</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" min="0" name="discount_value"
                                           id="discountValue" class="form-control" value="0" oninput="posCalc()">
                                    <select name="discount_type" id="discountType" class="form-select" onchange="posCalc()" style="max-width:70px">
                                        <option value="flat">৳</option>
                                        <option value="percent">%</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-3">
                                <label class="form-label small mb-1">VAT %</label>
                                <input type="number" step="0.01" min="0" max="100" name="tax_rate"
                                       id="taxRate" class="form-control form-control-sm" value="5" oninput="posCalc()">
                            </div>
                            <div class="col-3">
                                <label class="form-label small mb-1">Service %</label>
                                <input type="number" step="0.01" min="0" max="100" name="service_charge_rate"
                                       id="serviceRate" class="form-control form-control-sm" value="10" oninput="posCalc()">
                            </div>
                        </div>

                        {{-- Totals --}}
                        <div class="small">
                            <div class="d-flex justify-content-between"><span>Subtotal</span><span id="sumSubtotal">৳0.00</span></div>
                            <div class="d-flex justify-content-between text-danger"><span>Discount</span><span id="sumDiscount">-৳0.00</span></div>
                            <div class="d-flex justify-content-between"><span>VAT</span><span id="sumTax">৳0.00</span></div>
                            <div class="d-flex justify-content-between"><span>Service charge</span><span id="sumService">৳0.00</span></div>
                            <div class="d-flex justify-content-between fw-bold fs-5 border-top mt-1 pt-1">
                                <span>Total</span><span id="sumTotal">৳0.00</span>
                            </div>
                        </div>

                        {{-- Payment --}}
                        <div class="row g-2 mt-2">
                            <div class="col-6">
                                <label class="form-label small mb-1">Payment</label>
                                <select name="payment_method" id="paymentMethod" class="form-select form-select-sm" onchange="posCalc()">
                                    <option value="cash">Cash</option>
                                    <option value="card">Card</option>
                                </select>
                            </div>
                            <div class="col-6" id="paidWrap">
                                <label class="form-label small mb-1">Cash received</label>
                                <input type="number" step="0.01" min="0" name="paid_amount"
                                       id="paidAmount" class="form-control form-control-sm" value="0" oninput="posCalc()">
                            </div>
                            <div class="col-12 d-flex justify-content-between fw-semibold text-success" id="changeWrap">
                                <span>Change</span><span id="sumChange">৳0.00</span>
                            </div>
                        </div>

                        <textarea name="note" rows="1" class="form-control form-control-sm mt-2"
                                  placeholder="Note (optional)">{{ old('note') }}</textarea>

                        {{-- Hidden item inputs injected here on submit --}}
                        <div id="posHiddenItems"></div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-primary" id="posSubmitBtn" disabled>
                                ✅ Complete Sale
                            </button>
                            <button type="button" class="btn btn-outline-danger btn-sm" onclick="posClear()">
                                Clear Bill
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
