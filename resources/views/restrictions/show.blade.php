@extends('layouts.app')

@section('title', trans('seeker::messages.restrictions.details.title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    @php($restrictionIcon = match($restriction->type) {
        \Azuriom\Plugin\Seeker\Models\UserRestriction::TYPE_PUBLISH => 'bi-megaphone',
        \Azuriom\Plugin\Seeker\Models\UserRestriction::TYPE_CONTACT => 'bi-chat-dots',
        \Azuriom\Plugin\Seeker\Models\UserRestriction::TYPE_PROFILE => 'bi-person-badge',
        default => 'bi-shield-x',
    })
    <div class="row justify-content-center py-4 py-lg-5">
        <div class="col-lg-9 col-xl-8">
            <div class="card seeker-restriction-card overflow-hidden">
                <div class="seeker-restriction-header p-4 p-lg-5">
                    <div class="d-flex align-items-center gap-3 gap-lg-4">
                        <span class="seeker-restriction-icon flex-shrink-0"><i class="bi {{ $restrictionIcon }}" aria-hidden="true"></i></span>
                        <div class="min-w-0">
                            <span class="badge text-bg-warning mb-2">@lang('seeker::messages.restrictions.details.badge')</span>
                            <h1 class="h2 mb-2">@lang('seeker::messages.restrictions.details.title')</h1>
                            <p class="lead text-body-secondary mb-0">@lang('seeker::messages.restrictions.details.types.'.$restriction->type)</p>
                        </div>
                    </div>
                </div>

                <div class="card-body p-4 p-lg-5">
                    <div class="row g-3 mb-4">
                        <div class="col-lg-7">
                            <section class="seeker-restriction-detail h-100">
                                <div class="seeker-restriction-detail-label"><i class="bi bi-card-text" aria-hidden="true"></i>@lang('seeker::messages.restrictions.details.reason')</div>
                                <div class="seeker-restriction-reason" style="white-space: pre-wrap">{{ $restriction->reason }}</div>
                            </section>
                        </div>
                        <div class="col-lg-5">
                            <section class="seeker-restriction-detail h-100">
                                <div class="seeker-restriction-detail-label"><i class="bi bi-clock-history" aria-hidden="true"></i>@lang('seeker::messages.restrictions.details.duration')</div>
                                <div class="fw-semibold fs-5">
                            @if($restriction->expires_at)
                                @lang('seeker::messages.restrictions.details.until', ['date' => format_date($restriction->expires_at, true)])
                            @else
                                @lang('seeker::messages.restrictions.details.indefinite')
                            @endif
                                </div>
                            </section>
                        </div>
                    </div>

                    <div class="seeker-restriction-footer d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pt-4">
                        <div class="d-flex align-items-start gap-2 text-body-secondary"><i class="bi bi-info-circle mt-1 flex-shrink-0" aria-hidden="true"></i><span>@lang('seeker::messages.restrictions.details.help')</span></div>
                        <a class="btn btn-primary flex-shrink-0" href="{{ route('home') }}"><i class="bi bi-house me-1" aria-hidden="true"></i>@lang('seeker::messages.restrictions.details.back_home')</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
