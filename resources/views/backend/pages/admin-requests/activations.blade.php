@extends('backend.master')
@section('title', 'Admin Activation Requests')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Admin Activation Requests"
        subtitle="Admins whose accounts have been switched off, asking to be let back in. Approving reactivates the account and emails them."
        icon="feather-user-check"
        :breadcrumb="['System' => null, 'Activation Requests' => null]">

        <a href="{{ route('admin.admin-activation-requests.export', request()->query()) }}"
           class="btn btn-soft-primary">
            <i class="feather-download"></i> Export Excel
        </a>
        <a href="{{ route('admin.admin-activation-requests.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
           class="btn btn-soft">CSV</a>
        <a href="{{ route('admin.admin-activation-requests.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
           class="btn btn-soft" target="_blank" rel="noopener">PDF</a>
    </x-page-header>

    @include('backend.pages._partials.request-inbox', [
        'showTypeFilter' => false,
        'canManage'      => true,
        'canDelete'      => true,
        'approveLabel'   => 'Approve & reactivate',
        'approveConfirm' => 'The account will be switched back on immediately and the admin emailed.',
    ])
</div>
@endsection
