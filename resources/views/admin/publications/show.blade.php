@extends('admin.layouts.admin')

@section('title', $publication->title)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <a class="text-decoration-none" href="{{ route('seeker.admin.publications.index') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>@lang('seeker::admin.publications.back')</a>
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                <h1 class="h3 mb-0">{{ $publication->title }}</h1>
                <span class="badge {{ $publication->status === 'active' ? 'text-bg-success' : ($publication->status === 'hidden' ? 'text-bg-danger' : 'text-bg-secondary') }}">@lang('seeker::messages.statuses.'.$publication->status)</span>
                <span class="badge text-bg-light">@lang('seeker::messages.types.'.$publication->type)</span>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a class="btn btn-outline-primary" href="{{ route('seeker.publications.show', $publication) }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right me-1" aria-hidden="true"></i>@lang('seeker::admin.publications.public_view')</a>
            <form method="POST" action="{{ route('seeker.admin.publications.status', $publication) }}" class="d-flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="form-select" aria-label="@lang('seeker::admin.set_status')">
                    @foreach(\Azuriom\Plugin\Seeker\Models\Publication::statuses() as $publicationStatus)
                        <option value="{{ $publicationStatus }}" @selected($publication->status === $publicationStatus)>@lang('seeker::messages.statuses.'.$publicationStatus)</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">@lang('messages.actions.save')</button>
            </form>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card h-100">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.publications.content')</h2></div>
                <div class="card-body">
                    <div style="white-space: pre-wrap">{{ $publication->description }}</div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.publications.author')</h2></div>
                <div class="card-body">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <img src="{{ $publication->user->getAvatar(64) }}" width="64" height="64" class="rounded-circle" alt="">
                        <div><a class="fw-semibold text-body text-decoration-none" href="{{ route('seeker.profiles.show', $publication->user) }}" target="_blank" rel="noopener">{{ $publication->user->name }}</a><div class="small text-muted">ID #{{ $publication->user->id }}</div></div>
                    </div>
                    <dl class="row mb-0 small">
                        <dt class="col-6">@lang('seeker::admin.created_at')</dt><dd class="col-6 text-end">{{ format_date($publication->created_at, true) }}</dd>
                        <dt class="col-6">@lang('seeker::admin.updated_at')</dt><dd class="col-6 text-end">{{ format_date($publication->updated_at, true) }}</dd>
                        <dt class="col-6">@lang('seeker::admin.publications.published_at')</dt><dd class="col-6 text-end">{{ $publication->published_at ? format_date($publication->published_at, true) : trans('seeker::admin.not_available') }}</dd>
                        <dt class="col-6">@lang('seeker::admin.publications.visibility')</dt><dd class="col-6 text-end">@lang($publication->is_guest_visible ? 'seeker::messages.visibility.public' : 'seeker::messages.visibility.members')</dd>
                        <dt class="col-6">@lang('seeker::admin.publications.price')</dt><dd class="col-6 text-end">@include('seeker::publications._price', ['publication' => $publication])</dd>
                        <dt class="col-6">@lang('seeker::admin.conversations')</dt><dd class="col-6 text-end">{{ $publication->conversations_count }}</dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h2 class="h5 mb-0">@lang('seeker::admin.publications.portfolio')</h2></div>
        <div class="card-body">
            @if($publication->portfolio_type === 'external' && filled($publication->portfolio_url))
                <div class="small text-muted mb-1">@lang('seeker::admin.publications.external_portfolio')</div>
                <a href="{{ $publication->portfolio_url }}" target="_blank" rel="noopener noreferrer nofollow" class="text-break">{{ $publication->portfolio_url }}</a>
            @elseif($publication->portfolio_type === 'images' && $publication->images->isNotEmpty())
                <div class="row g-3">
                    @foreach($publication->images as $image)
                        <div class="col-sm-6 col-xl-3"><a href="{{ route('seeker.images.show', $image) }}" target="_blank" rel="noopener"><img src="{{ route('seeker.images.show', $image) }}" class="img-fluid rounded" alt="{{ $image->original_name }}"><div class="small text-muted text-truncate mt-1">{{ $image->original_name }}</div></a></div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">@lang('seeker::admin.publications.no_portfolio')</p>
            @endif
        </div>
    </div>

    <div class="card" id="conversations">
        <div class="card-header d-flex justify-content-between align-items-center gap-2"><h2 class="h5 mb-0">@lang('seeker::admin.publications.linked_conversations')</h2><span class="badge text-bg-primary">{{ $publication->conversations_count }}</span></div>
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead><tr><th>@lang('seeker::admin.publications.client')</th><th>@lang('seeker::admin.status')</th><th>@lang('seeker::admin.publications.completion')</th><th class="text-center">@lang('seeker::admin.publications.messages')</th><th class="text-center">@lang('seeker::admin.publications.reports')</th><th>@lang('seeker::admin.created_at')</th><th>@lang('seeker::admin.publications.last_activity')</th></tr></thead>
                <tbody>
                    @forelse($conversations as $conversation)
                        <tr>
                            <td><a class="text-body text-decoration-none" href="{{ route('seeker.profiles.show', $conversation->client) }}" target="_blank" rel="noopener">{{ $conversation->client->name }}</a><div class="small text-muted">ID #{{ $conversation->id }}</div></td>
                            <td><span class="badge {{ $conversation->status === 'completed' ? 'text-bg-success' : 'text-bg-primary' }}">@lang('seeker::admin.publications.conversation_statuses.'.$conversation->status)</span></td>
                            <td>@lang('seeker::admin.publications.completion_statuses.'.$conversation->completion_status)</td>
                            <td class="text-center">{{ $conversation->messages_count }}</td>
                            <td class="text-center">{{ $conversation->reports_count }}</td>
                            <td class="text-nowrap">{{ format_date($conversation->created_at, true) }}</td>
                            <td class="text-nowrap">{{ format_date($conversation->updated_at, true) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-5">@lang('seeker::admin.publications.no_conversations')</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($conversations->hasPages())<div class="card-footer d-flex justify-content-center">{{ $conversations->links() }}</div>@endif
    </div>
@endsection
