@php($fieldPrefix = $modalId)
@php($showErrors = (int) old('user_id') === $user->id && (int) old($contextName) === $contextId)
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true" data-restriction-modal @if($showErrors) data-show-on-load @endif>
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title fs-5" id="{{ $modalId }}Label"><i class="bi bi-person-lock text-warning me-2" aria-hidden="true"></i>@lang('seeker::admin.restrictions.apply_to_user', ['user' => $user->name])</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('messages.actions.close')"></button>
            </div>
            <form method="POST" action="{{ route('seeker.admin.restrictions.store') }}">
                @csrf
                <input type="hidden" name="user_id" value="{{ $user->id }}">
                <input type="hidden" name="{{ $contextName }}" value="{{ $contextId }}">
                <div class="modal-body">
                    <div class="seeker-admin-user mb-4"><img src="{{ $user->getAvatar(40) }}" width="40" height="40" class="rounded-circle" alt=""><div><strong class="d-block">{{ $user->name }}</strong><span class="small text-body-secondary">{{ $contextLabel }}</span></div></div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold d-block">@lang('seeker::admin.restrictions.type')</label>
                        <div class="row g-2">
                            @foreach(['publish' => 'bi-megaphone', 'contact' => 'bi-chat-dots', 'profile' => 'bi-person-badge', 'access' => 'bi-shield-x'] as $restrictionType => $restrictionIcon)
                                <div class="col-md-6 seeker-admin-choice"><input class="visually-hidden" type="radio" id="{{ $fieldPrefix }}Type_{{ $restrictionType }}" name="type" value="{{ $restrictionType }}" @checked($showErrors ? old('type', 'publish') === $restrictionType : $restrictionType === 'publish')><label for="{{ $fieldPrefix }}Type_{{ $restrictionType }}"><i class="bi {{ $restrictionIcon }} text-primary fs-5" aria-hidden="true"></i><span class="fw-semibold">@lang('seeker::admin.restrictions.types.'.$restrictionType)</span></label></div>
                            @endforeach
                        </div>
                        @if($showErrors)
                            @error('type')
                                <div class="text-danger small mt-2">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6"><label class="form-label fw-semibold" for="{{ $fieldPrefix }}Duration">@lang('seeker::admin.restrictions.duration')</label><select id="{{ $fieldPrefix }}Duration" name="duration" class="form-select" data-restriction-duration required><option value="indefinite" @selected(! $showErrors || old('duration', 'indefinite') === 'indefinite')>@lang('seeker::admin.restrictions.indefinite')</option><option value="until" @selected($showErrors && old('duration') === 'until')>@lang('seeker::admin.restrictions.until')</option></select></div>
                        <div class="col-md-6" data-restriction-expiration>
                            <label class="form-label fw-semibold" for="{{ $fieldPrefix }}Expires">@lang('seeker::admin.restrictions.expires_at')</label>
                            <input id="{{ $fieldPrefix }}Expires" type="datetime-local" name="expires_at" value="{{ $showErrors ? old('expires_at') : '' }}" min="{{ now()->addMinute()->format('Y-m-d\TH:i') }}" class="form-control {{ $showErrors && $errors->has('expires_at') ? 'is-invalid' : '' }}">
                            @if($showErrors)
                                @error('expires_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            @endif
                            <div class="form-text">@lang('seeker::admin.restrictions.expires_help')</div>
                        </div>
                    </div>
                    <div>
                        <label class="form-label fw-semibold" for="{{ $fieldPrefix }}Reason">@lang('seeker::admin.restrictions.reason')</label>
                        <textarea id="{{ $fieldPrefix }}Reason" name="reason" rows="3" minlength="5" maxlength="1000" class="form-control {{ $showErrors && $errors->has('reason') ? 'is-invalid' : '' }}" required>{{ $showErrors ? old('reason') : '' }}</textarea>
                        @if($showErrors)
                            @error('reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        @endif
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('messages.actions.cancel')</button><button class="btn btn-warning"><i class="bi bi-person-lock me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.apply')</button></div>
            </form>
        </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                document.querySelectorAll('[data-restriction-modal]').forEach((modal) => {
                    const duration = modal.querySelector('[data-restriction-duration]');
                    const expiration = modal.querySelector('[data-restriction-expiration]');
                    const input = expiration?.querySelector('input');
                    const updateExpiration = () => {
                        const timed = duration?.value === 'until';
                        expiration?.classList.toggle('d-none', ! timed);
                        if (input) input.required = timed;
                    };
                    duration?.addEventListener('change', updateExpiration);
                    updateExpiration();

                    if (modal.hasAttribute('data-show-on-load')) {
                        bootstrap.Modal.getOrCreateInstance(modal).show();
                    }
                });
            });
        </script>
    @endpush
@endonce
