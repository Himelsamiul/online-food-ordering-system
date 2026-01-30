@extends('frontend.master')

@section('content')

<section class="profile-section py-5">
    <div class="container">

        {{-- PROFILE CARD (CENTERED) --}}
        <div class="row justify-content-center mb-5">
            <div class="col-lg-5 col-md-7 col-sm-10">
                <div class="card glass-card shadow-lg p-4 text-center">

                    {{-- USER IMAGE / ICON --}}
                    <div class="mb-3">
                        @if($user->image && file_exists(public_path('storage/'.$user->image)))
                            <img src="{{ asset('storage/'.$user->image) }}"
                                 class="rounded-circle mb-2"
                                 width="130"
                                 height="130"
                                 style="object-fit: cover;">
                        @else
                            <i class="fa fa-user-circle text-secondary"
                               style="font-size:130px;"></i>
                        @endif
                    </div>

                    <h4 class="mb-0">{{ $user->full_name }}</h4>
                    <small class="text-muted">{{ '@'.$user->username }}</small>

                    <hr>

                    <p class="mb-1"><strong>Email:</strong> {{ $user->email }}</p>
                    <p class="mb-1"><strong>Phone:</strong> {{ $user->phone }}</p>
                    <p class="mb-1"><strong>Address:</strong> {{ $user->address }}</p>
                    <p class="mb-0"><strong>DOB:</strong> {{ $user->dob }}</p>
                        {{-- EDIT BUTTON --}}
    <hr>
    <a href="{{ route('profile.edit') }}" class="btn btn-outline-light mt-2">
        Edit Profile
    </a>
                </div>
            </div>
        </div>



        {{-- ORDER HISTORY (FULL WIDTH BELOW PROFILE) --}}
        <div class="row">
            <div class="col-12">
                <div class="card glass-card shadow-lg p-4">

                    <h4 class="mb-3">Order History</h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Order ID</th>
                                    <th>Date</th>
                                    <th>Total (৳)</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>ORD-1001</td>
                                    <td>2026-01-10</td>
                                    <td>850</td>
                                    <td><span class="badge bg-success">Completed</span></td>
                                </tr>
                                <tr>
                                    <td>ORD-1002</td>
                                    <td>2026-01-18</td>
                                    <td>420</td>
                                    <td><span class="badge bg-warning text-dark">Pending</span></td>
                                </tr>
                                <tr>
                                    <td>ORD-1003</td>
                                    <td>2026-01-25</td>
                                    <td>1290</td>
                                    <td><span class="badge bg-secondary">Cancelled</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted">
                        * Order history is demo data for future implementation.
                    </small>
                </div>
            </div>
        </div>

    </div>
</section>



@endsection
