@extends('layouts.app')

@section('title', $publication->title)

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="mb-4">
        <a class="text-decoration-none" href="{{ route('seeker.index') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> @lang('seeker::messages.back')</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <article class="card mb-4">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        <span class="badge {{ $publication->type === 'commission' ? 'text-bg-primary' : 'text-bg-info' }}">@lang('seeker::messages.types.'.$publication->type)</span>
                        @if($publication->status !== 'active')
                            <span class="badge text-bg-secondary">@lang('seeker::messages.statuses.'.$publication->status)</span>
                        @endif
                        @if(! $publication->is_guest_visible)
                            <span class="badge text-bg-secondary"><i class="bi bi-lock-fill me-1" aria-hidden="true"></i>@lang('seeker::messages.visibility.members')</span>
                        @endif
                    </div>
                    <h1 class="display-6 fw-bold">{{ $publication->title }}</h1>
                    <div class="d-flex align-items-center gap-3 text-muted mb-4">
                        <img src="{{ $publication->user->getAvatar(42) }}" width="42" height="42" class="rounded-circle" alt="">
                        <div>
                            <div>@lang('seeker::messages.published_by', ['user' => $publication->user->name])</div>
                            <small>@lang('seeker::messages.published_on', ['date' => format_date_compact($publication->published_at ?? $publication->created_at)])</small>
                        </div>
                    </div>
                    <div class="seeker-description">{!! nl2br(e($publication->description)) !!}</div>

                    @if($publication->portfolio_type === 'external' && $publication->portfolio_url)
                        <button class="btn btn-primary mt-4" type="button" data-bs-toggle="modal" data-bs-target="#externalPortfolioWarning">
                            <i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i> @lang('seeker::messages.portfolio')
                        </button>
                    @endif
                </div>
            </article>

            @if($publication->portfolio_type === 'images' && $publication->images->isNotEmpty())
                <section class="card">
                    <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::messages.references')</h2></div>
                    <div class="card-body">
                        <div class="row g-3">
                            @foreach($publication->images as $image)
                                <div class="col-md-6">
                                    <a href="{{ route('seeker.images.show', $image) }}" target="_blank" rel="noopener">
                                        <img src="{{ route('seeker.images.show', $image) }}" class="seeker-gallery-image rounded" alt="{{ $image->original_name }}">
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        </div>

        <aside class="col-lg-4">
            <div class="card position-sticky seeker-sidebar">
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <img src="{{ $publication->user->getAvatar(64) }}" width="64" height="64" class="rounded-circle" alt="">
                        <div><strong>{{ $publication->user->name }}</strong><div class="small text-muted">@lang('seeker::messages.types.'.$publication->type)</div></div>
                    </div>

                    <div class="seeker-price-box rounded p-3 mb-3">
                        <small class="d-block text-muted mb-1">@lang('seeker::messages.fields.pricing_type')</small>
                        <strong class="fs-5">
                            @include('seeker::publications._price', ['publication' => $publication])
                        </strong>
                    </div>

                    @if(auth()->id() === $publication->user_id)
                        <hr>
                        <h2 class="h6">@lang('seeker::messages.management')</h2>
                        <div class="d-grid gap-2">
                            <a class="btn btn-outline-primary" href="{{ route('seeker.publications.edit', $publication) }}"><i class="bi bi-pencil me-1" aria-hidden="true"></i> @lang('seeker::messages.edit')</a>
                            @if($publication->status !== 'hidden')
                                <form method="POST" action="{{ route('seeker.publications.status', $publication) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $publication->status === 'active' ? 'closed' : 'active' }}">
                                    <button class="btn btn-outline-secondary w-100"><i class="bi bi-{{ $publication->status === 'active' ? 'pause' : 'play' }} me-1" aria-hidden="true"></i> @lang($publication->status === 'active' ? 'seeker::messages.close' : 'seeker::messages.reopen')</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('seeker.publications.destroy', $publication) }}" onsubmit="return confirm(@js(trans('seeker::messages.delete_confirm')))">
                                @csrf @method('DELETE')
                                <button class="btn btn-outline-danger w-100"><i class="bi bi-trash me-1" aria-hidden="true"></i> @lang('seeker::messages.delete')</button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </aside>
    </div>

    @if($publication->portfolio_type === 'external' && $publication->portfolio_url)
        <div class="modal fade" id="externalPortfolioWarning" tabindex="-1" aria-labelledby="externalPortfolioWarningTitle" aria-describedby="externalPortfolioWarningDescription" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0 pb-0">
                        <div class="seeker-external-warning-icon" aria-hidden="true"><i class="bi bi-box-arrow-up-right"></i></div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('messages.actions.close')"></button>
                    </div>
                    <div class="modal-body px-4 pt-3 pb-4">
                        <h2 class="h4" id="externalPortfolioWarningTitle">@lang('seeker::messages.external_warning.title')</h2>
                        <p class="text-muted" id="externalPortfolioWarningDescription">@lang('seeker::messages.external_warning.description')</p>
                        <div class="seeker-external-destination rounded p-3">
                            <small class="d-block text-muted mb-1">@lang('seeker::messages.external_warning.destination')</small>
                            <span class="font-monospace text-break">{{ $publication->portfolio_url }}</span>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 px-4 pb-4">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('seeker::messages.external_warning.cancel')</button>
                        <a class="btn btn-primary" href="{{ $publication->portfolio_url }}" target="_blank" rel="noopener noreferrer nofollow">
                            @lang('seeker::messages.external_warning.continue') <i class="bi bi-arrow-right ms-1" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
