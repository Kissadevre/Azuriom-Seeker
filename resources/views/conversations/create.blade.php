@extends('layouts.app')

@section('title', trans('seeker::messages.contact.title_'.$publication->type))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="mb-4">
                <a class="text-decoration-none" href="{{ route('seeker.publications.show', $publication) }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> {{ $publication->title }}</a>
            </div>

            <div class="card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-3">
                        <img src="{{ $publication->user->getAvatar(56) }}" width="56" height="56" class="rounded-circle" alt="">
                        <div class="flex-grow-1">
                            <h1 class="h4 mb-1">@lang('seeker::messages.contact.title_'.$publication->type)</h1>
                            <div class="text-muted">{{ $publication->user->name }} · {{ $publication->title }}</div>
                        </div>
                        <strong>@include('seeker::publications._price', ['publication' => $publication])</strong>
                    </div>
                </div>
            </div>

            @if($publication->requiresPointHold())
                <div class="alert alert-warning d-flex gap-3" role="alert">
                    <i class="bi bi-shield-lock fs-4" aria-hidden="true"></i>
                    <div>
                        <strong class="d-block">@lang('seeker::messages.escrow.hold_title')</strong>
                        @lang('seeker::messages.escrow.hold_description', ['price' => format_money((float) $publication->price)])
                        <div class="small mt-1">@lang('seeker::messages.escrow.balance', ['balance' => format_money((float) auth()->user()->money)])</div>
                    </div>
                </div>
            @elseif($publication->type === 'talent' && $publication->pricing_type === 'points')
                <div class="alert alert-info"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>@lang('seeker::messages.escrow.talent_notice')</div>
            @elseif($publication->pricing_type === 'points' && $publication->price_basis === 'hourly')
                <div class="alert alert-info"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>@lang('seeker::messages.escrow.hourly_notice')</div>
            @endif

            <form method="POST" action="{{ route('seeker.conversations.store', $publication) }}" class="card">
                @csrf
                <div class="card-body p-4">
                    <label class="form-label fw-semibold" for="contactMessage">@lang('seeker::messages.contact.message_'.$publication->type)</label>
                    <textarea id="contactMessage" name="content" rows="7" minlength="5" maxlength="2000" class="form-control @error('content') is-invalid @enderror" required autofocus>{{ old('content') }}</textarea>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @error('contact')<div class="alert alert-danger mt-3 mb-0">{{ $message }}</div>@enderror
                    <div class="form-text">@lang('seeker::messages.contact.message_help_'.$publication->type)</div>
                </div>
                <div class="card-footer d-flex justify-content-end gap-2 p-3">
                    <a class="btn btn-outline-secondary" href="{{ route('seeker.publications.show', $publication) }}">@lang('messages.actions.cancel')</a>
                    <button class="btn btn-primary"><i class="bi bi-send me-1" aria-hidden="true"></i> @lang('seeker::messages.contact.send_'.$publication->type)</button>
                </div>
            </form>
        </div>
    </div>
@endsection
