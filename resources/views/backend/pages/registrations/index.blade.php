@extends('backend.master')

@section('content')

<div class="container-fluid">

    <div class="card shadow-sm">
        <div class="card-header">
            <h4 class="mb-0">Registered Users</h4>
        </div>

        <div class="card-body">
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Image</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>DOB</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($registrations as $index => $user)
                        <tr>
                            <td>{{ $index + 1 }}</td>

                            
                            <td>
                                @if($user->image && file_exists(public_path('storage/'.$user->image)))
                                    <img src="{{ asset('storage/'.$user->image) }}"
                                         alt="User Image"
                                         width="45"
                                         height="45"
                                         style="object-fit: cover; border-radius: 50%;">
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>

                            <td>{{ $user->full_name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->phone }}</td>
                            <td>{{ $user->dob }}</td>

                            <td>
                                <form action="{{ route('admin.registrations.delete', $user->id) }}"
                                      method="POST"
                                      onsubmit="return confirm('Are you sure you want to delete this user?')">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No users found
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
        </div>
    </div>

</div>

@endsection
