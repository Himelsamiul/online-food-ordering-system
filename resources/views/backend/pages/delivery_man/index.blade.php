@extends('backend.master')
@section('title', 'Delivery Men')

@section('content')
<div class="container-fluid">

    @php
        /*
         * The Actions column and the inline create form are both optional,
         * so the column count is worked out once and reused by every
         * colspan on the page (empty state and the "used in orders" panel).
         */
        $canCreate = \Illuminate\Support\Facades\Gate::allows('delivery_men.create');
        $canEdit   = \Illuminate\Support\Facades\Gate::allows('delivery_men.edit');
        $canDelete = \Illuminate\Support\Facades\Gate::allows('delivery_men.delete');

        $hasActions  = $canEdit || $canDelete;
        $columnCount = 6 + ($hasActions ? 1 : 0);
    @endphp

    <x-page-header
        title="Delivery Men"
        subtitle="The riders who carry orders out, and whether they are available right now."
        icon="feather-truck"
        :breadcrumb="['Delivery' => null, 'Delivery Men' => null]" />

    <div class="row g-3">

        {{-- ================= CREATE ================= --}}
        @can('delivery_men.create')
            <div class="col-xl-4">
                <div class="card">
                    <div class="card-header">
                        <h5>Register Delivery Man</h5>
                        <span class="text-muted fs-13">Starts active</span>
                    </div>

                    <div class="card-body">
                        <form action="{{ route('admin.delivery-men.store') }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf

                            <div class="row g-3">
                                <div class="col-12">
                                    <label class="filter-label" for="dm_name">Full Name</label>
                                    <input type="text" id="dm_name" name="name"
                                           value="{{ old('name') }}"
                                           class="form-control" required>
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="filter-label" for="dm_email">Email</label>
                                    <input type="email" id="dm_email" name="email"
                                           value="{{ old('email') }}"
                                           class="form-control" required>
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label class="filter-label" for="dm_phone">Phone</label>
                                    <input type="text" id="dm_phone" name="phone"
                                           value="{{ old('phone') }}"
                                           class="form-control"
                                           placeholder="01XXXXXXXXX" required>
                                    @error('phone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="filter-label" for="dm_nid">NID Number</label>
                                    <input type="text" id="dm_nid" name="nid_number"
                                           value="{{ old('nid_number') }}"
                                           class="form-control"
                                           placeholder="9 or 13 digits" required>
                                    @error('nid_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="filter-label" for="dm_address">Address</label>
                                    <textarea id="dm_address" name="address" class="form-control"
                                              rows="2">{{ old('address') }}</textarea>
                                    @error('address')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="filter-label" for="dm_note">Note</label>
                                    <textarea id="dm_note" name="note" class="form-control"
                                              rows="2"
                                              placeholder="Shift, coverage area, anything worth remembering">{{ old('note') }}</textarea>
                                    @error('note')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <label class="filter-label" for="dm_photo">Profile Photo</label>
                                    <input type="file" id="dm_photo" name="photo"
                                           class="form-control" accept="image/*" required>
                                    <small class="text-muted">JPG, PNG or WebP, up to 2 MB.</small>
                                    @error('photo')
                                        <br><small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="feather-user-plus"></i> Create Delivery Man
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        {{-- ================= LIST ================= --}}
        <div class="{{ $canCreate ? 'col-xl-8' : 'col-12' }}">

            <form method="GET" action="{{ route('admin.delivery-men.index') }}" class="filter-card">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="filter-label" for="f_name">Name</label>
                        <input type="text" id="f_name" name="name"
                               value="{{ request('name') }}"
                               class="form-control" placeholder="Search by name">
                    </div>

                    <div class="col-md-3">
                        <label class="filter-label" for="f_phone">Phone</label>
                        <input type="text" id="f_phone" name="phone"
                               value="{{ request('phone') }}"
                               class="form-control" placeholder="01XXXXXXXXX">
                    </div>

                    <div class="col-md-2">
                        <label class="filter-label" for="f_status">Status</label>
                        <select name="status" id="f_status" class="form-select">
                            <option value="">All</option>
                            <option value="1" @selected(request('status') === '1')>Active</option>
                            <option value="0" @selected(request('status') === '0')>Inactive</option>
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
                        <a href="{{ route('admin.delivery-men.index') }}" class="btn btn-soft">Reset</a>
                    </div>
                </div>
            </form>

            <div class="card">
                <div class="card-header">
                    <h5>All Delivery Men</h5>
                    <span class="text-muted fs-13">{{ $deliveryMen->total() }} registered</span>
                </div>

                <div class="table-scroll">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Delivery Man</th>
                                <th>Email</th>
                                <th>NID &amp; Address</th>
                                <th>Note</th>
                                <th>Status</th>
                                @if ($hasActions)
                                    <th class="text-end">Actions</th>
                                @endif
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($deliveryMen as $key => $man)
                                @php
                                    $orders   = $man->deliveryRuns->flatMap->orders;
                                    $initials = mb_strtoupper(mb_substr(trim((string) $man->name), 0, 2));
                                @endphp

                                <tr>
                                    <td class="text-muted">{{ $deliveryMen->firstItem() + $key }}</td>

                                    <td>
                                        <div class="cell-media">
                                            @if ($man->photo)
                                                <img src="{{ asset('storage/' . $man->photo) }}"
                                                     alt="{{ $man->name }}"
                                                     style="width:38px;height:38px;object-fit:cover;border-radius:11px">
                                            @else
                                                <span class="avatar-initials">{{ $initials ?: '?' }}</span>
                                            @endif

                                            <div>
                                                <p class="cell-title">{{ $man->name }}</p>
                                                <p class="cell-sub">{{ $man->phone }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    <td>{{ $man->email }}</td>

                                    <td>
                                        <p class="cell-title" style="font-weight:600">{{ $man->nid_number }}</p>
                                        <p class="cell-sub">{{ $man->address ?: 'No address on file' }}</p>
                                    </td>

                                    <td class="text-muted fs-13">{{ $man->note ?: '—' }}</td>

                                    <td>
                                        <div class="d-flex flex-column align-items-start gap-1">
                                            <span class="status-pill {{ $man->status ? 'on' : 'off' }}">
                                                {{ $man->status ? 'Active' : 'Inactive' }}
                                            </span>

                                            @can('delivery_men.edit')
                                                <form action="{{ route('admin.delivery-men.status', $man->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    @method('PATCH')

                                                    <button type="submit" class="btn btn-soft btn-sm"
                                                            style="font-size:11.5px;padding:2px 9px"
                                                            title="{{ $man->status ? 'Set to inactive' : 'Set to active' }}">
                                                        <i class="feather-{{ $man->status ? 'toggle-right' : 'toggle-left' }}"></i>
                                                        {{ $man->status ? 'Deactivate' : 'Activate' }}
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>

                                    @if ($hasActions)
                                        <td class="text-end">
                                            <div class="action-group justify-content-end">
                                                @can('delivery_men.edit')
                                                    <a href="{{ route('admin.delivery-men.edit', $man->id) }}"
                                                       class="btn btn-icon btn-soft-warning" title="Edit">
                                                        <i class="feather-edit-2"></i>
                                                    </a>
                                                @endcan

                                                @can('delivery_men.delete')
                                                    @if ($orders->count() > 0)
                                                        <button type="button"
                                                                class="btn btn-icon btn-soft-info"
                                                                data-ar-toggle="#orders-row-{{ $man->id }}"
                                                                title="On {{ $orders->count() }} order(s) — cannot be deleted">
                                                            <i class="feather-lock"></i>
                                                        </button>
                                                    @else
                                                        <form action="{{ route('admin.delivery-men.delete', $man->id) }}"
                                                              method="POST" class="delete-form"
                                                              data-confirm-title="Delete {{ $man->name }}?"
                                                              data-confirm-text="This delivery man will be removed permanently.">
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit" class="btn btn-icon btn-soft-danger"
                                                                    title="Delete">
                                                                <i class="feather-trash-2"></i>
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                        </td>
                                    @endif
                                </tr>

                                @can('delivery_men.delete')
                                    @if ($orders->count() > 0)
                                        <tr id="orders-row-{{ $man->id }}" hidden>
                                            <td colspan="{{ $columnCount }}" data-label=""
                                                style="background:var(--ar-surface-2)">

                                                <p class="section-title">
                                                    Carried on {{ $orders->count() }}
                                                    {{ $orders->count() === 1 ? 'order' : 'orders' }} — delete is blocked
                                                </p>

                                                <table class="table table-sm align-middle mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Order</th>
                                                            <th>Customer</th>
                                                            <th>Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($orders as $order)
                                                            <tr>
                                                                <td data-label="Order">{{ $order->order_number }}</td>
                                                                <td data-label="Customer">{{ $order->name }}</td>
                                                                <td data-label="Status">
                                                                    {{ ucfirst(str_replace('_', ' ', (string) $order->order_status)) }}
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endif
                                @endcan
                            @empty
                                <tr>
                                    <td colspan="{{ $columnCount }}" data-label="">
                                        <x-empty-state icon="feather-truck"
                                                       title="No delivery men match this filter"
                                                       message="Clear the filters, or register someone using the form." />
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($deliveryMen->hasPages())
                    <div class="table-foot">
                        <p class="foot-note">
                            Showing {{ $deliveryMen->firstItem() }}–{{ $deliveryMen->lastItem() }}
                            of {{ $deliveryMen->total() }}
                        </p>
                        {{ $deliveryMen->links() }}
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
@endsection
