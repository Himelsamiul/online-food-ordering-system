@extends('frontend.master')
@section('title', $type === \App\Models\AccountRequest::TYPE_ACTIVATION ? 'Request Reactivation' : 'Request a New Password')

@php
    use App\Models\AccountRequest;

    $isActivation = $type === AccountRequest::TYPE_ACTIVATION;

    $heading  = $isActivation ? 'Request reactivation' : 'Request a new password';
    $blurb    = $isActivation
        ? 'Your account has been switched off by an administrator. Send them a note and they will turn it back on and email you.'
        : 'If you cannot use the emailed reset code, an administrator can generate a new password and send it to you.';
    $cta      = $isActivation ? 'Send reactivation request' : 'Send password request';
    $otherUrl = $isActivation ? route('account.help', 'password') : route('account.help', 'activation');
    $otherTxt = $isActivation ? 'I just forgot my password' : 'My account is deactivated';
@endphp

@section('content')

<section class="sf-auth-wrap">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-6 col-md-8">

                <div class="sf-auth-card">
                    <div class="sf-request-badge">
                        <i class="fa {{ $isActivation ? 'fa-unlock-alt' : 'fa-key' }}" aria-hidden="true"></i>
                    </div>

                    <h3>{{ $heading }}</h3>
                    <p class="sf-auth-sub">{{ $blurb }}</p>

                    <form action="{{ route('account.help.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">

                        <div class="form-group mb-3">
                            <label for="name">Your Full Name <span class="sf-req">*</span></label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $prefill['name']) }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="As it appears on your account" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">Account Email <span class="sf-req">*</span></label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $prefill['email']) }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="example@mail.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="sf-hint">
                                The admin's reply goes to this address, so make sure you can read it.
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Phone <span class="sf-optional">(optional)</span></label>
                            <input type="text" id="phone" name="phone"
                                   value="{{ old('phone') }}"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="So we can reach you another way">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="message">Message to the admin <span class="sf-optional">(optional)</span></label>
                            <textarea id="message" name="message" rows="4"
                                      class="form-control @error('message') is-invalid @enderror"
                                      placeholder="{{ $isActivation
                                          ? 'Anything that helps them confirm this is your account.'
                                          : 'Anything you want the admin to know.' }}">{{ old('message') }}</textarea>
                            @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-warning">{{ $cta }}</button>
                    </form>

                    <div class="sf-auth-foot">
                        <a href="{{ $otherUrl }}" class="sf-link-muted">{{ $otherTxt }}</a>
                        <div class="mt-2">
                            <a href="{{ route('login') }}">Back to login</a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</section>

@endsection
