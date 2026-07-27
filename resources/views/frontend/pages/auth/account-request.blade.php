@php
    use App\Models\AccountRequest;

    $isActivation = $type === AccountRequest::TYPE_ACTIVATION;

    $heading = $isActivation ? 'Request reactivation' : 'Ask an admin for a reset link';
    $blurb   = $isActivation
        ? 'Your account has been switched off by an administrator. Send them a note and they will turn it back on and email you.'
        : 'If the emailed code never reaches you, an administrator can send a secure one-time link so you can set a new password yourself.';
    $cta      = $isActivation ? 'Send reactivation request' : 'Send password request';
    $action   = $isActivation ? route('account.help.activation') : route('account.help.password');
    $otherUrl = $isActivation ? route('account.help', 'password') : route('account.help', 'activation');
    $otherTxt = $isActivation ? 'I just forgot my password' : 'My account is deactivated';
@endphp

@extends('frontend.master')
@section('title', $isActivation ? 'Request Reactivation' : 'Request a Reset Link')

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

                    <form action="{{ $action }}" method="POST">
                        @csrf

                        <div class="form-group mb-3">
                            <label for="name">Your Full Name <span class="sf-req">*</span></label>
                            <input type="text" id="name" name="name"
                                   value="{{ old('name', $prefill['name']) }}"
                                   class="form-control @error('name') is-invalid @enderror"
                                   placeholder="As it appears on your account" required>
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group mb-3">
                            <label for="email">Registered Email <span class="sf-req">*</span></label>
                            <input type="email" id="email" name="email"
                                   value="{{ old('email', $prefill['email']) }}"
                                   class="form-control @error('email') is-invalid @enderror"
                                   placeholder="example@mail.com" autocomplete="email" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <small class="sf-hint">
                                Must be the exact address you signed up with — it is how the admin knows the
                                request is really yours, and the reply goes there and nowhere else.
                            </small>
                        </div>

                        <div class="form-group mb-3">
                            <label for="phone">Phone <span class="sf-optional">(optional)</span></label>
                            <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   placeholder="So we can reach you another way">
                            @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="form-group mb-4">
                            <label for="reason">
                                {{ $isActivation ? 'Reason for reactivation' : 'Reason for request' }}
                                <span class="sf-req">*</span>
                            </label>
                            <textarea id="reason" name="reason" rows="4"
                                      class="form-control @error('reason') is-invalid @enderror"
                                      placeholder="{{ $isActivation
                                          ? 'Anything that helps the admin confirm this is your account.'
                                          : 'Tell the admin why you cannot use the emailed code.' }}"
                                      required>{{ old('reason') }}</textarea>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <button type="submit" class="btn btn-warning">{{ $cta }}</button>
                    </form>

                    @unless ($isActivation)
                        <div class="sf-help-box">
                            <p>
                                <strong>Faster option:</strong>
                                the self-service reset emails you a code straight away — no waiting for an admin.
                            </p>
                            <a class="btn btn-warning" href="{{ route('password.request') }}">
                                Reset it myself
                            </a>
                        </div>
                    @endunless

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
