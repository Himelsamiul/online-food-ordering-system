@extends('backend.master')
@section('title', 'Delivery Runs')

@section('content')
<div class="container-fluid">

    @php
        $canView   = \Illuminate\Support\Facades\Gate::allows('delivery_runs.view');
        $canEdit   = \Illuminate\Support\Facades\Gate::allows('delivery_runs.edit');
        $canDelete = \Illuminate\Support\Facades\Gate::allows('delivery_runs.delete');

        $hasActions  = $canView || $canEdit || $canDelete;
        $columnCount = 6 + ($hasActions ? 1 : 0);
    @endphp

    <x-page-header
        title="Delivery Runs"
        subtitle="Each run is one rider carrying up to five orders out and back."
        icon="feather-map"
        :breadcrumb="['Delivery' => null, 'Delivery Runs' => null]">

        @can('delivery_runs.create')
            <a href="{{ route('admin.delivery-runs.create') }}" class="btn btn-primary">
                <i class="feather-plus"></i> Create Delivery Run
            </a>
        @endcan
    </x-page-header>

    {{-- ================= FILTERS ================= --}}
    <form method="GET" action="{{ route('admin.delivery-runs.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-md-3">
                <label class="filter-label" for="f_man">Delivery Man</label>
                <select name="delivery_man_id" id="f_man" class="form-select">
                    <option value="">All</option>
                    @foreach ($deliveryMen as $man)
                        <option value="{{ $man->id }}" @selected(request('delivery_man_id') == $man->id)>
                            {{ $man->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="f_phone">Customer Phone</label>
                <input type="text" id="f_phone" name="phone"
                       value="{{ request('phone') }}"
                       class="form-control" placeholder="01XXXXXXXXX">
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="f_status">Status</label>
                <select name="status" id="f_status" class="form-select">
                    <option value="">All</option>
                    <option value="on_the_way" @selected(request('status') === 'on_the_way')>On The Way</option>
                    <option value="completed" @selected(request('status') === 'completed')>Completed</option>
                </select>
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="f_from">From Date</label>
                <input type="date" id="f_from" name="from_date"
                       value="{{ request('from_date') }}" class="form-control">
            </div>

            <div class="col-md-2">
                <label class="filter-label" for="f_to">To Date</label>
                <input type="date" id="f_to" name="to_date"
                       value="{{ request('to_date') }}" class="form-control">
            </div>

            <div class="col-12 col-md-auto">
                <button type="submit" class="btn btn-primary">
                    <i class="feather-filter"></i> Filter
                </button>
                <a href="{{ route('admin.delivery-runs.index') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    {{-- ================= TABLE ================= --}}
    <div class="card">
        <div class="card-header">
            <h5>All Delivery Runs</h5>
            <span class="text-muted fs-13">{{ $runs->total() }} runs</span>
        </div>

        <div class="table-scroll">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Run</th>
                        <th>Delivery Man</th>
                        <th class="num">Orders</th>
                        <th>Departed</th>
                        <th>Delivered</th>
                        <th>Status</th>
                        @if ($hasActions)
                            <th class="text-end">Actions</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse ($runs as $run)
                        @php
                            $orderCount = count($run->order_ids ?? []);
                            $manName    = $run->deliveryMan->name ?? null;
                            $initials   = $manName
                                ? mb_strtoupper(mb_substr(trim($manName), 0, 2))
                                : '?';
                            $isDone     = $run->status === 'completed';
                        @endphp

                        <tr>
                            <td>
                                <p class="cell-title">Run #{{ $run->id }}</p>
                                @if ($run->note)
                                    <p class="cell-sub">{{ \Illuminate\Support\Str::limit($run->note, 40) }}</p>
                                @endif
                            </td>

                            <td>
                                <div class="cell-media">
                                    <span class="avatar-initials sm">{{ $initials }}</span>
                                    <div>
                                        <p class="cell-title">{{ $manName ?? 'Unassigned' }}</p>
                                        <p class="cell-sub">{{ $run->deliveryMan->phone ?? 'No phone on file' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="num">{{ $orderCount }}</td>

                            <td>{{ $run->departed_at ? $run->departed_at->format('d M Y, h:i A') : '—' }}</td>

                            <td>{{ $run->returned_at ? $run->returned_at->format('d M Y, h:i A') : '—' }}</td>

                            <td>
                                <span class="badge {{ $isDone ? 'bg-success' : 'bg-warning' }}">
                                    {{ $isDone ? 'Completed' : 'On The Way' }}
                                </span>
                            </td>

                            @if ($hasActions)
                                <td class="text-end">
                                    <div class="action-group justify-content-end">
                                        @can('delivery_runs.view')
                                            <a href="{{ route('admin.delivery-runs.show', $run->id) }}"
                                               class="btn btn-icon btn-soft-primary" title="View">
                                                <i class="feather-eye"></i>
                                            </a>
                                        @endcan

                                        @can('delivery_runs.edit')
                                            <a href="{{ route('admin.delivery-runs.edit', $run->id) }}"
                                               class="btn btn-icon btn-soft-warning" title="Edit">
                                                <i class="feather-edit-2"></i>
                                            </a>

                                            @if (! $isDone)
                                                <form action="{{ route('admin.delivery-runs.complete', $run->id) }}"
                                                      method="POST" class="delete-form"
                                                      data-confirm-title="Mark run #{{ $run->id }} as delivered?"
                                                      data-confirm-text="All {{ $orderCount }} order(s) on this run will be set to delivered."
                                                      data-confirm-button="Yes, complete it">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit" class="btn btn-icon btn-soft-success"
                                                            title="Complete">
                                                        <i class="feather-check"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        @endcan

                                        @can('delivery_runs.delete')
                                            <form action="{{ route('admin.delivery-runs.delete', $run->id) }}"
                                                  method="POST" class="delete-form"
                                                  data-confirm-title="Delete run #{{ $run->id }}?"
                                                  data-confirm-text="Its {{ $orderCount }} order(s) go back to pending and become assignable again.">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-icon btn-soft-danger"
                                                        title="Delete">
                                                    <i class="feather-trash-2"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $columnCount }}" data-label="">
                                <x-empty-state icon="feather-map"
                                               title="No delivery runs match this filter"
                                               message="Clear the filters, or start a run once there are orders waiting.">
                                    @can('delivery_runs.create')
                                        <a href="{{ route('admin.delivery-runs.create') }}" class="btn btn-primary">
                                            <i class="feather-plus"></i> Create Delivery Run
                                        </a>
                                    @endcan
                                </x-empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($runs->hasPages())
            <div class="table-foot">
                <p class="foot-note">
                    Showing {{ $runs->firstItem() }}–{{ $runs->lastItem() }} of {{ $runs->total() }}
                </p>
                {{ $runs->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
