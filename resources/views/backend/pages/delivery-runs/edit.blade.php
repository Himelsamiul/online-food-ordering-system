@extends('backend.master')

@section('content')
<div class="container-fluid">
    <h4 class="fw-bold mb-3">Edit Delivery Run</h4>

    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.delivery-runs.update', $run->id) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Delivery Man --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Delivery Man</label>
                    <select name="delivery_man_id" class="form-select" required>
                        @foreach($deliveryMen as $man)
                            <option value="{{ $man->id }}"
                                {{ $man->id == $run->delivery_man_id ? 'selected' : '' }}>
                                {{ $man->name }} ({{ $man->phone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Orders (readonly) --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Orders</label>
                    <input type="text"
                           class="form-control"
                           value="{{ count($run->order_ids) }} Order(s)"
                           readonly>
                </div>

                {{-- Note --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">Note</label>
                    <textarea name="note" class="form-control" rows="2">{{ $run->note }}</textarea>
                </div>

                <button class="btn btn-primary">
                    Update Delivery Run
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
