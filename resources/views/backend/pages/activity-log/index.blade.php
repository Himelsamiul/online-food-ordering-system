@extends('backend.master')
@section('title', 'Activity Log')

@push('styles')
<style>
    .bulk-bar {
        display: flex;
        align-items: center;
        gap: 12px;
        font-size: 13px;
        color: var(--ar-ink-2);
    }

    /* Each timeline entry is checkbox + text + actions. */
    .log-row {
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }

    .log-body { flex: 1 1 auto; min-width: 0; }
    .log-check { margin-top: 2px; flex-shrink: 0; }
    .log-actions { flex-shrink: 0; }

    .timeline-meta .who { color: var(--ar-ink-2); font-weight: 650; }
    .timeline-meta .sep { color: var(--ar-faint); margin: 0 5px; }
    .timeline-meta .mono { font-variant-numeric: tabular-nums; }
    .timeline-meta .trail { word-break: break-all; }

    .subject-chip { margin-top: 8px; }
    .subject-chip .subject-label { color: var(--ar-muted); font-weight: 500; }

    .prune-form { display: flex; align-items: center; gap: 8px; }
    .prune-form .form-select { padding: 8px 12px; font-size: 13px; width: auto; }
</style>
@endpush

@section('content')
<div class="container-fluid">

    @php
        // Used to word the empty state, and to decide whether to offer a reset.
        $activeFilters = collect(['q', 'event', 'user', 'subject', 'from', 'to'])
            ->filter(fn ($key) => filled(request($key)))
            ->isNotEmpty();

        // Diff values arrive as scalars or null; keep them short and readable.
        $showValue = function ($value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            if (is_null($value) || $value === '') {
                return '—';
            }

            if (is_bool($value)) {
                return $value ? 'Yes' : 'No';
            }

            return \Illuminate\Support\Str::limit((string) $value, 90);
        };
    @endphp

    <x-page-header
        title="Activity Log"
        subtitle="Who created, changed or removed what in the admin panel, and from which address."
        icon="feather-activity"
        :breadcrumb="['Monitoring' => null, 'Activity Log' => null]">

        @can('activity_log.delete')
            <form method="POST" action="{{ route('admin.activity-log.prune') }}"
                  class="delete-form prune-form"
                  data-confirm-title="Prune old entries?"
                  data-confirm-text="Everything older than the age you picked is removed permanently."
                  data-confirm-button="Yes, prune them">
                @csrf

                <select name="days" class="form-select" aria-label="Remove entries older than">
                    <option value="30">Older than 30 days</option>
                    <option value="90" selected>Older than 90 days</option>
                    <option value="180">Older than 180 days</option>
                    <option value="365">Older than 365 days</option>
                </select>

                <button type="submit" class="btn btn-soft-danger">
                    <i class="feather-scissors"></i> Prune
                </button>
            </form>
        @endcan
    </x-page-header>

    {{-- ================= Stats ================= --}}
    <div class="stat-grid">
        <x-stat-card label="Recorded Today" :value="$stats['today']"
                     icon="feather-clock" tone="primary" foot="since midnight" />

        <x-stat-card label="Created" :value="$stats['created']"
                     icon="feather-plus-circle" tone="success" foot="all time" />

        <x-stat-card label="Updated" :value="$stats['updated']"
                     icon="feather-edit-2" tone="warning" foot="all time" />

        <x-stat-card label="Deleted" :value="$stats['deleted']"
                     icon="feather-trash-2" tone="danger" foot="all time" />
    </div>

    {{-- ================= Filters ================= --}}
    <form method="GET" action="{{ route('admin.activity-log.index') }}" class="filter-card">
        <div class="row g-3 align-items-end">

            <div class="col-lg-4 col-md-6">
                <label class="filter-label" for="filter-q">Search</label>
                <input type="text" id="filter-q" name="q" value="{{ request('q') }}"
                       class="form-control" placeholder="Description, admin name or record">
            </div>

            <div class="col-lg-2 col-md-3">
                <label class="filter-label" for="filter-event">Event</label>
                <select name="event" id="filter-event" class="form-select">
                    <option value="">All events</option>
                    @foreach ($events as $eventKey => $eventMeta)
                        <option value="{{ $eventKey }}" @selected(request('event') === $eventKey)>
                            {{ $eventMeta['label'] }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-md-3">
                <label class="filter-label" for="filter-user">Admin</label>
                <select name="user" id="filter-user" class="form-select">
                    <option value="">Everyone</option>
                    @foreach ($admins as $admin)
                        <option value="{{ $admin->id }}"
                            @selected((string) request('user') === (string) $admin->id)>{{ $admin->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-3 col-md-6">
                <label class="filter-label" for="filter-subject">Record Type</label>
                <select name="subject" id="filter-subject" class="form-select">
                    <option value="">All record types</option>
                    @foreach ($subjectTypes as $fqcn => $shortName)
                        <option value="{{ $fqcn }}" @selected(request('subject') === $fqcn)>{{ $shortName }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-lg-2 col-md-3">
                <label class="filter-label" for="filter-from">From</label>
                <input type="date" id="filter-from" name="from" value="{{ request('from') }}" class="form-control">
            </div>

            <div class="col-lg-2 col-md-3">
                <label class="filter-label" for="filter-to">To</label>
                <input type="date" id="filter-to" name="to" value="{{ request('to') }}" class="form-control">
            </div>

            <div class="col-lg-2 col-md-3">
                <label class="filter-label" for="filter-per-page">Per page</label>
                <select name="per_page" id="filter-per-page" class="form-select">
                    @foreach ([25, 50, 100] as $size)
                        <option value="{{ $size }}"
                            {{ (int) request('per_page', 25) === $size ? 'selected' : '' }}>{{ $size }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-auto">
                <button type="submit" class="btn btn-primary"><i class="feather-filter"></i> Filter</button>
                <a href="{{ route('admin.activity-log.index') }}" class="btn btn-soft">Reset</a>
            </div>

        </div>
    </form>

    {{--
        The bulk-delete form is kept outside the timeline: every row has its own
        delete form, and forms cannot be nested. The row checkboxes and the bar's
        submit button join this form through the HTML form attribute instead.
    --}}
    @can('activity_log.delete')
        <form id="bulkDeleteLogs" method="POST" action="{{ route('admin.activity-log.bulk-delete') }}"
              class="delete-form"
              data-confirm-title="Delete selected entries?"
              data-confirm-text="The selected audit entries will be removed permanently."
              data-confirm-button="Yes, delete them">
            @csrf
        </form>
    @endcan

    <div class="card">
        <div class="card-header">
            <h5>Audit Trail</h5>

            <div class="d-flex align-items-center gap-3">
                @can('activity_log.delete')
                    <div class="bulk-bar" data-ar-bulk-bar style="display:none">
                        <span><strong data-ar-bulk-count>0</strong> selected</span>
                        <button type="submit" form="bulkDeleteLogs" class="btn btn-soft-danger btn-sm">
                            <i class="feather-trash-2"></i> Delete selected
                        </button>
                    </div>

                    @if ($logs->count())
                        <div class="form-check m-0">
                            <input type="checkbox" class="form-check-input" id="checkAllLogs"
                                   data-ar-check-all="#activityTimeline"
                                   aria-label="Select every entry on this page">
                            <label class="form-check-label fs-13" for="checkAllLogs">Select page</label>
                        </div>
                    @endif
                @endcan

                <span class="text-muted fs-13">{{ $logs->total() }} entries</span>
            </div>
        </div>

        <div class="card-body">
            @if ($logs->count())
                <div class="timeline" id="activityTimeline">
                    @foreach ($logs as $log)
                        <div class="timeline-item">
                            <span class="timeline-dot tone-{{ $log->tone() }}">
                                <i class="{{ $log->icon() }}"></i>
                            </span>

                            <div class="log-row">
                                @can('activity_log.delete')
                                    <input type="checkbox" class="form-check-input log-check"
                                           name="ids[]" value="{{ $log->id }}"
                                           form="bulkDeleteLogs" data-ar-check
                                           aria-label="Select entry {{ $log->id }}">
                                @endcan

                                <div class="log-body">
                                    <p class="timeline-title">{{ $log->description }}</p>

                                    <p class="timeline-meta">
                                        <span class="who">{{ $log->user_name ?: 'System' }}</span>

                                        @if (filled($log->user_role))
                                            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $log->user_role)) }}</span>
                                        @endif

                                        @if ($log->created_at)
                                            <span class="sep">·</span>{{ $log->created_at->diffForHumans() }}
                                            <span class="sep">·</span><span class="mono">{{ $log->created_at->format('d M Y, h:i A') }}</span>
                                        @endif

                                        @if (filled($log->ip_address))
                                            <span class="sep">·</span><span class="mono" title="IP address">{{ $log->ip_address }}</span>
                                        @endif
                                    </p>

                                    @if (filled($log->url))
                                        <p class="timeline-meta trail">
                                            <i class="feather-link-2"></i> {{ \Illuminate\Support\Str::limit($log->url, 90) }}
                                        </p>
                                    @endif

                                    @if (filled($log->subject_type) || filled($log->subject_label))
                                        <span class="chip subject-chip">
                                            <i class="feather-tag"></i>
                                            {{ $log->subjectName() }}
                                            @if (filled($log->subject_label))
                                                <span class="subject-label">{{ $log->subject_label }}</span>
                                            @endif
                                            @if ($log->subject_id)
                                                <span class="subject-label">#{{ $log->subject_id }}</span>
                                            @endif
                                        </span>
                                    @endif

                                    @if (is_array($log->changes) && count($log->changes))
                                        <ul class="diff-list">
                                            @foreach ($log->changes as $field => $pair)
                                                @php
                                                    $values = is_array($pair) ? array_values($pair) : [null, $pair];
                                                @endphp
                                                <li class="diff-row">
                                                    <span class="diff-key">{{ ucfirst(str_replace('_', ' ', $field)) }}</span>
                                                    <span class="diff-old">{{ $showValue($values[0] ?? null) }}</span>
                                                    <span class="diff-arrow"><i class="feather-arrow-right"></i></span>
                                                    <span class="diff-new">{{ $showValue($values[1] ?? null) }}</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>

                                @can('activity_log.delete')
                                    <div class="action-group log-actions">
                                        <form action="{{ route('admin.activity-log.delete', $log->id) }}" method="POST"
                                              class="delete-form"
                                              data-confirm-title="Delete this entry?"
                                              data-confirm-text="The audit trail will no longer show this action."
                                              data-confirm-button="Yes, delete it">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-soft-danger"
                                                    title="Delete entry" aria-label="Delete entry {{ $log->id }}">
                                                <i class="feather-trash-2"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endcan
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state icon="feather-activity"
                               :title="$activeFilters ? 'No entries match this filter' : 'Nothing has been recorded yet'"
                               :message="$activeFilters
                                    ? 'Try a wider date range or clear the filters.'
                                    : 'Actions taken in the admin panel will appear here as they happen.'">
                    @if ($activeFilters)
                        <a href="{{ route('admin.activity-log.index') }}" class="btn btn-soft">Clear filters</a>
                    @endif
                </x-empty-state>
            @endif
        </div>

        @if ($logs->hasPages())
            <div class="table-foot">
                <p class="foot-note">Showing {{ $logs->firstItem() }}&ndash;{{ $logs->lastItem() }}
                    of {{ $logs->total() }}</p>
                {{ $logs->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

</div>
@endsection
