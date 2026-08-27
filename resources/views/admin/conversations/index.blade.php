@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.conversations.title'))

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
        @include('seeker::admin._header', [
            'headerIcon' => 'bi-chat-dots',
            'headerTitle' => trans('seeker::admin.conversations.title'),
            'headerSubtitle' => trans('seeker::admin.conversations.subtitle'),
            'headerTotal' => $conversations->total(),
            'headerTotalIcon' => 'bi-chat-dots',
        ])

        <form method="GET" class="seeker-admin-toolbar mb-4">
            <div class="seeker-admin-toolbar-title"><i class="bi bi-funnel" aria-hidden="true"></i>@lang('seeker::admin.conversations.filters')</div>
            <div class="row g-2 align-items-end">
                <div class="col-md"><label class="form-label small fw-semibold" for="conversationStatus">@lang('seeker::admin.status')</label><select id="conversationStatus" name="status" class="form-select"><option value="">@lang('seeker::admin.conversations.all_statuses')</option>@foreach(\Azuriom\Plugin\Seeker\Models\Conversation::statuses() as $conversationStatus)<option value="{{ $conversationStatus }}" @selected($status === $conversationStatus)>@lang('seeker::admin.conversations.statuses.'.$conversationStatus)</option>@endforeach</select></div>
                <div class="col-md"><label class="form-label small fw-semibold" for="conversationReports">@lang('seeker::admin.conversations.report_filter')</label><select id="conversationReports" name="reports" class="form-select"><option value="">@lang('seeker::admin.conversations.all_report_states')</option><option value="with" @selected($reports === 'with')>@lang('seeker::admin.conversations.with_reports')</option><option value="without" @selected($reports === 'without')>@lang('seeker::admin.conversations.without_reports')</option></select></div>
                <div class="col-md-auto seeker-admin-filter-actions">
                    <button class="btn btn-primary"><i class="bi bi-funnel me-1" aria-hidden="true"></i>@lang('seeker::admin.conversations.apply_filters')</button>
                    @if(filled($status) || filled($reports))
                        <a class="btn btn-outline-secondary" href="{{ route('seeker.admin.conversations.index') }}"><i class="bi bi-x-lg me-1" aria-hidden="true"></i>@lang('seeker::admin.clear_filters')</a>
                    @endif
                </div>
            </div>
        </form>

        <div class="card seeker-admin-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle seeker-admin-table seeker-admin-report-indicator-table mb-0">
                    <thead><tr><th>@lang('seeker::admin.conversations.publication')</th><th>@lang('seeker::admin.conversations.participants')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.conversations.dates')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                    <tbody>
                        @forelse($conversations as $conversation)
                            <tr @class(['seeker-admin-row-reported' => $conversation->reports_count > 0])>
                                <td style="min-width: 15rem">@if($conversation->reports_count > 0)<span class="visually-hidden">@choice('seeker::admin.conversations.report_count', $conversation->reports_count, ['count' => $conversation->reports_count]) </span>@endif<a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.admin.publications.show', $conversation->publication) }}">{{ $conversation->publication->title }}</a><div class="small text-body-secondary mt-1">#{{ $conversation->id }} · @lang('seeker::messages.types.'.$conversation->publication->type)</div></td>
                                <td style="min-width: 13rem"><div class="small"><span class="text-body-secondary">@lang('seeker::admin.conversations.author'):</span> <strong>{{ $conversation->author->name }}</strong></div><div class="small mt-1"><span class="text-body-secondary">@lang('seeker::admin.conversations.client'):</span> <strong>{{ $conversation->client->name }}</strong></div></td>
                                <td><span class="badge text-bg-{{ $conversation->status === 'active' ? 'primary' : ($conversation->status === 'completed' ? 'success' : 'danger') }} seeker-admin-status">@lang('seeker::admin.conversations.statuses.'.$conversation->status)</span></td>
                                <td class="text-nowrap small"><div class="seeker-admin-date"><i class="bi bi-calendar-plus" aria-hidden="true"></i><span><span class="visually-hidden">@lang('seeker::admin.created_at'): </span>{{ format_date($conversation->created_at, true) }}</span></div><div class="seeker-admin-date text-body-secondary mt-1"><i class="bi bi-clock-history" aria-hidden="true"></i><span><span class="visually-hidden">@lang('seeker::admin.conversations.last_activity'): </span>{{ format_date($conversation->updated_at, true) }}</span></div></td>
                                <td class="text-end"><div class="seeker-admin-action-group"><a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.admin.conversations.show', $conversation) }}" title="@lang('seeker::admin.details')" aria-label="@lang('seeker::admin.details')" data-bs-toggle="tooltip"><i class="bi bi-eye" aria-hidden="true"></i></a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.conversations.empty')</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($conversations->hasPages())<div class="seeker-admin-pagination">{{ $conversations->links() }}</div>@endif
        </div>
    </div>
@endsection
