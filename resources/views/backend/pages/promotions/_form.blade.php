@php
    /** @var \App\Models\Promotion|null $promotion */
    $promotion = $promotion ?? null;

    $val = fn ($field, $default = null) => old($field, $promotion->{$field} ?? $default);

    $dt = function ($field) use ($promotion) {
        $value = old($field);

        if ($value) {
            return $value;
        }

        return $promotion && $promotion->{$field}
            ? $promotion->{$field}->format('Y-m-d\TH:i')
            : '';
    };
@endphp

<div class="mb-3">
    <label class="form-label">Title <span class="text-danger">*</span></label>
    <input type="text" name="title" class="form-control"
           placeholder="e.g. Eid Special Offer" value="{{ $val('title') }}" required>
</div>

<div class="mb-3">
    <label class="form-label">Subtitle</label>
    <input type="text" name="subtitle" class="form-control"
           placeholder="e.g. Order now and save big this Eid" value="{{ $val('subtitle') }}">
</div>

<div class="mb-3">
    <label class="form-label">Banner Image</label>

    @if($promotion && $promotion->image)
        <div class="mb-2">
            <img src="{{ asset('storage/'.$promotion->image) }}" width="180" class="rounded border">
        </div>
    @endif

    <input type="file" name="image" class="form-control">
    <small class="text-muted">Wide image works best (about 1200×400).</small>
</div>

<div class="mb-3">
    <label class="form-label">Attach Coupon</label>
    <select name="coupon_id" class="form-control">
        <option value="">— none, just a banner —</option>
        @foreach($coupons as $c)
            <option value="{{ $c->id }}" {{ (string) $val('coupon_id') === (string) $c->id ? 'selected' : '' }}>
                {{ $c->code }} ({{ $c->offer_label }})
            </option>
        @endforeach
    </select>
    <small class="text-muted">
        If set, the code and its terms are shown on the banner and customers can copy it.
    </small>
</div>

<div class="mb-3">
    <label class="form-label">Link URL</label>
    <input type="url" name="link_url" class="form-control"
           placeholder="https://..." value="{{ $val('link_url') }}">
    <small class="text-muted">Optional. Where the banner button sends the customer.</small>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Starts At</label>
        <input type="datetime-local" name="starts_at" class="form-control" value="{{ $dt('starts_at') }}">
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Ends At</label>
        <input type="datetime-local" name="ends_at" class="form-control" value="{{ $dt('ends_at') }}">
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label">Sort Order</label>
        <input type="number" min="0" name="sort_order" class="form-control"
               value="{{ $val('sort_order', 0) }}">
        <small class="text-muted">Lower number shows first.</small>
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-control">
            <option value="1" {{ (string) $val('status', 1) === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ (string) $val('status', 1) === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
</div>
