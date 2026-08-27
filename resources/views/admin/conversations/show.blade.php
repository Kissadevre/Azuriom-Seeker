@extends('admin.layouts.admin')

@section('title', trans('seeker::admin.conversations.detail_title', ['id' => $conversation->id]))

@include('seeker::admin._styles')

@section('content')
    <div class="seeker-admin-shell">
    <div class="seeker-admin-header">
        <div class="seeker-admin-heading">
            <span class="seeker-admin-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
            <div>
            <a class="text-decoration-none" href="{{ route('seeker.admin.conversations.index') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('seeker::admin.conversations.back')</a>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <h1 class="h3 mb-0">@lang('seeker::admin.conversations.detail_title', ['id' => $conversation->id])</h1>
                <span class="badge text-bg-{{ $conversation->status === 'active' ? 'primary' : ($conversation->status === 'completed' ? 'success' : 'danger') }}">@lang('seeker::admin.conversations.statuses.'.$conversation->status)</span>
            </div>
        </div>
        </div>
        @if($conversation->status === 'active')
            <div class="seeker-admin-header-actions text-end">
                <button class="btn btn-danger" type="button" data-bs-toggle="modal" data-bs-target="#forceCloseConversationModal"><i class="bi bi-lock me-1" aria-hidden="true"></i>@lang('seeker::admin.conversations.force_close')</button>
                <div class="small text-muted mt-1">@lang('seeker::admin.conversations.close_help')</div>
            </div>
        @elseif($conversation->status === 'closed')
            <div class="seeker-admin-header-actions text-end">
                <button class="btn btn-success" type="button" data-bs-toggle="modal" data-bs-target="#reopenConversationModal"><i class="bi bi-unlock me-1" aria-hidden="true"></i>@lang('seeker::admin.conversations.reopen')</button>
                <div class="small text-muted mt-1">@lang('seeker::admin.conversations.reopen_help')</div>
            </div>
        @endif
    </div>

    @if($conversation->status === 'active')
        <div class="modal fade" id="forceCloseConversationModal" tabindex="-1" aria-labelledby="forceCloseConversationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="forceCloseConversationModalLabel"><i class="bi bi-shield-lock text-danger me-2" aria-hidden="true"></i>@lang('seeker::admin.conversations.force_close')</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('messages.actions.close')"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-0">@lang('seeker::admin.conversations.close_confirm')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('messages.actions.cancel')</button>
                        <form method="POST" action="{{ route('seeker.admin.conversations.close', $conversation) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-danger"><i class="bi bi-lock me-1" aria-hidden="true"></i>@lang('seeker::admin.conversations.force_close')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($conversation->status === 'closed')
        <div class="modal fade" id="reopenConversationModal" tabindex="-1" aria-labelledby="reopenConversationModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h2 class="modal-title fs-5" id="reopenConversationModalLabel"><i class="bi bi-unlock text-success me-2" aria-hidden="true"></i>@lang('seeker::admin.conversations.reopen')</h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="@lang('messages.actions.close')"></button>
                    </div>
                    <div class="modal-body"><p class="mb-0">@lang('seeker::admin.conversations.reopen_confirm')</p></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">@lang('messages.actions.cancel')</button>
                        <form method="POST" action="{{ route('seeker.admin.conversations.reopen', $conversation) }}">
                            @csrf @method('PATCH')
                            <button class="btn btn-success"><i class="bi bi-unlock me-1" aria-hidden="true"></i>@lang('seeker::admin.conversations.reopen')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if($conversation->status === 'closed')
        <div class="alert alert-danger"><i class="bi bi-shield-lock me-2" aria-hidden="true"></i>@lang('seeker::admin.conversations.closed_notice')</div>
    @endif
    @if($conversation->escrow_status === 'held')
        <div class="alert alert-warning"><i class="bi bi-exclamation-triangle me-2" aria-hidden="true"></i>@lang('seeker::admin.conversations.escrow_notice', ['points' => format_money((float) $conversation->held_points)])</div>
    @endif

    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card seeker-admin-card h-100">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.conversations.publication')</h2></div>
                <div class="card-body">
                    <a class="h5 text-decoration-none" href="{{ route('seeker.admin.publications.show', $conversation->publication) }}">{{ $conversation->publication->title }}</a>
                    <div class="d-flex flex-wrap gap-2 mt-2"><span class="badge text-bg-light">@lang('seeker::messages.types.'.$conversation->publication->type)</span><span class="badge text-bg-light">@include('seeker::publications._price', ['publication' => $conversation->publication])</span></div>
                    <p class="text-muted mt-3 mb-0">{{ \Illuminate\Support\Str::limit($conversation->publication->description, 300) }}</p>
                </div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card seeker-admin-card h-100">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.conversations.details')</h2></div>
                <div class="card-body">
                    <dl class="row mb-0 small">
                        <dt class="col-6">@lang('seeker::admin.created_at')</dt><dd class="col-6 text-end">{{ format_date($conversation->created_at, true) }}</dd>
                        <dt class="col-6">@lang('seeker::admin.conversations.last_activity')</dt><dd class="col-6 text-end">{{ format_date($conversation->updated_at, true) }}</dd>
                        <dt class="col-6">@lang('seeker::admin.conversations.completion')</dt><dd class="col-6 text-end">@lang('seeker::admin.conversations.completion_statuses.'.$conversation->completion_status)</dd>
                        <dt class="col-6">@lang('seeker::admin.conversations.delivery_attempts')</dt><dd class="col-6 text-end">{{ $conversation->delivery_attempts }}</dd>
                        <dt class="col-6">@lang('seeker::admin.conversations.escrow')</dt><dd class="col-6 text-end">@lang('seeker::admin.conversations.escrow_statuses.'.$conversation->escrow_status)</dd>
                        <dt class="col-6">@lang('seeker::admin.conversations.held_points')</dt><dd class="col-6 text-end">{{ format_money((float) $conversation->held_points) }}</dd>
                        @if($conversation->proposed_hours !== null)<dt class="col-6">@lang('seeker::admin.conversations.proposed_hours')</dt><dd class="col-6 text-end">{{ $conversation->proposed_hours }}</dd>@endif
                        @if($conversation->service_points !== null)<dt class="col-6">@lang('seeker::admin.conversations.service_points')</dt><dd class="col-6 text-end">{{ format_money((float) $conversation->service_points) }}</dd>@endif
                        @if((float) $conversation->tip_points > 0)<dt class="col-6">@lang('seeker::admin.conversations.tip_points')</dt><dd class="col-6 text-end">{{ format_money((float) $conversation->tip_points) }}</dd>@endif
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach([['role' => 'author', 'user' => $conversation->author], ['role' => 'client', 'user' => $conversation->client]] as $participant)
            <div class="col-md-6"><div class="card seeker-admin-card h-100"><div class="card-body d-flex align-items-center gap-3"><img src="{{ $participant['user']->getAvatar(56) }}" width="56" height="56" class="rounded-circle" alt=""><div><div class="small text-body-secondary">@lang('seeker::admin.conversations.'.$participant['role'])</div><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $participant['user']) }}" target="_blank" rel="noopener">{{ $participant['user']->name }}</a><div class="small text-body-secondary">ID #{{ $participant['user']->id }}</div></div></div></div>
            </div>
        @endforeach
    </div>

    @if($reports->isNotEmpty())
        <div class="card seeker-admin-card mb-4">
            <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.conversations.reports')</h2></div>
            <div class="table-responsive"><table class="table table-hover align-middle seeker-admin-table mb-0"><thead><tr><th>@lang('seeker::admin.conversations.reporter')</th><th>@lang('seeker::admin.conversations.reported_user')</th><th>@lang('seeker::admin.conversations.reason')</th><th>@lang('seeker::admin.conversations.report_details')</th><th>@lang('seeker::admin.status')</th></tr></thead><tbody>
                @foreach($reports as $report)
                    <tr><td>{{ $report->reporter->name }}</td><td>{{ $report->reportedUser->name }}</td><td>@lang('seeker::messages.reports.reasons.'.$report->reason)</td><td style="min-width: 18rem; white-space: pre-wrap">{{ $report->details }}</td><td><span class="badge text-bg-{{ $report->status === 'pending' ? 'warning' : ($report->status === 'reviewed' ? 'success' : 'secondary') }}">@lang('seeker::messages.reports.statuses.'.$report->status)</span></td></tr>
                @endforeach
            </tbody></table></div>
        </div>
    @endif

    <div class="card seeker-admin-card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h2 class="h5 mb-0">@lang('seeker::admin.conversations.message_history')</h2><div class="small text-muted">@lang('seeker::admin.conversations.read_only')</div></div><span class="badge text-bg-secondary">{{ $conversation->messages_count }}</span></div>
        <div class="card-body p-3 p-md-4">
            @if($messages->hasPages())<div class="d-flex justify-content-center mb-4">{{ $messages->links() }}</div>@endif
            @forelse($messages->getCollection()->reverse() as $message)
                <div class="d-flex {{ $message->sender_id === $conversation->client_id ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                    <div class="seeker-admin-message {{ $message->sender_id === $conversation->client_id ? 'is-client' : '' }}">
                        <div class="small fw-semibold mb-2">{{ $message->sender->name }} <span class="fw-normal text-muted">· @lang($message->sender_id === $conversation->author_id ? 'seeker::admin.conversations.author' : 'seeker::admin.conversations.client')</span></div>
                        @if($message->image_path)
                            <a href="{{ route('seeker.messages.images.show', $message) }}" target="_blank" rel="noopener"><img src="{{ route('seeker.messages.images.show', $message) }}" class="img-fluid rounded" style="max-height: 24rem" loading="lazy" alt="{{ $message->image_original_name }}"></a>
                        @endif
                        @if(filled($message->content))<div class="{{ $message->image_path ? 'mt-2' : '' }}" style="white-space: pre-wrap">{{ $message->content }}</div>@endif
                        <div class="small text-muted text-end mt-2">{{ format_date($message->created_at, true) }}</div>
                    </div>
                </div>
            @empty
                <div class="seeker-admin-empty"><span class="seeker-admin-empty-icon"><i class="bi bi-chat-square-text" aria-hidden="true"></i></span><div class="fw-semibold">@lang('seeker::admin.conversations.no_messages')</div></div>
            @endforelse
        </div>
    </div>
    </div>
@endsection
