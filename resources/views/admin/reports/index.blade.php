@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.reports.title'))

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
        @include('seeker::admin._header', [
            'headerIcon' => 'bi-flag',
            'headerTitle' => trans('seeker::admin.reports.title'),
            'headerSubtitle' => trans('seeker::admin.reports.subtitle'),
            'headerTotal' => trans('seeker::admin.reports.pending_count', ['count' => $counts['pending']]),
        ])

        <div class="row g-3 mb-4">
            @foreach(['publication' => 'bi-megaphone', 'profile' => 'bi-person-badge', 'conversation' => 'bi-chat-dots'] as $reportType => $icon)
                <div class="col-md-4"><a class="seeker-admin-stat d-flex align-items-center gap-3 text-body text-decoration-none {{ $type === $reportType ? 'border-primary' : '' }}" href="{{ route('seeker.admin.reports.index', ['type' => $reportType, 'status' => $status]) }}"><span class="seeker-admin-stat-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></span><div><div class="seeker-admin-stat-value">{{ $counts[$reportType] }}</div><div class="small text-body-secondary">@lang('seeker::admin.reports.types.'.$reportType)</div></div></a></div>
            @endforeach
        </div>

        <form method="GET" class="seeker-admin-toolbar mb-4">
            <div class="seeker-admin-toolbar-title"><i class="bi bi-funnel" aria-hidden="true"></i>@lang('seeker::admin.reports.filters')</div>
            <div class="row g-2 align-items-end">
                <div class="col-md"><label class="form-label small fw-semibold" for="reportType">@lang('seeker::admin.reports.type')</label><select id="reportType" name="type" class="form-select"><option value="">@lang('seeker::admin.reports.all_types')</option>@foreach(\Azuriom\Plugin\Seeker\Controllers\Admin\ReportController::TYPES as $reportType)<option value="{{ $reportType }}" @selected($type === $reportType)>@lang('seeker::admin.reports.types.'.$reportType)</option>@endforeach</select></div>
                <div class="col-md"><label class="form-label small fw-semibold" for="reportStatus">@lang('seeker::admin.status')</label><select id="reportStatus" name="status" class="form-select"><option value="">@lang('seeker::admin.reports.all_statuses')</option>@foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($status === $reportStatus)>@lang('seeker::admin.reports.statuses.'.$reportStatus)</option>@endforeach</select></div>
                <div class="col-md-auto"><button class="btn btn-primary w-100"><i class="bi bi-funnel me-1" aria-hidden="true"></i>@lang('seeker::admin.reports.filter')</button></div>
            </div>
        </form>

        <div class="card seeker-admin-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle seeker-admin-table mb-0">
                    <thead><tr><th>@lang('seeker::admin.reports.type')</th><th>@lang('seeker::admin.reports.target')</th><th>@lang('seeker::admin.reports.reporter')</th><th>@lang('seeker::admin.reports.reason')</th><th>@lang('seeker::admin.reports.details')</th><th>@lang('seeker::admin.created_at')</th><th>@lang('seeker::admin.status')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                    <tbody>
                        @forelse($reports as $item)
                            @php($report = $item->report)
                            @php($reportedUser = $item->report_type === 'publication' ? $report->publication->user : ($item->report_type === 'profile' ? $report->profileUser : $report->reportedUser))
                            <tr>
                                <td><span class="badge rounded-pill text-bg-{{ $item->report_type === 'publication' ? 'primary' : ($item->report_type === 'profile' ? 'info' : 'secondary') }}">@lang('seeker::admin.reports.types.'.$item->report_type)</span></td>
                                <td style="min-width: 14rem">
                                    @if($item->report_type === 'publication')
                                        <a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.admin.publications.show', $report->publication) }}">{{ $report->publication->title }}</a><div class="small text-body-secondary mt-1">@lang('seeker::admin.reports.by_user', ['user' => $report->publication->user->name])</div>
                                    @elseif($item->report_type === 'profile')
                                        <a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $report->profileUser) }}" target="_blank" rel="noopener">{{ $report->profileUser->name }}</a>
                                    @else
                                        <a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.admin.conversations.show', $report->conversation) }}">@lang('seeker::admin.conversations.detail_title', ['id' => $report->conversation_id])</a><div class="small text-body-secondary mt-1">{{ $report->conversation->publication->title }}</div>
                                    @endif
                                </td>
                                <td><span class="fw-semibold">{{ $report->reporter->name }}</span><div class="small text-body-secondary">ID #{{ $report->reporter_id }}</div></td>
                                <td>@lang($item->report_type === 'profile' ? 'seeker::messages.profile_reports.reasons.'.$report->reason : ($item->report_type === 'publication' ? 'seeker::messages.publication_reports.reasons.'.$report->reason : 'seeker::messages.reports.reasons.'.$report->reason))</td>
                                <td style="min-width: 20rem"><div style="white-space: pre-wrap">{{ $report->details }}</div>
                                    @if($item->report_type === 'publication')
                                        <details class="small mt-2"><summary class="text-primary">@lang('seeker::admin.reports.publication_snapshot')</summary><div class="seeker-admin-detail-item mt-2"><strong class="d-block">{{ $report->reported_title }}</strong><div class="mt-1" style="white-space: pre-wrap">{{ $report->reported_description }}</div>@if(filled($report->reported_portfolio_url))<div class="small text-break mt-2">{{ $report->reported_portfolio_url }}</div>@endif</div></details>
                                    @elseif($item->report_type === 'profile' && filled($report->reported_bio))
                                        <details class="small mt-2"><summary class="text-primary">@lang('seeker::admin.reports.profile_snapshot')</summary><div class="seeker-admin-detail-item mt-2" style="white-space: pre-wrap">{{ $report->reported_bio }}</div></details>
                                    @elseif($item->report_type === 'conversation')
                                        <div class="small text-body-secondary mt-2">@lang('seeker::admin.reports.reported_user', ['user' => $report->reportedUser->name]) @if($report->reported_through_message_id)· @lang('seeker::admin.reports.through_message', ['id' => $report->reported_through_message_id])@endif</div>
                                    @endif
                                </td>
                                <td class="text-nowrap text-body-secondary small">{{ format_date($report->created_at, true) }}</td>
                                <td><span class="badge text-bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'reviewed' ? 'success' : 'secondary') }} seeker-admin-status">@lang('seeker::admin.reports.statuses.'.$report->status)</span></td>
                                <td style="min-width: 13rem">
                                    <div class="d-flex justify-content-end gap-1 mb-2">
                                        <a class="btn btn-sm btn-outline-warning" href="{{ route('seeker.admin.restrictions.index', ['user_id' => $report->reporter_id]) }}" title="@lang('seeker::admin.reports.restrict_reporter')" aria-label="@lang('seeker::admin.reports.restrict_reporter')" data-bs-toggle="tooltip"><i class="bi bi-person-exclamation" aria-hidden="true"></i></a>
                                        <a class="btn btn-sm btn-outline-danger" href="{{ route('seeker.admin.restrictions.index', ['user_id' => $reportedUser->id]) }}" title="@lang('seeker::admin.reports.restrict_reported')" aria-label="@lang('seeker::admin.reports.restrict_reported')" data-bs-toggle="tooltip"><i class="bi bi-person-lock" aria-hidden="true"></i></a>
                                    </div>
                                    <form method="POST" action="{{ route('seeker.admin.reports.update', [$item->report_type, $report->id]) }}" class="d-flex justify-content-end gap-2">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm" aria-label="@lang('seeker::admin.reports.update_status')">@foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($report->status === $reportStatus)>@lang('seeker::admin.reports.statuses.'.$reportStatus)</option>@endforeach</select><button class="btn btn-sm btn-primary" title="@lang('messages.actions.save')" data-bs-toggle="tooltip"><i class="bi bi-check-lg" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.save')</span></button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.reports.empty')</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($reports->hasPages())<div class="seeker-admin-pagination">{{ $reports->links() }}</div>@endif
        </div>
    </div>
@endsection
