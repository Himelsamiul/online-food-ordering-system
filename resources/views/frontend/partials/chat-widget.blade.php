@php
    /**
     * Live support widget, on every storefront page.
     *
     * The signed-out state is rendered server-side rather than fetched, so a
     * visitor who is not logged in never has a transcript endpoint to call and
     * the panel has something useful to say the instant it opens.
     */
    $chatCustomer = auth('frontend')->user();
@endphp

<div id="sf-chat"
     class="sf-chat {{ $chatCustomer ? 'is-auth' : 'is-guest' }}"
     @if ($chatCustomer)
         data-poll-url="{{ route('chat.poll') }}"
         data-send-url="{{ route('chat.send') }}"
         data-active-ms="{{ (int) config('security.chat.poll.active_ms', 3000) }}"
         data-idle-ms="{{ (int) config('security.chat.poll.idle_ms', 20000) }}"
         data-max-length="{{ (int) config('security.chat.max_length', 2000) }}"
     @endif>

    <button type="button" class="sf-chat-launcher" aria-expanded="false" aria-controls="sf-chat-panel"
            aria-label="Open support chat">
        <i class="fa fa-comments sf-chat-launcher-open" aria-hidden="true"></i>
        <i class="fa fa-times sf-chat-launcher-close" aria-hidden="true"></i>
        <span class="sf-chat-dot" hidden>0</span>
    </button>

    <section id="sf-chat-panel" class="sf-chat-panel" role="dialog" aria-label="Support chat" hidden>

        <header class="sf-chat-head">
            <span class="sf-chat-avatar"><i class="fa fa-headphones" aria-hidden="true"></i></span>
            <div class="sf-chat-head-text">
                <strong>Feane Support</strong>
                <small class="sf-chat-status">We usually reply within a few minutes</small>
            </div>
            <button type="button" class="sf-chat-close" aria-label="Close chat">
                <i class="fa fa-chevron-down" aria-hidden="true"></i>
            </button>
        </header>

        @if ($chatCustomer)
            <div class="sf-chat-body" tabindex="0" aria-live="polite">
                <div class="sf-chat-loading">
                    <span class="sf-chat-spinner" aria-hidden="true"></span> Loading your conversation…
                </div>
            </div>

            <div class="sf-chat-alert" role="alert" hidden></div>

            <form class="sf-chat-composer" autocomplete="off">
                <label class="sr-only" for="sf-chat-input">Message</label>
                <textarea id="sf-chat-input" rows="1"
                          maxlength="{{ (int) config('security.chat.max_length', 2000) }}"
                          placeholder="Type your message…"></textarea>
                <button type="submit" class="sf-chat-send" aria-label="Send message">
                    <i class="fa fa-paper-plane" aria-hidden="true"></i>
                </button>
            </form>
            <p class="sf-chat-hint">Enter to send · Shift + Enter for a new line</p>
        @else
            <div class="sf-chat-gate">
                <span class="sf-chat-gate-icon"><i class="fa fa-lock" aria-hidden="true"></i></span>
                <h4>Sign in to chat with us</h4>
                <p>Chat is tied to your account so our team can see your orders and pick up where you left off.</p>
                <a href="{{ route('login') }}" class="sf-chat-gate-btn">Login</a>
                <a href="{{ route('register') }}" class="sf-chat-gate-link">Don't have an account? Register</a>
            </div>
        @endif

    </section>
</div>

@once
    @push('styles')
        <link rel="stylesheet" href="{{ asset('assets/css/chat-widget.css') }}">
    @endpush
    @push('scripts')
        <script src="{{ asset('assets/js/chat-widget.js') }}"></script>
    @endpush
@endonce
