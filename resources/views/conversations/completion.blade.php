@extends('layouts.app')

@section('title', trans('seeker::messages.completion.confirm_title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="mb-4">
        <a class="text-decoration-none" href="{{ route('seeker.conversations.show', $conversation) }}">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> @lang('seeker::messages.completion.back')
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h3 mb-2">@lang('seeker::messages.completion.confirm_title')</h1>
                    <p class="text-muted">@lang('seeker::messages.completion.confirm_description', ['author' => $conversation->author->name])</p>
                    <span class="badge text-bg-warning mb-4">@lang('seeker::messages.completion.attempt_count', ['count' => $conversation->delivery_attempts])</span>

                    <div class="card bg-body-tertiary mb-4">
                        <div class="card-body">
                            <div class="fw-semibold mb-2">{{ $conversation->publication->title }}</div>
                            @if($conversation->isHourlyCommission())
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span>@lang('seeker::messages.completion.hours')</span>
                                    <strong>@lang('seeker::messages.completion.hours_value', ['hours' => $conversation->proposed_hours])</strong>
                                </div>
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span>@lang('seeker::messages.completion.hourly_rate')</span>
                                    <strong>{{ format_money((float) $conversation->publication->price) }}</strong>
                                </div>
                            @endif
                            <div class="d-flex justify-content-between gap-3 border-top pt-2">
                                <span>@lang('seeker::messages.completion.service_total')</span>
                                <strong>{{ format_money($servicePoints) }}</strong>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-wallet2 me-2" aria-hidden="true"></i>
                        @lang($conversation->isHourlyCommission()
                            ? 'seeker::messages.completion.hourly_payment_notice'
                            : 'seeker::messages.completion.fixed_payment_notice',
                            ['points' => format_money($servicePoints)])
                    </div>

                    <div class="alert alert-warning">
                        <i class="bi bi-lock me-2" aria-hidden="true"></i>
                        @lang('seeker::messages.completion.read_only_notice')
                    </div>

                    <form method="POST" action="{{ route('seeker.conversations.completion.confirm', $conversation) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label" for="tipPoints">@lang('seeker::messages.completion.tip')</label>
                            <div class="input-group">
                                <input id="tipPoints" type="number" name="tip_points" value="{{ old('tip_points', 0) }}" min="0" max="999999999999.99" step="0.01" class="form-control @error('tip_points') is-invalid @enderror">
                                <span class="input-group-text">@lang('seeker::messages.completion.points')</span>
                                @error('tip_points')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="form-text">@lang('seeker::messages.completion.balance', ['balance' => format_money((float) auth()->user()->money)])</div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="finalMessage">@lang('seeker::messages.completion.final_message')</label>
                            <textarea id="finalMessage" name="final_message" rows="4" maxlength="2000" class="form-control @error('final_message') is-invalid @enderror">{{ old('final_message') }}</textarea>
                            <div class="form-text">@lang('seeker::messages.completion.final_message_help')</div>
                            @error('final_message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <a class="btn btn-outline-secondary" href="{{ route('seeker.conversations.show', $conversation) }}">@lang('seeker::messages.completion.cancel')</a>
                            <button class="btn btn-success" type="submit">
                                <i class="bi bi-check-circle me-1" aria-hidden="true"></i> @lang('seeker::messages.completion.confirm_action')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
