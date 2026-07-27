@extends('backend.master')
@section('title', 'Customer Account Requests')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Customer Account Requests"
        subtitle="Customers who cannot sign in — either they need a password reset link or their account was switched off."
        icon="feather-inbox"
        :breadcrumb="['People' => null, 'Account Requests' => null]">

        <a href="{{ route('admin.account-requests.export', request()->query()) }}"
           class="btn btn-soft-primary">
            <i class="feather-download"></i> Export Excel
        </a>
        <a href="{{ route('admin.account-requests.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
           class="btn btn-soft">CSV</a>
        <a href="{{ route('admin.account-requests.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
           class="btn btn-soft" target="_blank" rel="noopener">PDF</a>
    </x-page-header>

    @include('backend.pages._partials.request-inbox', [
        'showTypeFilter' => true,
        'canManage'      => auth()->user()->hasPermission('account_requests.edit'),
        'canDelete'      => auth()->user()->hasPermission('account_requests.delete'),
        'approveLabel'   => 'Approve',
        'approveConfirm' => 'A password request emails a signed single-use reset link; '
                            . 'a reactivation request switches the account back on. Either way the customer is emailed.',
    ])
</div>
@endsection
