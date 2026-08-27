@extends('layouts.app')

@section('title', trans('seeker::messages.profile_reports.title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="mb-4"><a class="text-decoration-none" href="{{ route('seeker.profiles.show', $user) }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('seeker::messages.profile_reports.back')</a></div>
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('seeker.profiles.reports.store', $user) }}" class="card">
                @csrf
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex align-items-center gap-3 mb-4"><img src="{{ $user->getAvatar(56) }}" width="56" height="56" class="rounded-circle" alt=""><div><h1 class="h3 mb-1">@lang('seeker::messages.profile_reports.title')</h1><div class="text-muted">@lang('seeker::messages.profile_reports.reporting', ['user' => $user->name])</div></div></div>
                    <div class="alert alert-info"><i class="bi bi-info-circle me-2" aria-hidden="true"></i>@lang('seeker::messages.profile_reports.notice')</div>
                    <div class="mb-3">
                        <label class="form-label" for="profileReportReason">@lang('seeker::messages.profile_reports.reason')</label>
                        <select id="profileReportReason" name="reason" class="form-select @error('reason') is-invalid @enderror" required>
                            <option value="" disabled @selected(old('reason') === null)>@lang('seeker::messages.profile_reports.select_reason')</option>
                            @foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::reasons() as $reason)<option value="{{ $reason }}" @selected(old('reason') === $reason)>@lang('seeker::messages.profile_reports.reasons.'.$reason)</option>@endforeach
                        </select>
                        @error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="profileReportDetails">@lang('seeker::messages.profile_reports.details')</label>
                        <textarea id="profileReportDetails" name="details" rows="7" minlength="20" maxlength="2000" class="form-control @error('details') is-invalid @enderror" required>{{ old('details') }}</textarea>
                        <div class="form-text">@lang('seeker::messages.profile_reports.details_help')</div>
                        @error('details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex justify-content-end gap-2"><a class="btn btn-outline-secondary" href="{{ route('seeker.profiles.show', $user) }}">@lang('seeker::messages.profile_reports.cancel')</a><button class="btn btn-danger" type="submit"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('seeker::messages.profile_reports.submit')</button></div>
                </div>
            </form>
        </div>
    </div>
@endsection
