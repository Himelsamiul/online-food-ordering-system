@props([
    'label',
    'value',
    'icon' => 'feather-bar-chart-2',
    'tone' => 'primary',      // primary | success | danger | warning | info
    'foot' => null,
    'href' => null,
    'prefix' => null,
    'suffix' => null,
])

@php
    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }} class="stat-card tone-{{ $tone }}"
    @if ($href) href="{{ $href }}" style="text-decoration:none;display:block" @endif>

    <div class="stat-head">
        <p class="stat-label">{{ $label }}</p>
        <span class="stat-icon"><i class="{{ $icon }}"></i></span>
    </div>

    <p class="stat-value">
        @if ($prefix)<small>{{ $prefix }}</small>@endif{{ $value }}@if ($suffix)<small>{{ $suffix }}</small>@endif
    </p>

    @if ($foot)
        <p class="stat-foot">{!! $foot !!}</p>
    @endif
</{{ $tag }}>
