{{--
    Just the cards. Rendered both on first page load and on every AJAX
    filter/load-more request, so markup never drifts between the two.
--}}
@forelse ($foods as $food)
    @include('frontend.partials.food-card', ['food' => $food])
@empty
    <div class="col-12">
        <div class="menu-empty">
            <i class="fa fa-search fa-3x mb-3 opacity-50"></i>
            <h5 class="fw-bold mb-1">No dish matched your filters</h5>
            <p class="mb-0">Try a different keyword, or widen the price range.</p>
        </div>
    </div>
@endforelse
