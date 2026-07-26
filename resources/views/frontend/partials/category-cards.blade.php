{{--
    Category cards. Expects $categories (with foods_count).
    Optional: $activeCategory (id) to highlight one, $compact (bool) for
    the tighter row used on the menu page.
--}}
@php
    $compact        = $compact ?? false;
    $activeCategory = (string) ($activeCategory ?? '');
@endphp

<div class="cat-card-row {{ $compact ? 'is-compact' : '' }}">

    <a href="{{ $compact ? 'javascript:void(0);' : route('menu.index') }}"
       class="cat-card cat-card-all {{ $activeCategory === '' ? 'active' : '' }}"
       data-id="">
        <div class="cat-card-img">
            <i class="fa fa-th-large"></i>
        </div>
        <div class="cat-card-body">
            <h6>All Items</h6>
            <small>{{ $categories->sum('foods_count') }} items</small>
        </div>
    </a>

    @foreach ($categories as $category)
        <a href="{{ $compact ? 'javascript:void(0);' : route('menu.index', ['category' => $category->id]) }}"
           class="cat-card {{ $activeCategory === (string) $category->id ? 'active' : '' }}"
           data-id="{{ $category->id }}">

            <div class="cat-card-img">
                @if ($category->image)
                    <img src="{{ asset('storage/'.$category->image) }}"
                         alt="{{ $category->name }}" loading="lazy">
                @else
                    <i class="fa fa-cutlery"></i>
                @endif
            </div>

            <div class="cat-card-body">
                <h6>{{ $category->name }}</h6>
                <small>{{ $category->foods_count }} item{{ $category->foods_count == 1 ? '' : 's' }}</small>
            </div>
        </a>
    @endforeach

</div>
