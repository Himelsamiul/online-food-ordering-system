@extends('backend.master')
@section('title', 'Live Chat')

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/css/admin-chat.css') }}">
@endpush

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Live Chat"
        subtitle="Customer support conversations. Each customer has one thread; replying reopens a resolved one."
        icon="feather-message-circle"
        :breadcrumb="['Support' => null, 'Live Chat' => null]" />

    <div class="chat-shell"
         data-poll-url="{{ route('admin.chat.poll') }}"
         data-active-id="{{ $active?->id ?? '' }}"
         data-last-id="{{ $messages->last()?->id ?? 0 }}"
         data-poll-ms="{{ (int) config('security.chat.poll.active_ms', 3000) }}">

        {{-- ------------------------------------------------ conversation list --}}
        <aside class="chat-list">

            <form method="GET" action="{{ route('admin.chat.index') }}" class="chat-list-filters">
                <div class="chat-search">
                    <i class="feather-search"></i>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                           placeholder="Search name, email or phone" aria-label="Search conversations">
                </div>

                <div class="chat-chips">
                    @php
                        $statusNow = $filters['status'] ?? '';
                        $unreadNow = request()->boolean('unread');
                        $chip = fn (array $params) => route('admin.chat.index', array_filter(
                            array_merge(request()->only('q'), $params),
                            fn ($v) => $v !== null && $v !== '' && $v !== false
                        ));
                    @endphp

                    <a href="{{ $chip([]) }}"
                       class="chat-chip {{ !$statusNow && !$unreadNow ? 'active' : '' }}">All</a>
                    <a href="{{ $chip(['unread' => 1]) }}"
                       class="chat-chip {{ $unreadNow ? 'active' : '' }}">Unread</a>
                    <a href="{{ $chip(['status' => 'open']) }}"
                       class="chat-chip {{ $statusNow === 'open' ? 'active' : '' }}">Open</a>
                    <a href="{{ $chip(['status' => 'closed']) }}"
                       class="chat-chip {{ $statusNow === 'closed' ? 'active' : '' }}">Resolved</a>
                </div>
            </form>

            <div class="chat-threads" id="chat-threads">
                @forelse ($conversations as $conversation)
                    @php $isActive = $active && $active->id === $conversation->id; @endphp

                    <a href="{{ route('admin.chat.index', array_merge(request()->only('q', 'status', 'unread'), ['conversation' => $conversation->id])) }}"
                       class="chat-thread {{ $isActive ? 'active' : '' }} {{ $conversation->admin_unread ? 'unread' : '' }}"
                       data-thread="{{ $conversation->id }}">

                        <span class="chat-thread-avatar">
                            {{ strtoupper(mb_substr($conversation->customer?->full_name ?? '?', 0, 1)) }}
                        </span>

                        <span class="chat-thread-main">
                            <span class="chat-thread-top">
                                <strong>{{ $conversation->customer?->full_name ?? 'Deleted customer' }}</strong>
                                <small>{{ optional($conversation->last_message_at ?? $conversation->created_at)->diffForHumans(null, true) }}</small>
                            </span>
                            <span class="chat-thread-preview">
                                @if ($conversation->last_message_from === 'admin')
                                    <i class="feather-corner-up-left"></i>
                                @endif
                                {{ $conversation->last_message_preview ?: 'No messages yet' }}
                            </span>
                        </span>

                        <span class="chat-thread-meta">
                            @if ($conversation->admin_unread)
                                <span class="chat-thread-badge">{{ $conversation->admin_unread }}</span>
                            @endif
                            @if ($conversation->isClosed())
                                <span class="chat-thread-closed" title="Resolved"><i class="feather-check-circle"></i></span>
                            @endif
                        </span>
                    </a>
                @empty
                    <div class="chat-list-empty">
                        <i class="feather-message-square"></i>
                        <p>No conversations match this filter.</p>
                    </div>
                @endforelse
            </div>

            @if ($conversations->hasPages())
                <div class="chat-list-pager">{{ $conversations->links() }}</div>
            @endif
        </aside>

        {{-- ---------------------------------------------------- message pane --}}
        <section class="chat-pane">
            @if (!$active)
                <div class="chat-pane-empty">
                    <x-empty-state
                        icon="feather-message-circle"
                        title="No conversation selected"
                        message="Pick a customer on the left, or wait for someone to start a chat from the storefront." />
                </div>
            @else
                <header class="chat-pane-head">
                    <span class="chat-pane-avatar">
                        {{ strtoupper(mb_substr($active->customer?->full_name ?? '?', 0, 1)) }}
                    </span>

                    <div class="chat-pane-who">
                        <strong>{{ $active->customer?->full_name ?? 'Deleted customer' }}</strong>
                        <small>
                            {{ $active->customer?->email }}
                            @if ($active->customer?->phone)
                                · {{ $active->customer->phone }}
                            @endif
                        </small>
                    </div>

                    <div class="chat-pane-actions">
                        <span class="status-pill {{ $active->isOpen() ? 'on' : 'off' }}" id="chat-status-pill">
                            {{ $active->isOpen() ? 'Open' : 'Resolved' }}
                        </span>

                        {{-- Gated: a link that only ever 403s is worse than no link. --}}
                        @if ($active->customer)
                            @can('customers.view')
                                <a href="{{ route('admin.registrations', ['q' => $active->customer->email]) }}"
                                   class="btn btn-soft btn-sm" title="Open customer record">
                                    <i class="feather-user"></i>
                                </a>
                            @endcan
                        @endif

                        @if ($canManage)
                            <form method="POST" action="{{ route('admin.chat.status', $active) }}" class="d-inline">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status" value="{{ $active->isOpen() ? 'closed' : 'open' }}">
                                <button type="submit" class="btn btn-sm {{ $active->isOpen() ? 'btn-soft-success' : 'btn-soft-warning' }}">
                                    <i class="feather-{{ $active->isOpen() ? 'check' : 'rotate-ccw' }}"></i>
                                    {{ $active->isOpen() ? 'Mark resolved' : 'Reopen' }}
                                </button>
                            </form>
                        @endif

                        @if ($canDelete)
                            <form method="POST" action="{{ route('admin.chat.delete', $active) }}"
                                  class="delete-form d-inline"
                                  data-confirm-title="Delete this conversation?"
                                  data-confirm-text="The whole transcript with {{ $active->customer?->full_name ?? 'this customer' }} is removed permanently."
                                  data-confirm-button="Yes, delete it">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-soft-danger btn-sm" title="Delete conversation">
                                    <i class="feather-trash-2"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </header>

                <div class="chat-transcript" id="chat-transcript">
                    @php $lastDay = null; @endphp

                    @forelse ($messages as $message)
                        @php $day = $message->created_at?->format('d M Y'); @endphp

                        @if ($day && $day !== $lastDay)
                            @php $lastDay = $day; @endphp
                            <div class="chat-day">{{ $day }}</div>
                        @endif

                        <div class="chat-bubble {{ $message->isFromCustomer() ? 'from-customer' : ($message->isSystem() ? 'from-system' : 'from-admin') }}"
                             data-id="{{ $message->id }}">
                            @if ($message->isFromAdmin() && $message->sender_name)
                                <span class="chat-bubble-name">{{ $message->sender_name }}</span>
                            @endif
                            {{ $message->body }}
                            <span class="chat-bubble-time">{{ $message->created_at?->format('g:i A') }}</span>
                        </div>
                    @empty
                        <div class="chat-transcript-empty">
                            <i class="feather-message-circle"></i>
                            <p>No messages in this conversation yet.</p>
                        </div>
                    @endforelse
                </div>

                <div class="chat-composer-alert" role="alert" hidden></div>

                @if ($canReply)
                    <form class="chat-composer" id="chat-composer"
                          data-url="{{ route('admin.chat.send', $active) }}"
                          autocomplete="off">
                        <textarea id="chat-input" rows="1"
                                  maxlength="{{ (int) config('security.chat.max_length', 2000) }}"
                                  placeholder="Write a reply… (Enter to send, Shift + Enter for a new line)"
                                  aria-label="Reply"></textarea>
                        <button type="submit" class="btn btn-primary chat-send">
                            <i class="feather-send"></i> Send
                        </button>
                    </form>
                @else
                    <div class="chat-composer-locked">
                        <i class="feather-lock"></i>
                        You have read-only access to the support inbox. Ask a super admin for the
                        <strong>Reply to customers in the chat</strong> permission to answer.
                    </div>
                @endif
            @endif
        </section>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('assets/js/admin-chat.js') }}"></script>
@endpush
