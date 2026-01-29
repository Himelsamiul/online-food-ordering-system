@extends('backend.master')

@section('content')

<!-- MAIN CONTENT ONLY -->
<!--<div class="main-content">-->

    <div class="row">
        <div class="col-12">

            <div class="card stretch stretch-full mt-3">
                <div class="card-header">
                    <h5 class="mb-0">User List</h5>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center">
                            <thead class="table-dark">
                                <tr>
                                    <th>#</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td>himel@example.com</td>
                                    <td>Admin</td>
                                    <td>
                                        <span class="badge bg-success">Active</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>

                                <tr>
                                    <td>2</td>
                                    <td>rahim@example.com</td>
                                    <td>User</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">Pending</span>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary">Edit</button>
                                        <button class="btn btn-sm btn-danger">Delete</button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

 <!-- </div>-->
<!-- MAIN CONTENT END -->



@endsection
