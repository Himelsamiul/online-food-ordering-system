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
</style>

<div class="container py-5">

    {{-- FOOD SUBCATEGORY NOTE --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="glass-card p-4 text-center">
                <h5 class="fw-bold mb-2 text-success">
                    Explore Our Menu
                </h5>

                <p class="fw-bold text-success mb-0">
                    Choose from a variety of food subcategories to find your favorite dishes quickly and easily.
                    Each section is carefully organized to help you explore different flavors and meal options
                    with a smooth and enjoyable browsing experience.
                </p>
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
                <p class="text-muted">No subcategories found</p>
            </div>

        @endforelse

    </div>

</div>

@endsection
