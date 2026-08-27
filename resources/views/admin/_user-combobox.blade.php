@php
    $comboboxRequired = $comboboxRequired ?? false;
    $comboboxShowError = $comboboxShowError ?? false;
    $comboboxAutoSubmit = $comboboxAutoSubmit ?? false;
@endphp

<div class="seeker-admin-user-combobox"
     data-user-combobox
     data-search-url="{{ route('seeker.admin.restrictions.users.search') }}"
     data-required="{{ $comboboxRequired ? 'true' : 'false' }}"
     data-auto-submit="{{ $comboboxAutoSubmit ? 'true' : 'false' }}"
     data-searching="@lang('seeker::admin.restrictions.user_searching')"
     data-no-results="@lang('seeker::admin.restrictions.user_no_results')"
     data-search-error="@lang('seeker::admin.restrictions.user_search_error')"
     data-selection-required="@lang('seeker::admin.restrictions.user_selection_required')">
    <label class="form-label fw-semibold" for="{{ $comboboxId }}">@lang('seeker::admin.restrictions.user_id')</label>
    <input type="hidden" name="user_id" value="{{ $comboboxUser?->id }}" data-user-combobox-value>

    <div class="seeker-admin-selected-user" data-user-combobox-selected @if($comboboxUser === null) hidden @endif>
        <img src="{{ $comboboxUser?->getAvatar(40) ?? '' }}" width="40" height="40" class="rounded-circle" alt="" data-user-combobox-selected-avatar>
        <div class="min-w-0">
            <strong class="d-block text-truncate" data-user-combobox-selected-name>{{ $comboboxUser?->name }}</strong>
            <small class="text-body-secondary" data-user-combobox-selected-id>@if($comboboxUser)ID #{{ $comboboxUser->id }}@endif</small>
        </div>
        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" data-user-combobox-clear>
            <i class="bi bi-arrow-repeat me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.change_user')
        </button>
    </div>

    <div data-user-combobox-search @if($comboboxUser !== null) hidden @endif>
        <div class="input-group">
            <span class="input-group-text"><i class="bi bi-person" aria-hidden="true"></i></span>
            <input id="{{ $comboboxId }}"
                   type="text"
                   class="form-control {{ $comboboxShowError && $errors->has('user_id') ? 'is-invalid' : '' }}"
                   placeholder="@lang('seeker::admin.restrictions.user_search_placeholder')"
                   autocomplete="off"
                   role="combobox"
                   aria-autocomplete="list"
                   aria-controls="{{ $comboboxId }}Results"
                   aria-describedby="{{ $comboboxId }}Help"
                   aria-expanded="false"
                   data-user-combobox-input>
            <span class="input-group-text seeker-admin-user-combobox-spinner" hidden data-user-combobox-spinner>
                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            </span>
            @if($comboboxShowError)
                @error('user_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>

        <div id="{{ $comboboxId }}Results"
             class="list-group seeker-admin-user-combobox-results"
             role="listbox"
             hidden
             data-user-combobox-results></div>
        <div id="{{ $comboboxId }}Help" class="form-text">@lang('seeker::admin.restrictions.user_search_help')</div>
    </div>
    <span class="visually-hidden" aria-live="polite" data-user-combobox-status></span>
</div>
