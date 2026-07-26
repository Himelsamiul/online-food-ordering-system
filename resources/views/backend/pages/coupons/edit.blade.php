@extends('backend.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9 col-md-11">
        <div class="card">
            <div class="card-header">
                <h5>Edit Coupon — {{ $coupon->code }}</h5>
            </div>

            <div class="card-body">

                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if($coupon->used_count > 0)
                    <div class="alert alert-info">
                        This coupon has been redeemed <strong>{{ $coupon->used_count }}</strong> time(s).
                        Past orders keep the discount they were given — editing only affects future use.
                    </div>
                @endif

                <form action="{{ route('admin.coupons.update', $coupon->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    @include('backend.pages.coupons._form', ['coupon' => $coupon])

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Coupon</button>
                        <a href="{{ route('admin.coupons.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
