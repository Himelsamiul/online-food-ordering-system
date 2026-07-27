@props([
    'title',
    'subtitle' => null,
    'icon' => null,
    // ['Label' => route(...), 'Current' => null]
    'breadcrumb' => [],
])

{{--
    The block above the first card on every admin page: breadcrumb, title,
    one line of context, and whatever actions the page offers on the right.
--}}
<div class="page-header">
    <div class="page-header-titles">
        @if (!empty($breadcrumb))
            <nav class="page-breadcrumb" aria-label="Breadcrumb">
                <a href="{{ route('admin.dashboard') }}">Dashboard</a>
                @foreach ($breadcrumb as $label => $url)
                    <span class="sep">/</span>
                    @if ($url)
                        <a href="{{ $url }}">{{ $label }}</a>
                    @else
                        <span>{{ $label }}</span>
                    @endif
                @endforeach
            </nav>
        @endif

        <h1 class="page-title">
            @if ($icon)
                <span class="title-icon"><i class="{{ $icon }}"></i></span>
            @endif
            {{ $title }}
        </h1>

        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
    </div>

    @if (trim($slot) !== '')
        <div class="page-actions">{{ $slot }}</div>
    @endif
</div>
