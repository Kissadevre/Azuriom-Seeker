@extends('layouts.app')

@section('title', trans('seeker::messages.conversations.title'))

@include('seeker::_assets')

@section('content')
    <div class="seeker-public-shell">
    @include('seeker::_page-header', [
        'pageIcon' => 'bi-chat-dots',
        'pageTitle' => trans('seeker::messages.conversations.title'),
        'pageSubtitle' => trans('seeker::messages.conversations.subtitle'),
        'breadcrumbs' => [['label' => trans('seeker::messages.conversations.title')]],
    ])

    <nav class="seeker-inbox-filters mb-3" aria-label="@lang('seeker::messages.conversations.filters.label')">
        @foreach(['all' => 'bi-inbox', 'unread' => 'bi-envelope', 'active' => 'bi-chat-dots', 'completed' => 'bi-check2-circle'] as $filter => $icon)
            <a class="seeker-inbox-filter {{ $state === $filter ? 'active' : '' }}" href="{{ route('seeker.conversations.index', $filter === 'all' ? [] : ['state' => $filter]) }}" @if($state === $filter) aria-current="page" @endif>
                <i class="bi {{ $icon }}" aria-hidden="true"></i>
                <span>@lang('seeker::messages.conversations.filters.'.$filter)</span>
                <span class="badge rounded-pill {{ $state === $filter ? 'text-bg-primary' : 'text-bg-light' }}">{{ $conversationCounts[$filter] }}</span>
            </a>
        @endforeach
    </nav>

    <div class="card seeker-conversation-list overflow-hidden">
        <div class="list-group list-group-flush">
            @forelse($conversations as $conversation)
                @php($other = $conversation->otherParticipant($user))
                <a class="list-group-item list-group-item-action seeker-conversation-item {{ $conversation->unread_count > 0 ? 'is-unread' : '' }} p-3 p-md-4" href="{{ route('seeker.conversations.show', $conversation) }}">
                    <div class="d-flex gap-3 align-items-center">
                        <img src="{{ $other->getAvatar(56) }}" width="56" height="56" class="rounded-circle seeker-conversation-avatar flex-shrink-0" alt="">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <strong class="fs-6">{{ $other->name }}</strong>
                                @if($conversation->status === 'completed')
                                    <span class="badge text-bg-success">@lang('seeker::messages.completion.completed')</span>
                                @elseif($conversation->status === 'closed')
                                    <span class="badge text-bg-danger">@lang('seeker::messages.conversations.closed_by_moderation_short')</span>
                                @elseif($conversation->completion_status === 'pending')
                                    <span class="badge text-bg-warning">@lang('seeker::messages.completion.pending')</span>
                                @endif
                            </div>
                            <div class="seeker-conversation-publication small text-truncate"><i class="bi {{ $conversation->publication->type === 'commission' ? 'bi-briefcase' : 'bi-people' }} me-1" aria-hidden="true"></i>{{ $conversation->publication->title }}</div>
                            @if($conversation->latestMessage)
                                <div class="seeker-conversation-preview text-truncate mt-1">
                                    @if($conversation->latestMessage->isHidden())
                                        <i class="bi bi-eye-slash me-1" aria-hidden="true"></i>@lang('seeker::messages.conversations.hidden_by_moderation_short')
                                    @else
                                        @if($conversation->latestMessage->image_path)<i class="bi bi-image me-1" aria-hidden="true"></i>@endif
                                        {{ filled($conversation->latestMessage->content) ? $conversation->latestMessage->content : trans('seeker::messages.conversations.image_message') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        <div class="seeker-conversation-trailing text-end flex-shrink-0">
                            @if($conversation->last_message_at)<small class="text-muted text-nowrap">{{ format_date_compact($conversation->last_message_at) }}</small>@endif
                            <div class="d-flex align-items-center justify-content-end gap-2 mt-2">
                                @if($conversation->unread_count > 0)<span class="badge rounded-pill text-bg-primary">{{ $conversation->unread_count }}</span>@endif
                                <i class="bi bi-chevron-right text-muted" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                @include('seeker::_empty-state', ['emptyIcon' => 'bi-chat-square-text', 'emptyTitle' => trans($state === 'all' ? 'seeker::messages.conversations.empty' : 'seeker::messages.conversations.empty_filtered')])
            @endforelse
        </div>
    </div>
    @if($conversations->hasPages())<div class="d-flex justify-content-center mt-4">{{ $conversations->links() }}</div>@endif
    </div>
@endsection
