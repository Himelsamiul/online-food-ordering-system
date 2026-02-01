@extends('frontend.master')

@section('content')

<style>
    /* IMAGE CONTAINER */
    .image-box {
        height: 180px;              /* লম্বা ঠিক */
        overflow: hidden;           /* overflow hide */
        border-radius: 14px;
        background: rgba(255,255,255,0.05);
    }

    .image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;          /* box fill করবে */
    }
</style>

<div class="container py-5">

    <div class="row">

        @forelse ($category->subcategories as $subcategory)

            {{-- CARD WIDTH SLIM --}}
            <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                <div class="glass-card h-100 p-3 text-center">

                    {{-- IMAGE AREA --}}
                    <div class="image-box d-flex align-items-center justify-content-center mb-3">
                        @if ($subcategory->image)
                            <img
                                src="{{ asset('storage/'.$subcategory->image) }}"
                                alt="{{ $subcategory->name }}"
                            >
                        @else
                            {{-- DEFAULT ICON --}}
                            <i class="fa fa-image fa-3x text-light opacity-75"></i>
                        @endif
                    </div>

                    {{-- SUBCATEGORY NAME --}}
                    <h5 class="fw-bold mb-1">
                        {{ $subcategory->name }}
                    </h5>

                    {{-- MAIN CATEGORY NAME (ICONIC) --}}
                    <div class="d-flex justify-content-center align-items-center gap-2 text-muted small">
                        <i class="fa fa-tag"></i>
                        <span>{{ $category->name }}</span>
                    </div>

                </div>
            </div>

        @empty

            <div class="col-12 text-center">
                <p class="text-muted">No subcategories found</p>
            </div>

        @endforelse

    </div>

</div>

@endsection
