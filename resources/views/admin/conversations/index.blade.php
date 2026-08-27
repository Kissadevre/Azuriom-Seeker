@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.conversations.title'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div><h1 class="h3 mb-1">@lang('seeker::admin.conversations.title')</h1><p class="text-muted mb-0">@lang('seeker::admin.conversations.subtitle')</p></div>
        <form method="GET">
            <select name="status" class="form-select" onchange="this.form.submit()" aria-label="@lang('seeker::admin.conversations.filter_status')">
                <option value="">@lang('seeker::admin.conversations.all_statuses')</option>
                @foreach(\Azuriom\Plugin\Seeker\Models\Conversation::statuses() as $conversationStatus)
                    <option value="{{ $conversationStatus }}" @selected($status === $conversationStatus)>@lang('seeker::admin.conversations.statuses.'.$conversationStatus)</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>@lang('seeker::admin.conversations.publication')</th><th>@lang('seeker::admin.conversations.participants')</th><th>@lang('seeker::admin.status')</th><th class="text-center">@lang('seeker::admin.conversations.messages')</th><th class="text-center">@lang('seeker::admin.conversations.reports')</th><th>@lang('seeker::admin.created_at')</th><th>@lang('seeker::admin.conversations.last_activity')</th><th class="text-end">@lang('seeker::admin.actions')</th></tr></thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        <tr>
                            <td style="min-width: 14rem"><a class="fw-semibold text-decoration-none" href="{{ route('seeker.admin.publications.show', $conversation->publication) }}">{{ $conversation->publication->title }}</a><div class="small text-muted">#{{ $conversation->id }} · @lang('seeker::messages.types.'.$conversation->publication->type)</div></td>
                            <td style="min-width: 13rem"><div><span class="small text-muted">@lang('seeker::admin.conversations.author'):</span> {{ $conversation->author->name }}</div><div><span class="small text-muted">@lang('seeker::admin.conversations.client'):</span> {{ $conversation->client->name }}</div></td>
                            <td><span class="badge text-bg-{{ $conversation->status === 'active' ? 'primary' : ($conversation->status === 'completed' ? 'success' : 'danger') }}">@lang('seeker::admin.conversations.statuses.'.$conversation->status)</span></td>
                            <td class="text-center">{{ $conversation->messages_count }}</td>
                            <td class="text-center">{{ $conversation->reports_count }}</td>
                            <td class="text-nowrap">{{ format_date($conversation->created_at, true) }}</td>
                            <td class="text-nowrap">{{ format_date($conversation->updated_at, true) }}</td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('seeker.admin.conversations.show', $conversation) }}"><i class="bi bi-eye me-1" aria-hidden="true"></i>@lang('seeker::admin.details')</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-5">@lang('seeker::admin.conversations.empty')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($conversations->hasPages())<div class="d-flex justify-content-center mt-4">{{ $conversations->links() }}</div>@endif
@endsection
