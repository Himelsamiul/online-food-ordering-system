@php
    /**
     * Live support widget, on every storefront page.
     *
     * Messenger-shaped: a light thread panel that slides up from the launcher,
     * grouped bubbles with an agent avatar on the last of each run, and a
     * composer pinned to the bottom.
     *
     * The signed-out state is rendered server-side rather than fetched, so a
     * visitor who is not logged in never has a transcript endpoint to call and
     * the panel has something useful to say the instant it opens.
     */
    $chatCustomer = auth('frontend')->user();
    $chatInitial  = $chatCustomer ? mb_strtoupper(mb_substr($chatCustomer->full_name, 0, 1)) : '?';
@endphp

<div id="sf-chat"
     class="sf-chat {{ $chatCustomer ? 'is-auth' : 'is-guest' }}"
     @if ($chatCustomer)
         data-poll-url="{{ route('chat.poll') }}"
         data-send-url="{{ route('chat.send') }}"
         data-active-ms="{{ (int) config('security.chat.poll.active_ms', 3000) }}"
         data-idle-ms="{{ (int) config('security.chat.poll.idle_ms', 20000) }}"
         data-max-length="{{ (int) config('security.chat.max_length', 2000) }}"
         data-initial="{{ $chatInitial }}"
     @endif>

    {{-- ----------------------------------------------------------- launcher --}}
    <button type="button" class="sf-chat-launcher" aria-expanded="false" aria-controls="sf-chat-panel"
            aria-label="Open support chat">
        <svg class="sf-chat-launcher-open" viewBox="0 0 24 24" width="26" height="26"
             fill="currentColor" aria-hidden="true">
            <path d="M12 2C6.48 2 2 6.13 2 11.2c0 2.66 1.24 5.05 3.23 6.72-.14 1.2-.6 2.6-1.68 3.66-.2.2-.07.53.2.5 2.2-.2 3.9-1.1 4.94-1.85 1.03.3 2.15.47 3.31.47 5.52 0 10-4.13 10-9.2S17.52 2 12 2z"/>
        </svg>
        <svg class="sf-chat-launcher-close" viewBox="0 0 24 24" width="22" height="22"
             fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
            <path d="M6 6l12 12M18 6L6 18"/>
        </svg>
        <span class="sf-chat-dot" hidden>0</span>
    </button>

    {{-- -------------------------------------------------------------- panel --}}
    <section id="sf-chat-panel" class="sf-chat-panel" role="dialog" aria-label="Support chat" hidden>

        <header class="sf-chat-head">
            <span class="sf-chat-avatar">
                <i class="fa fa-cutlery" aria-hidden="true"></i>
                <span class="sf-chat-presence" aria-hidden="true"></span>
            </span>

            <div class="sf-chat-head-text">
                <strong>Feane Support</strong>
                <small class="sf-chat-status">Typically replies within a few minutes</small>
            </div>

            <button type="button" class="sf-chat-close" aria-label="Close chat">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor"
                     stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
            </button>
        </header>

        @if ($chatCustomer)
            <div class="sf-chat-body" tabindex="0" aria-live="polite">
                <div class="sf-chat-loading">
                    <span class="sf-chat-spinner" aria-hidden="true"></span>
                </div>
            </div>

            {{-- Appears only when the user has scrolled up and something new lands. --}}
            <button type="button" class="sf-chat-jump" hidden aria-label="Jump to newest messages">
                <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor"
                     stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M6 9l6 6 6-6"/>
                </svg>
                New messages
            </button>

            <div class="sf-chat-alert" role="alert" hidden></div>

            <form class="sf-chat-composer" autocomplete="off">
                <label class="sr-only" for="sf-chat-input">Message</label>
                <div class="sf-chat-input-wrap">
                    <textarea id="sf-chat-input" rows="1"
                              maxlength="{{ (int) config('security.chat.max_length', 2000) }}"
                              placeholder="Write a message…"></textarea>
                </div>
                <button type="submit" class="sf-chat-send" aria-label="Send message" disabled>
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="currentColor" aria-hidden="true">
                        <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                    </svg>
                </button>
            </form>
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

{{-- The stylesheet is linked in master.blade.php's <head>; a @push from here
     would land after @stack('styles') had already rendered. --}}
@once
    @push('scripts')
        <script src="{{ asset('assets/js/chat-widget.js') }}"></script>
    @endpush
@endonce
