@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.restrictions.title'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">@lang('seeker::admin.restrictions.title')</h1><p class="text-muted mb-0">@lang('seeker::admin.restrictions.subtitle')</p></div>
        <form method="GET" class="d-flex gap-2">
            <input type="number" min="1" name="user_id" value="{{ $selectedUser?->id }}" class="form-control" placeholder="@lang('seeker::admin.restrictions.user_id')" aria-label="@lang('seeker::admin.restrictions.user_id')">
            <select name="state" class="form-select" aria-label="@lang('seeker::admin.restrictions.state')">
                @foreach(['active', 'history', 'all'] as $restrictionState)<option value="{{ $restrictionState }}" @selected($state === $restrictionState)>@lang('seeker::admin.restrictions.states.'.$restrictionState)</option>@endforeach
            </select>
            <button class="btn btn-outline-primary"><i class="bi bi-search" aria-hidden="true"></i><span class="visually-hidden">@lang('seeker::admin.restrictions.filter')</span></button>
        </form>
    </div>

    @if($selectedUser)
        <div class="alert alert-info d-flex flex-wrap justify-content-between align-items-center gap-3"><div><strong>{{ $selectedUser->name }}</strong><div class="small">@lang('seeker::admin.restrictions.selected_user', ['id' => $selectedUser->id])</div></div>@if($selectedUserProfileRestricted)<span class="badge text-bg-dark">@lang('seeker::admin.restrictions.profile_hidden')</span>@else<a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.profiles.show', $selectedUser) }}" target="_blank" rel="noopener">@lang('seeker::admin.restrictions.view_profile')</a>@endif</div>
    @endif

    <div class="card mb-4">
        <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.restrictions.apply_title')</h2></div>
        <div class="card-body">
            <form method="POST" action="{{ route('seeker.admin.restrictions.store') }}" class="row g-3">
                @csrf
                <div class="col-md-4"><label class="form-label" for="restrictionUser">@lang('seeker::admin.restrictions.user_id')</label><input id="restrictionUser" type="number" min="1" name="user_id" value="{{ old('user_id', $selectedUser?->id) }}" class="form-control @error('user_id') is-invalid @enderror" required>@error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label" for="restrictionType">@lang('seeker::admin.restrictions.type')</label><select id="restrictionType" name="type" class="form-select @error('type') is-invalid @enderror" required>@foreach(\Azuriom\Plugin\Seeker\Models\UserRestriction::types() as $restrictionType)<option value="{{ $restrictionType }}" @selected(old('type') === $restrictionType)>@lang('seeker::admin.restrictions.types.'.$restrictionType)</option>@endforeach</select>@error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-md-4"><label class="form-label" for="restrictionDuration">@lang('seeker::admin.restrictions.duration')</label><select id="restrictionDuration" name="duration" class="form-select" required><option value="indefinite" @selected(old('duration', 'indefinite') === 'indefinite')>@lang('seeker::admin.restrictions.indefinite')</option><option value="until" @selected(old('duration') === 'until')>@lang('seeker::admin.restrictions.until')</option></select></div>
                <div class="col-md-4"><label class="form-label" for="restrictionExpires">@lang('seeker::admin.restrictions.expires_at')</label><input id="restrictionExpires" type="datetime-local" name="expires_at" value="{{ old('expires_at') }}" class="form-control @error('expires_at') is-invalid @enderror">@error('expires_at')<div class="invalid-feedback">{{ $message }}</div>@enderror<div class="form-text">@lang('seeker::admin.restrictions.expires_help')</div></div>
                <div class="col-md-8"><label class="form-label" for="restrictionReason">@lang('seeker::admin.restrictions.reason')</label><textarea id="restrictionReason" name="reason" rows="3" minlength="5" maxlength="1000" class="form-control @error('reason') is-invalid @enderror" required>{{ old('reason') }}</textarea>@error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
                <div class="col-12"><button class="btn btn-danger"><i class="bi bi-person-lock me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.apply')</button></div>
            </form>
        </div>
    </div>

    @if($selectedUser)
        <div class="card border-danger mb-4">
            <div class="card-header text-bg-danger"><h2 class="h5 mb-0">@lang('seeker::admin.restrictions.danger_title')</h2></div>
            <div class="card-body">
                <p>@lang('seeker::admin.restrictions.remove_publications_help', ['user' => $selectedUser->name])</p>
                <form method="POST" action="{{ route('seeker.admin.restrictions.publications.remove', $selectedUser) }}" onsubmit="return confirm(@js(trans('seeker::admin.restrictions.remove_publications_confirm', ['user' => $selectedUser->name])))">
                    @csrf @method('DELETE')
                    <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="confirm" value="1" id="confirmRemovePublications" required><label class="form-check-label" for="confirmRemovePublications">@lang('seeker::admin.restrictions.remove_publications_acknowledge')</label></div>
                    <button class="btn btn-danger"><i class="bi bi-trash me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.remove_publications')</button>
                </form>
            </div>
        </div>
    @endif

    <div class="card overflow-hidden">
        <div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>@lang('seeker::admin.restrictions.user')</th><th>@lang('seeker::admin.restrictions.type')</th><th>@lang('seeker::admin.restrictions.reason')</th><th>@lang('seeker::admin.restrictions.applied_by')</th><th>@lang('seeker::admin.restrictions.duration')</th><th>@lang('seeker::admin.status')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead><tbody>
            @forelse($restrictions as $restriction)
                <tr>
                    <td><a class="fw-semibold text-decoration-none" href="{{ route('seeker.admin.restrictions.index', ['user_id' => $restriction->user_id, 'state' => $state]) }}">{{ $restriction->user->name }}</a><div class="small text-muted">ID #{{ $restriction->user_id }}</div></td>
                    <td>@lang('seeker::admin.restrictions.types.'.$restriction->type)</td>
                    <td style="min-width: 18rem; white-space: pre-wrap">{{ $restriction->reason }}</td>
                    <td>{{ $restriction->createdBy->name }}<div class="small text-muted">{{ format_date($restriction->created_at, true) }}</div>@if($restriction->revokedBy)<div class="small text-success">@lang('seeker::admin.restrictions.revoked_by', ['user' => $restriction->revokedBy->name, 'date' => format_date($restriction->revoked_at, true)])</div>@endif</td>
                    <td>@if($restriction->expires_at){{ format_date($restriction->expires_at, true) }}@else @lang('seeker::admin.restrictions.indefinite') @endif</td>
                    <td>@if($restriction->isActive())<span class="badge text-bg-danger">@lang('seeker::admin.restrictions.statuses.active')</span>@elseif($restriction->isExpired())<span class="badge text-bg-secondary">@lang('seeker::admin.restrictions.statuses.expired')</span>@else<span class="badge text-bg-success">@lang('seeker::admin.restrictions.statuses.revoked')</span>@endif</td>
                    <td class="text-end">@if($restriction->isActive())<form method="POST" action="{{ route('seeker.admin.restrictions.revoke', $restriction) }}" onsubmit="return confirm(@js(trans('seeker::admin.restrictions.revoke_confirm')))">@csrf @method('PATCH')<button class="btn btn-sm btn-outline-success"><i class="bi bi-unlock me-1" aria-hidden="true"></i>@lang('seeker::admin.restrictions.revoke')</button></form>@endif</td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-5">@lang('seeker::admin.restrictions.empty')</td></tr>
            @endforelse
        </tbody></table></div>
    </div>
    @if($restrictions->hasPages())<div class="d-flex justify-content-center mt-4">{{ $restrictions->links() }}</div>@endif
@endsection
