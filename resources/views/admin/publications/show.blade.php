@extends('admin.layouts.admin')

@section('title', $publication->title)

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
    @if($publication->trashed())
        <div class="alert alert-dark"><i class="bi bi-trash me-2" aria-hidden="true"></i>@lang('seeker::admin.publications.removed_notice')</div>
    @endif
    <div class="seeker-admin-header">
        <div class="seeker-admin-heading">
            <span class="seeker-admin-icon"><i class="bi bi-megaphone" aria-hidden="true"></i></span>
            <div>
            <a class="text-decoration-none" href="{{ route('seeker.admin.publications.index') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('seeker::admin.publications.back')</a>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <h1 class="h3 mb-0">{{ $publication->title }}</h1>
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
                                        <button class="dropdown-item d-flex align-items-center gap-2 {{ $publication->status === $publicationStatus ? 'active' : '' }}" @disabled($publication->status === $publicationStatus)>
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
                @if($publication->is_pinned)
                    <span class="badge text-bg-warning seeker-admin-status"><i class="bi bi-pin-angle-fill" aria-hidden="true"></i>@lang('seeker::admin.publications.pinned')</span>
                @endif
                <span class="badge text-bg-light">@lang('seeker::messages.types.'.$publication->type)</span>
            </div>
        </div>
        </div>
        <div class="seeker-admin-header-actions d-flex flex-wrap gap-2">
            @unless($publication->trashed())
            <form method="POST" action="{{ route('seeker.admin.publications.status', $publication) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="pinned" value="{{ $publication->is_pinned ? 0 : 1 }}">
                <button class="btn {{ $publication->is_pinned ? 'btn-warning' : 'btn-outline-warning' }}"><i class="bi bi-pin-angle{{ $publication->is_pinned ? '-fill' : '' }} me-1" aria-hidden="true"></i>@lang($publication->is_pinned ? 'seeker::admin.publications.unpin' : 'seeker::admin.publications.pin')</button>
            </form>
            <a class="btn btn-outline-primary" href="{{ route('seeker.publications.show', $publication) }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>@lang('seeker::admin.publications.public_view')</a>
            @endunless
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card seeker-admin-card">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.publications.content')</h2></div>
                <div class="card-body">
                    <div style="white-space: pre-wrap">{{ $publication->description }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card seeker-admin-card mb-4">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.publications.author')</h2></div>
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between gap-3 mb-4">
                        <div class="d-flex align-items-center gap-3"><img src="{{ $publication->user->getAvatar(64) }}" width="64" height="64" class="rounded-circle" alt=""><div><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $publication->user) }}" target="_blank" rel="noopener">{{ $publication->user->name }}</a><div class="small text-muted">ID #{{ $publication->user->id }}</div></div></div>
                        <button class="btn btn-sm btn-outline-warning flex-shrink-0" type="button" data-bs-toggle="modal" data-bs-target="#publicationRestriction{{ $publication->user->id }}" title="@lang('seeker::admin.restrictions.apply_to_user', ['user' => $publication->user->name])" aria-label="@lang('seeker::admin.restrictions.apply_to_user', ['user' => $publication->user->name])"><i class="bi bi-person-lock" aria-hidden="true"></i></button>
                    </div>
                    <dl class="row mb-0 small">
                        <dt class="col-6">@lang('seeker::admin.created_at')</dt><dd class="col-6 text-end">{{ format_date($publication->created_at, true) }}</dd>
                        <dt class="col-6">@lang('seeker::admin.updated_at')</dt><dd class="col-6 text-end">{{ format_date($publication->updated_at, true) }}</dd>
                        <dt class="col-6">@lang('seeker::admin.publications.published_at')</dt><dd class="col-6 text-end">{{ $publication->published_at ? format_date($publication->published_at, true) : trans('seeker::admin.not_available') }}</dd>
                        <dt class="col-6">@lang('seeker::admin.publications.visibility')</dt><dd class="col-6 text-end">@lang($publication->is_guest_visible ? 'seeker::messages.visibility.public' : 'seeker::messages.visibility.members')</dd>
                        <dt class="col-6">@lang('seeker::admin.publications.price')</dt><dd class="col-6 text-end">@include('seeker::publications._price', ['publication' => $publication])</dd>
                        <dt class="col-6">@lang('seeker::admin.conversation_count')</dt><dd class="col-6 text-end">{{ $publication->conversations_count }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card seeker-admin-card">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.publications.portfolio')</h2></div>
                <div class="card-body">
                    @if($publication->portfolio_type === 'external' && filled($publication->portfolio_url))
                        <div class="small text-muted mb-1">@lang('seeker::admin.publications.external_portfolio')</div>
                        <a href="{{ $publication->portfolio_url }}" target="_blank" rel="noopener noreferrer nofollow" class="text-break">{{ $publication->portfolio_url }}</a>
                    @elseif($publication->portfolio_type === 'images' && $publication->images->isNotEmpty())
                        <div class="row g-3">
                            @foreach($publication->images as $image)
                                <div class="col-6"><a href="{{ route('seeker.images.show', $image) }}" target="_blank" rel="noopener" class="text-decoration-none"><img src="{{ route('seeker.images.show', $image) }}" class="img-fluid rounded border" alt="{{ $image->original_name }}"><div class="small text-body-secondary text-truncate mt-1">{{ $image->original_name }}</div></a></div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted mb-0">@lang('seeker::admin.publications.no_portfolio')</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('seeker::admin._restriction-modal', [
        'user' => $publication->user,
        'modalId' => 'publicationRestriction'.$publication->user->id,
        'contextName' => 'publication_id',
        'contextId' => $publication->id,
        'contextLabel' => $publication->title,
    ])

    @if($reports->isNotEmpty())
        <div class="card seeker-admin-card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center gap-2"><h2 class="h5 mb-0">@lang('seeker::admin.publications.linked_reports')</h2><span class="badge text-bg-warning">{{ $reports->count() }}</span></div>
            <div class="table-responsive"><table class="table table-hover align-middle seeker-admin-table seeker-admin-row-details-table seeker-admin-report-status-table mb-0"><thead><tr><th><span class="visually-hidden">@lang('seeker::admin.publications.report_details')</span></th><th>@lang('seeker::admin.reports.reporter')</th><th>@lang('seeker::admin.reports.reason')</th><th>@lang('seeker::admin.created_at')</th></tr></thead><tbody>
                @foreach($reports as $report)
                    <tr class="seeker-admin-report-status-{{ $report->status }}">
                        <td class="seeker-admin-row-details-control"><span class="visually-hidden">@lang('seeker::admin.status'): @lang('seeker::admin.reports.statuses.'.$report->status). </span><button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#publicationReportDetails{{ $report->id }}" aria-expanded="false" aria-controls="publicationReportDetails{{ $report->id }}" title="@lang('seeker::admin.publications.report_details')"><i class="bi bi-chevron-right" aria-hidden="true"></i><span class="visually-hidden">@lang('seeker::admin.publications.report_details')</span></button></td>
                        <td><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $report->reporter) }}" target="_blank" rel="noopener">{{ $report->reporter->name }}</a></td>
                        <td>@lang('seeker::messages.publication_reports.reasons.'.$report->reason)</td>
                        <td class="text-nowrap text-body-secondary small">{{ format_date($report->created_at, true) }}</td>
                    </tr>
                    <tr class="seeker-admin-row-details seeker-admin-report-status-{{ $report->status }}"><td colspan="4"><div class="collapse" id="publicationReportDetails{{ $report->id }}"><div class="seeker-admin-row-details-content"><div class="small fw-semibold text-body-secondary mb-2"><i class="bi bi-card-text me-1" aria-hidden="true"></i>@lang('seeker::admin.publications.report_details')</div><div style="white-space: pre-wrap">{{ $report->details }}</div><div class="seeker-admin-detail-item mt-3"><div class="small fw-semibold text-body-secondary mb-2">@lang('seeker::admin.publications.reported_snapshot')</div><strong class="d-block">{{ $report->reported_title }}</strong><div class="mt-1" style="white-space: pre-wrap">{{ $report->reported_description }}</div>@if(filled($report->reported_portfolio_url))<div class="small text-break mt-2">{{ $report->reported_portfolio_url }}</div>@endif</div><div class="seeker-admin-row-details-actions"><form method="POST" action="{{ route('seeker.admin.reports.update', ['publication', $report->id]) }}" class="d-flex flex-wrap justify-content-end gap-2">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm" aria-label="@lang('seeker::admin.reports.update_status')">@foreach(\Azuriom\Plugin\Seeker\Models\PublicationReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($report->status === $reportStatus)>@lang('seeker::admin.reports.statuses.'.$reportStatus)</option>@endforeach</select><button class="btn btn-sm btn-primary"><i class="bi bi-check-lg me-1" aria-hidden="true"></i>@lang('messages.actions.save')</button></form></div></div></div></td></tr>
                @endforeach
            </tbody></table></div>
        </div>
    @endif

    <div class="card seeker-admin-card" id="conversations">
        <div class="card-header d-flex justify-content-between align-items-center gap-2"><h2 class="h5 mb-0">@lang('seeker::admin.publications.linked_conversations')</h2><span class="badge text-bg-primary">{{ $publication->conversations_count }}</span></div>
        <div class="table-responsive">
            <table class="table table-hover align-middle seeker-admin-table seeker-admin-report-status-table mb-0">
                <thead><tr><th>@lang('seeker::admin.publications.client')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.publications.completion')</th><th class="text-center">@lang('seeker::admin.publications.messages')</th><th>@lang('seeker::admin.publications.dates')</th></tr></thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        @php($reportStatus = $conversation->pending_reports_count > 0 ? 'pending' : ($conversation->reviewed_reports_count > 0 ? 'reviewed' : ($conversation->dismissed_reports_count > 0 ? 'dismissed' : null)))
                        <tr @class(['seeker-admin-report-status-'.$reportStatus => $reportStatus !== null])>
                            <td>@if($conversation->reports_count > 0)<span class="visually-hidden">@choice('seeker::admin.conversations.report_count', $conversation->reports_count, ['count' => $conversation->reports_count]) </span>@endif<a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $conversation->client) }}" target="_blank" rel="noopener">{{ $conversation->client->name }}</a><div><a class="small text-decoration-none" href="{{ route('seeker.admin.conversations.show', $conversation) }}">@lang('seeker::admin.conversations.detail_title', ['id' => $conversation->id])</a></div></td>
                            <td><span class="badge {{ $conversation->status === 'completed' ? 'text-bg-success' : ($conversation->status === 'closed' ? 'text-bg-danger' : 'text-bg-primary') }} seeker-admin-status">@lang('seeker::admin.publications.conversation_statuses.'.$conversation->status)</span></td>
                            <td>@lang('seeker::admin.publications.completion_statuses.'.$conversation->completion_status)</td>
                            <td class="text-center">{{ $conversation->messages_count }}</td>
                            <td class="text-nowrap small"><div class="seeker-admin-date"><i class="bi bi-calendar-plus" aria-hidden="true"></i><span><span class="visually-hidden">@lang('seeker::admin.created_at'): </span>{{ format_date($conversation->created_at, true) }}</span></div><div class="seeker-admin-date text-body-secondary mt-1"><i class="bi bi-clock-history" aria-hidden="true"></i><span><span class="visually-hidden">@lang('seeker::admin.publications.last_activity'): </span>{{ format_date($conversation->updated_at, true) }}</span></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.publications.no_conversations')</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($conversations->hasPages())<div class="seeker-admin-pagination">{{ $conversations->links() }}</div>@endif
    </div>
    </div>
@endsection
