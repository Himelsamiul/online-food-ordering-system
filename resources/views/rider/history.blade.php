@extends('rider.layout')
@section('title', 'Completed')

@section('content')

<h2 class="rider-page-title">Completed runs</h2>

@forelse ($runs as $run)
    <article class="rider-history-row">
        <div>
            <strong>Run #{{ $run->id }}</strong>
            <small>
                {{ count($run->order_ids ?? []) }} {{ Str::plural('order', count($run->order_ids ?? [])) }}
                @if ($run->departed_at)
                    · {{ $run->departed_at->format('d M, g:i A') }}
                @endif
            </small>
        </div>

        <span class="rider-badge is-done">
            @if ($run->returned_at)
                {{ $run->returned_at->diffForHumans() }}
            @else
                Completed
            @endif
        </span>
    </article>
@empty
    <div class="rider-empty">
        <i class="fa fa-check-square-o" aria-hidden="true"></i>
        <h3>No completed runs yet</h3>
        <p>Runs you finish will be listed here.</p>
    </div>
@endforelse

@if ($runs->hasPages())
    <div class="rider-pager">{{ $runs->links() }}</div>
@endif

@endsection
