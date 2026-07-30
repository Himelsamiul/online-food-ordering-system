@extends('backend.master')
@section('title', 'Reviews')

@section('content')
<div class="container-fluid">

    <x-page-header
        title="Reviews"
        subtitle="Ratings customers left on food they actually received. Hiding one recalculates that item's average immediately."
        icon="feather-star"
        :breadcrumb="['Support' => null, 'Reviews' => null]" />

    {{-- ------------------------------------------------------------ stats --}}
    <div class="row g-3 mb-3">
        <div class="col-6 col-lg-3">
            <x-stat-card label="Total reviews" :value="$stats['total']" icon="feather-star" tone="primary" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Average rating" :value="number_format($stats['average'], 2)" icon="feather-trending-up" tone="success" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="Hidden" :value="$stats['hidden']" icon="feather-eye-off" tone="warning" />
        </div>
        <div class="col-6 col-lg-3">
            <x-stat-card label="No reply yet" :value="$stats['unanswered']" icon="feather-message-square" tone="info" />
        </div>
    </div>

    {{-- ---------------------------------------------------------- filters --}}
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.reviews.index') }}" class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="search" name="q" value="{{ $filters['q'] ?? '' }}"
                           class="form-control" placeholder="Food, customer or comment">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="approved" @selected(($filters['status'] ?? '') === 'approved')>Visible</option>
                        <option value="hidden" @selected(($filters['status'] ?? '') === 'hidden')>Hidden</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Rating</label>
                    <select name="rating" class="form-select">
                        <option value="">Any</option>
                        @for ($s = 5; $s >= 1; $s--)
                            <option value="{{ $s }}" @selected(($filters['rating'] ?? '') == $s)>{{ $s }} star</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" name="unanswered" value="1"
                               id="unansweredOnly" @checked(($filters['unanswered'] ?? '') === '1')>
                        <label class="form-check-label" for="unansweredOnly">No reply</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-soft-primary"><i class="feather-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ------------------------------------------------------------- list --}}
    <div class="card">
        <div class="card-body">
            @forelse ($reviews as $review)
                <div class="admin-review {{ $review->isApproved() ? '' : 'is-hidden' }}">

                    <div class="admin-review-main">
                        <div class="admin-review-head">
                            <span class="admin-review-stars" title="{{ $review->rating }} out of 5">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i class="feather-star {{ $i <= $review->rating ? 'filled' : '' }}"></i>
                                @endfor
                            </span>

                            <strong>{{ $review->food?->name ?? 'Deleted food' }}</strong>

                            @unless ($review->isApproved())
                                <span class="badge bg-warning-subtle text-warning">Hidden</span>
                            @endunless
                        </div>

                        <div class="admin-review-meta">
                            {{ $review->customer_name }}
                            @if ($review->customer?->email)
                                · {{ $review->customer->email }}
                            @endif
                            · {{ $review->created_at?->format('d M Y, g:i A') }}
                            · order #{{ $review->order_id }}
                        </div>

                        @if ($review->title)
                            <div class="admin-review-title">{{ $review->title }}</div>
                        @endif

                        @if ($review->comment)
                            <p class="admin-review-body">{{ $review->comment }}</p>
                        @endif

                        @if ($review->hasReply())
                            <div class="admin-review-reply">
                                <strong>{{ $review->admin_reply_by }} replied
                                    {{ $review->replied_at?->diffForHumans() }}</strong>
                                <p>{{ $review->admin_reply }}</p>
                            </div>
                        @endif

                        @if ($canEdit)
                            <form method="POST" action="{{ route('admin.reviews.reply', $review) }}"
                                  class="admin-review-replyform">
                                @csrf
                                <textarea name="admin_reply" rows="2" maxlength="800"
                                          placeholder="{{ $review->hasReply() ? 'Update your reply…' : 'Reply publicly…' }}"
                                          required>{{ $review->admin_reply }}</textarea>
                                <button type="submit" class="btn btn-sm btn-soft-primary">
                                    <i class="feather-corner-up-left"></i> {{ $review->hasReply() ? 'Update reply' : 'Reply' }}
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="admin-review-actions">
                        @if ($canEdit)
                            <form method="POST" action="{{ route('admin.reviews.status', $review) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="status"
                                       value="{{ $review->isApproved() ? 'hidden' : 'approved' }}">
                                <button type="submit"
                                        class="btn btn-sm {{ $review->isApproved() ? 'btn-soft-warning' : 'btn-soft-success' }}"
                                        title="{{ $review->isApproved() ? 'Hide from the storefront' : 'Show again' }}">
                                    <i class="feather-{{ $review->isApproved() ? 'eye-off' : 'eye' }}"></i>
                                </button>
                            </form>
                        @endif

                        @if ($canDelete)
                            <form method="POST" action="{{ route('admin.reviews.delete', $review) }}"
                                  class="delete-form"
                                  data-confirm-title="Delete this review?"
                                  data-confirm-text="The item's average rating will be recalculated without it."
                                  data-confirm-button="Yes, delete it">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-soft-danger" title="Delete">
                                    <i class="feather-trash-2"></i>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @empty
                <x-empty-state
                    icon="feather-star"
                    title="No reviews yet"
                    message="Customers can review an item once their order has been delivered." />
            @endforelse
        </div>

        @if ($reviews->hasPages())
            <div class="card-footer">{{ $reviews->links() }}</div>
        @endif
    </div>
</div>
@endsection
