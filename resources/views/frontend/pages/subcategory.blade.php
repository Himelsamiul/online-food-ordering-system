@extends('frontend.master')

@section('content')

<div class="container py-5">

    <div class="row">

        @forelse ($category->subcategories as $subcategory)

            <div class="col-md-4 mb-4">
                <div class="glass-card h-100 p-3 text-center">

                    {{-- IMAGE AREA (FUTURE READY) --}}
                    <div
                        class="d-flex align-items-center justify-content-center mb-3"
                        style="height: 160px;"
                    >
                        {{-- FUTURE: image will come here --}}
                        <i class="fa fa-image fa-3x text-light opacity-75"></i>
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
