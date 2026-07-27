@extends('backend.master')
@section('title', 'Contact Messages')

@section('content')
<div class="container-fluid">

    @php
        $canDelete   = \Illuminate\Support\Facades\Gate::allows('contact_messages.delete');
        $columnCount = 5 + ($canDelete ? 1 : 0);
    @endphp

    <x-page-header
        title="Contact Messages"
        subtitle="Everything sent through the storefront contact form, newest first."
        icon="feather-life-buoy"
        :breadcrumb="['People' => null, 'Contact Messages' => null]" />

    <div class="card">
        <div class="card-header">
            <h5>Inbox</h5>
            <span class="text-muted fs-13">{{ $messages->total() }} messages</span>
        </div>

        <div class="table-scroll">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>From</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Received</th>
                        @if ($canDelete)
                            <th class="text-end">Actions</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse ($messages as $key => $msg)
                        @php
                            $displayName = $msg->name ?: 'Anonymous';
                            $initials    = mb_strtoupper(mb_substr(trim($displayName), 0, 2));
                        @endphp

                        <tr>
                            <td class="text-muted">{{ $messages->firstItem() + $key }}</td>

                            <td>
                                <div class="cell-media">
                                    <span class="avatar-initials sm">{{ $initials ?: '?' }}</span>
                                    <div>
                                        <p class="cell-title">{{ $displayName }}</p>
                                        <p class="cell-sub">{{ $msg->email ?: 'No email given' }}</p>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $msg->phone ?: '—' }}</td>

                            <td style="min-width:260px;max-width:420px">
                                <p class="mb-0 fs-13" style="white-space:pre-line">{{ $msg->message ?: '—' }}</p>
                            </td>

                            <td class="text-muted fs-13">
                                {{ $msg->created_at ? $msg->created_at->format('d M Y, h:i A') : '—' }}
                            </td>

                            @if ($canDelete)
                                <td class="text-end">
                                    <div class="action-group justify-content-end">
                                        @can('contact_messages.delete')
                                            <form action="{{ route('admin.aboutus.delete', $msg->id) }}"
                                                  method="POST" class="delete-form"
                                                  data-confirm-title="Delete this message?"
                                                  data-confirm-text="The message from {{ $displayName }} will be removed permanently.">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-icon btn-soft-danger"
                                                        title="Delete">
                                                    <i class="feather-trash-2"></i>
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ $columnCount }}" data-label="">
                                <x-empty-state icon="feather-mail"
                                               title="No messages yet"
                                               message="Nothing needs your attention right now. New enquiries from the contact form land here." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="table-foot">
                <p class="foot-note">
                    Showing {{ $messages->firstItem() }}–{{ $messages->lastItem() }} of {{ $messages->total() }}
                </p>
                {{ $messages->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
