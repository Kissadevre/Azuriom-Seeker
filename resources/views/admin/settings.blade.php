@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.settings.title'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">@lang('seeker::admin.settings.title')</h1>
            <p class="text-muted mb-0">@lang('seeker::admin.settings.subtitle')</p>
        </div>
    </div>

    <form method="POST" action="{{ route('seeker.admin.settings.save') }}">
        @csrf
        @php($globalEnabled = (bool) old('seeker_enabled', $seekerEnabled))

        <div class="card border-{{ $globalEnabled ? 'success' : 'danger' }} mb-4">
            <div class="card-header text-bg-{{ $globalEnabled ? 'success' : 'danger' }}"><h2 class="h5 mb-0">@lang('seeker::admin.settings.global.title')</h2></div>
            <div class="card-body">
                <div class="form-check form-switch">
                    <input type="hidden" name="seeker_enabled" value="0">
                    <input class="form-check-input" type="checkbox" role="switch" id="seeker_enabled" name="seeker_enabled" value="1" @checked($globalEnabled)>
                    <label class="form-check-label fw-semibold" for="seeker_enabled">@lang('seeker::admin.settings.global.enabled')</label>
                    <div class="form-text">@lang('seeker::admin.settings.global.enabled_help')</div>
                </div>
                @unless($globalEnabled)<div class="alert alert-danger mt-3 mb-0"><i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>@lang('seeker::admin.settings.global.disabled_notice')</div>@endunless
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.settings.features.title')</h2></div>
            <div class="card-body">
                @foreach([
                    'publications_enabled' => $publicationsEnabled,
                    'new_conversations_enabled' => $newConversationsEnabled,
                    'biographies_enabled' => $biographiesEnabled,
                    'message_images_enabled' => $messageImagesEnabled,
                ] as $settingName => $settingValue)
                    <div class="form-check form-switch {{ ! $loop->last ? 'mb-4' : '' }}">
                        <input type="hidden" name="{{ $settingName }}" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="{{ $settingName }}" name="{{ $settingName }}" value="1" @checked(old($settingName, $settingValue))>
                        <label class="form-check-label fw-semibold" for="{{ $settingName }}">@lang('seeker::admin.settings.features.'.$settingName)</label>
                        <div class="form-text">@lang('seeker::admin.settings.features.'.$settingName.'_help')</div>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h2 class="h5 mb-1">@lang('seeker::admin.settings.rate_limits.title')</h2>
                <p class="text-muted mb-0">@lang('seeker::admin.settings.rate_limits.subtitle')</p>
            </div>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>@lang('seeker::admin.settings.rate_limits.rule')</th><th>@lang('seeker::admin.settings.rate_limits.attempts')</th><th>@lang('seeker::admin.settings.rate_limits.window')</th></tr></thead>
                    <tbody>
                        @foreach($rateLimits as $name => $limit)
                            <tr>
                                <td style="min-width: 16rem">
                                    <label class="fw-semibold" for="limit_{{ $name }}_attempts">@lang('seeker::admin.settings.rate_limits.rules.'.$name)</label>
                                    <div class="small text-muted">@lang('seeker::admin.settings.rate_limits.rules.'.$name.'_help')</div>
                                </td>
                                <td style="min-width: 11rem">
                                    <input id="limit_{{ $name }}_attempts" type="number" min="0" max="10000" name="limits[{{ $name }}][attempts]" value="{{ old('limits.'.$name.'.attempts', $limit['attempts']) }}" class="form-control @error('limits.'.$name.'.attempts') is-invalid @enderror" required>
                                    @error('limits.'.$name.'.attempts')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </td>
                                <td style="min-width: 14rem">
                                    <div class="input-group">
                                        <input type="number" min="1" max="10080" name="limits[{{ $name }}][window]" value="{{ old('limits.'.$name.'.window', $limit['window']) }}" class="form-control @error('limits.'.$name.'.window') is-invalid @enderror" required>
                                        <span class="input-group-text">@lang('seeker::admin.settings.rate_limits.minutes')</span>
                                        @error('limits.'.$name.'.window')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer text-muted small">@lang('seeker::admin.settings.rate_limits.disable_help')</div>
        </div>

        <button class="btn btn-primary" type="submit"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button>
    </form>
@endsection
