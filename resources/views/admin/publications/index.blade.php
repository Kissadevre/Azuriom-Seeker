@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.publications.title'))

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
        @include('seeker::admin._header', [
            'headerIcon' => 'bi-megaphone',
            'headerTitle' => trans('seeker::admin.publications.title'),
            'headerSubtitle' => trans('seeker::admin.publications.subtitle'),
            'headerTotal' => $publications->total(),
            'headerTotalIcon' => 'bi-megaphone',
        ])

        <div class="seeker-admin-toolbar mb-4">
            <div class="seeker-admin-toolbar-title"><i class="bi bi-funnel" aria-hidden="true"></i>@lang('seeker::admin.publications.filters')</div>
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                <form method="GET" class="flex-grow-1" style="max-width: 24rem">
                    <label class="form-label small fw-semibold" for="publicationStatus">@lang('seeker::admin.status')</label>
                    <select id="publicationStatus" name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">@lang('seeker::admin.all_statuses')</option>
                        @foreach(\Azuriom\Plugin\Seeker\Models\Publication::statuses() as $publicationStatus)
                            <option value="{{ $publicationStatus }}" @selected($status === $publicationStatus)>@lang('seeker::messages.statuses.'.$publicationStatus)</option>
                        @endforeach
                    </select>
                </form>
                <div class="seeker-admin-filter-actions">
                    @if(filled($status))
                        <a class="btn btn-outline-secondary" href="{{ route('seeker.admin.publications.index') }}"><i class="bi bi-x-lg me-1" aria-hidden="true"></i>@lang('seeker::admin.clear_filters')</a>
                    @endif
                    <a class="btn btn-outline-danger" href="{{ route('seeker.admin.reports.index') }}"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('seeker::admin.reports.title')</a>
                </div>
            </div>
        </div>

        <div class="card seeker-admin-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle seeker-admin-table mb-0">
                    <thead><tr><th>@lang('seeker::admin.publication')</th><th>@lang('seeker::admin.author')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.publications.dates')</th><th class="text-center">@lang('seeker::admin.conversation_count')</th><th><span class="visually-hidden">@lang('seeker::admin.actions')</span></th></tr></thead>
                    <tbody>
                        @forelse($publications as $publication)
                            <tr>
                                <td style="min-width: 15rem"><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.admin.publications.show', $publication) }}">{{ $publication->title }}</a><div class="small text-body-secondary mt-1"><i class="bi bi-{{ $publication->type === 'commission' ? 'briefcase' : 'people' }} me-1" aria-hidden="true"></i>@lang('seeker::messages.types.'.$publication->type)</div></td>
                                <td><div class="seeker-admin-user"><img src="{{ $publication->user->getAvatar(38) }}" class="rounded-circle" alt=""><div><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $publication->user) }}" target="_blank" rel="noopener">{{ $publication->user->name }}</a><div class="small text-body-secondary">ID #{{ $publication->user_id }}</div></div></div></td>
                                <td>
                                    @if($publication->trashed())
                                        <span class="badge text-bg-dark seeker-admin-status"><i class="bi bi-trash" aria-hidden="true"></i>@lang('seeker::admin.publications.removed')</span>
                                    @else
                                        <div class="dropdown">
                                            <button class="badge border-0 dropdown-toggle {{ $publication->status === 'active' ? 'text-bg-success' : ($publication->status === 'hidden' ? 'text-bg-danger' : 'text-bg-secondary') }} seeker-admin-status seeker-admin-status-toggle"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false"
                                                    aria-label="@lang('seeker::admin.set_status')">
                                                @lang('seeker::messages.statuses.'.$publication->status)
                                            </button>
                                            <ul class="dropdown-menu seeker-admin-status-menu">
                                                @foreach([
                                                    \Azuriom\Plugin\Seeker\Models\Publication::STATUS_ACTIVE => ['check-lg', 'text-success'],
                                                    \Azuriom\Plugin\Seeker\Models\Publication::STATUS_CLOSED => ['x-lg', 'text-secondary'],
                                                    \Azuriom\Plugin\Seeker\Models\Publication::STATUS_HIDDEN => ['eye-slash', 'text-danger'],
                                                ] as $publicationStatus => [$statusIcon, $statusColor])
                                                    <li>
                                                        <form method="POST" action="{{ route('seeker.admin.publications.status', $publication) }}">
                                                            @csrf @method('PATCH')
                                                            <input type="hidden" name="status" value="{{ $publicationStatus }}">
                                                            <button class="dropdown-item d-flex align-items-center gap-2 {{ $publication->status === $publicationStatus ? 'active' : '' }}"
                                                                    @disabled($publication->status === $publicationStatus)>
                                                                <i class="bi bi-{{ $statusIcon }} {{ $publication->status === $publicationStatus ? '' : $statusColor }}" aria-hidden="true"></i>
                                                                <span>@lang('seeker::messages.statuses.'.$publicationStatus)</span>
                                                                @if($publication->status === $publicationStatus)<i class="bi bi-check ms-auto" aria-hidden="true"></i>@endif
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif
                                </td>
                                <td class="text-nowrap small">
                                    <div class="seeker-admin-date"><i class="bi bi-calendar-plus" aria-hidden="true"></i><span><span class="visually-hidden">@lang('seeker::admin.created_at'): </span>{{ format_date($publication->created_at, true) }}</span></div>
                                    <div class="seeker-admin-date text-body-secondary mt-1"><i class="bi bi-pencil" aria-hidden="true"></i><span><span class="visually-hidden">@lang('seeker::admin.updated_at'): </span>{{ format_date($publication->updated_at, true) }}</span></div>
                                </td>
                                <td class="text-center"><a class="badge rounded-pill text-bg-primary text-decoration-none" href="{{ route('seeker.admin.publications.show', $publication) }}#conversations">{{ $publication->conversations_count }}</a></td>
                                <td class="text-end text-nowrap">
                                    <div class="seeker-admin-action-group"><a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.admin.publications.show', $publication) }}" title="@lang('seeker::admin.details')" aria-label="@lang('seeker::admin.details')" data-bs-toggle="tooltip"><i class="bi bi-eye" aria-hidden="true"></i></a></div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-megaphone" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.empty')</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($publications->hasPages())<div class="seeker-admin-pagination">{{ $publications->links() }}</div>@endif
        </div>
    </div>
@endsection
