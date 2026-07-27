{{--
    Just the cards. Rendered both on first page load and on every AJAX
    filter/load-more request, so markup never drifts between the two.
--}}
@forelse ($foods as $food)
    @include('frontend.partials.food-card', ['food' => $food])
@empty
    <div class="col-12">
        <div class="menu-empty">
            <div class="menu-empty-icon">
                <i class="fa fa-search" aria-hidden="true"></i>
            </div>

            <h5>Nothing matched those filters</h5>
            <p>Try a shorter keyword, another category, or a wider price range.</p>

            {{-- The menu page catches this over JS and clears the filters in
                 place; without JS the link still lands on a clean menu. --}}
            <a href="{{ route('menu.index') }}" class="btn btn-warning js-reset-filters">
                Show the full menu
            </a>
        </div>
    </div>
@endforelse
