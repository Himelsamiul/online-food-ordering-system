@extends('backend.master')
@section('title', 'Delivery Areas')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Delivery Areas"
        subtitle="What each area costs to reach. A customer picks one at checkout and the charge is added to their total."
        icon="feather-map-pin"
        :breadcrumb="['Delivery' => null, 'Areas' => null]">

        @if ($canCreate)
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#zoneModal"
                    onclick="zoneForm.reset()">
                <i class="feather-plus"></i> Add area
            </button>
        @endif
    </x-page-header>

    {{-- ---------------------------------------------------------- filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.delivery-zones.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="search" name="q" value="{{ request('q') }}"
                           class="form-control" placeholder="Area name">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" @selected(request('status') === 'active')>Active</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Paused</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-soft-primary"><i class="feather-search"></i> Filter</button>
                    <a href="{{ route('admin.delivery-zones.index') }}" class="btn btn-soft">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- ------------------------------------------------------------ table --}}
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Area</th>
                            <th class="text-end">Charge</th>
                            <th class="text-end">Min order</th>
                            <th class="text-end">Free above</th>
                            <th class="text-center">ETA</th>
                            <th class="text-center">Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($zones as $zone)
                            <tr>
                                <td>
                                    <strong>{{ $zone->name }}</strong>
                                    @if ($zone->areas)
                                        <small class="d-block text-muted">{{ $zone->areas }}</small>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if ((float) $zone->charge > 0)
                                        ৳{{ number_format((float) $zone->charge, 2) }}
                                    @else
                                        <span class="badge bg-success-subtle text-success">Free</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    {{ $zone->min_order ? '৳' . number_format((float) $zone->min_order, 2) : '—' }}
                                </td>
                                <td class="text-end">
                                    {{ $zone->free_above ? '৳' . number_format((float) $zone->free_above, 2) : '—' }}
                                </td>
                                <td class="text-center">
                                    {{ $zone->eta_minutes ? $zone->eta_minutes . ' min' : '—' }}
                                </td>
                                <td class="text-center">
                                    <span class="status-pill {{ $zone->is_active ? 'on' : 'off' }}">
                                        {{ $zone->is_active ? 'Active' : 'Paused' }}
                                    </span>
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($canEdit)
                                        <button type="button" class="btn btn-sm btn-soft-primary"
                                                data-bs-toggle="modal" data-bs-target="#zoneModal"
                                                data-zone="{{ json_encode([
                                                    'id'          => $zone->id,
                                                    'name'        => $zone->name,
                                                    'areas'       => $zone->areas,
                                                    'charge'      => (float) $zone->charge,
                                                    'min_order'   => $zone->min_order ? (float) $zone->min_order : '',
                                                    'free_above'  => $zone->free_above ? (float) $zone->free_above : '',
                                                    'eta_minutes' => $zone->eta_minutes,
                                                    'sort_order'  => $zone->sort_order,
                                                    'is_active'   => (bool) $zone->is_active,
                                                    'url'         => route('admin.delivery-zones.update', $zone),
                                                ]) }}"
                                                title="Edit">
                                            <i class="feather-edit-2"></i>
                                        </button>

                                        <form method="POST" action="{{ route('admin.delivery-zones.status', $zone) }}"
                                              class="d-inline">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="btn btn-sm {{ $zone->is_active ? 'btn-soft-warning' : 'btn-soft-success' }}"
                                                    title="{{ $zone->is_active ? 'Pause deliveries here' : 'Resume deliveries here' }}">
                                                <i class="feather-{{ $zone->is_active ? 'pause' : 'play' }}"></i>
                                            </button>
                                        </form>
                                    @endif

                                    @if ($canDelete)
                                        <form method="POST" action="{{ route('admin.delivery-zones.delete', $zone) }}"
                                              class="delete-form d-inline"
                                              data-confirm-title="Delete {{ $zone->name }}?"
                                              data-confirm-text="Past orders keep the area name on their own records, so invoices stay correct."
                                              data-confirm-button="Yes, delete it">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <x-empty-state
                                        icon="feather-map-pin"
                                        title="No delivery areas yet"
                                        message="Until at least one area exists, customers cannot complete checkout." />
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($zones->hasPages())
            <div class="card-footer">{{ $zones->links() }}</div>
        @endif
    </div>
</div>

{{-- ------------------------------------------------------------- modal --}}
@if ($canCreate || $canEdit)
<div class="modal fade" id="zoneModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" id="zoneForm" action="{{ route('admin.delivery-zones.store') }}" class="modal-content">
            @csrf
            <input type="hidden" name="_method" id="zoneMethod" value="POST">

            <div class="modal-header">
                <h5 class="modal-title" id="zoneModalTitle">Add delivery area</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Area name <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="zoneName" class="form-control" required
                           placeholder="Dhanmondi">
                </div>

                <div class="mb-3">
                    <label class="form-label">Covers</label>
                    <input type="text" name="areas" id="zoneAreas" class="form-control"
                           placeholder="Road 27, Jigatola, Shankar">
                    <small class="text-muted">Shown to the customer so they can tell which area is theirs.</small>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Delivery charge (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="charge" id="zoneCharge"
                               class="form-control" value="0" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">ETA (minutes)</label>
                        <input type="number" min="1" max="600" name="eta_minutes" id="zoneEta" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Minimum order (৳)</label>
                        <input type="number" step="0.01" min="0" name="min_order" id="zoneMin" class="form-control">
                        <small class="text-muted">Checkout is blocked below this.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Free delivery above (৳)</label>
                        <input type="number" step="0.01" min="0" name="free_above" id="zoneFree" class="form-control">
                        <small class="text-muted">Leave blank to always charge.</small>
                    </div>
                </div>

                <div class="row align-items-center">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Sort order</label>
                        <input type="number" min="0" name="sort_order" id="zoneSort" class="form-control" value="0">
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="form-check form-switch mt-4">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" name="is_active"
                                   id="zoneActive" value="1" checked>
                            <label class="form-check-label" for="zoneActive">Accepting orders</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-soft" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save area</button>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    (function () {
        var modal = document.getElementById('zoneModal');
        if (!modal) return;

        var form = document.getElementById('zoneForm');
        var createAction = form.getAttribute('action');

        /*
         * One modal serves both add and edit. The trigger button carries the
         * row as JSON; with no payload we fall back to the create action, so
         * the "Add area" button needs no special handling.
         */
        modal.addEventListener('show.bs.modal', function (event) {
            var trigger = event.relatedTarget;
            var raw = trigger && trigger.dataset ? trigger.dataset.zone : null;

            if (!raw) {
                form.setAttribute('action', createAction);
                document.getElementById('zoneMethod').value = 'POST';
                document.getElementById('zoneModalTitle').textContent = 'Add delivery area';
                form.reset();
                document.getElementById('zoneActive').checked = true;
                return;
            }

            var zone = JSON.parse(raw);

            form.setAttribute('action', zone.url);
            document.getElementById('zoneMethod').value = 'PUT';
            document.getElementById('zoneModalTitle').textContent = 'Edit ' + zone.name;

            document.getElementById('zoneName').value   = zone.name || '';
            document.getElementById('zoneAreas').value  = zone.areas || '';
            document.getElementById('zoneCharge').value = zone.charge;
            document.getElementById('zoneMin').value    = zone.min_order;
            document.getElementById('zoneFree').value   = zone.free_above;
            document.getElementById('zoneEta').value    = zone.eta_minutes || '';
            document.getElementById('zoneSort').value   = zone.sort_order || 0;
            document.getElementById('zoneActive').checked = zone.is_active;
        });
    })();
</script>
@endpush
