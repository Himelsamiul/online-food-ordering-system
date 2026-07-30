@props(['value' => 0, 'count' => null, 'size' => null])

@php
    /**
     * A read-only star row.
     *
     * $value is rounded to the nearest half before drawing, because a row of
     * icons can only show whole and half stars — 4.37 has to become 4.5 or the
     * picture lies about itself.
     */
    $rounded = round(((float) $value) * 2) / 2;
@endphp

<span {{ $attributes->merge(['class' => 'sf-stars' . ($size ? ' sf-stars-' . $size : '')]) }}
      role="img"
      aria-label="{{ number_format((float) $value, 1) }} out of 5 stars{{ $count !== null ? ', ' . $count . ' reviews' : '' }}">
    @for ($i = 1; $i <= 5; $i++)
        @if ($rounded >= $i)
            <i class="fa fa-star" aria-hidden="true"></i>
        @elseif ($rounded >= $i - 0.5)
            <i class="fa fa-star-half-o" aria-hidden="true"></i>
        @else
            <i class="fa fa-star-o" aria-hidden="true"></i>
        @endif
    @endfor

    @if ($count !== null)
        <small class="sf-stars-count">({{ $count }})</small>
    @endif
</span>
