@extends('frontend.master')

@section('content')

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            <div class="glass-card p-4 shadow-lg">
                <h4 class="fw-bold mb-3 text-center">Contact Us</h4>

                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('contact.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Your Name
                           <span class="text-danger">*</span>
                           </label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               placeholder="Optional">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Email <span class="text-danger">*</span>
                        </label>
                        <input type="email"
                               name="email"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Phone <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               name="phone"
                               class="form-control"
                               required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Message</label>
                        <textarea name="message"
                                  rows="4"
                                  class="form-control"
                                  placeholder="Your comment or query..."></textarea>
                    </div>

                    <button class="btn btn-success w-100">
                        Send Message
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

@endsection
