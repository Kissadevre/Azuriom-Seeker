@extends('layouts.app')

@section('title', trans('seeker::messages.profiles.title', ['user' => $user->name]))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@if($canModerateProfile)
    @include('seeker::admin._styles')
@endif

@section('content')
    <div class="card mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="d-flex flex-wrap align-items-start gap-4">
                <img src="{{ $user->getAvatar(112) }}" width="112" height="112" class="rounded-circle" alt="">
                <div class="flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <h1 class="h2 mb-1">{{ $user->name }}</h1>
                            <div class="text-muted">@lang('seeker::messages.profiles.member_since', ['date' => format_date_compact($user->created_at)])</div>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            @if($canModerateProfile)
                                <button class="btn btn-outline-warning" type="button" data-bs-toggle="modal" data-bs-target="#publicProfileRestriction{{ $user->id }}"><i class="bi bi-person-lock me-1" aria-hidden="true"></i>@lang('seeker::messages.profiles.moderation.restrict')</button>
                                @if(filled($profile?->bio))
                                    <button class="btn btn-outline-danger" type="button" data-bs-toggle="modal" data-bs-target="#clearBiographyModal"><i class="bi bi-trash me-1" aria-hidden="true"></i>@lang('seeker::messages.profiles.moderation.clear_biography')</button>
                                @endif
                            @elseif(auth()->id() === $user->id)
                                @if($biographiesEnabled)
                                    <a class="btn btn-primary" href="{{ route('seeker.profiles.edit', $user) }}"><i class="bi bi-pencil me-1" aria-hidden="true"></i>@lang('seeker::messages.profiles.edit')</a>
                                @endif
                            @elseif(auth()->check())
                                @if($profileReport === null)
                                    <a class="btn btn-outline-danger" href="{{ route('seeker.profiles.reports.create', $user) }}"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('seeker::messages.profile_reports.action')</a>
                                @else
                                    <span class="badge text-bg-warning align-self-center"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('seeker::messages.profile_reports.sent_badge')</span>
                                @endif
                            @endif
                        </div>
                    </div>
                    <div class="fs-5 mt-3">@include('seeker::publications._reputation', ['rating' => $reputation['overall']->rating, 'count' => $reputation['overall']->reviews_count])</div>
                    @if($biographiesEnabled)
                        <div class="mt-3 seeker-description">
                            @if(filled($profile?->bio))
                                {!! nl2br(e($profile->bio)) !!}
                            @else
                                <span class="text-muted">@lang('seeker::messages.profiles.no_bio')</span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($canModerateProfile)
        @include('seeker::admin._restriction-modal', [
            'user' => $user,
            'modalId' => 'publicProfileRestriction'.$user->id,
            'contextName' => 'profile_id',
            'contextId' => $user->id,
            'contextLabel' => trans('seeker::messages.profiles.title', ['user' => $user->name]),
        ])

        @if(filled($profile?->bio))
            <div class="modal fade" id="clearBiographyModal" tabindex="-1" aria-labelledby="clearBiographyModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h2 class="modal-title fs-5" id="clearBiographyModalLabel"><i class="bi bi-trash text-danger me-2" aria-hidden="true"></i>@lang('seeker::messages.profiles.moderation.clear_biography_title')</h2>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('messages.actions.close')"></button>
                        </div>
                        <div class="modal-body">
                            <p>@lang('seeker::messages.profiles.moderation.clear_biography_confirm', ['user' => $user->name])</p>
                            <div class="border rounded bg-body-tertiary p-3" style="white-space: pre-wrap">{{ $profile->bio }}</div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('messages.actions.cancel')</button>
                            <form method="POST" action="{{ route('seeker.admin.profiles.biography.clear', $user) }}">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>@lang('seeker::messages.profiles.moderation.clear_biography')</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    @endif

    <div class="row g-3 mb-4">
        @foreach([
            ['icon' => 'bi-megaphone', 'value' => $statistics['active_publications'], 'label' => 'active_publications'],
            ['icon' => 'bi-briefcase', 'value' => $statistics['author_commissions'], 'label' => 'author_commissions'],
            ['icon' => 'bi-patch-check', 'value' => $statistics['author_completed'], 'label' => 'author_completed'],
            ['icon' => 'bi-bag-check', 'value' => $statistics['client_completed'], 'label' => 'client_completed'],
        ] as $stat)
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body text-center"><i class="bi {{ $stat['icon'] }} fs-3 text-primary" aria-hidden="true"></i><div class="display-6 fw-semibold">{{ $stat['value'] }}</div><div class="small text-muted">@lang('seeker::messages.profiles.stats.'.$stat['label'])</div></div></div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::messages.profiles.reputation_by_role')</h2></div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item p-3"><strong class="d-block mb-1">@lang('seeker::messages.profiles.as_author')</strong>@include('seeker::publications._reputation', ['rating' => $reputation['author']->rating, 'count' => $reputation['author']->reviews_count])</div>
                    <div class="list-group-item p-3"><strong class="d-block mb-1">@lang('seeker::messages.profiles.as_client')</strong>@include('seeker::publications._reputation', ['rating' => $reputation['client']->rating, 'count' => $reputation['client']->reviews_count])</div>
                    <div class="list-group-item p-3"><strong class="d-block mb-1">@lang('seeker::messages.profiles.client_commissions')</strong><span class="text-muted">@lang('seeker::messages.profiles.client_summary', ['total' => $statistics['client_commissions'], 'completed' => $statistics['client_completed']])</span></div>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::messages.profiles.verified_reviews')</h2></div>
                <div class="card-body">
                    @forelse($reviews as $review)
                        <article class="{{ ! $loop->last ? 'border-bottom mb-3 pb-3' : '' }}">
                            <div class="d-flex flex-wrap justify-content-between gap-2 mb-2">
                                <div><a class="fw-semibold text-decoration-none" href="{{ route('seeker.profiles.show', $review->reviewer) }}">{{ $review->reviewer->name }}</a><span class="badge text-bg-light ms-2">@lang($review->conversation->author_id === $user->id ? 'seeker::messages.profiles.as_author' : 'seeker::messages.profiles.as_client')</span></div>
                                <span class="text-warning" aria-label="@lang('seeker::messages.reviews.rating_value', ['rating' => $review->rating])">@foreach(range(1, 5) as $star)<i class="bi bi-star{{ $star <= $review->rating ? '-fill' : '' }}" aria-hidden="true"></i>@endforeach</span>
                            </div>
                            <p class="mb-1">{{ $review->comment }}</p>
                            <small class="text-muted">{{ format_date_compact($review->created_at) }}</small>
                        </article>
                    @empty
                        <div class="text-center text-muted py-4">@lang('seeker::messages.reviews.no_reviews_yet')</div>
                    @endforelse
                </div>
                @if($reviews->hasPages())<div class="card-footer d-flex justify-content-center">{{ $reviews->links() }}</div>@endif
            </div>
        </div>
    </div>

    @if($publications->isNotEmpty())
        <section class="mt-4">
            <h2 class="h4 mb-3">@lang('seeker::messages.profiles.publications')</h2>
            <div class="row g-3">
                @foreach($publications as $publication)
                    <div class="col-md-6 col-xl-4"><a class="card h-100 text-decoration-none" href="{{ route('seeker.publications.show', $publication) }}"><div class="card-body"><span class="badge text-bg-light mb-2">@lang('seeker::messages.types.'.$publication->type)</span><h3 class="h6 text-body">{{ $publication->title }}</h3><span class="small text-muted">@include('seeker::publications._price', ['publication' => $publication])</span></div></a></div>
                @endforeach
            </div>
        </section>
    @endif
@endsection
