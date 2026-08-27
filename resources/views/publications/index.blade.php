@extends('layouts.app')

@section('title', trans('seeker::messages.title'))

@include('seeker::_assets')

@section('content')
    <div class="seeker-public-shell">
    <div class="seeker-hero card border-0 mb-4 overflow-hidden">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg">
                    <span class="seeker-eyebrow mb-2">Seeker by Zibuu</span>
                    <h1 class="display-6 fw-bold mb-2">@lang('seeker::messages.title')</h1>
                    <p class="lead mb-0">@lang('seeker::messages.subtitle')</p>
                </div>
                <div class="col-lg-auto d-flex flex-wrap gap-2">
                    @auth
                        <a class="btn btn-outline-primary" href="{{ route('seeker.profiles.show', auth()->user()) }}">
                            <i class="bi bi-person-badge me-1" aria-hidden="true"></i> @lang('seeker::messages.profiles.my_profile')
                        </a>
                        <a class="btn btn-outline-primary" href="{{ route('seeker.publications.mine') }}">
                            <i class="bi bi-briefcase me-1" aria-hidden="true"></i> @lang('seeker::messages.my_publications')
                        </a>
                        @if($publicationsEnabled)
                            <a class="btn btn-primary" href="{{ route('seeker.publications.create') }}">
                                <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> @lang('seeker::messages.publish')
                            </a>
                        @elseif($publishRestriction)
                            <a class="btn btn-outline-warning" href="{{ route('seeker.restrictions.show', \Azuriom\Plugin\Seeker\Models\UserRestriction::TYPE_PUBLISH) }}"><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>@lang('seeker::messages.restrictions.details.view')</a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <div class="card seeker-filter-card mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" class="row g-2" role="search">
                <div class="col-lg">
                    <label class="visually-hidden" for="seekerSearch">@lang('seeker::messages.search')</label>
                    <div class="input-group"><span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span><input id="seekerSearch" type="search" name="search" class="form-control" value="{{ $search }}" placeholder="@lang('seeker::messages.search')"></div>
                </div>
                <div class="col-lg-3">
                    <label class="visually-hidden" for="seekerType">@lang('seeker::messages.fields.type')</label>
                    <select id="seekerType" name="type" class="form-select">
                        <option value="">@lang('seeker::messages.all_types')</option>
                        @foreach(\Azuriom\Plugin\Seeker\Models\Publication::types() as $publicationType)
                            <option value="{{ $publicationType }}" @selected($type === $publicationType)>@lang('seeker::messages.types.'.$publicationType)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-auto">
                    <button class="btn btn-primary w-100"><i class="bi bi-search me-1" aria-hidden="true"></i> @lang('seeker::messages.actions.filter')</button>
                </div>
            </form>
        </div>
    </div>

    @if($publications->isEmpty())
        @include('seeker::_empty-state', ['emptyIcon' => 'bi-search', 'emptyTitle' => trans('seeker::messages.empty')])
    @else
        <div class="row g-4">
            @foreach($publications as $publication)
                <div class="col-md-6 col-xl-4">
                    <article class="card seeker-card seeker-publication-card h-100 overflow-hidden">
                        @if($image = $publication->images->first())
                            <img class="seeker-card-image" src="{{ route('seeker.images.show', $image) }}" alt="">
                        @else
                            <div class="seeker-card-placeholder"><i class="bi bi-stars" aria-hidden="true"></i></div>
                        @endif
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <span class="badge {{ $publication->type === 'commission' ? 'text-bg-primary' : 'text-bg-info' }}">@lang('seeker::messages.types.'.$publication->type)</span>
                                <span class="badge text-bg-light">
                                    @include('seeker::publications._price', ['publication' => $publication])
                                </span>
                                @if(! $publication->is_guest_visible)
                                    <span class="badge text-bg-secondary"><i class="bi bi-lock-fill me-1" aria-hidden="true"></i>@lang('seeker::messages.visibility.members')</span>
                                @endif
                            </div>
                            <h2 class="h5 card-title">{{ $publication->title }}</h2>
                            <p class="card-text text-muted flex-grow-1">{{ \Illuminate\Support\Str::limit($publication->description, 150) }}</p>
                            <div class="d-flex align-items-center gap-2 small text-muted mb-3">
                                <img src="{{ $publication->user->getAvatar(32) }}" width="32" height="32" class="rounded-circle" alt="">
                                <div class="position-relative z-2">
                                    <div><a class="text-reset text-decoration-none" href="{{ route('seeker.profiles.show', $publication->user) }}">{{ $publication->user->name }}</a></div>
                                    <div>@include('seeker::publications._reputation', ['rating' => $publication->author_rating, 'count' => $publication->author_reviews_count])</div>
                                </div>
                            </div>
                            <a class="btn btn-outline-primary stretched-link" href="{{ route('seeker.publications.show', $publication) }}">@lang('seeker::messages.view_details')</a>
                        </div>
                    </article>
                </div>
            @endforeach
        </div>
        @if($publications->hasPages())
            <div class="d-flex justify-content-center mt-4">{{ $publications->links() }}</div>
        @endif
    @endif
    </div>
@endsection
