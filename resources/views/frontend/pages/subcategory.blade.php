{{--
    LEGACY PAGE — kept, not reachable.

    `category.show` (/category/{id}) now redirects into /menu with the category
    filter pre-applied, so MenuController never renders this view any more. It
    is left in place, retheme only, in case the old drill-down route is ever
    wired back up.

    Expects: $category (with ->subcategories)
--}}
@extends('frontend.master')

@section('title', $category->name ?? 'Category')

@push('styles')
<style>
    .legacy-page .sf-page-head { margin-bottom: 26px; }

    .legacy-page .sf-page-head h2 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .legacy-page .sf-page-head p {
        color: var(--sf-muted);
        margin-bottom: 0;
    }

    /* IMAGE CONTAINER */
    .image-box {
        height: 180px;
        overflow: hidden;
        border-radius: var(--sf-radius-sm);
        background: rgba(0, 0, 0, .35);
    }

    .image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-box i { color: rgba(255, 255, 255, .3); }

    /* CLICKABLE CARD EFFECT */
    .hover-card {
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-6px);
        border-color: var(--sf-accent) !important;
        box-shadow: var(--sf-shadow-lg);
    }

    .hover-card h5 {
        color: var(--sf-ink);
        font-size: 16px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .hover-card:hover h5 { color: var(--sf-accent); }

    .card-meta {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        color: var(--sf-faint);
        font-size: 12.5px;
    }

    .card-meta i { color: var(--sf-accent); }

    .food-note {
        padding: 18px 22px;
        border-radius: var(--sf-radius-sm);
        background: rgba(29, 191, 115, .12);
        border: 1px solid rgba(29, 191, 115, .35);
        color: rgba(255, 255, 255, .85);
        text-align: center;
        font-size: 14.5px;
        line-height: 1.65;
    }

    .legacy-empty {
        text-align: center;
        padding: 54px 22px;
        border-radius: var(--sf-radius);
        background: var(--sf-glass);
        border: 1px dashed var(--sf-glass-line);
    }

    .legacy-empty-icon {
        width: 64px;
        height: 64px;
        margin: 0 auto 16px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        color: rgba(0, 0, 0, .85);
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        box-shadow: 0 12px 26px rgba(0, 0, 0, .45);
    }

    .legacy-empty h5 { color: var(--sf-ink); font-weight: 700; margin-bottom: 6px; }
    .legacy-empty p { color: var(--sf-muted); margin-bottom: 20px; }

    /* The card carries `.h-100`, which only resolves if every ancestor between
       it and the stretched column has a height — otherwise the cards in a row
       come out ragged. */
    .details-link,
    .details-link:hover,
    .details-link:focus {
        display: block;
        height: 100%;
        text-decoration: none;
        color: inherit;
    }
</style>
@endpush

@section('content')

<section class="legacy-category-section legacy-page layout_padding">
    <div class="container">

        {{-- PAGE TITLE --}}
        <div class="sf-page-head text-center">
            <h2>{{ $category->name }}</h2>
            <p>Pick a section to see what the kitchen is cooking.</p>
        </div>

        {{-- NOTE --}}
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10">
                <div class="food-note">
                    Each section is grouped by what it is, so you can find your favourite dish
                    without scrolling the whole menu.
                </div>
            </div>
        </div>

        {{-- SUBCATEGORY LIST --}}
        <div class="row">

            @forelse ($category->subcategories as $subcategory)

                <div class="col-lg-3 col-md-4 col-sm-6 mb-4">

                    {{-- CLICKABLE CARD --}}
                    <a href="{{ route('menu.foods', $subcategory->id) }}" class="details-link">

                        <div class="glass-card h-100 p-3 text-center hover-card">

                            {{-- IMAGE --}}
                            <div class="image-box d-flex align-items-center justify-content-center mb-3">
                                @if ($subcategory->image)
                                    <img src="{{ asset('storage/'.$subcategory->image) }}"
                                         alt="{{ $subcategory->name }}">
                                @else
                                    <i class="fa fa-cutlery fa-3x" aria-hidden="true"></i>
                                @endif
                            </div>

                            {{-- SUBCATEGORY NAME --}}
                            <h5>{{ $subcategory->name }}</h5>

                            {{-- CATEGORY NAME --}}
                            <div class="card-meta">
                                <i class="fa fa-tag" aria-hidden="true"></i>
                                <span>{{ $category->name }}</span>
                            </div>

                        </div>

                    </a>
                </div>

            @empty

                <div class="col-12">
                    <div class="legacy-empty">
                        <div class="legacy-empty-icon">
                            <i class="fa fa-th-large" aria-hidden="true"></i>
                        </div>

                        <h5>Nothing in this category yet</h5>
                        <p>No sections have been added here. The rest of the menu is still open.</p>

                        <a href="{{ route('menu.index') }}" class="btn btn-warning">
                            Show the full menu
                        </a>
                    </div>
                </div>

            @endforelse

        </div>

    </div>
</section>

@endsection
