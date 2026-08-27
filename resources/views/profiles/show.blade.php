@extends('layouts.app')

@section('title', trans('seeker::messages.profiles.title', ['user' => $user->name]))

@include('seeker::_assets')

@if($canModerateProfile)
    @include('seeker::admin._styles')
@endif

@section('content')
    <div class="seeker-public-shell">
    <a class="seeker-back-link" href="{{ route('seeker.index') }}"><i class="bi bi-arrow-left" aria-hidden="true"></i>@lang('seeker::messages.back')</a>
    <div class="card seeker-profile-hero position-relative mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap align-items-start gap-4">
                <img src="{{ $user->getAvatar(112) }}" width="112" height="112" class="rounded-circle seeker-profile-avatar" alt="">
                <div class="seeker-profile-copy flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <span class="seeker-eyebrow">@lang('seeker::messages.profiles.profile_label')</span>
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
                        <div class="seeker-profile-bio mt-3">
                            @if(filled($profile?->bio))
                                {!! nl2br(e($profile->bio)) !!}
                            @else
                                <span class="seeker-profile-bio-empty"><i class="bi bi-person-lines-fill" aria-hidden="true"></i>@lang('seeker::messages.profiles.no_bio')</span>
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

    <div class="card seeker-profile-metrics mb-4">
        <div class="row g-0">
            @foreach([
                ['icon' => 'bi-megaphone', 'value' => $statistics['active_publications'], 'label' => 'active_publications'],
                ['icon' => 'bi-briefcase', 'value' => $statistics['author_commissions'], 'label' => 'author_commissions'],
                ['icon' => 'bi-patch-check', 'value' => $statistics['author_completed'], 'label' => 'author_completed'],
                ['icon' => 'bi-bag-check', 'value' => $statistics['client_completed'], 'label' => 'client_completed'],
            ] as $stat)
                <div class="seeker-profile-metric-column col-6 col-lg-3">
                    <div class="seeker-profile-metric">
                        <span class="seeker-profile-metric-icon"><i class="bi {{ $stat['icon'] }}" aria-hidden="true"></i></span>
                        <div>
                            <div class="seeker-profile-metric-value">{{ $stat['value'] }}</div>
                            <div class="seeker-profile-metric-label">@lang('seeker::messages.profiles.stats.'.$stat['label'])</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <section class="card overflow-hidden mb-4">
        <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::messages.profiles.reputation_by_role')</h2></div>
        <div class="row g-0">
            <div class="seeker-role-summary-column col-md-4">
                <div class="seeker-role-summary">
                    <strong class="d-block mb-2">@lang('seeker::messages.profiles.as_author')</strong>
                    @include('seeker::publications._reputation', ['rating' => $reputation['author']->rating, 'count' => $reputation['author']->reviews_count])
                </div>
            </div>
            <div class="seeker-role-summary-column col-md-4">
                <div class="seeker-role-summary">
                    <strong class="d-block mb-2">@lang('seeker::messages.profiles.as_client')</strong>
                    @include('seeker::publications._reputation', ['rating' => $reputation['client']->rating, 'count' => $reputation['client']->reviews_count])
                </div>
            </div>
            <div class="seeker-role-summary-column col-md-4">
                <div class="seeker-role-summary">
                    <strong class="d-block mb-2">@lang('seeker::messages.profiles.client_commissions')</strong>
                    <span class="text-muted">@lang('seeker::messages.profiles.client_summary', ['total' => $statistics['client_commissions'], 'completed' => $statistics['client_completed']])</span>
                </div>
            </div>
        </div>
    </section>

    <section class="card mb-4">
        <div class="card-header d-flex align-items-center justify-content-between gap-3">
            <h2 class="h5 mb-0">@lang('seeker::messages.profiles.verified_reviews')</h2>
            <span class="badge rounded-pill text-bg-light">{{ $reviews->total() }}</span>
        </div>
        <div class="card-body">
            @forelse($reviews as $review)
                <article class="seeker-review-entry">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $review->reviewer->getAvatar(44) }}" width="44" height="44" class="rounded-circle seeker-conversation-avatar" alt="">
                            <div>
                                <a class="fw-semibold text-decoration-none" href="{{ route('seeker.profiles.show', $review->reviewer) }}">{{ $review->reviewer->name }}</a>
                                <div><span class="badge text-bg-light">@lang($review->conversation->author_id === $user->id ? 'seeker::messages.profiles.as_author' : 'seeker::messages.profiles.as_client')</span></div>
                            </div>
                        </div>
                        <span class="text-warning" aria-label="@lang('seeker::messages.reviews.rating_value', ['rating' => $review->rating])">@foreach(range(1, 5) as $star)<i class="bi bi-star{{ $star <= $review->rating ? '-fill' : '' }}" aria-hidden="true"></i>@endforeach</span>
                    </div>
                    <p class="seeker-review-comment">{{ $review->comment }}</p>
                    <small class="text-muted"><i class="bi bi-calendar3 me-1" aria-hidden="true"></i>{{ format_date_compact($review->created_at) }}</small>
                </article>
            @empty
                @include('seeker::_empty-state', ['emptyIcon' => 'bi-star', 'emptyTitle' => trans('seeker::messages.reviews.no_reviews_yet')])
            @endforelse
        </div>
        @if($reviews->hasPages())<div class="card-footer d-flex justify-content-center">{{ $reviews->links() }}</div>@endif
    </section>

    @if($publications->isNotEmpty())
        <section class="mt-4">
            <h2 class="seeker-section-title"><i class="bi bi-megaphone" aria-hidden="true"></i>@lang('seeker::messages.profiles.publications')</h2>
            <div class="row g-3">
                @foreach($publications as $publication)
                    <div class="col-md-6 col-xl-4"><a class="card seeker-profile-publication h-100 text-decoration-none" href="{{ route('seeker.publications.show', $publication) }}"><div class="card-body"><div class="d-flex flex-wrap gap-1 mb-2">@if($publication->is_pinned)<span class="badge seeker-featured-badge"><i class="bi bi-pin-angle-fill me-1" aria-hidden="true"></i>@lang('seeker::messages.featured')</span>@endif<span class="badge text-bg-light">@lang('seeker::messages.types.'.$publication->type)</span></div><h3 class="h6 text-body">{{ $publication->title }}</h3><span class="small text-muted">@include('seeker::publications._price', ['publication' => $publication])</span></div></a></div>
                @endforeach
            </div>
        </section>
    @endif
    </div>
@endsection
