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
        ])

        <form method="GET" class="seeker-admin-toolbar mb-4">
            <div class="seeker-admin-toolbar-title"><i class="bi bi-funnel" aria-hidden="true"></i>@lang('seeker::admin.conversations.filters')</div>
            <div class="row g-2 align-items-end">
                <div class="col-md-5"><label class="form-label small fw-semibold" for="conversationStatus">@lang('seeker::admin.status')</label><select id="conversationStatus" name="status" class="form-select"><option value="">@lang('seeker::admin.conversations.all_statuses')</option>@foreach(\Azuriom\Plugin\Seeker\Models\Conversation::statuses() as $conversationStatus)<option value="{{ $conversationStatus }}" @selected($status === $conversationStatus)>@lang('seeker::admin.conversations.statuses.'.$conversationStatus)</option>@endforeach</select></div>
                <div class="col-md-auto"><button class="btn btn-primary w-100"><i class="bi bi-funnel me-1" aria-hidden="true"></i>@lang('seeker::admin.conversations.apply_filters')</button></div>
            </div>
        </form>

        <div class="card seeker-admin-card">
            <div class="table-responsive">
                <table class="table table-hover align-middle seeker-admin-table mb-0">
                    <thead><tr><th>@lang('seeker::admin.conversations.publication')</th><th>@lang('seeker::admin.conversations.participants')</th><th>@lang('seeker::admin.status')</th><th class="text-center">@lang('seeker::admin.conversations.messages')</th><th class="text-center">@lang('seeker::admin.conversations.reports')</th><th>@lang('seeker::admin.created_at')</th><th>@lang('seeker::admin.conversations.last_activity')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                    <tbody>
                        @forelse($conversations as $conversation)
                            <tr>
                                <td style="min-width: 15rem"><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.admin.publications.show', $conversation->publication) }}">{{ $conversation->publication->title }}</a><div class="small text-body-secondary mt-1">#{{ $conversation->id }} · @lang('seeker::messages.types.'.$conversation->publication->type)</div></td>
                                <td style="min-width: 13rem"><div class="small"><span class="text-body-secondary">@lang('seeker::admin.conversations.author'):</span> <strong>{{ $conversation->author->name }}</strong></div><div class="small mt-1"><span class="text-body-secondary">@lang('seeker::admin.conversations.client'):</span> <strong>{{ $conversation->client->name }}</strong></div></td>
                                <td><span class="badge text-bg-{{ $conversation->status === 'active' ? 'primary' : ($conversation->status === 'completed' ? 'success' : 'danger') }} seeker-admin-status">@lang('seeker::admin.conversations.statuses.'.$conversation->status)</span></td>
                                <td class="text-center"><span class="badge rounded-pill text-bg-light">{{ $conversation->messages_count }}</span></td>
                                <td class="text-center"><span class="badge rounded-pill {{ $conversation->reports_count > 0 ? 'text-bg-warning' : 'text-bg-light' }}">{{ $conversation->reports_count }}</span></td>
                                <td class="text-nowrap text-body-secondary small">{{ format_date($conversation->created_at, true) }}</td>
                                <td class="text-nowrap text-body-secondary small">{{ format_date($conversation->updated_at, true) }}</td>
                                <td class="text-end"><div class="seeker-admin-action-group"><a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.admin.conversations.show', $conversation) }}" title="@lang('seeker::admin.details')" aria-label="@lang('seeker::admin.details')" data-bs-toggle="tooltip"><i class="bi bi-eye" aria-hidden="true"></i></a></div></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.conversations.empty')</div></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($conversations->hasPages())<div class="seeker-admin-pagination">{{ $conversations->links() }}</div>@endif
        </div>
    </div>
@endsection
