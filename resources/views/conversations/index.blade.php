@extends('layouts.app')

@section('title', trans('seeker::messages.conversations.title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    <div class="d-flex align-items-center gap-3 mb-4">
        <span class="seeker-title-icon"><i class="bi bi-chat-dots" aria-hidden="true"></i></span>
        <div><h1 class="h2 mb-0">@lang('seeker::messages.conversations.title')</h1><p class="text-muted mb-0">@lang('seeker::messages.conversations.subtitle')</p></div>
    </div>

    <div class="card overflow-hidden">
        <div class="list-group list-group-flush">
            @forelse($conversations as $conversation)
                @php($other = $conversation->otherParticipant($user))
                <a class="list-group-item list-group-item-action p-3 p-md-4" href="{{ route('seeker.conversations.show', $conversation) }}">
                    <div class="d-flex gap-3 align-items-center">
                        <img src="{{ $other->getAvatar(52) }}" width="52" height="52" class="rounded-circle flex-shrink-0" alt="">
                        <div class="flex-grow-1 overflow-hidden">
                            <div class="d-flex justify-content-between gap-3">
                                <strong>{{ $other->name }}</strong>
                                @if($conversation->last_message_at)<small class="text-muted text-nowrap">{{ format_date_compact($conversation->last_message_at) }}</small>@endif
                            </div>
                            <div class="small text-muted text-truncate">{{ $conversation->publication->title }}</div>
                            @if($conversation->latestMessage)<div class="text-truncate mt-1">{{ $conversation->latestMessage->content }}</div>@endif
                        </div>
                        @if($conversation->unread_count > 0)<span class="badge rounded-pill text-bg-primary">{{ $conversation->unread_count }}</span>@endif
                    </div>
                </a>
            @empty
                <div class="p-5 text-center text-muted">@lang('seeker::messages.conversations.empty')</div>
            @endforelse
        </div>
    </div>
    @if($conversations->hasPages())<div class="d-flex justify-content-center mt-4">{{ $conversations->links() }}</div>@endif
@endsection
