@extends('frontend.master')
@section('title', 'Notifications')

@section('content')
<section class="sf-page">
    <div class="container">

        <div class="sf-page-head">
            <div>
                <h2>Notifications</h2>
                <p>
                    @if ($unreadCount)
                        You have <strong>{{ $unreadCount }}</strong> unread {{ Str::plural('notification', $unreadCount) }}.
                    @else
                        You are all caught up.
                    @endif
                </p>
            </div>

            @if ($notifications->total())
                <div class="sf-page-actions">
                    @if ($unreadCount)
                        <form method="POST" action="{{ route('notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="sf-btn sf-btn-ghost">Mark all read</button>
                        </form>
                    @endif

                    <form method="POST" action="{{ route('notifications.clear') }}"
                          onsubmit="return confirm('Remove every notification? This cannot be undone.');">
                        @csrf
                        <button type="submit" class="sf-btn sf-btn-ghost sf-btn-danger">Clear all</button>
                    </form>
                </div>
            @endif
        </div>

        {{-- filters --}}
        @php
            $chip = fn (array $params) => route('notifications.index', array_filter(
                array_merge(['filter' => $filter, 'type' => $type], $params),
                fn ($v) => filled($v)
            ));
        @endphp

        <div class="sf-note-filters">
            <a href="{{ route('notifications.index') }}"
               class="sf-note-chip {{ !$filter && !$type ? 'active' : '' }}">All</a>
            <a href="{{ $chip(['filter' => 'unread', 'type' => null]) }}"
               class="sf-note-chip {{ $filter === 'unread' ? 'active' : '' }}">Unread</a>
            <a href="{{ $chip(['type' => 'order', 'filter' => null]) }}"
               class="sf-note-chip {{ $type === 'order' ? 'active' : '' }}">Orders</a>
            <a href="{{ $chip(['type' => 'chat', 'filter' => null]) }}"
               class="sf-note-chip {{ $type === 'chat' ? 'active' : '' }}">Support</a>
            <a href="{{ $chip(['type' => 'account', 'filter' => null]) }}"
               class="sf-note-chip {{ $type === 'account' ? 'active' : '' }}">Account</a>
        </div>

        <div class="sf-note-list">
            @forelse ($notifications as $note)
                <div class="sf-note-row {{ $note->isUnread() ? 'unread' : '' }}">
                    <a href="{{ route('notifications.open', $note->id) }}" class="sf-note-main">
                        <span class="sf-note-icon tone-{{ $note->tone ?: 'info' }}">
                            <i class="fa {{ $note->icon ?: 'fa-bell' }}" aria-hidden="true"></i>
                        </span>

                        <span class="sf-note-text">
                            <span class="sf-note-title">{{ $note->title }}</span>
                            @if ($note->body)
                                <span class="sf-note-body">{{ $note->body }}</span>
                            @endif
                            <span class="sf-note-time">{{ $note->created_at?->diffForHumans() }}</span>
                        </span>

                        @if ($note->isUnread())
                            <span class="sf-note-dot" title="Unread"></span>
                        @endif
                    </a>

                    <form method="POST" action="{{ route('notifications.delete', $note->id) }}"
                          class="sf-note-remove">
                        @csrf @method('DELETE')
                        <button type="submit" aria-label="Remove notification" title="Remove">
                            <i class="fa fa-times" aria-hidden="true"></i>
                        </button>
                    </form>
                </div>
            @empty
                <div class="sf-note-empty">
                    <i class="fa fa-bell-slash-o" aria-hidden="true"></i>
                    <h4>Nothing here yet</h4>
                    <p>Order updates, delivery progress and replies from our support team will show up here.</p>
                    <a href="{{ route('menu.index') }}" class="sf-btn sf-btn-solid">Browse the menu</a>
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="sf-note-pager">{{ $notifications->links() }}</div>
        @endif

    </div>
</section>
@endsection
