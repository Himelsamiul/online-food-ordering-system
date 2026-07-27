{{--
    Category cards. Expects $categories (with foods_count).
    Optional: $activeCategory (id) to highlight one, $compact (bool) for
    the tighter row used on the menu page.

    In compact mode these are filter controls, not links: the menu page's
    script reads data-id off .cat-card and calls preventDefault(), so the
    href stays inert on purpose.
--}}
@php
    $compact        = $compact ?? false;
    $activeCategory = (string) ($activeCategory ?? '');
    $totalItems     = $categories->sum('foods_count');
@endphp

<div class="cat-card-row {{ $compact ? 'is-compact' : '' }}">

    @php $allActive = $activeCategory === ''; @endphp

    <a href="{{ $compact ? 'javascript:void(0);' : route('menu.index') }}"
       class="cat-card cat-card-all {{ $allActive ? 'active' : '' }}"
       data-id=""
       title="Show every dish"
       @if ($allActive) aria-current="true" @endif>
        <div class="cat-card-img">
            <i class="fa fa-th-large" aria-hidden="true"></i>
        </div>
        <div class="cat-card-body">
            <h6>All Items</h6>
            <small>{{ $totalItems }} item{{ $totalItems == 1 ? '' : 's' }}</small>
        </div>
    </a>

    @foreach ($categories as $category)
        @php $isActive = $activeCategory === (string) $category->id; @endphp

        <a href="{{ $compact ? 'javascript:void(0);' : route('menu.index', ['category' => $category->id]) }}"
           class="cat-card {{ $isActive ? 'active' : '' }}"
           data-id="{{ $category->id }}"
           title="{{ $category->name }}"
           @if ($isActive) aria-current="true" @endif>

            <div class="cat-card-img">
                @if ($category->image)
                    <img src="{{ asset('storage/'.$category->image) }}"
                         alt="{{ $category->name }}" loading="lazy">
                @else
                    <i class="fa fa-cutlery" aria-hidden="true"></i>
                @endif
            </div>

            <div class="cat-card-body">
                <h6>{{ $category->name }}</h6>
                <small>{{ $category->foods_count }} item{{ $category->foods_count == 1 ? '' : 's' }}</small>
            </div>
        </a>
    @endforeach

</div>
