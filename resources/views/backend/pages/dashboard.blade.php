@extends('backend.master')

@section('content')

<!-- ================= Page Header ================= -->
<div class="page-header">
    <div class="page-header-left d-flex align-items-center">
        <div class="page-header-title">
            <h5 class="m-b-10">Dashboard</h5>
        </div>
        <ul class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="#">Home</a>
            </li>
            <li class="breadcrumb-item">
                Dashboard
            </li>
        </ul>
    </div>

    <div class="page-header-right ms-auto">
        <div class="page-header-right-items">
            <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                <div id="reportrange" class="reportrange-picker d-flex align-items-center">
                    <span class="reportrange-picker-field"></span>
                </div>
                <div class="dropdown filter-dropdown">
                    <a class="btn btn-md btn-light-brand" data-bs-toggle="dropdown" data-bs-offset="0, 10">
                        <i class="feather-filter me-2"></i>
                        <span>Filter</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ================= Page Header End ================= -->


<!-- ================= Main Content ================= -->
<div class="main-content">
    <div class="row">

        <!-- Invoices Awaiting -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="avatar-text avatar-lg bg-gray-200">
                                <i class="feather-dollar-sign"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-dark">
                                    <span class="counter">45</span>/<span class="counter">76</span>
                                </div>
                                <h3 class="fs-13 fw-semibold text-truncate-1-line">
                                    Invoices Awaiting Payment
                                </h3>
                            </div>
                        </div>
                        <i class="feather-more-vertical"></i>
                    </div>
                    <div class="pt-4">
                        <div class="d-flex justify-content-between">
                            <span class="fs-12 text-muted">Invoices Awaiting</span>
                            <span class="fs-12 text-dark">$5,569 (56%)</span>
                        </div>
                        <div class="progress mt-2 ht-3">
                            <div class="progress-bar bg-primary" style="width:56%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Converted Leads -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="avatar-text avatar-lg bg-gray-200">
                                <i class="feather-cast"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-dark">
                                    <span class="counter">48</span>/<span class="counter">86</span>
                                </div>
                                <h3 class="fs-13 fw-semibold text-truncate-1-line">
                                    Converted Leads
                                </h3>
                            </div>
                        </div>
                        <i class="feather-more-vertical"></i>
                    </div>
                    <div class="pt-4">
                        <div class="d-flex justify-content-between">
                            <span class="fs-12 text-muted">Completed</span>
                            <span class="fs-12 text-dark">63%</span>
                        </div>
                        <div class="progress mt-2 ht-3">
                            <div class="progress-bar bg-warning" style="width:63%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="avatar-text avatar-lg bg-gray-200">
                                <i class="feather-briefcase"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-dark">
                                    <span class="counter">16</span>/<span class="counter">20</span>
                                </div>
                                <h3 class="fs-13 fw-semibold text-truncate-1-line">
                                    Projects In Progress
                                </h3>
                            </div>
                        </div>
                        <i class="feather-more-vertical"></i>
                    </div>
                    <div class="pt-4">
                        <div class="progress mt-2 ht-3">
                            <div class="progress-bar bg-success" style="width:78%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        
        <!-- Conversion Rate -->
        <div class="col-xxl-3 col-md-6">
            <div class="card stretch stretch-full">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between mb-4">
                        <div class="d-flex gap-4 align-items-center">
                            <div class="avatar-text avatar-lg bg-gray-200">
                                <i class="feather-activity"></i>
                            </div>
                            <div>
                                <div class="fs-4 fw-bold text-dark">
                                    <span class="counter">46.59</span>%
                                </div>
                                <h3 class="fs-13 fw-semibold text-truncate-1-line">
                                    Conversion Rate
                                </h3>
                            </div>
                        </div>
                        <i class="feather-more-vertical"></i>
                    </div>
                    <div class="progress mt-2 ht-3">
                        <div class="progress-bar bg-danger" style="width:46%"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
<!-- ================= Main Content End ================= -->

@endsection
