@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.profile_reports.title'))

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
        @include('seeker::admin._header', [
            'headerIcon' => 'bi-person-badge',
            'headerTitle' => trans('seeker::admin.profile_reports.title'),
            'headerSubtitle' => trans('seeker::admin.profile_reports.subtitle'),
            'headerTotal' => $reports->total(),
            'headerTotalIcon' => 'bi-person-badge',
            'headerTone' => 'danger',
        ])

        <form method="GET" class="seeker-admin-toolbar mb-4">
            <div class="seeker-admin-toolbar-title"><i class="bi bi-funnel" aria-hidden="true"></i>@lang('seeker::admin.profile_reports.filters')</div>
            <div class="row g-2 align-items-end"><div class="col-md"><label class="form-label small fw-semibold" for="profileReportStatus">@lang('seeker::admin.profile_reports.status')</label><select id="profileReportStatus" name="status" class="form-select"><option value="">@lang('seeker::admin.profile_reports.all_statuses')</option>@foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($status === $reportStatus)>@lang('seeker::admin.profile_reports.statuses.'.$reportStatus)</option>@endforeach</select></div><div class="col-md-auto seeker-admin-filter-actions"><button class="btn btn-primary"><i class="bi bi-funnel me-1" aria-hidden="true"></i>@lang('seeker::admin.profile_reports.filter')</button>@if(filled($status))<a class="btn btn-outline-secondary" href="{{ route('seeker.admin.profile-reports.index') }}"><i class="bi bi-x-lg me-1" aria-hidden="true"></i>@lang('seeker::admin.clear_filters')</a>@endif</div><div class="col-md-auto"><a class="btn btn-outline-secondary w-100" href="{{ route('seeker.admin.reports.index') }}"><i class="bi bi-flag me-1" aria-hidden="true"></i>@lang('seeker::admin.reports.title')</a></div></div>
        </form>

        <div class="card seeker-admin-card">
            <div class="table-responsive"><table class="table table-hover align-middle seeker-admin-table seeker-admin-table--actions mb-0"><thead><tr><th>@lang('seeker::admin.profile_reports.profile')</th><th>@lang('seeker::admin.profile_reports.reporter')</th><th>@lang('seeker::admin.profile_reports.reason')</th><th>@lang('seeker::admin.profile_reports.details')</th><th>@lang('seeker::admin.profile_reports.status')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead><tbody>
                @forelse($reports as $report)
                    <tr>
                        <td><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $report->profileUser) }}" target="_blank" rel="noopener">{{ $report->profileUser->name }}</a><div class="small text-body-secondary">{{ format_date($report->created_at, true) }}</div></td>
                        <td>{{ $report->reporter->name }}</td>
                        <td>@lang('seeker::messages.profile_reports.reasons.'.$report->reason)</td>
                        <td style="min-width: 18rem"><div style="white-space: pre-wrap">{{ $report->details }}</div>@if(filled($report->reported_bio))<details class="small mt-2"><summary class="text-primary">@lang('seeker::admin.profile_reports.bio_snapshot')</summary><div class="seeker-admin-detail-item mt-2" style="white-space: pre-wrap">{{ $report->reported_bio }}</div></details>@endif</td>
                        <td><span class="badge text-bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'reviewed' ? 'success' : 'secondary') }} seeker-admin-status">@lang('seeker::admin.profile_reports.statuses.'.$report->status)</span></td>
                        <td><form method="POST" action="{{ route('seeker.admin.profile-reports.update', $report) }}" class="d-flex justify-content-end gap-2">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm">@foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($report->status === $reportStatus)>@lang('seeker::admin.profile_reports.statuses.'.$reportStatus)</option>@endforeach</select><button class="btn btn-sm btn-primary" title="@lang('messages.actions.save')" data-bs-toggle="tooltip"><i class="bi bi-check-lg" aria-hidden="true"></i><span class="visually-hidden">@lang('messages.actions.save')</span></button></form></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-shield-check" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.profile_reports.empty')</div></td></tr>
                @endforelse
            </tbody></table></div>
            @if($reports->hasPages())<div class="seeker-admin-pagination">{{ $reports->links() }}</div>@endif
        </div>
    </div>
@endsection
