@extends('backend.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between mb-3">
        <h4 class="fw-bold">Delivery Runs</h4>
        <a href="{{ route('admin.delivery-runs.create') }}" class="btn btn-primary">
            + Create Delivery Run
        </a>
    </div>

    <div class="card">
        <div class="card-body table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Delivery Man</th>
                        <th>Orders</th>
                        <th>Departed At</th>
                        <th>Status</th>
                        <th width="180">Action</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($runs as $key => $run)
                    <tr>
                        <td>{{ $runs->firstItem() + $key }}</td>
                        <td>{{ $run->deliveryMan->name ?? 'N/A' }}</td>
                        <td>{{ count($run->order_ids) }} Order(s)</td>
                        <td>{{ $run->departed_at->format('d M Y, h:i A') }}</td>
                        <td>
                            @if($run->status === 'on_the_way')
                                <span class="badge bg-warning">On The Way</span>
                            @else
                                <span class="badge bg-success">Completed</span>
                            @endif
                        </td>
                        <td class="d-flex gap-1">
                            <a href="{{ route('admin.delivery-runs.edit', $run->id) }}"
                               class="btn btn-sm btn-info">Edit</a>

                            @if($run->status !== 'completed')
                                <form action="{{ route('admin.delivery-runs.complete', $run->id) }}" method="POST">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-success">
                                        Complete
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('admin.delivery-runs.delete', $run->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Delete this delivery run?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">
                                    Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">
                            No delivery runs found
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            {{ $runs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
