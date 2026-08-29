@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.settings.title'))

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
        @include('seeker::admin._header', [
            'headerIcon' => 'bi-sliders',
            'headerTitle' => trans('seeker::admin.settings.title'),
            'headerSubtitle' => trans('seeker::admin.settings.subtitle'),
        ])

        <form method="POST" action="{{ route('seeker.admin.settings.save') }}">
            @csrf
            @php($globalEnabled = (bool) old('seeker_enabled', $seekerEnabled))

            <div class="card seeker-admin-card border-{{ $globalEnabled ? 'success' : 'danger' }} mb-4">
                <div class="card-header d-flex align-items-center gap-2"><i class="bi bi-power text-{{ $globalEnabled ? 'success' : 'danger' }}" aria-hidden="true"></i><h2>@lang('seeker::admin.settings.global.title')</h2><span class="badge rounded-pill text-bg-{{ $globalEnabled ? 'success' : 'danger' }} ms-auto">@lang($globalEnabled ? 'seeker::admin.settings.global.status_enabled' : 'seeker::admin.settings.global.status_disabled')</span></div>
                <div class="seeker-admin-switch-row">
                    <label for="seeker_enabled" class="mb-0"><span class="d-block fw-semibold">@lang('seeker::admin.settings.global.enabled')</span><small class="text-body-secondary">@lang('seeker::admin.settings.global.enabled_help')</small></label>
                    <div class="form-check form-switch"><input type="hidden" name="seeker_enabled" value="0"><input class="form-check-input" type="checkbox" role="switch" id="seeker_enabled" name="seeker_enabled" value="1" @checked($globalEnabled)></div>
                </div>
                @unless($globalEnabled)<div class="alert alert-danger mx-3 mb-3"><i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>@lang('seeker::admin.settings.global.disabled_notice')</div>@endunless
            </div>

            <div class="card seeker-admin-card mb-4">
                <div class="card-header d-flex align-items-center gap-2"><i class="bi bi-toggles text-primary" aria-hidden="true"></i><h2>@lang('seeker::admin.settings.features.title')</h2></div>
                <div class="card-body p-0">
                    @foreach([
                        'publications_enabled' => $publicationsEnabled,
                        'new_conversations_enabled' => $newConversationsEnabled,
                        'biographies_enabled' => $biographiesEnabled,
                        'message_images_enabled' => $messageImagesEnabled,
                    ] as $settingName => $settingValue)
                        <div class="seeker-admin-switch-row">
                            <label for="{{ $settingName }}" class="mb-0"><span class="d-block fw-semibold">@lang('seeker::admin.settings.features.'.$settingName)</span><small class="text-body-secondary">@lang('seeker::admin.settings.features.'.$settingName.'_help')</small></label>
                            <div class="form-check form-switch"><input type="hidden" name="{{ $settingName }}" value="0"><input class="form-check-input" type="checkbox" role="switch" id="{{ $settingName }}" name="{{ $settingName }}" value="1" @checked(old($settingName, $settingValue))></div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card seeker-admin-card mb-4">
                <div class="card-header d-flex align-items-start gap-2"><i class="bi bi-person-lines-fill text-primary mt-1" aria-hidden="true"></i><div><h2>@lang('seeker::admin.settings.user_menu.title')</h2><p class="small text-body-secondary mb-0 mt-1">@lang('seeker::admin.settings.user_menu.subtitle')</p></div></div>
                <div class="card-body p-0">
                    @foreach($userMenuItems as $menuItem => $menuSettings)
                        @php($enabledInput = 'user_menu.'.$menuItem.'.enabled')
                        @php($iconInput = 'user_menu.'.$menuItem.'.icon')
                        <div class="seeker-admin-switch-row seeker-admin-menu-row">
                            <label for="user_menu_{{ $menuItem }}_enabled" class="mb-0"><span class="d-block fw-semibold">@lang('seeker::admin.settings.user_menu.items.'.$menuItem)</span><small class="text-body-secondary">@lang('seeker::admin.settings.user_menu.items.'.$menuItem.'_help')</small></label>
                            <div class="seeker-admin-menu-controls">
                                <div class="seeker-admin-icon-field" data-bootstrap-icon-field>
                                    <label class="form-label small text-body-secondary" for="user_menu_{{ $menuItem }}_icon">@lang('seeker::admin.settings.user_menu.icon_label')</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi {{ old($iconInput, $menuSettings['icon']) }}" data-bootstrap-icon-preview aria-hidden="true"></i></span>
                                        <input type="text" id="user_menu_{{ $menuItem }}_icon" name="user_menu[{{ $menuItem }}][icon]" value="{{ old($iconInput, $menuSettings['icon']) }}" class="form-control @error($iconInput) is-invalid @enderror" placeholder="bi-briefcase" maxlength="64" pattern="bi-[a-z0-9]+(?:-[a-z0-9]+)*" data-bootstrap-icon-input required>
                                        @error($iconInput)<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="form-check form-switch"><input type="hidden" name="user_menu[{{ $menuItem }}][enabled]" value="0"><input class="form-check-input" type="checkbox" role="switch" id="user_menu_{{ $menuItem }}_enabled" name="user_menu[{{ $menuItem }}][enabled]" value="1" @checked(old($enabledInput, $menuSettings['enabled']))></div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-body text-body-secondary small"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>@lang('seeker::admin.settings.user_menu.icon_help')</div>
            </div>

            <div class="card seeker-admin-card mb-4" data-portfolio-settings data-portfolio-settings-message="@lang('seeker::admin.settings.portfolio_types.at_least_one')">
                <div class="card-header d-flex align-items-start gap-2"><i class="bi bi-collection-play text-primary mt-1" aria-hidden="true"></i><div><h2>@lang('seeker::admin.settings.portfolio_types.title')</h2><p class="small text-body-secondary mb-0 mt-1">@lang('seeker::admin.settings.portfolio_types.subtitle')</p></div></div>
                <div class="card-body p-0">
                    @foreach(\Azuriom\Plugin\Seeker\Models\Publication::portfolioTypes() as $portfolioType)
                        <div class="seeker-admin-switch-row">
                            <label for="portfolio_type_{{ $portfolioType }}" class="mb-0"><span class="d-block fw-semibold">@lang('seeker::admin.settings.portfolio_types.'.$portfolioType)</span><small class="text-body-secondary">@lang('seeker::admin.settings.portfolio_types.'.$portfolioType.'_help')</small></label>
                            <div><div class="form-check form-switch"><input type="hidden" name="portfolio_types[{{ $portfolioType }}]" value="0"><input class="form-check-input" type="checkbox" role="switch" id="portfolio_type_{{ $portfolioType }}" name="portfolio_types[{{ $portfolioType }}]" value="1" data-portfolio-setting @checked(old('portfolio_types.'.$portfolioType, $portfolioTypes[$portfolioType]))></div>@error('portfolio_types.'.$portfolioType)<div class="text-danger small mt-1">{{ $message }}</div>@enderror</div>
                        </div>
                    @endforeach
                </div>
                <div class="card-footer bg-body">
                    @error('portfolio_types')<div class="text-danger small"><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>@enderror
                    <div class="text-danger small d-none" data-portfolio-settings-client-error><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>@lang('seeker::admin.settings.portfolio_types.at_least_one')</div>
                    <div class="small text-body-secondary mt-1"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>@lang('seeker::admin.settings.portfolio_types.existing_help')</div>
                </div>
            </div>

            <div class="card seeker-admin-card mb-4">
                <div class="card-header d-flex align-items-start gap-2"><i class="bi bi-speedometer2 text-primary mt-1" aria-hidden="true"></i><div><h2>@lang('seeker::admin.settings.rate_limits.title')</h2><p class="small text-body-secondary mb-0 mt-1">@lang('seeker::admin.settings.rate_limits.subtitle')</p></div></div>
                <div class="table-responsive">
                    <table class="table align-middle seeker-admin-table mb-0">
                        <thead><tr><th>@lang('seeker::admin.settings.rate_limits.rule')</th><th>@lang('seeker::admin.settings.rate_limits.attempts')</th><th>@lang('seeker::admin.settings.rate_limits.window')</th></tr></thead>
                        <tbody>
                            @foreach($rateLimits as $name => $limit)
                                <tr>
                                    <td style="min-width: 18rem"><label class="fw-semibold" for="limit_{{ $name }}_attempts">@lang('seeker::admin.settings.rate_limits.rules.'.$name)</label><div class="small text-body-secondary mt-1">@lang('seeker::admin.settings.rate_limits.rules.'.$name.'_help')</div></td>
                                    <td style="min-width: 11rem"><input id="limit_{{ $name }}_attempts" type="number" min="0" max="10000" name="limits[{{ $name }}][attempts]" value="{{ old('limits.'.$name.'.attempts', $limit['attempts']) }}" class="form-control @error('limits.'.$name.'.attempts') is-invalid @enderror" required>@error('limits.'.$name.'.attempts')<div class="invalid-feedback">{{ $message }}</div>@enderror</td>
                                    <td style="min-width: 14rem"><div class="input-group"><input type="number" min="1" max="10080" name="limits[{{ $name }}][window]" value="{{ old('limits.'.$name.'.window', $limit['window']) }}" class="form-control @error('limits.'.$name.'.window') is-invalid @enderror" required><span class="input-group-text">@lang('seeker::admin.settings.rate_limits.minutes')</span>@error('limits.'.$name.'.window')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-body text-body-secondary small"><i class="bi bi-info-circle me-1" aria-hidden="true"></i>@lang('seeker::admin.settings.rate_limits.disable_help')</div>
            </div>

            <div class="text-end"><button class="btn btn-primary px-4" type="submit"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button></div>
        </form>
    </div>
@endsection

@push('scripts')
    <script src="{{ plugin_asset('seeker', 'js/admin-settings.js') }}" defer></script>
@endpush
