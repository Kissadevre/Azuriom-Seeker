@extends('layouts.app')

@section('title', trans('seeker::messages.reports.title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="mb-4">
        <a class="text-decoration-none" href="{{ route('seeker.conversations.show', $conversation) }}">
            <i class="bi bi-arrow-left me-1" aria-hidden="true"></i> @lang('seeker::messages.reports.back')
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="{{ $reportedUser->getAvatar(56) }}" width="56" height="56" class="rounded-circle" alt="">
                        <div>
                            <h1 class="h3 mb-1">@lang('seeker::messages.reports.title')</h1>
                            <div class="text-muted">@lang('seeker::messages.reports.reported_user', ['user' => $reportedUser->name])</div>
                            <div class="small text-muted">{{ $conversation->publication->title }}</div>
                        </div>
                    </div>

                    <p>@lang('seeker::messages.reports.description')</p>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2" aria-hidden="true"></i>
                        @lang('seeker::messages.reports.notice')
                    </div>

                    <form method="POST" action="{{ route('seeker.conversations.reports.store', $conversation) }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="reportReason">@lang('seeker::messages.reports.reason')</label>
                            <select id="reportReason" name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                                <option value="" disabled @selected(old('reason') === null)>@lang('seeker::messages.reports.select_reason')</option>
                                @foreach(\Azuriom\Plugin\Seeker\Models\ConversationReport::reasons() as $reason)
                                    <option value="{{ $reason }}" @selected(old('reason') === $reason)>@lang('seeker::messages.reports.reasons.'.$reason)</option>
                                @endforeach
                            </select>
                            @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label" for="reportDetails">@lang('seeker::messages.reports.details')</label>
                            <textarea id="reportDetails" name="details" rows="7" minlength="20" maxlength="2000" class="form-control @error('details') is-invalid @enderror" required>{{ old('details') }}</textarea>
                            <div class="form-text">@lang('seeker::messages.reports.details_help')</div>
                            @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="d-flex flex-wrap justify-content-end gap-2">
                            <a class="btn btn-outline-secondary" href="{{ route('seeker.conversations.show', $conversation) }}">@lang('seeker::messages.reports.cancel')</a>
                            <button class="btn btn-danger" type="submit">
                                <i class="bi bi-flag me-1" aria-hidden="true"></i> @lang('seeker::messages.reports.submit')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
