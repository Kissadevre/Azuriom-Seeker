@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.reports.title'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">@lang('seeker::admin.reports.title')</h1><p class="text-muted mb-0">@lang('seeker::admin.reports.subtitle')</p></div>
        <span class="badge text-bg-warning fs-6">@lang('seeker::admin.reports.pending_count', ['count' => $counts['pending']])</span>
    </div>

    <div class="row g-3 mb-4">
        @foreach(['publication' => 'bi-megaphone', 'profile' => 'bi-person-badge', 'conversation' => 'bi-chat-dots'] as $reportType => $icon)
            <div class="col-md-4"><a class="card h-100 text-decoration-none {{ $type === $reportType ? 'border-primary' : '' }}" href="{{ route('seeker.admin.reports.index', ['type' => $reportType, 'status' => $status]) }}"><div class="card-body d-flex align-items-center gap-3"><i class="bi {{ $icon }} fs-3 text-primary" aria-hidden="true"></i><div><div class="h4 mb-0">{{ $counts[$reportType] }}</div><div class="text-muted">@lang('seeker::admin.reports.types.'.$reportType)</div></div></div></a></div>
        @endforeach
    </div>

    <form method="GET" class="card card-body mb-4">
        <div class="row g-2">
            <div class="col-md"><label class="visually-hidden" for="reportType">@lang('seeker::admin.reports.type')</label><select id="reportType" name="type" class="form-select"><option value="">@lang('seeker::admin.reports.all_types')</option>@foreach(\Azuriom\Plugin\Seeker\Controllers\Admin\ReportController::TYPES as $reportType)<option value="{{ $reportType }}" @selected($type === $reportType)>@lang('seeker::admin.reports.types.'.$reportType)</option>@endforeach</select></div>
            <div class="col-md"><label class="visually-hidden" for="reportStatus">@lang('seeker::admin.status')</label><select id="reportStatus" name="status" class="form-select"><option value="">@lang('seeker::admin.reports.all_statuses')</option>@foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($status === $reportStatus)>@lang('seeker::admin.reports.statuses.'.$reportStatus)</option>@endforeach</select></div>
            <div class="col-md-auto"><button class="btn btn-primary w-100"><i class="bi bi-funnel me-1" aria-hidden="true"></i>@lang('seeker::admin.reports.filter')</button></div>
        </div>
    </form>

    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>@lang('seeker::admin.reports.type')</th><th>@lang('seeker::admin.reports.target')</th><th>@lang('seeker::admin.reports.reporter')</th><th>@lang('seeker::admin.reports.reason')</th><th>@lang('seeker::admin.reports.details')</th><th>@lang('seeker::admin.created_at')</th><th>@lang('seeker::admin.status')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                <tbody>
                    @forelse($reports as $item)
                        @php($report = $item->report)
                        <tr>
                            <td><span class="badge text-bg-{{ $item->report_type === 'publication' ? 'primary' : ($item->report_type === 'profile' ? 'info' : 'secondary') }}">@lang('seeker::admin.reports.types.'.$item->report_type)</span></td>
                            <td style="min-width: 14rem">
                                @if($item->report_type === 'publication')
                                    <a class="fw-semibold text-decoration-none" href="{{ route('seeker.admin.publications.show', $report->publication) }}">{{ $report->publication->title }}</a><div class="small text-muted">@lang('seeker::admin.reports.by_user', ['user' => $report->publication->user->name])</div>
                                @elseif($item->report_type === 'profile')
                                    <a class="fw-semibold text-decoration-none" href="{{ route('seeker.profiles.show', $report->profileUser) }}" target="_blank" rel="noopener">{{ $report->profileUser->name }}</a>
                                @else
                                    <a class="fw-semibold text-decoration-none" href="{{ route('seeker.admin.conversations.show', $report->conversation) }}">@lang('seeker::admin.conversations.detail_title', ['id' => $report->conversation_id])</a><div class="small text-muted">{{ $report->conversation->publication->title }}</div>
                                @endif
                            </td>
                            <td>{{ $report->reporter->name }}</td>
                            <td>@lang($item->report_type === 'profile' ? 'seeker::messages.profile_reports.reasons.'.$report->reason : ($item->report_type === 'publication' ? 'seeker::messages.publication_reports.reasons.'.$report->reason : 'seeker::messages.reports.reasons.'.$report->reason))</td>
                            <td style="min-width: 20rem"><div style="white-space: pre-wrap">{{ $report->details }}</div>
                                @if($item->report_type === 'publication')
                                    <details class="mt-2"><summary>@lang('seeker::admin.reports.publication_snapshot')</summary><div class="border rounded p-2 mt-2"><strong class="d-block">{{ $report->reported_title }}</strong><div class="mt-1" style="white-space: pre-wrap">{{ $report->reported_description }}</div>@if(filled($report->reported_portfolio_url))<div class="small text-break mt-2">{{ $report->reported_portfolio_url }}</div>@endif</div></details>
                                @elseif($item->report_type === 'profile' && filled($report->reported_bio))
                                    <details class="mt-2"><summary>@lang('seeker::admin.reports.profile_snapshot')</summary><div class="border rounded p-2 mt-2" style="white-space: pre-wrap">{{ $report->reported_bio }}</div></details>
                                @elseif($item->report_type === 'conversation')
                                    <div class="small text-muted mt-2">@lang('seeker::admin.reports.reported_user', ['user' => $report->reportedUser->name]) @if($report->reported_through_message_id)· @lang('seeker::admin.reports.through_message', ['id' => $report->reported_through_message_id])@endif</div>
                                @endif
                            </td>
                            <td class="text-nowrap">{{ format_date($report->created_at, true) }}</td>
                            <td><span class="badge text-bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'reviewed' ? 'success' : 'secondary') }}">@lang('seeker::admin.reports.statuses.'.$report->status)</span></td>
                            <td><form method="POST" action="{{ route('seeker.admin.reports.update', [$item->report_type, $report->id]) }}" class="d-flex justify-content-end gap-2">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm" aria-label="@lang('seeker::admin.reports.update_status')">@foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($report->status === $reportStatus)>@lang('seeker::admin.reports.statuses.'.$reportStatus)</option>@endforeach</select><button class="btn btn-sm btn-primary">@lang('messages.actions.save')</button></form></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">@lang('seeker::admin.reports.empty')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())<div class="d-flex justify-content-center mt-4">{{ $reports->links() }}</div>@endif
@endsection
