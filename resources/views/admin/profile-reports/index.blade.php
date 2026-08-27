@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.profile_reports.title'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">@lang('seeker::admin.profile_reports.title')</h1><p class="text-muted mb-0">@lang('seeker::admin.profile_reports.subtitle')</p></div>
        <a class="btn btn-outline-secondary" href="{{ route('seeker.admin.publications.index') }}">@lang('seeker::admin.profile_reports.publications')</a>
    </div>
    <form method="GET" class="card card-body mb-4"><div class="row g-2"><div class="col-md"><select name="status" class="form-select"><option value="">@lang('seeker::admin.profile_reports.all_statuses')</option>@foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($status === $reportStatus)>@lang('seeker::admin.profile_reports.statuses.'.$reportStatus)</option>@endforeach</select></div><div class="col-md-auto"><button class="btn btn-primary w-100">@lang('seeker::admin.profile_reports.filter')</button></div></div></form>
    <div class="card overflow-hidden"><div class="table-responsive"><table class="table align-middle mb-0"><thead><tr><th>@lang('seeker::admin.profile_reports.profile')</th><th>@lang('seeker::admin.profile_reports.reporter')</th><th>@lang('seeker::admin.profile_reports.reason')</th><th>@lang('seeker::admin.profile_reports.details')</th><th>@lang('seeker::admin.profile_reports.status')</th><th></th></tr></thead><tbody>
        @forelse($reports as $report)
            <tr><td><a href="{{ route('seeker.profiles.show', $report->profileUser) }}" target="_blank" rel="noopener">{{ $report->profileUser->name }}</a><div class="small text-muted">{{ format_date($report->created_at, true) }}</div></td><td>{{ $report->reporter->name }}</td><td>@lang('seeker::messages.profile_reports.reasons.'.$report->reason)</td><td style="min-width: 18rem"><div style="white-space: pre-wrap">{{ $report->details }}</div>@if(filled($report->reported_bio))<details class="mt-2"><summary>@lang('seeker::admin.profile_reports.bio_snapshot')</summary><div class="border rounded p-2 mt-2" style="white-space: pre-wrap">{{ $report->reported_bio }}</div></details>@endif</td><td><span class="badge text-bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'reviewed' ? 'success' : 'secondary') }}">@lang('seeker::admin.profile_reports.statuses.'.$report->status)</span></td><td><form method="POST" action="{{ route('seeker.admin.profile-reports.update', $report) }}" class="d-flex gap-2">@csrf @method('PATCH')<select name="status" class="form-select form-select-sm">@foreach(\Azuriom\Plugin\Seeker\Models\ProfileReport::statuses() as $reportStatus)<option value="{{ $reportStatus }}" @selected($report->status === $reportStatus)>@lang('seeker::admin.profile_reports.statuses.'.$reportStatus)</option>@endforeach</select><button class="btn btn-sm btn-primary">@lang('seeker::admin.profile_reports.update')</button></form></td></tr>
        @empty
            <tr><td colspan="6" class="text-center text-muted py-5">@lang('seeker::admin.profile_reports.empty')</td></tr>
        @endforelse
    </tbody></table></div></div>
    @if($reports->hasPages())<div class="d-flex justify-content-center mt-4">{{ $reports->links() }}</div>@endif
@endsection
