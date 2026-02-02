@extends('frontend.master')

@section('content')

<style>
    /* IMAGE CONTAINER */
    .image-box {
        height: 180px;
        overflow: hidden;
        border-radius: 14px;
        background: rgba(255,255,255,0.05);
    }

    .image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* CLICKABLE CARD EFFECT */
    .hover-card {
        transition: all 0.25s ease;
        cursor: pointer;
    }

    .hover-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.25);
    }

    /* FOOD NOTE (SAME STYLE AS FOOD PAGE) */
    .food-note {
        background: linear-gradient(
            135deg,
            rgba(25,135,84,0.25),
            rgba(25,135,84,0.05)
        );
        border: 1px solid rgba(25,135,84,0.4);
        border-radius: 16px;
        padding: 18px 22px;
        text-align: center;
        color: #1dbf73;
        font-weight: 700;
        animation: fadeSlide 0.8s ease;
        box-shadow: 0 8px 25px rgba(25,135,84,0.25);
    }

    .food-note:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(25,135,84,0.35);
    }

    @keyframes fadeSlide {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="container py-5">

    {{-- PAGE TITLE --}}
    <div class="text-center mb-3">
        <h3 class="fw-bold text-success">
            {{ $category->name }}
        </h3>
    </div>

    {{-- GREEN NOTE --}}
    <div class="row justify-content-center mb-5">
        <div class="col-lg-10">
            <div class="food-note">
                🍽️ Select a food subcategory below to explore delicious items.
                Each subcategory is carefully organized to help you find your favorite meals quickly and easily.
            </div>
        </div>
    </div>

    {{-- SUBCATEGORY LIST --}}
    <div class="row">

        @forelse ($category->subcategories as $subcategory)

            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">

                {{-- CLICKABLE CARD --}}
                <a href="{{ route('menu.foods', $subcategory->id) }}"
                   class="text-decoration-none text-light">

                    <div class="glass-card h-100 p-3 text-center hover-card">

                        {{-- IMAGE --}}
                        <div class="image-box d-flex align-items-center justify-content-center mb-3">
                            @if ($subcategory->image)
                                <img
                                    src="{{ asset('storage/'.$subcategory->image) }}"
                                    alt="{{ $subcategory->name }}"
                                >
                            @else
                                <i class="fa fa-image fa-3x text-light opacity-75"></i>
                            @endif
                        </div>

                        {{-- SUBCATEGORY NAME --}}
                        <h5 class="fw-bold mb-1">
                            {{ $subcategory->name }}
                        </h5>

                        {{-- CATEGORY NAME --}}
                        <div class="d-flex justify-content-center align-items-center gap-2 text-muted small">
                            <i class="fa fa-tag"></i>
                            <span>{{ $category->name }}</span>
                        </div>

                    </div>

                </a>
            </div>

        @empty

            <div class="col-12 text-center">
                <div class="glass-card p-5">
                    <h5 class="fw-bold text-muted">
                        No subcategories found
                    </h5>
                    <p class="text-muted">
                        Please check back later for more options.
                    </p>
                </div>
            </div>

        @endforelse

    </div>

</div>

@endsection
