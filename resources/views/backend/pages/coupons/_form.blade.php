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
@endphp

<div class="mb-3">
    <label class="form-label">Coupon Code <span class="text-danger">*</span></label>
    <input type="text" name="code" class="form-control text-uppercase"
           placeholder="e.g. EID20" value="{{ $val('code') }}" required>
    <small class="text-muted">Customers type this at checkout. Letters, numbers, - and _ only.</small>
</div>

<div class="mb-3">
    <label class="form-label">Description</label>
    <input type="text" name="description" class="form-control"
           placeholder="e.g. Eid special offer" value="{{ $val('description') }}">
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Discount Type <span class="text-danger">*</span></label>
        <select name="type" id="couponType" class="form-control" required>
            <option value="percent" {{ $val('type') === 'percent' ? 'selected' : '' }}>Percentage (%)</option>
            <option value="fixed"   {{ $val('type') === 'fixed' ? 'selected' : '' }}>Fixed amount (৳)</option>
        </select>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Value <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" name="value" class="form-control"
               placeholder="20" value="{{ $val('value') }}" required>
        <small class="text-muted" id="valueHint"></small>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Minimum Order Amount</label>
        <input type="number" step="0.01" min="0" name="min_order_amount" class="form-control"
               placeholder="e.g. 500" value="{{ $val('min_order_amount') }}">
        <small class="text-muted">Leave empty for no minimum.</small>
    </div>

    <div class="col-md-6 mb-3" id="maxDiscountWrap">
        <label class="form-label">Maximum Discount Cap</label>
        <input type="number" step="0.01" min="0" name="max_discount" class="form-control"
               placeholder="e.g. 200" value="{{ $val('max_discount') }}">
        <small class="text-muted">Caps a percentage coupon, e.g. 20% but never more than ৳200.</small>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Starts At</label>
        <input type="datetime-local" name="starts_at" class="form-control" value="{{ $dt('starts_at') }}">
        <small class="text-muted">Empty = active immediately.</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Expires At</label>
        <input type="datetime-local" name="expires_at" class="form-control" value="{{ $dt('expires_at') }}">
        <small class="text-muted">Empty = never expires.</small>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Total Usage Limit</label>
        <input type="number" min="1" name="usage_limit" class="form-control"
               placeholder="e.g. 100" value="{{ $val('usage_limit') }}">
        <small class="text-muted">How many times in total. Empty = unlimited.</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Per Customer Limit</label>
        <input type="number" min="1" name="per_user_limit" class="form-control"
               placeholder="e.g. 1" value="{{ $val('per_user_limit') }}">
        <small class="text-muted">How many times one customer may use it. Empty = unlimited.</small>
    </div>
</div>

<div class="mb-3">
    <label class="form-label">Status</label>
    <select name="status" class="form-control">
        <option value="1" {{ (string) $val('status', 1) === '1' ? 'selected' : '' }}>Active</option>
        <option value="0" {{ (string) $val('status', 1) === '0' ? 'selected' : '' }}>Inactive</option>
    </select>
</div>

<script>
    (function () {
        const type = document.getElementById('couponType');
        const wrap = document.getElementById('maxDiscountWrap');
        const hint = document.getElementById('valueHint');

        function sync() {
            const isPercent = type.value === 'percent';

            // The cap is meaningless on a fixed-amount coupon
            wrap.style.display = isPercent ? '' : 'none';
            hint.textContent = isPercent ? '20 means 20% off' : '100 means ৳100 off';
        }

        type.addEventListener('change', sync);
        sync();
    })();
</script>
