@extends('backend.master')

@section('content')
<div class="row justify-content-center">
    <div class="col-xl-7 col-lg-9 col-md-11">
        <div class="card">
            <div class="card-header">
                <h5>Edit Promo Banner</h5>
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

                <form action="{{ route('admin.promotions.update', $promotion->id) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @include('backend.pages.promotions._form', ['promotion' => $promotion])

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Banner</button>
                        <a href="{{ route('admin.promotions.index') }}" class="btn btn-secondary">Back</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
@endsection
