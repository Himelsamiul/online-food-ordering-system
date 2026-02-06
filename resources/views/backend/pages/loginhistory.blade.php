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
            <button class="btn btn-primary">Search</button>

            <a href="{{ route('admin.login.history') }}"
               class="btn btn-outline-secondary">
                Reset
            </a>
        </div>

    </form>

    {{-- 🔥 Bulk Delete Form START --}}
    <form action="{{ route('admin.login.history.bulk.delete') }}" method="POST">
        @csrf

        {{-- Delete Button --}}
        <div class="mb-2">
            <button type="submit"
                    class="btn btn-danger btn-sm"
                    onclick="return confirm('Delete selected login history?')">
                Delete Selected
            </button>
        </div>

        {{--  Table --}}
        <table class="table table-bordered table-striped align-middle">
            <thead>
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="selectAll">
                    </th>
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
                        {{-- Checkbox --}}
                        <td>
                            <input type="checkbox"
                                   name="ids[]"
                                   value="{{ $history->id }}">
                        </td>

                        {{-- SL --}}
                        <td>
                            {{ ($histories->currentPage() - 1) * $histories->perPage() + $loop->iteration }}
                        </td>

                        {{-- User --}}
                        <td>
                            {{ $history->registration->full_name ?? 'Deleted User' }}<br>
                            <small class="text-muted">
                                {{ $history->registration->username ?? '' }}
                            </small>
                        </td>

                        {{-- IP --}}
                        <td style="
                            color:#{{ substr(md5($history->ip_address), 0, 6) }};
                            font-weight:600;
                        ">
                            {{ $history->ip_address }}
                        </td>

                        {{-- Location --}}
                        <td>
                            <strong>{{ $history->country ?? '-' }}</strong><br>
                            <small class="text-muted">
                                {{ $history->city ?? '' }}
                            </small>
                        </td>

                        {{-- Login Time --}}
                        <td>
                            {{ $history->logged_in_at
                                ? date('d M Y h:i A', strtotime($history->logged_in_at))
                                : '-' }}
                        </td>

                        {{-- Logout Time --}}
                        <td>
                            @if ($history->logged_out_at)
                                {{ date('d M Y h:i A', strtotime($history->logged_out_at)) }}
                            @else
                                <span class="badge bg-success">Still Logged In</span>
                            @endif
                        </td>

                        {{-- Device --}}
                        <td class="user-agent">
                            {{ $history->user_agent }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center">
                            No login history found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </form>
    {{-- 🔥 Bulk Delete Form END --}}

    {{--  Bottom Controls --}}
    <div class="d-flex justify-content-between align-items-center mt-3">

        {{-- Per Page --}}
        <form method="GET" class="d-flex align-items-center gap-2">
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

        {{ $histories->withQueryString()->links() }}
    </div>

</div>

{{-- CSS --}}
<style>
.user-agent{
    max-width:250px;
    white-space: normal;
    word-break: break-word;
    overflow-wrap: break-word;
    line-height: 1.5;
    font-size: 13px;
}
</style>

{{-- Select All Script --}}
<script>
document.getElementById('selectAll').addEventListener('click', function () {
    document.querySelectorAll('input[name="ids[]"]').forEach(cb => {
        cb.checked = this.checked;
    });
});
</script>

@endsection
