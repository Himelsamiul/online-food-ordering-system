{{--
    Site footer. Included from frontend.master on every customer-facing page,
    so it carries its own styling rather than relying on a page-level @push.

    #displayYear must stay: feane-1.0.0/js/custom.js fills it with the current
    year via a single querySelector, so there is exactly one of them here.
--}}
<style>
    /* ---------------------------------------------------------------
       The template painted this band a flat slate plate and centred
       everything. The page already sits on a dark scrim, so the footer
       becomes a translucent slab that carries on the glass treatment.
       --------------------------------------------------------------- */
    .footer_section {
        background: rgba(0, 0, 0, .55);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border-top: 1px solid var(--sf-glass-line);
        color: var(--sf-ink);
        text-align: left;
        padding: 62px 0 26px;
        margin-top: 40px;
    }

    .footer_section p { color: var(--sf-muted); }

    .footer_section .footer-col { margin-bottom: 30px; }

    /* ---------- brand ---------- */
    .footer_section .sf-foot-logo {
        display: inline-block;
        font-family: 'Dancing Script', cursive;
        font-size: 38px;
        font-weight: 700;
        line-height: 1;
        color: var(--sf-ink);
        text-decoration: none;
        margin-bottom: 14px;
    }

    .footer_section .sf-foot-logo:hover {
        color: var(--sf-accent);
        text-decoration: none;
    }

    .sf-foot-blurb {
        max-width: 340px;
        font-size: 14.5px;
        line-height: 1.75;
        margin-bottom: 18px;
    }

    .sf-foot-trust {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        margin-bottom: 20px;
    }

    .sf-foot-trust span {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 5px 12px;
        border-radius: 20px;
        background: rgba(255, 255, 255, .07);
        border: 1px solid var(--sf-glass-line);
        color: rgba(255, 255, 255, .78);
        font-size: 12px;
        font-weight: 600;
        line-height: 1.5;
    }

    .sf-foot-trust i { color: var(--sf-accent); }

    /* ---------- column headings ---------- */
    .footer_section .sf-foot-col h4 {
        font-family: inherit;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .16em;
        text-transform: uppercase;
        color: var(--sf-accent);
        margin-bottom: 18px;
    }

    /* ---------- link lists ---------- */
    .sf-foot-links {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .sf-foot-links li { margin-bottom: 11px; }

    .sf-foot-links a {
        display: inline-flex;
        align-items: center;
        gap: 9px;
        color: rgba(255, 255, 255, .74);
        font-size: 14.5px;
        text-decoration: none;
        transition: color .18s, transform .18s;
    }

    .sf-foot-links a:hover,
    .sf-foot-links a:focus {
        color: var(--sf-accent);
        transform: translateX(3px);
        text-decoration: none;
    }

    .sf-foot-links i {
        width: 14px;
        font-size: 12px;
        color: var(--sf-faint);
        transition: color .18s;
    }

    .sf-foot-links a:hover i { color: var(--sf-accent); }

    /* ---------- contact rows ---------- */
    .sf-foot-contact {
        display: flex;
        flex-direction: column;
        gap: 14px;
    }

    .sf-foot-contact a,
    .sf-foot-contact div {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        color: rgba(255, 255, 255, .74);
        font-size: 14.5px;
        line-height: 1.55;
        text-decoration: none;
        transition: color .18s;
    }

    .sf-foot-contact a:hover {
        color: var(--sf-accent);
        text-decoration: none;
    }

    .sf-foot-ico {
        flex: 0 0 34px;
        width: 34px;
        height: 34px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        color: var(--sf-accent);
        background: rgba(255, 255, 255, .07);
        border: 1px solid var(--sf-glass-line);
    }

    .sf-foot-contact strong {
        display: block;
        color: var(--sf-ink);
        font-size: 14.5px;
        font-weight: 600;
    }

    .sf-foot-contact small {
        display: block;
        color: var(--sf-faint);
        font-size: 12px;
    }

    /* ---------- opening hours ---------- */
    .sf-foot-hours {
        list-style: none;
        padding: 0;
        margin: 0 0 18px;
    }

    .sf-foot-hours li {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 0;
        border-bottom: 1px dashed rgba(255, 255, 255, .12);
        color: var(--sf-muted);
        font-size: 14px;
    }

    .sf-foot-hours li:last-child { border-bottom: none; }
    .sf-foot-hours li span:last-child { color: var(--sf-ink); font-weight: 600; }

    /* ---------- social ---------- */
    .footer_section .sf-foot-social {
        display: flex;
        flex-wrap: wrap;
        justify-content: flex-start;
        gap: 10px;
        margin: 0;
    }

    .footer_section .sf-foot-social a {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, .08);
        border: 1px solid var(--sf-glass-line);
        color: var(--sf-ink);
        font-size: 15px;
        margin: 0;
        transition: background-color .18s, color .18s, transform .18s;
    }

    .footer_section .sf-foot-social a:hover {
        background: var(--sf-accent);
        border-color: transparent;
        color: rgba(0, 0, 0, .85);
        transform: translateY(-3px);
    }

    /* ---------- bottom bar ---------- */
    .footer_section .footer-info {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 10px 24px;
        margin-top: 14px;
        padding-top: 22px;
        border-top: 1px solid var(--sf-glass-line);
        text-align: left;
    }

    .footer_section .footer-info p {
        margin: 0;
        color: var(--sf-faint);
        font-size: 13px;
    }

    .footer_section .footer-info a {
        color: rgba(255, 255, 255, .68);
        text-decoration: none;
    }

    .footer_section .footer-info a:hover { color: var(--sf-accent); }

    @media (max-width: 767.98px) {
        .footer_section { padding: 46px 0 22px; }
        .sf-foot-blurb { max-width: none; }
        .footer_section .footer-info { justify-content: center; text-align: center; }
    }
</style>

<footer class="footer_section">
    <div class="container">
        <div class="row">

            {{-- ---------------- brand ---------------- --}}
            <div class="col-lg-3 col-md-6 footer-col sf-foot-col">
                <a href="{{ route('home') }}" class="sf-foot-logo">Feane</a>

                <p class="sf-foot-blurb">
                    A small kitchen with a short menu. Everything is cooked to order, packed
                    the moment it comes off the heat, and sent straight to your door.
                </p>

                <div class="sf-foot-trust">
                    <span><i class="fa fa-credit-card" aria-hidden="true"></i> Card or cash on delivery</span>
                    <span><i class="fa fa-ticket" aria-hidden="true"></i> Coupon codes</span>
                </div>

                <div class="footer_social sf-foot-social">
                    <a href="#" aria-label="Facebook"><i class="fa fa-facebook" aria-hidden="true"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fa fa-twitter" aria-hidden="true"></i></a>
                    <a href="#" aria-label="LinkedIn"><i class="fa fa-linkedin" aria-hidden="true"></i></a>
                    <a href="#" aria-label="Instagram"><i class="fa fa-instagram" aria-hidden="true"></i></a>
                    <a href="#" aria-label="Pinterest"><i class="fa fa-pinterest" aria-hidden="true"></i></a>
                </div>
            </div>

            {{-- ---------------- explore ---------------- --}}
            <div class="col-lg-2 col-md-6 col-6 footer-col sf-foot-col">
                <h4>Explore</h4>

                <ul class="sf-foot-links">
                    <li>
                        <a href="{{ route('home') }}">
                            <i class="fa fa-home" aria-hidden="true"></i> Home
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('menu.index') }}">
                            <i class="fa fa-cutlery" aria-hidden="true"></i> Menu
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('about') }}">
                            <i class="fa fa-info-circle" aria-hidden="true"></i> About us
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('contact.page') }}">
                            <i class="fa fa-comment-o" aria-hidden="true"></i> Contact
                        </a>
                    </li>
                </ul>
            </div>

            {{-- ---------------- account ---------------- --}}
            <div class="col-lg-2 col-md-6 col-6 footer-col sf-foot-col">
                <h4>Your account</h4>

                <ul class="sf-foot-links">
                    @auth('frontend')
                        <li>
                            <a href="{{ route('profile') }}">
                                <i class="fa fa-user" aria-hidden="true"></i> My profile
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('cart.index') }}">
                                <i class="fa fa-shopping-cart" aria-hidden="true"></i> My cart
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('profile.edit') }}">
                                <i class="fa fa-cog" aria-hidden="true"></i> Edit profile
                            </a>
                        </li>
                    @endauth

                    @guest('frontend')
                        <li>
                            <a href="{{ route('login') }}">
                                <i class="fa fa-sign-in" aria-hidden="true"></i> Login
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('register') }}">
                                <i class="fa fa-user-plus" aria-hidden="true"></i> Create account
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('password.request') }}">
                                <i class="fa fa-key" aria-hidden="true"></i> Forgot password
                            </a>
                        </li>
                    @endguest
                </ul>
            </div>

            {{-- ---------------- contact ---------------- --}}
            <div class="col-lg-3 col-md-6 footer-col sf-foot-col">
                <h4>Contact us</h4>

                <div class="footer_contact contact_link_box sf-foot-contact">
                    <div>
                        <span class="sf-foot-ico"><i class="fa fa-map-marker" aria-hidden="true"></i></span>
                        <span>
                            <strong>24 Gulshan Avenue</strong>
                            <small>Dhaka 1212, Bangladesh</small>
                        </span>
                    </div>

                    <a href="tel:+8801700000000">
                        <span class="sf-foot-ico"><i class="fa fa-phone" aria-hidden="true"></i></span>
                        <span>
                            <strong>+880 1700 000000</strong>
                            <small>10:00 – 22:00, every day</small>
                        </span>
                    </a>

                    <a href="mailto:support@feane.com">
                        <span class="sf-foot-ico"><i class="fa fa-envelope" aria-hidden="true"></i></span>
                        <span>
                            <strong>support@feane.com</strong>
                            <small>We reply within a day</small>
                        </span>
                    </a>
                </div>
            </div>

            {{-- ---------------- hours ---------------- --}}
            <div class="col-lg-2 col-md-6 footer-col sf-foot-col">
                <h4>Opening hours</h4>

                <ul class="sf-foot-hours">
                    <li><span>Monday – Friday</span><span>10:00 – 22:00</span></li>
                    <li><span>Saturday</span><span>10:00 – 22:00</span></li>
                    <li><span>Sunday</span><span>10:00 – 22:00</span></li>
                </ul>

                <a href="{{ route('menu.index') }}" class="sf-btn sf-btn-solid">Order now</a>
            </div>

        </div>

        <div class="footer-info">
            <p>&copy; <span id="displayYear"></span> Feane. All rights reserved.</p>

            <p>
                Template by <a href="https://html.design/" target="_blank" rel="noopener">Free Html Templates</a>,
                distributed by <a href="https://themewagon.com/" target="_blank" rel="noopener">ThemeWagon</a>.
            </p>
        </div>
    </div>
</footer>
