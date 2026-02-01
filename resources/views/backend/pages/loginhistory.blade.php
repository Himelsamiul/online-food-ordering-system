@extends('backend.master')

@section('content')
<div class="container">

    {{-- Header + Per Page --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Login History</h4>

        <form method="GET" class="d-flex align-items-center gap-2">
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
    </div>

    {{-- Table --}}
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-light">
            <tr>
                <th>SL</th>
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
                    <td>{{ $loop->iteration }}</td>
                    <td>
                        {{ $history->registration->full_name ?? 'Deleted User' }}<br>
                        <small class="text-muted">
                            {{ $history->registration->username ?? '' }}
                        </small>
                    </td>

                    <td>{{ $history->ip_address }}</td>

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
                    <td colspan="6" class="text-center">
                        No login history found
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $histories->withQueryString()->links() }}
    </div>

</div>
@endsection
