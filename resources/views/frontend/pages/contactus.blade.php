@extends('frontend.master')

@section('title', 'Contact Us')

@push('styles')
<style>
    .contact-page .sf-page-head { margin-bottom: 34px; }

    .contact-page .sf-page-head h2 {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 2.4rem;
        margin-bottom: 8px;
    }

    .contact-page .sf-page-head p {
        color: var(--sf-muted);
        max-width: 600px;
        margin: 0 auto;
    }

    .sf-eyebrow {
        display: inline-block;
        margin-bottom: 8px;
        color: var(--sf-accent);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
    }

    /* ---------- form panel ---------- */
    .contact-panel { padding: 32px; }

    .contact-panel h4 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 4px;
    }

    .contact-panel .panel-sub {
        color: var(--sf-muted);
        font-size: 14px;
        margin-bottom: 24px;
    }

    /* The template's .alert is built for a light page — repaint it. */
    .contact-page .alert {
        border-radius: var(--sf-radius-sm);
        border: 1px solid transparent;
        font-size: 14.5px;
    }

    .contact-page .alert-success {
        background: rgba(29, 191, 115, .16);
        border-color: rgba(29, 191, 115, .4);
        color: var(--sf-ink);
    }

    .contact-page .alert-danger {
        background: rgba(231, 76, 60, .16);
        border-color: rgba(231, 76, 60, .42);
        color: var(--sf-ink);
    }

    .contact-page .alert i { color: var(--sf-accent); }

    .contact-page .form-group { margin-bottom: 18px; }

    .contact-page label {
        display: block;
        margin-bottom: 8px;
        font-size: 13.5px;
    }

    .contact-page .invalid-feedback { font-size: 13px; }

    .contact-submit {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        width: 100%;
        padding: 13px 26px;
        border: none;
        border-radius: 30px;
        background: linear-gradient(135deg, var(--sf-green-dark), var(--sf-green));
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 15px;
        transition: transform .18s, box-shadow .18s;
    }

    .contact-submit:hover,
    .contact-submit:focus {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(29, 191, 115, .4);
        color: var(--sf-ink);
        outline: none;
    }

    .form-foot-note {
        margin: 14px 0 0;
        color: var(--sf-faint);
        font-size: 12.5px;
        text-align: center;
    }

    /* ---------- detail cards ---------- */
    .detail-panel { padding: 26px 24px; }
    .detail-panel + .detail-panel { margin-top: 18px; }

    .detail-panel h5 {
        color: var(--sf-ink);
        font-weight: 700;
        font-size: 17px;
        margin-bottom: 18px;
    }

    .contact-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .contact-list a,
    .contact-list div.contact-row {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        color: rgba(255, 255, 255, .8);
        text-decoration: none;
        transition: color .18s;
    }

    .contact-list a:hover { color: var(--sf-accent); text-decoration: none; }

    .contact-ico {
        flex: 0 0 42px;
        width: 42px;
        height: 42px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        color: var(--sf-accent);
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
    }

    .contact-list strong {
        display: block;
        color: var(--sf-ink);
        font-size: 15px;
        font-weight: 600;
        line-height: 1.4;
    }

    .contact-list span.contact-sub {
        display: block;
        color: var(--sf-faint);
        font-size: 12.5px;
        margin-top: 2px;
    }

    /* ---------- opening hours ---------- */
    .hours-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .hours-list li {
        display: flex;
        justify-content: space-between;
        gap: 14px;
        padding: 10px 0;
        border-bottom: 1px dashed rgba(255, 255, 255, .12);
        color: var(--sf-muted);
        font-size: 14.5px;
    }

    .hours-list li:last-child { border-bottom: none; }
    .hours-list li span:last-child { color: var(--sf-ink); font-weight: 600; }

    .hours-note {
        margin: 16px 0 0;
        padding: 12px 14px;
        border-radius: var(--sf-radius-sm);
        background: rgba(241, 184, 22, .1);
        border: 1px solid rgba(241, 184, 22, .3);
        color: rgba(255, 255, 255, .84);
        font-size: 13px;
        line-height: 1.6;
    }

    .hours-note strong { color: var(--sf-accent); }

    /* ---------- social ---------- */
    .contact-social {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
    }

    .contact-social a {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-ink);
        transition: background-color .18s, color .18s, transform .18s;
    }

    .contact-social a:hover {
        background: var(--sf-accent);
        border-color: transparent;
        color: rgba(0, 0, 0, .85);
        transform: translateY(-3px);
        text-decoration: none;
    }

    @media (max-width: 991.98px) {
        .contact-page .contact-side { margin-top: 24px; }
    }

    @media (max-width: 767.98px) {
        .contact-page .sf-page-head h2 { font-size: 1.95rem; }
        .contact-panel { padding: 22px; }
    }
</style>
@endpush

@section('content')

<section class="contact-section contact-page layout_padding">
    <div class="container">

        <div class="sf-page-head text-center">
            <span class="sf-eyebrow">We are listening</span>
            <h2>Contact us</h2>
            <p>
                A question about an order, an allergy to flag, or something we got wrong —
                send it over and we will come back to you.
            </p>
        </div>

        <div class="row">

            {{-- ================= FORM ================= --}}
            <div class="col-lg-7">
                <div class="contact-panel glass-card">

                    <h4>Send us a message</h4>
                    <p class="panel-sub">We usually reply within one working day.</p>

                    @if (session('success'))
                        <div class="alert alert-success" role="status">
                            <i class="fa fa-check-circle mr-2" aria-hidden="true"></i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <i class="fa fa-exclamation-circle mr-2" aria-hidden="true"></i>
                            Please check the fields marked below and try again.
                        </div>
                    @endif

                    <form method="POST" action="{{ route('contact.store') }}">
                        @csrf

                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="contactName">
                                    Your name <span class="sf-optional">(optional)</span>
                                </label>
                                <input type="text"
                                       id="contactName"
                                       name="name"
                                       value="{{ old('name') }}"
                                       maxlength="100"
                                       class="form-control @error('name') is-invalid @enderror"
                                       placeholder="So we know who we are talking to">
                                @error('name')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="form-group col-md-6">
                                <label for="contactPhone">
                                    Phone <span class="sf-req">*</span>
                                </label>
                                <input type="text"
                                       id="contactPhone"
                                       name="phone"
                                       value="{{ old('phone') }}"
                                       maxlength="20"
                                       required
                                       class="form-control @error('phone') is-invalid @enderror"
                                       placeholder="01XXXXXXXXX">
                                @error('phone')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contactEmail">
                                Email <span class="sf-req">*</span>
                            </label>
                            <input type="email"
                                   id="contactEmail"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="you@example.com">
                            @error('email')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <span class="sf-hint">This is where our reply will land.</span>
                        </div>

                        <div class="form-group">
                            <label for="contactMessage">
                                Your message <span class="sf-optional">(optional)</span>
                            </label>
                            <textarea id="contactMessage"
                                      name="message"
                                      rows="5"
                                      maxlength="1000"
                                      class="form-control @error('message') is-invalid @enderror"
                                      placeholder="Tell us what happened, or what you would like to know.">{{ old('message') }}</textarea>
                            @error('message')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror
                            <span class="sf-hint">Up to 1000 characters.</span>
                        </div>

                        <button type="submit" class="contact-submit">
                            <i class="fa fa-paper-plane" aria-hidden="true"></i>
                            Send message
                        </button>

                        <p class="form-foot-note">
                            We only use your details to answer this message.
                        </p>
                    </form>

                </div>
            </div>

            {{-- ================= DETAILS ================= --}}
            <div class="col-lg-5 contact-side">

                <div class="detail-panel glass-card">
                    <h5>Where to find us</h5>

                    <div class="contact-list">
                        <div class="contact-row">
                            <span class="contact-ico">
                                <i class="fa fa-map-marker" aria-hidden="true"></i>
                            </span>
                            <span>
                                <strong>24 Gulshan Avenue</strong>
                                <span class="contact-sub">Dhaka 1212, Bangladesh</span>
                            </span>
                        </div>

                        <a href="tel:+8801700000000">
                            <span class="contact-ico">
                                <i class="fa fa-phone" aria-hidden="true"></i>
                            </span>
                            <span>
                                <strong>+880 1700 000000</strong>
                                <span class="contact-sub">Kitchen line, 10:00 – 22:00</span>
                            </span>
                        </a>

                        <a href="mailto:support@feane.com">
                            <span class="contact-ico">
                                <i class="fa fa-envelope" aria-hidden="true"></i>
                            </span>
                            <span>
                                <strong>support@feane.com</strong>
                                <span class="contact-sub">Orders, refunds and everything else</span>
                            </span>
                        </a>
                    </div>
                </div>

                <div class="detail-panel glass-card">
                    <h5>Opening hours</h5>

                    <ul class="hours-list">
                        <li><span>Monday – Friday</span><span>10:00 – 22:00</span></li>
                        <li><span>Saturday</span><span>10:00 – 22:00</span></li>
                        <li><span>Sunday</span><span>10:00 – 22:00</span></li>
                    </ul>

                    <p class="hours-note">
                        <strong>Last orders 21:30.</strong>
                        Anything sent after closing is answered first thing the next morning.
                    </p>
                </div>

                <div class="detail-panel glass-card">
                    <h5>Somewhere else to look</h5>

                    <div class="contact-list mb-4">
                        <a href="{{ route('menu.index') }}">
                            <span class="contact-ico">
                                <i class="fa fa-cutlery" aria-hidden="true"></i>
                            </span>
                            <span>
                                <strong>The full menu</strong>
                                <span class="contact-sub">Prices, discounts and what is in stock</span>
                            </span>
                        </a>

                        <a href="{{ route('about') }}">
                            <span class="contact-ico">
                                <i class="fa fa-info-circle" aria-hidden="true"></i>
                            </span>
                            <span>
                                <strong>About Feane</strong>
                                <span class="contact-sub">How we cook and how we deliver</span>
                            </span>
                        </a>

                        @auth('frontend')
                            <a href="{{ route('profile') }}">
                                <span class="contact-ico">
                                    <i class="fa fa-list-alt" aria-hidden="true"></i>
                                </span>
                                <span>
                                    <strong>Your orders</strong>
                                    <span class="contact-sub">Check a status before you write to us</span>
                                </span>
                            </a>
                        @endauth

                        @guest('frontend')
                            <a href="{{ route('login') }}">
                                <span class="contact-ico">
                                    <i class="fa fa-sign-in" aria-hidden="true"></i>
                                </span>
                                <span>
                                    <strong>Sign in</strong>
                                    <span class="contact-sub">Track an order from your profile</span>
                                </span>
                            </a>
                        @endguest
                    </div>

                    <div class="contact-social">
                        <a href="#" aria-label="Facebook"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                        <a href="#" aria-label="Twitter"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                        <a href="#" aria-label="Instagram"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                        <a href="#" aria-label="LinkedIn"><i class="fa fa-linkedin" aria-hidden="true"></i></a>
                    </div>
                </div>

            </div>

        </div>

    </div>
</section>

@endsection
