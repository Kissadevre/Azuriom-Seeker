@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.restrictions.title'))

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
        @include('seeker::admin._header', [
            'headerIcon' => 'bi-person-lock',
            'headerTitle' => trans('seeker::admin.restrictions.title'),
            'headerSubtitle' => trans('seeker::admin.restrictions.subtitle'),
            'headerTotal' => $restrictions->total(),
            'headerTotalIcon' => 'bi-person-lock',
            'headerTone' => 'danger',
        ])

        <form method="GET" class="seeker-admin-toolbar mb-4">
            <div class="seeker-admin-toolbar-title"><i class="bi bi-search" aria-hidden="true"></i>@lang('seeker::admin.restrictions.search_title')</div>
            <div class="row g-3 align-items-start">
                <div class="col-md">
                    @include('seeker::admin._user-combobox', [
                        'comboboxId' => 'restrictionFilterUser',
                        'comboboxUser' => $selectedUser,
                        'comboboxAutoSubmit' => true,
                    ])
                </div>
                <div class="col-md"><label class="form-label small fw-semibold" for="restrictionFilterState">@lang('seeker::admin.restrictions.state')</label><select id="restrictionFilterState" name="state" class="form-select">@foreach(['active', 'history', 'all'] as $restrictionState)<option value="{{ $restrictionState }}" @selected($state === $restrictionState)>@lang('seeker::admin.restrictions.states.'.$restrictionState)</option>@endforeach</select></div>
                <div class="col-md-auto seeker-admin-filter-actions seeker-admin-filter-submit">
                    <button class="btn btn-primary"><i class="bi bi-search me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.filter')</button>
                    @if($selectedUser || $state !== 'active')
                        <a class="btn btn-outline-secondary" href="{{ route('seeker.admin.restrictions.index') }}"><i class="bi bi-x-lg me-1" aria-hidden="true"></i>@lang('seeker::admin.clear_filters')</a>
                    @endif
                </div>
            </div>
        </form>

        @if($selectedUser)
            <div class="card seeker-admin-card mb-4">
                <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="seeker-admin-user">
                        <img src="{{ $selectedUser->getAvatar(48) }}" class="rounded-circle" alt="">
                        <div><h2 class="h5 mb-0">{{ $selectedUser->name }}</h2><span class="small text-body-secondary">@lang('seeker::admin.restrictions.workspace_subtitle')</span></div>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if($selectedUserProfileRestricted)
                            <span class="badge text-bg-dark seeker-admin-status"><i class="bi bi-eye-slash" aria-hidden="true"></i>@lang('seeker::admin.restrictions.profile_hidden')</span>
                        @else
                            <a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.profiles.show', $selectedUser) }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.view_profile')</a>
                        @endif
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center gap-2 mb-4"><i class="bi bi-plus-circle text-primary" aria-hidden="true"></i><h3 class="h5 mb-0">@lang('seeker::admin.restrictions.apply_title')</h3></div>
                <form method="POST" action="{{ route('seeker.admin.restrictions.store') }}">
                    @csrf
                    <input type="hidden" name="user_id" value="{{ $selectedUser->id }}">
                    <div class="row g-4">
                        <div class="col-lg-6"><label class="form-label fw-semibold" for="restrictionDuration">@lang('seeker::admin.restrictions.duration')</label><select id="restrictionDuration" name="duration" class="form-select" data-restriction-duration required><option value="indefinite" @selected(old('duration', 'indefinite') === 'indefinite')>@lang('seeker::admin.restrictions.indefinite')</option><option value="until" @selected(old('duration') === 'until')>@lang('seeker::admin.restrictions.until')</option></select></div>
                        <div class="col-lg-6" data-restriction-expiration><label class="form-label fw-semibold" for="restrictionExpires">@lang('seeker::admin.restrictions.expires_at')</label><input id="restrictionExpires" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}" class="form-control @error('expires_at') is-invalid @enderror">@error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">@lang('seeker::admin.restrictions.expires_help')</div></div>
                        <div class="col-12">
                            <label class="form-label fw-semibold d-block">@lang('seeker::admin.restrictions.type')</label>
                            <div class="row g-2">
                                @foreach(['publish' => 'bi-megaphone', 'contact' => 'bi-chat-dots', 'profile' => 'bi-person-badge', 'access' => 'bi-shield-x'] as $restrictionType => $restrictionIcon)
                                    <div class="col-md-6 col-xl-3 seeker-admin-choice"><input class="visually-hidden" type="radio" id="restrictionType_{{ $restrictionType }}" name="type" value="{{ $restrictionType }}" @checked(old('type', 'publish') === $restrictionType)><label for="restrictionType_{{ $restrictionType }}"><i class="bi {{ $restrictionIcon }} text-primary fs-5" aria-hidden="true"></i><span class="fw-semibold">@lang('seeker::admin.restrictions.types.'.$restrictionType)</span></label></div>
                                @endforeach
                            </div>
                            @error('type')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12"><label class="form-label fw-semibold" for="restrictionReason">@lang('seeker::admin.restrictions.reason')</label><textarea id="restrictionReason" name="reason" rows="3" minlength="5" maxlength="1000" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>@error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                    </div>
                    <button class="btn btn-warning mt-4"><i class="bi bi-person-lock me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.apply')</button>
                </form>
                </div>
                <div class="card-footer p-0 bg-transparent">
                    <div class="accordion accordion-flush" id="restrictionPublicationActions">
                        <div class="accordion-item bg-transparent">
                            <h3 class="accordion-header"><button class="accordion-button collapsed text-danger bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#restrictionPublicationActionsBody" aria-expanded="false" aria-controls="restrictionPublicationActionsBody"><i class="bi bi-exclamation-octagon me-2" aria-hidden="true"></i>@lang('seeker::admin.restrictions.danger_title')</button></h3>
                            <div id="restrictionPublicationActionsBody" class="accordion-collapse collapse" data-bs-parent="#restrictionPublicationActions"><div class="accordion-body"><p class="text-body-secondary">@lang('seeker::admin.restrictions.remove_publications_help', ['user' => $selectedUser->name])</p><form method="POST" action="{{ route('seeker.admin.restrictions.publications.remove', $selectedUser) }}" onsubmit="return confirm(@js(trans('seeker::admin.restrictions.remove_publications_confirm', ['user' => $selectedUser->name])))">@csrf @method('DELETE')<div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="confirm" value="1" id="confirmRemovePublications" required><label class="form-check-label" for="confirmRemovePublications">@lang('seeker::admin.restrictions.remove_publications_acknowledge')</label></div><button class="btn btn-outline-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.remove_publications')</button></form></div></div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="card seeker-admin-card mb-4">
                <div class="card-body py-5 text-center"><span class="seeker-admin-empty-icon"><i class="bi bi-person-plus" aria-hidden="true"></i></span><h2 class="h5 mt-3">@lang('seeker::admin.restrictions.select_user_title')</h2><p class="text-body-secondary mb-0">@lang('seeker::admin.restrictions.select_user_help')</p></div>
            </div>
        @endif

        <div class="card seeker-admin-card">
            <div class="card-header d-flex justify-content-between align-items-center"><h2>@lang('seeker::admin.restrictions.history_title')</h2><span class="badge rounded-pill text-bg-secondary">{{ $restrictions->total() }}</span></div>
            <div class="table-responsive"><table class="table table-hover align-middle seeker-admin-table mb-0"><thead><tr><th>@lang('seeker::admin.restrictions.user')</th><th>@lang('seeker::admin.restrictions.type')</th><th>@lang('seeker::admin.restrictions.reason')</th><th>@lang('seeker::admin.restrictions.applied_by')</th><th>@lang('seeker::admin.restrictions.duration')</th><th>@lang('seeker::admin.status')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead><tbody>
                @forelse($restrictions as $restriction)
                    <tr>
                        <td><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.admin.restrictions.index', ['user_id' => $restriction->user_id, 'state' => $state]) }}">{{ $restriction->user->name }}</a><div class="small text-body-secondary">ID #{{ $restriction->user_id }}</div></td>
                        <td><span class="badge rounded-pill text-bg-light">@lang('seeker::admin.restrictions.types.'.$restriction->type)</span></td>
                        <td style="min-width: 18rem; white-space: pre-wrap">{{ $restriction->reason }}</td>
                        <td><span class="fw-semibold">{{ $restriction->createdBy->name }}</span><div class="small text-body-secondary">{{ format_date($restriction->created_at, true) }}</div>@if($restriction->revokedBy)<div class="small text-success mt-1">@lang('seeker::admin.restrictions.revoked_by', ['user' => $restriction->revokedBy->name, 'date' => format_date($restriction->revoked_at, true)])</div>@endif</td>
                        <td class="text-nowrap">@if($restriction->expires_at){{ format_date($restriction->expires_at, true) }}@else @lang('seeker::admin.restrictions.indefinite') @endif</td>
                        <td>@if($restriction->isActive())<span class="badge text-bg-danger seeker-admin-status">@lang('seeker::admin.restrictions.statuses.active')</span>@elseif($restriction->isExpired())<span class="badge text-bg-secondary seeker-admin-status">@lang('seeker::admin.restrictions.statuses.expired')</span>@else<span class="badge text-bg-success seeker-admin-status">@lang('seeker::admin.restrictions.statuses.revoked')</span>@endif</td>
                        <td class="text-end">@if($restriction->isActive())<div class="seeker-admin-action-group"><form method="POST" action="{{ route('seeker.admin.restrictions.revoke', $restriction) }}" onsubmit="return confirm(@js(trans('seeker::admin.restrictions.revoke_confirm')))">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success" title="@lang('seeker::admin.restrictions.revoke')" aria-label="@lang('seeker::admin.restrictions.revoke')" data-bs-toggle="tooltip"><i class="bi bi-unlock" aria-hidden="true"></i></button></form></div>@endif</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.restrictions.empty')</div></td></tr>
                @endforelse
            </tbody></table></div>
            @if($restrictions->hasPages())<div class="seeker-admin-pagination">{{ $restrictions->links() }}</div>@endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-user-combobox]').forEach((combobox) => {
                const input = combobox.querySelector('[data-user-combobox-input]');
                const value = combobox.querySelector('[data-user-combobox-value]');
                const results = combobox.querySelector('[data-user-combobox-results]');
                const spinner = combobox.querySelector('[data-user-combobox-spinner]');
                const status = combobox.querySelector('[data-user-combobox-status]');
                const search = combobox.querySelector('[data-user-combobox-search]');
                const selected = combobox.querySelector('[data-user-combobox-selected]');
                const selectedAvatar = combobox.querySelector('[data-user-combobox-selected-avatar]');
                const selectedName = combobox.querySelector('[data-user-combobox-selected-name]');
                const selectedId = combobox.querySelector('[data-user-combobox-selected-id]');
                const clear = combobox.querySelector('[data-user-combobox-clear]');
                const form = combobox.closest('form');
                const required = combobox.dataset.required === 'true';
                const autoSubmit = combobox.dataset.autoSubmit === 'true';
                let options = [];
                let activeIndex = -1;
                let debounceTimer;
                let requestController;

                const closeResults = () => {
                    results.hidden = true;
                    input.setAttribute('aria-expanded', 'false');
                    input.removeAttribute('aria-activedescendant');
                    activeIndex = -1;
                };

                const setActiveOption = (index) => {
                    if (options.length === 0) {
                        return;
                    }

                    activeIndex = Math.max(0, Math.min(index, options.length - 1));
                    options.forEach((option, optionIndex) => option.classList.toggle('active', optionIndex === activeIndex));
                    options[activeIndex].scrollIntoView({ block: 'nearest' });
                    input.setAttribute('aria-activedescendant', options[activeIndex].id);
                };

                const chooseUser = (user) => {
                    value.value = user.id;
                    input.setCustomValidity('');
                    status.textContent = '';
                    selectedAvatar.src = user.avatar;
                    selectedName.textContent = user.name;
                    selectedId.textContent = `ID #${user.id}`;
                    selected.hidden = false;
                    search.hidden = true;
                    closeResults();
                    if (autoSubmit) form?.requestSubmit();
                };

                const renderMessage = (message, isError = false) => {
                    results.replaceChildren();
                    const item = document.createElement('div');
                    item.className = `list-group-item small text-body-secondary${isError ? ' text-danger' : ''}`;
                    item.textContent = message;
                    results.append(item);
                    status.textContent = message;
                    options = [];
                    results.hidden = false;
                    input.setAttribute('aria-expanded', 'true');
                };

                const renderUsers = (users) => {
                    results.replaceChildren();
                    status.textContent = '';
                    options = users.map((user, index) => {
                        const option = document.createElement('button');
                        option.type = 'button';
                        option.id = `${input.id}Option${index}`;
                        option.className = 'list-group-item list-group-item-action seeker-admin-user-option';
                        option.setAttribute('role', 'option');

                        const avatar = document.createElement('img');
                        avatar.src = user.avatar;
                        avatar.alt = '';
                        avatar.width = 32;
                        avatar.height = 32;
                        avatar.className = 'rounded-circle';

                        const text = document.createElement('span');
                        const name = document.createElement('strong');
                        name.className = 'd-block';
                        name.textContent = user.name;
                        const id = document.createElement('small');
                        id.className = 'text-body-secondary';
                        id.textContent = `ID #${user.id}`;
                        text.append(name, id);
                        option.append(avatar, text);
                        option.addEventListener('mousedown', (event) => event.preventDefault());
                        option.addEventListener('click', () => chooseUser(user));
                        results.append(option);

                        return option;
                    });

                    if (options.length === 0) {
                        renderMessage(combobox.dataset.noResults);
                        return;
                    }

                    results.hidden = false;
                    input.setAttribute('aria-expanded', 'true');
                };

                const searchUsers = async () => {
                    const query = input.value.trim();

                    if (query.length < 2) {
                        spinner.hidden = true;
                        closeResults();
                        return;
                    }

                    requestController?.abort();
                    requestController = new AbortController();
                    spinner.hidden = false;
                    status.textContent = combobox.dataset.searching;

                    try {
                        const response = await fetch(`${combobox.dataset.searchUrl}?q=${encodeURIComponent(query)}`, {
                            headers: { Accept: 'application/json' },
                            signal: requestController.signal,
                        });

                        if (! response.ok) {
                            throw new Error('Unable to search users');
                        }

                        const payload = await response.json();
                        renderUsers(payload.data ?? []);
                    } catch (error) {
                        if (error.name !== 'AbortError') {
                            renderMessage(combobox.dataset.searchError, true);
                        }
                    } finally {
                        if (! requestController?.signal.aborted) {
                            spinner.hidden = true;
                        }
                    }
                };

                input.addEventListener('input', () => {
                    value.value = '';
                    input.setCustomValidity('');
                    window.clearTimeout(debounceTimer);
                    debounceTimer = window.setTimeout(searchUsers, 250);
                });

                input.addEventListener('keydown', (event) => {
                    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                        event.preventDefault();
                        setActiveOption(activeIndex + (event.key === 'ArrowDown' ? 1 : -1));
                    } else if (event.key === 'Enter' && activeIndex >= 0) {
                        event.preventDefault();
                        options[activeIndex].click();
                    } else if (event.key === 'Escape') {
                        closeResults();
                    }
                });

                input.addEventListener('blur', () => window.setTimeout(closeResults, 100));
                clear.addEventListener('click', () => {
                    value.value = '';
                    input.value = '';
                    input.setCustomValidity('');
                    selected.hidden = true;
                    search.hidden = false;
                    input.focus();
                });
                form?.addEventListener('submit', (event) => {
                    if ((required || input.value.trim() !== '') && value.value === '') {
                        event.preventDefault();
                        input.setCustomValidity(combobox.dataset.selectionRequired);
                        input.reportValidity();
                    }
                });
            });

            const duration = document.querySelector('[data-restriction-duration]');
            const expiration = document.querySelector('[data-restriction-expiration]');
            const input = expiration?.querySelector('input');
            const updateExpiration = () => {
                const timed = duration?.value === 'until';
                expiration?.classList.toggle('d-none', ! timed);
                if (input) input.required = timed;
            };
            duration?.addEventListener('change', updateExpiration);
            updateExpiration();
        });
    </script>
@endpush
