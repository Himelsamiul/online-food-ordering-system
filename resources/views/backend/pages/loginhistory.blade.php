@extends('backend.master')

@section('content')
<div class="container">

    <h4 class="mb-4">Login History</h4>

    {{-- 🔍 Search Filters --}}
    <form method="GET" class="row g-2 mb-4">

        <div class="col-md-3">
            <input type="text"
                   name="name"
                   class="form-control"
                   placeholder="Search by name / username"
                   value="{{ request('name') }}">
        </div>

        <div class="col-md-3">
            <input type="text"
                   name="country"
                   class="form-control"
                   placeholder="Search by country"
                   value="{{ request('country') }}">
        </div>

        <div class="col-md-3">
            <input type="date"
                   name="date"
                   class="form-control"
                   value="{{ request('date') }}">
        </div>

        <div class="col-md-3 d-flex gap-2">
            <button class="btn btn-primary">
                Search
            </button>

            {{-- Reset --}}
            <a href="{{ route('admin.login.history') }}"
               class="btn btn-outline-secondary">
                Reset
            </a>
        </div>

    </form>

    {{-- 📋 Table --}}
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-responsive">
            <tr>
                <th style="width:60px">SL</th>
                <th>User</th>
                <th>IP</th>
                <th>Location</th>
                <th>Login Time</th>
                <th>Logout Time</th>
                <th>Device</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($histories as $history)
                <tr>
                    {{-- Dynamic serial with pagination --}}
                    <td>
                        {{ ($histories->currentPage() - 1) * $histories->perPage() + $loop->iteration }}
                    </td>

                    <td>
                        {{ $history->registration->full_name ?? 'Deleted User' }}<br>
                        <small class="text-muted">
                            {{ $history->registration->username ?? '' }}
                        </small>
                    </td>

                   <td style="
    color: #{{ substr(md5($history->ip_address), 0, 6) }};
    font-weight: 600;
">
    {{ $history->ip_address }}
</td>

                    <td>{{ $history->country }}, {{ $history->city }}</td>

                    <td>{{ $history->logged_in_at }}</td>

                    <td>
                        @if ($history->logged_out_at)
                            {{ $history->logged_out_at }}
                        @else
                            <span class="badge bg-success">
                                Still Logged In
                            </span>
                        @endif
                    </td>

                    <td style="max-width:250px">
                        {{ $history->user_agent }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">
                        No login history found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ⬇️ Bottom Controls --}}
    <div class="d-flex justify-content-between align-items-center mt-3">

        {{-- Per Page (Bottom) --}}
        <form method="GET" class="d-flex align-items-center gap-2">
            {{-- keep search params --}}
            <input type="hidden" name="name" value="{{ request('name') }}">
            <input type="hidden" name="country" value="{{ request('country') }}">
            <input type="hidden" name="date" value="{{ request('date') }}">

            <label class="mb-0 text-muted">Show</label>

            <select name="per_page"
                    class="form-select form-select-sm"
                    onchange="this.form.submit()">
                @foreach ([20, 50, 100, 200, 500] as $size)
                    <option value="{{ $size }}"
                        {{ request('per_page', 20) == $size ? 'selected' : '' }}>
                        {{ $size }}
                    </option>
                @endforeach
            </select>
        </form>

        {{-- Pagination Links --}}
        {{ $histories->withQueryString()->links() }}
    </div>

</div>
@endsection
