@extends('backend.master')
@section('title', 'Password Reset Requests')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Password Reset Requests"
        subtitle="Admins who are not super admins cannot reset their own password. Approving a request emails them a signed, single-use reset link — never a password."
        icon="feather-key"
        :breadcrumb="['System' => null, 'Password Reset Requests' => null]">

        <a href="{{ route('admin.password-reset-requests.export', request()->query()) }}"
           class="btn btn-soft-primary">
            <i class="feather-download"></i> Export Excel
        </a>
        <a href="{{ route('admin.password-reset-requests.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
           class="btn btn-soft">CSV</a>
        <a href="{{ route('admin.password-reset-requests.export', array_merge(request()->query(), ['format' => 'pdf'])) }}"
           class="btn btn-soft" target="_blank" rel="noopener">PDF</a>
    </x-page-header>

    @include('backend.pages._partials.request-inbox', [
        'showTypeFilter' => false,
        'canManage'      => true,
        'canDelete'      => true,
        'approveLabel'   => 'Approve & send reset link',
        'approveConfirm' => 'A signed, single-use link will be emailed to the address on the account. '
                            . 'Their current password keeps working until they use it.',
    ])
</div>
@endsection
