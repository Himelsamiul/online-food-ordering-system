@php
    /**
     * "Rate what you ordered" — shown on a delivered order.
     *
     * Expects $order and $reviewableItems (App\Services\ReviewService).
     * Renders nothing at all when there is nothing left to rate, so the caller
     * does not have to guard the include.
     */
@endphp

@if ($reviewableItems->isNotEmpty())
    <div class="review-prompt">
        <div class="review-prompt-head">
            <i class="fa fa-star" aria-hidden="true"></i>
            <div>
                <strong>How was your food?</strong>
                <small>Your rating helps other customers decide.</small>
            </div>
        </div>

        @foreach ($reviewableItems as $item)
            <form method="POST" action="{{ route('review.store', $order->id) }}" class="review-form">
                @csrf
                <input type="hidden" name="food_id" value="{{ $item->food_id }}">

                <div class="review-form-item">
                    @if ($item->food?->image)
                        <img src="{{ asset('storage/' . $item->food->image) }}" alt="">
                    @else
                        <span class="review-form-thumb"><i class="fa fa-cutlery" aria-hidden="true"></i></span>
                    @endif
                    <strong>{{ $item->food?->name ?? 'Item' }}</strong>
                </div>

                {{-- Radio inputs reversed in the DOM so the CSS sibling selector
                     can light up every star to the LEFT of the hovered one. --}}
                <div class="review-stars" role="radiogroup" aria-label="Rating for {{ $item->food?->name }}">
                    @for ($star = 5; $star >= 1; $star--)
                        <input type="radio" name="rating" value="{{ $star }}"
                               id="r{{ $order->id }}_{{ $item->food_id }}_{{ $star }}" required>
                        <label for="r{{ $order->id }}_{{ $item->food_id }}_{{ $star }}"
                               title="{{ $star }} star{{ $star > 1 ? 's' : '' }}">
                            <i class="fa fa-star" aria-hidden="true"></i>
                            <span class="sr-only">{{ $star }} stars</span>
                        </label>
                    @endfor
                </div>

                <textarea name="comment" rows="2" maxlength="1000"
                          placeholder="Anything you want to say about it? (optional)"></textarea>

                <button type="submit" class="sf-btn sf-btn-solid review-submit">Submit review</button>
            </form>
        @endforeach
    </div>
@endif
