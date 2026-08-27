@extends('layouts.app')

@section('title', trans('seeker::messages.reviews.title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="mb-4">
        <a class="text-decoration-none" href="{{ route('seeker.conversations.show', $conversation) }}">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> @lang('seeker::messages.reviews.back')
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('seeker.conversations.reviews.store', $conversation) }}" class="card">
                @csrf
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="{{ $reviewedUser->getAvatar(56) }}" width="56" height="56" class="rounded-circle" alt="">
                        <div>
                            <h1 class="h3 mb-1">@lang('seeker::messages.reviews.title')</h1>
                            <div class="text-muted">@lang('seeker::messages.reviews.reviewing', ['user' => $reviewedUser->name])</div>
                            <div class="small text-muted">{{ $conversation->publication->title }}</div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <fieldset>
                            <legend class="form-label fw-semibold">@lang('seeker::messages.reviews.rating')</legend>
                            <div class="d-flex flex-wrap gap-2">
                                @foreach(range(1, 5) as $rating)
                                    <input class="btn-check" type="radio" name="rating" id="reviewRating{{ $rating }}" value="{{ $rating }}" @checked((int) old('rating') === $rating) required>
                                    <label class="btn btn-outline-warning" for="reviewRating{{ $rating }}">
                                        {{ $rating }} <i class="bi bi-star-fill" aria-hidden="true"></i>
                                    </label>
                                @endforeach
                            </div>
                            @error('rating')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </fieldset>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-semibold" for="reviewComment">@lang('seeker::messages.reviews.comment')</label>
                        <textarea id="reviewComment" name="comment" rows="5" minlength="10" maxlength="500" class="form-control @error('comment') is-invalid @enderror" required>{{ old('comment') }}</textarea>
                        <div class="form-text">@lang('seeker::messages.reviews.comment_help')</div>
                        @error('comment')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-shield-check me-2" aria-hidden="true"></i>@lang('seeker::messages.reviews.verified_notice')
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <a class="btn btn-outline-secondary" href="{{ route('seeker.conversations.show', $conversation) }}">@lang('seeker::messages.reviews.cancel')</a>
                        <button class="btn btn-primary" type="submit"><i class="bi bi-star me-1" aria-hidden="true"></i>@lang('seeker::messages.reviews.submit')</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
