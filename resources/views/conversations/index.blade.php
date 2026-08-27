@extends('layouts.app')

@section('title', trans('seeker::messages.conversations.title'))

@include('seeker::_assets')

@section('content')
    <div class="seeker-public-shell">
    @include('seeker::_page-header', ['pageIcon' => 'bi-chat-dots', 'pageTitle' => trans('seeker::messages.conversations.title'), 'pageSubtitle' => trans('seeker::messages.conversations.subtitle')])

    <div class="card seeker-conversation-list overflow-hidden">
        <div class="list-group list-group-flush">
            @forelse($conversations as $conversation)
                @php($other = $conversation->otherParticipant($user))
                <a class="list-group-item list-group-item-action seeker-conversation-item p-3 p-md-4" href="{{ route('seeker.conversations.show', $conversation) }}">
                    <div class="d-flex gap-3 align-items-center">
                        <img src="{{ $other->getAvatar(52) }}" width="52" height="52" class="rounded-circle seeker-conversation-avatar flex-shrink-0" alt="">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between gap-3">
                                <strong>{{ $other->name }}</strong>
                                @if($conversation->last_message_at)<small class="text-muted text-nowrap">{{ format_date_compact($conversation->last_message_at) }}</small>@endif
                            </div>
                            <div class="small text-muted text-truncate">{{ $conversation->publication->title }}</div>
                            @if($conversation->status === 'completed')
                                <span class="badge text-bg-success mt-1">@lang('seeker::messages.completion.completed')</span>
                            @elseif($conversation->status === 'closed')
                                <span class="badge text-bg-danger mt-1">@lang('seeker::messages.conversations.closed_by_moderation_short')</span>
                            @elseif($conversation->completion_status === 'pending')
                                <span class="badge text-bg-warning mt-1">@lang('seeker::messages.completion.pending')</span>
                            @endif
                            @if($conversation->latestMessage)
                                <div class="text-truncate mt-1">
                                    @if($conversation->latestMessage->isHidden())
                                        <i class="bi bi-eye-slash me-1" aria-hidden="true"></i>@lang('seeker::messages.conversations.hidden_by_moderation_short')
                                    @else
                                        @if($conversation->latestMessage->image_path)<i class="bi bi-image me-1" aria-hidden="true"></i>@endif
                                        {{ filled($conversation->latestMessage->content) ? $conversation->latestMessage->content : trans('seeker::messages.conversations.image_message') }}
                                    @endif
                                </div>
                            @endif
                        </div>
                        @if($conversation->unread_count > 0)<span class="badge rounded-pill text-bg-primary">{{ $conversation->unread_count }}</span>@endif
                    </div>
                </a>
            @empty
                @include('seeker::_empty-state', ['emptyIcon' => 'bi-chat-square-text', 'emptyTitle' => trans('seeker::messages.conversations.empty')])
            @endforelse
        </div>
    </div>
    @if($conversations->hasPages())<div class="d-flex justify-content-center mt-4">{{ $conversations->links() }}</div>@endif
    </div>
@endsection
