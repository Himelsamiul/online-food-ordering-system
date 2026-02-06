@extends('backend.master')

@section('content')

<div class="container-fluid">
    <h4 class="mb-3">Contact Messages</h4>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Message</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                @forelse($messages as $index => $msg)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $msg->name ?? '-' }}</td>
                        <td>{{ $msg->email }}</td>
                        <td>{{ $msg->phone }}</td>
                        <td style="max-width:300px;">
                            {{ $msg->message ?? '-' }}
                        </td>
                        <td>{{ $msg->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            No messages found
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $messages->links() }}
</div>

@endsection
