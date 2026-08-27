@extends('layouts.app')

@section('title', trans('seeker::messages.restrictions.details.title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="row justify-content-center py-lg-5">
        <div class="col-lg-8 col-xl-7">
            <div class="card border-warning overflow-hidden">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning-emphasis flex-shrink-0" style="width: 3.5rem; height: 3.5rem"><i class="bi bi-shield-lock fs-3" aria-hidden="true"></i></span>
                        <div>
                            <span class="badge text-bg-warning mb-2">@lang('seeker::messages.restrictions.details.badge')</span>
                            <h1 class="h3 mb-2">@lang('seeker::messages.restrictions.details.title')</h1>
                            <p class="text-body-secondary mb-0">@lang('seeker::messages.restrictions.details.types.'.$restriction->type)</p>
                        </div>
                    </div>

                    <dl class="row g-3 mb-4">
                        <dt class="col-sm-4 text-body-secondary">@lang('seeker::messages.restrictions.details.reason')</dt>
                        <dd class="col-sm-8"><div class="rounded bg-body-tertiary p-3" style="white-space: pre-wrap">{{ $restriction->reason }}</div></dd>
                        <dt class="col-sm-4 text-body-secondary">@lang('seeker::messages.restrictions.details.duration')</dt>
                        <dd class="col-sm-8 fw-semibold mb-0">
                            @if($restriction->expires_at)
                                @lang('seeker::messages.restrictions.details.until', ['date' => format_date($restriction->expires_at, true)])
                            @else
                                @lang('seeker::messages.restrictions.details.indefinite')
                            @endif
                        </dd>
                    </dl>

                    <div class="alert alert-light border mb-4"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>@lang('seeker::messages.restrictions.details.help')</div>
                    <a class="btn btn-primary" href="{{ route('home') }}"><i class="bi bi-house me-1" aria-hidden="true"></i>@lang('seeker::messages.restrictions.details.back_home')</a>
                </div>
            </div>
        </div>
    </div>
@endsection
