@php
    /** @var \App\Models\Coupon|null $coupon */
    $coupon = $coupon ?? null;

    $val = fn ($field, $default = null) => old($field, $coupon->{$field} ?? $default);

    $dt = function ($field) use ($coupon) {
        $value = old($field);

        if ($value) {
            return $value;
        }

        return $coupon && $coupon->{$field}
            ? $coupon->{$field}->format('Y-m-d\TH:i')
            : '';
    };

    // `status` is cast to boolean, so (string) false is '' and matches neither
    // option — the select would fall back to its first entry and quietly
    // reactivate a paused coupon. Normalise to 0/1 before comparing.
    $statusValue = (int) old('status', $coupon->status ?? 1);
    $typeValue   = old('type', $coupon->type ?? 'percent');
@endphp

<p class="section-title">Identity</p>

<div class="row">
    <div class="col-md-7 mb-3">
        <label class="form-label" for="coupon-code">Coupon Code <span class="text-danger">*</span></label>
        <input type="text" id="coupon-code" name="code"
               class="form-control text-uppercase @error('code') is-invalid @enderror"
               placeholder="e.g. EID20" value="{{ $val('code') }}" required>
        @error('code')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Customers type this at checkout. Letters, numbers, - and _ only.</small>
        @enderror
    </div>

    <div class="col-md-5 mb-3">
        <label class="form-label" for="coupon-status">Status</label>
        <select name="status" id="coupon-status" class="form-select @error('status') is-invalid @enderror">
            <option value="1" {{ $statusValue === 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ $statusValue === 0 ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Inactive codes are rejected at checkout.</small>
        @enderror
    </div>

    <div class="col-12 mb-3">
        <label class="form-label" for="coupon-description">Description</label>
        <input type="text" id="coupon-description" name="description"
               class="form-control @error('description') is-invalid @enderror"
               placeholder="e.g. Eid special offer" value="{{ $val('description') }}">
        @error('description')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Internal note. Shown under the code in this list.</small>
        @enderror
    </div>
</div>

<hr class="hr-soft">

<p class="section-title">Discount</p>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="couponType">Discount Type <span class="text-danger">*</span></label>
        <select name="type" id="couponType" class="form-select @error('type') is-invalid @enderror" required>
            <option value="percent" {{ $typeValue === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
            <option value="fixed"   {{ $typeValue === 'fixed' ? 'selected' : '' }}>Fixed amount (৳)</option>
        </select>
        @error('type')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="coupon-value">Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" id="coupon-value" name="value"
               class="form-control @error('value') is-invalid @enderror"
               placeholder="20" value="{{ $val('value') }}" required>
        @error('value')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @enderror
        <small class="text-muted d-block mt-1">
            On a percentage coupon this is a percent of the subtotal, so 20 means 20% off and
            the most you can enter is 100. On a fixed coupon it is taka, so 100 means ৳100 off.
        </small>
        <small class="text-muted d-block" id="valueHint"></small>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="coupon-min-order">Minimum Order Amount</label>
        <input type="number" step="0.01" min="0" id="coupon-min-order" name="min_order_amount"
               class="form-control @error('min_order_amount') is-invalid @enderror"
               placeholder="e.g. 500" value="{{ $val('min_order_amount') }}">
        @error('min_order_amount')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">The subtotal the cart must reach before the code works. Empty means no minimum.</small>
        @enderror
    </div>

    <div class="col-md-6 mb-3" id="maxDiscountWrap">
        <label class="form-label" for="coupon-max-discount">Maximum Discount Cap</label>
        <input type="number" step="0.01" min="0" id="coupon-max-discount" name="max_discount"
               class="form-control @error('max_discount') is-invalid @enderror"
               placeholder="e.g. 200" value="{{ $val('max_discount') }}">
        @error('max_discount')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">
                Caps what a percentage coupon can take off: 20% with a ৳200 cap never gives more
                than ৳200. Ignored on fixed coupons, so it is cleared when you save one.
            </small>
        @enderror
    </div>
</div>

<hr class="hr-soft">

<p class="section-title">Schedule</p>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="coupon-starts-at">Starts At</label>
        <input type="datetime-local" id="coupon-starts-at" name="starts_at"
               class="form-control @error('starts_at') is-invalid @enderror" value="{{ $dt('starts_at') }}">
        @error('starts_at')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Empty means it works immediately.</small>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="coupon-expires-at">Expires At</label>
        <input type="datetime-local" id="coupon-expires-at" name="expires_at"
               class="form-control @error('expires_at') is-invalid @enderror" value="{{ $dt('expires_at') }}">
        @error('expires_at')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Empty means it never expires.</small>
        @enderror
    </div>
</div>

<hr class="hr-soft">

<p class="section-title">Limits</p>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="coupon-usage-limit">Total Usage Limit</label>
        <input type="number" min="1" id="coupon-usage-limit" name="usage_limit"
               class="form-control @error('usage_limit') is-invalid @enderror"
               placeholder="e.g. 100" value="{{ $val('usage_limit') }}">
        @error('usage_limit')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">How many redemptions in total. Empty means unlimited.</small>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="coupon-per-user-limit">Per Customer Limit</label>
        <input type="number" min="1" id="coupon-per-user-limit" name="per_user_limit"
               class="form-control @error('per_user_limit') is-invalid @enderror"
               placeholder="e.g. 1" value="{{ $val('per_user_limit') }}">
        @error('per_user_limit')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">How many times one customer may use it. Empty means unlimited.</small>
        @enderror
    </div>
</div>

<script>
    (function () {
        const type = document.getElementById('couponType');
        const wrap = document.getElementById('maxDiscountWrap');
        const hint = document.getElementById('valueHint');

        if (!type) {
            return;
        }

        function sync() {
            const isPercent = type.value === 'percent';

            // The cap is meaningless on a fixed-amount coupon
            if (wrap) {
                wrap.style.display = isPercent ? '' : 'none';
            }

            if (hint) {
                hint.textContent = isPercent ? '20 means 20% off' : '100 means ৳100 off';
            }
        }

        type.addEventListener('change', sync);
        sync();
    })();
</script>
