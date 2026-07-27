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

    // `status` is cast to boolean, so (string) false is '' and matches neither
    // option — the select would fall back to its first entry and quietly
    // republish a paused banner. Normalise to 0/1 before comparing.
    $statusValue   = (int) old('status', $promotion->status ?? 1);
    $selectedCoupon = (string) ($val('coupon_id') ?? '');
@endphp

<p class="section-title">Content</p>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-title">Title <span class="text-danger">*</span></label>
        <input type="text" id="promotion-title" name="title"
               class="form-control @error('title') is-invalid @enderror"
               placeholder="e.g. Eid Special Offer" value="{{ $val('title') }}" required>
        @error('title')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">The headline printed over the banner.</small>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-subtitle">Subtitle</label>
        <input type="text" id="promotion-subtitle" name="subtitle"
               class="form-control @error('subtitle') is-invalid @enderror"
               placeholder="e.g. Order now and save big this Eid" value="{{ $val('subtitle') }}">
        @error('subtitle')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">One supporting line under the headline. Optional.</small>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-image">Banner Image</label>

        @if ($promotion && $promotion->image)
            <div class="mb-2">
                <img src="{{ asset('storage/' . $promotion->image) }}"
                     alt="{{ $promotion->title }}" class="banner-preview">
            </div>
        @endif

        <input type="file" id="promotion-image" name="image"
               class="form-control @error('image') is-invalid @enderror" accept="image/*">
        @error('image')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">
                A wide image works best, about 1200&times;400. JPG, PNG or WEBP up to 2 MB.
                @if ($promotion && $promotion->image) Leave empty to keep the current one. @endif
            </small>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-link">Link URL</label>
        <input type="url" id="promotion-link" name="link_url"
               class="form-control @error('link_url') is-invalid @enderror"
               placeholder="https://..." value="{{ $val('link_url') }}">
        @error('link_url')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Where the banner button sends the customer. Must start with http:// or https://.</small>
        @enderror
    </div>
</div>

<hr class="hr-soft">

<p class="section-title">Offer</p>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-coupon">Attach Coupon</label>
        <select name="coupon_id" id="promotion-coupon" class="form-select @error('coupon_id') is-invalid @enderror">
            <option value="">None — just a banner</option>
            @foreach ($coupons as $c)
                <option value="{{ $c->id }}" {{ $selectedCoupon === (string) $c->id ? 'selected' : '' }}>
                    {{ $c->code }} ({{ $c->offer_label }})
                </option>
            @endforeach
        </select>
        @error('coupon_id')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">
                If set, the code and its terms are shown on the banner and customers can copy it.
                Only active coupons are listed.
            </small>
        @enderror

        @if ($promotion && $promotion->coupon_id && ! $coupons->contains('id', $promotion->coupon_id))
            <small class="text-danger d-block mt-1">
                The coupon currently attached to this banner is inactive, so it is not in this list.
                Saving now detaches it — reactivate the coupon first if you want to keep it.
            </small>
        @endif
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-sort">Sort Order</label>
        <input type="number" min="0" id="promotion-sort" name="sort_order"
               class="form-control @error('sort_order') is-invalid @enderror"
               value="{{ $val('sort_order', 0) }}">
        @error('sort_order')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Lower numbers show first on the storefront.</small>
        @enderror
    </div>
</div>

<hr class="hr-soft">

<p class="section-title">Schedule</p>

<div class="row">
    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-starts-at">Starts At</label>
        <input type="datetime-local" id="promotion-starts-at" name="starts_at"
               class="form-control @error('starts_at') is-invalid @enderror" value="{{ $dt('starts_at') }}">
        @error('starts_at')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Empty means it goes live as soon as it is active.</small>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-ends-at">Ends At</label>
        <input type="datetime-local" id="promotion-ends-at" name="ends_at"
               class="form-control @error('ends_at') is-invalid @enderror" value="{{ $dt('ends_at') }}">
        @error('ends_at')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">Empty means it runs until you turn it off.</small>
        @enderror
    </div>

    <div class="col-md-6 mb-3">
        <label class="form-label" for="promotion-status">Status</label>
        <select name="status" id="promotion-status" class="form-select @error('status') is-invalid @enderror">
            <option value="1" {{ $statusValue === 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ $statusValue === 0 ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')
            <small class="text-danger d-block mt-1">{{ $message }}</small>
        @else
            <small class="text-muted">An inactive banner never shows, whatever its schedule says.</small>
        @enderror
    </div>
</div>
