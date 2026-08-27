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
                <a class="btn btn-outline-danger" href="{{ route('seeker.admin.reports.index') }}"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('seeker::admin.reports.title')</a>
            </div>
        </div>

        <div class="card seeker-admin-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle seeker-admin-table mb-0">
                    <thead><tr><th>@lang('seeker::admin.publication')</th><th>@lang('seeker::admin.author')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.created_at')</th><th>@lang('seeker::admin.updated_at')</th><th class="text-center">@lang('seeker::admin.conversation_count')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                    <tbody>
                        @forelse($publications as $publication)
                            <tr>
                                <td style="min-width: 15rem"><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.admin.publications.show', $publication) }}">{{ $publication->title }}</a><div class="small text-body-secondary mt-1"><i class="bi bi-{{ $publication->type === 'commission' ? 'briefcase' : 'people' }} me-1" aria-hidden="true"></i>@lang('seeker::messages.types.'.$publication->type)</div></td>
                                <td><div class="seeker-admin-user"><img src="{{ $publication->user->getAvatar(38) }}" class="rounded-circle" alt=""><div><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $publication->user) }}" target="_blank" rel="noopener">{{ $publication->user->name }}</a><div class="small text-body-secondary">ID #{{ $publication->user_id }}</div></div></div></td>
                                <td>@if($publication->trashed())<span class="badge text-bg-dark seeker-admin-status"><i class="bi bi-trash" aria-hidden="true"></i>@lang('seeker::admin.publications.removed')</span>@else<span class="badge {{ $publication->status === 'active' ? 'text-bg-success' : ($publication->status === 'hidden' ? 'text-bg-danger' : 'text-bg-secondary') }} seeker-admin-status">@lang('seeker::messages.statuses.'.$publication->status)</span>@endif</td>
                                <td class="text-nowrap text-body-secondary small">{{ format_date($publication->created_at, true) }}</td>
                                <td class="text-nowrap text-body-secondary small">{{ format_date($publication->updated_at, true) }}</td>
                                <td class="text-center"><a class="badge rounded-pill text-bg-primary text-decoration-none" href="{{ route('seeker.admin.publications.show', $publication) }}#conversations">{{ $publication->conversations_count }}</a></td>
                                <td class="text-end text-nowrap">
                                    <div class="d-flex justify-content-end align-items-center gap-2">
                                        @unless($publication->trashed())
                                            <form method="POST" action="{{ route('seeker.admin.publications.status', $publication) }}" class="d-flex gap-2">
                                                @csrf @method('PATCH')
                                                <select name="status" class="form-select form-select-sm" aria-label="@lang('seeker::admin.set_status')">
                                                    @foreach(\Azuriom\Plugin\Seeker\Models\Publication::statuses() as $publicationStatus)
                                                        <option value="{{ $publicationStatus }}" @selected($publication->status === $publicationStatus)>@lang('seeker::messages.statuses.'.$publicationStatus)</option>
                                                    @endforeach
                                                </select>
                                                <button class="btn btn-sm btn-primary" title="@lang('messages.actions.save')" data-bs-toggle="tooltip"><i class="bi bi-check-lg" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.save')</span></button>
                                            </form>
                                        @endunless
                                        <div class="seeker-admin-action-group"><a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.admin.publications.show', $publication) }}" title="@lang('seeker::admin.details')" aria-label="@lang('seeker::admin.details')" data-bs-toggle="tooltip"><i class="bi bi-eye" aria-hidden="true"></i></a></div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-megaphone" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.empty')</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($publications->hasPages())<div class="seeker-admin-pagination">{{ $publications->links() }}</div>@endif
        </div>
    </div>
@endsection
