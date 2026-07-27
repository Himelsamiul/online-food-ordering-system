@extends('frontend.master')
@section('title', 'Edit Profile')

@php
    $hasImage = $user->image && file_exists(public_path('storage/'.$user->image));
    $initial  = \Illuminate\Support\Str::of($user->full_name)->substr(0, 1);
@endphp

@push('styles')
<style>
    .sf-page-head { margin-bottom: 26px; }

    .sf-page-head h2 {
        color: var(--sf-ink);
        font-weight: 700;
        margin-bottom: 6px;
    }

    .sf-page-head p {
        color: var(--sf-muted);
        margin-bottom: 0;
    }

    /* `.glass-card small` in the design layer out-specifies `.sf-hint`,
       which would otherwise render the hints at full brightness. */
    .glass-card .sf-hint { color: var(--sf-faint); }

    .sf-fieldset { margin-bottom: 28px; }
    .sf-fieldset:last-of-type { margin-bottom: 18px; }

    .sf-fieldset-title {
        display: block;
        color: var(--sf-accent);
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: .09em;
        text-transform: uppercase;
        margin: 0 0 16px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--sf-glass-line);
    }

    /* ---------- photo picker ---------- */
    .sf-photo {
        display: flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 18px;
    }

    .sf-photo-frame {
        width: 84px;
        height: 84px;
        flex-shrink: 0;
        border-radius: 50%;
        overflow: hidden;
        display: grid;
        place-items: center;
        background: linear-gradient(135deg, var(--sf-accent), var(--sf-accent-dark));
        color: rgba(0, 0, 0, .8);
        font-size: 30px;
        font-weight: 800;
        text-transform: uppercase;
        border: 2px solid var(--sf-glass-line);
    }

    .sf-photo-frame img { width: 100%; height: 100%; object-fit: cover; }
    .sf-photo-frame [hidden] { display: none !important; }

    .sf-photo-field { flex: 1 1 240px; min-width: 0; }

    .sf-actions-row {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    .sf-actions-row .btn { flex: 1 1 180px; padding: 11px 20px; }
</style>
@endpush

@section('content')

<section class="profile-edit-section layout_padding">
    <div class="container">

        <div class="sf-page-head text-center">
            <h2>Edit Profile</h2>
            <p>Keep your details current so deliveries reach you without a phone call.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-7 col-md-9">
                <div class="glass-card p-4">

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        {{-- ============ PERSONAL ============ --}}
                        <div class="sf-fieldset">
                            <span class="sf-fieldset-title">Personal details</span>

                            <div class="form-row">
                                <div class="form-group col-md-7">
                                    <label for="full_name">Full Name <span class="sf-req">*</span></label>
                                    <input type="text"
                                           id="full_name"
                                           name="full_name"
                                           value="{{ old('full_name', $user->full_name) }}"
                                           placeholder="e.g. John Doe"
                                           autocomplete="name"
                                           class="form-control @error('full_name') is-invalid @enderror">
                                    @error('full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group col-md-5">
                                    <label for="dob">Date of Birth <span class="sf-req">*</span></label>
                                    <input type="date"
                                           id="dob"
                                           name="dob"
                                           value="{{ old('dob', $user->dob) }}"
                                           class="form-control @error('dob') is-invalid @enderror">
                                    @error('dob')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group mb-0">
                                <label for="username">Username</label>
                                <input type="text"
                                       id="username"
                                       name="username"
                                       value="{{ old('username', $user->username) }}"
                                       placeholder="e.g. johndoe123"
                                       class="form-control @error('username') is-invalid @enderror" readonly>
                                @error('username')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="sf-hint">
                                    Your username is fixed. An administrator can change it for you.
                                </small>
                            </div>
                        </div>

                        {{-- ============ CONTACT ============ --}}
                        <div class="sf-fieldset">
                            <span class="sf-fieldset-title">Contact &amp; delivery</span>

                            <div class="form-group">
                                <label for="phone">Phone Number <span class="sf-req">*</span></label>
                                <input type="text"
                                       id="phone"
                                       name="phone"
                                       value="{{ old('phone', $user->phone) }}"
                                       placeholder="01XXXXXXXXX"
                                       inputmode="numeric"
                                       autocomplete="tel"
                                       class="form-control @error('phone') is-invalid @enderror">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="sf-hint">
                                    11 digits, starting 013 to 019. The rider calls this number.
                                </small>
                            </div>

                            <div class="form-group">
                                <label for="email">Email Address</label>
                                <input type="email"
                                       id="email"
                                       name="email"
                                       value="{{ old('email', $user->email) }}"
                                       placeholder="example@mail.com"
                                       class="form-control @error('email') is-invalid @enderror" readonly>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="sf-hint">
                                    This is the address we verified, so it cannot be edited here.
                                </small>
                            </div>

                            <div class="form-group mb-0">
                                <label for="address">Address <span class="sf-optional">(optional)</span></label>
                                <textarea id="address"
                                          name="address"
                                          rows="3"
                                          placeholder="House, road, area and city"
                                          class="form-control @error('address') is-invalid @enderror">{{ old('address', $user->address ?? '') }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="sf-hint">
                                    Saved here so checkout fills it in for you. Up to 500 characters.
                                </small>
                            </div>
                        </div>

                        {{-- ============ PHOTO ============ --}}
                        <div class="sf-fieldset">
                            <span class="sf-fieldset-title">Profile picture</span>

                            <div class="sf-photo">
                                <div class="sf-photo-frame">
                                    {{-- No src attribute at all when there is nothing to show:
                                         an empty src makes the browser re-request the page. --}}
                                    <img id="profileImagePreview"
                                         alt="Your profile picture"
                                         @if ($hasImage) src="{{ asset('storage/'.$user->image) }}" @else hidden @endif>
                                    <span id="profileImageInitial" @if ($hasImage) hidden @endif>{{ $initial }}</span>
                                </div>

                                <div class="sf-photo-field">
                                    <label for="image" class="sr-only">Profile Image</label>
                                    <input type="file"
                                           id="image"
                                           name="image"
                                           accept="image/jpeg,image/png"
                                           class="form-control @error('image') is-invalid @enderror">
                                    @error('image')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="sf-hint">
                                        JPG or PNG, up to 2 MB. Leave it empty to keep the picture you have.
                                    </small>
                                </div>
                            </div>
                        </div>

                        {{-- ============ ACTIONS ============ --}}
                        <div class="sf-actions-row">
                            <button type="submit" class="btn btn-warning">Save Changes</button>
                            <a href="{{ route('profile') }}" class="btn btn-outline-light">Cancel</a>
                        </div>
                    </form>

                </div>

                <p class="text-center mt-3 mb-0">
                    <a href="{{ route('account.help', ['type' => 'password', 'email' => $user->email]) }}"
                       class="sf-link-muted">
                        Need your password changed? Ask an admin
                    </a>
                </p>
            </div>
        </div>

    </div>
</section>

@endsection

@push('scripts')
<script>
    (function () {
        var input   = document.getElementById('image');
        var preview = document.getElementById('profileImagePreview');
        var initial = document.getElementById('profileImageInitial');

        if (!input || !preview || !window.FileReader) {
            return;
        }

        input.addEventListener('change', function () {
            var file = input.files && input.files[0];

            if (!file || file.type.indexOf('image/') !== 0) {
                return;
            }

            var reader = new FileReader();

            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.hidden = false;

                if (initial) {
                    initial.hidden = true;
                }
            };

            reader.readAsDataURL(file);
        });
    })();
</script>
@endpush
