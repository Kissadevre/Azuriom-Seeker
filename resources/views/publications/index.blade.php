@extends('layouts.app')

@section('title', trans('seeker::messages.title'))

@include('seeker::_assets')

@section('content')
    <div class="seeker-public-shell">
    <div class="seeker-hero card border-0 mb-4">
        <div class="card-body p-4 p-lg-5">
            <div class="row align-items-center g-4">
                <div class="col-lg">
                    <span class="seeker-eyebrow mb-2">Seeker by Zibuu</span>
                    <h1 class="display-6 fw-bold mb-2">@lang('seeker::messages.title')</h1>
                    <p class="lead mb-0">@lang('seeker::messages.subtitle')</p>
                </div>
                <div class="col-lg-auto">
                    @auth
                        <div class="seeker-hero-actions">
                            <div class="dropdown">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-grid me-1" aria-hidden="true"></i>@lang('seeker::messages.quick_menu.label')
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end seeker-quick-menu">
                                    <li>
                                        <a class="dropdown-item seeker-quick-menu-item" href="{{ route('seeker.profiles.show', auth()->user()) }}">
                                            <span class="seeker-quick-menu-icon"><i class="bi bi-person-badge" aria-hidden="true"></i></span>
                                            <span><strong>@lang('seeker::messages.profiles.my_profile')</strong><small>@lang('seeker::messages.quick_menu.profile_description')</small></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item seeker-quick-menu-item" href="{{ route('seeker.publications.mine') }}">
                                            <span class="seeker-quick-menu-icon"><i class="bi bi-briefcase" aria-hidden="true"></i></span>
                                            <span><strong>@lang('seeker::messages.my_publications')</strong><small>@lang('seeker::messages.quick_menu.publications_description')</small></span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item seeker-quick-menu-item" href="{{ route('seeker.conversations.index') }}">
                                            <span class="seeker-quick-menu-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
                                            <span><strong>@lang('seeker::messages.conversations.title')</strong><small>@lang('seeker::messages.quick_menu.messages_description')</small></span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                            @if($publicationsEnabled)
                                <a class="btn btn-primary" href="{{ route('seeker.publications.create') }}">
                                    <i class="bi bi-plus-lg me-1" aria-hidden="true"></i> @lang('seeker::messages.publish')
                                </a>
                            @elseif($publishRestriction)
                                <a class="btn btn-outline-warning" href="{{ route('seeker.restrictions.show', \Azuriom\Plugin\Seeker\Models\UserRestriction::TYPE_PUBLISH) }}"><i class="bi bi-shield-lock me-1" aria-hidden="true"></i>@lang('seeker::messages.restrictions.details.view')</a>
                            @endif
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    <nav class="seeker-category-nav mb-3" aria-label="@lang('seeker::messages.categories.label')">
        <a class="seeker-category-link {{ $type === null ? 'active' : '' }}" href="{{ route('seeker.index', array_filter(['search' => $search])) }}" @if($type === null) aria-current="page" @endif>
            <span class="seeker-category-icon"><i class="bi bi-grid" aria-hidden="true"></i></span>
            <span><strong>@lang('seeker::messages.categories.all')</strong><small>@lang('seeker::messages.categories.all_description')</small></span>
        </a>
        <a class="seeker-category-link {{ $type === \Azuriom\Plugin\Seeker\Models\Publication::TYPE_COMMISSION ? 'active' : '' }}" href="{{ route('seeker.index', array_filter(['type' => \Azuriom\Plugin\Seeker\Models\Publication::TYPE_COMMISSION, 'search' => $search])) }}" @if($type === \Azuriom\Plugin\Seeker\Models\Publication::TYPE_COMMISSION) aria-current="page" @endif>
            <span class="seeker-category-icon"><i class="bi bi-briefcase" aria-hidden="true"></i></span>
            <span><strong>@lang('seeker::messages.categories.commissions')</strong><small>@lang('seeker::messages.categories.commissions_description')</small></span>
        </a>
        <a class="seeker-category-link {{ $type === \Azuriom\Plugin\Seeker\Models\Publication::TYPE_TALENT ? 'active' : '' }}" href="{{ route('seeker.index', array_filter(['type' => \Azuriom\Plugin\Seeker\Models\Publication::TYPE_TALENT, 'search' => $search])) }}" @if($type === \Azuriom\Plugin\Seeker\Models\Publication::TYPE_TALENT) aria-current="page" @endif>
            <span class="seeker-category-icon"><i class="bi bi-people" aria-hidden="true"></i></span>
            <span><strong>@lang('seeker::messages.categories.talent')</strong><small>@lang('seeker::messages.categories.talent_description')</small></span>
        </a>
    </nav>

    <div class="card seeker-filter-card mb-4">
        <div class="card-body p-3 p-lg-4">
            <form method="GET" class="d-flex flex-column flex-sm-row gap-2" role="search">
                @if($type !== null)<input type="hidden" name="type" value="{{ $type }}">@endif
                <div class="flex-grow-1">
                    <label class="visually-hidden" for="seekerSearch">@lang('seeker::messages.search')</label>
                    <div class="input-group"><span class="input-group-text"><i class="bi bi-search" aria-hidden="true"></i></span><input id="seekerSearch" type="search" name="search" class="form-control" value="{{ $search }}" placeholder="@lang('seeker::messages.search')"></div>
                </div>
                <button class="btn btn-primary px-4"><i class="bi bi-search me-1" aria-hidden="true"></i>@lang('seeker::messages.actions.search')</button>
            </form>
        </div>
    </div>

    <div class="seeker-results-heading d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
        <div>
            <span class="seeker-eyebrow">@lang('seeker::messages.categories.label')</span>
            <h2 class="h4 mb-0">@lang('seeker::messages.categories.'.($type === 'commission' ? 'commissions' : ($type === 'talent' ? 'talent' : 'all')))</h2>
        </div>
        <span class="text-muted">@choice('seeker::messages.results_count', $publications->total(), ['count' => $publications->total()])</span>
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
                                @if($publication->is_pinned)
                                    <span class="badge seeker-featured-badge"><i class="bi bi-pin-angle-fill me-1" aria-hidden="true"></i>@lang('seeker::messages.featured')</span>
                                @endif
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
