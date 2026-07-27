@extends('backend.master')
@section('title', 'Edit Run #' . $run->id)

@section('content')
<div class="container-fluid">

    @php
        $orderCount = count($run->order_ids ?? []);
        $isDone     = $run->status === 'completed';
    @endphp

    <x-page-header
        title="Edit Run #{{ $run->id }}"
        subtitle="Only the rider and the note can change — the orders on a run are fixed once it leaves."
        icon="feather-map"
        :breadcrumb="['Delivery' => null, 'Delivery Runs' => route('admin.delivery-runs.index'), 'Run #' . $run->id => null]">

        @can('delivery_runs.view')
            <a href="{{ route('admin.delivery-runs.show', $run->id) }}" class="btn btn-soft">
                <i class="feather-eye"></i> View Run
            </a>
        @endcan

        <a href="{{ route('admin.delivery-runs.index') }}" class="btn btn-soft">
            <i class="feather-arrow-left"></i> Back to Delivery Runs
        </a>
    </x-page-header>

    <div class="row g-3">

        <div class="col-xl-8">
            <div class="card">
                <div class="card-header">
                    <h5>Assignment</h5>
                    <span class="text-muted fs-13">{{ $orderCount }} order(s) on this run</span>
                </div>

                <form action="{{ route('admin.delivery-runs.update', $run->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-12">
                                <label class="filter-label" for="delivery_man_id">Delivery Man</label>
                                <select name="delivery_man_id" id="delivery_man_id" class="form-select" required>
                                    @foreach ($deliveryMen as $man)
                                        <option value="{{ $man->id }}"
                                            @selected(old('delivery_man_id', $run->delivery_man_id) == $man->id)>
                                            {{ $man->name }} ({{ $man->phone }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('delivery_man_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="col-12">
                                <label class="filter-label" for="orders_readonly">Orders</label>
                                <input type="text" id="orders_readonly"
                                       class="form-control"
                                       value="{{ $orderCount }} Order(s)"
                                       readonly>
                                <small class="text-muted">
                                    Orders cannot be added or removed after a run has departed.
                                </small>
                            </div>

                            <div class="col-12">
                                <label class="filter-label" for="note">Note</label>
                                <textarea name="note" id="note" class="form-control"
                                          rows="3">{{ old('note', $run->note) }}</textarea>
                                @error('note')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="action-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="feather-save"></i> Update Delivery Run
                            </button>

                            <a href="{{ route('admin.delivery-runs.index') }}" class="btn btn-soft">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h5>Run Summary</h5></div>

                <div class="card-body">
                    <div class="kv-list">
                        <div class="kv-row">
                            <span class="kv-key">Status</span>
                            <span class="kv-val">
                                <span class="badge {{ $isDone ? 'bg-success' : 'bg-warning' }}">
                                    {{ $isDone ? 'Completed' : 'On The Way' }}
                                </span>
                            </span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Orders</span>
                            <span class="kv-val num">{{ $orderCount }}</span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Departed</span>
                            <span class="kv-val">
                                {{ $run->departed_at ? $run->departed_at->format('d M Y, h:i A') : '—' }}
                            </span>
                        </div>

                        <div class="kv-row">
                            <span class="kv-key">Delivered</span>
                            <span class="kv-val">
                                {{ $run->returned_at ? $run->returned_at->format('d M Y, h:i A') : 'Not yet' }}
                            </span>
                        </div>
                    </div>

                    @can('delivery_runs.edit')
                        @if (! $isDone)
                            <hr class="hr-soft">

                            <form action="{{ route('admin.delivery-runs.complete', $run->id) }}"
                                  method="POST" class="delete-form"
                                  data-confirm-title="Mark run #{{ $run->id }} as delivered?"
                                  data-confirm-text="All {{ $orderCount }} order(s) on this run will be set to delivered."
                                  data-confirm-button="Yes, complete it">
                                @csrf
                                @method('PATCH')

                                <button type="submit" class="btn btn-soft-success w-100">
                                    <i class="feather-check-circle"></i> Mark Run as Delivered
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
