@extends('layouts.app')

@section('title', trans('seeker::messages.conversations.title'))

@push('styles')
    <link rel="stylesheet" href="{{ plugin_asset('seeker', 'css/style.css') }}">
@endpush

@section('content')
    @php($other = $conversation->otherParticipant(auth()->user()))
    <div class="mb-4"><a class="text-decoration-none" href="{{ route('seeker.conversations.index') }}"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i> @lang('seeker::messages.conversations.back')</a></div>

    <div class="card seeker-chat">
        <div class="card-header p-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ $other->getAvatar(48) }}" width="48" height="48" class="rounded-circle" alt="">
                <div class="flex-grow-1"><h1 class="h5 mb-0">{{ $other->name }}</h1><a class="small text-decoration-none" href="{{ route('seeker.publications.show', $conversation->publication) }}">{{ $conversation->publication->title }}</a></div>
                <span class="badge text-bg-light">@include('seeker::publications._price', ['publication' => $conversation->publication])</span>
            </div>
        </div>

        @if($conversation->escrow_status === 'held')
            <div class="alert alert-warning rounded-0 border-start-0 border-end-0 mb-0">
                <i class="bi bi-shield-lock me-2" aria-hidden="true"></i>
                @lang('seeker::messages.escrow.held', ['points' => format_money((float) $conversation->held_points)])
            </div>
        @endif

        <div class="card-body seeker-chat-messages p-3 p-md-4">
            @if($messages->hasPages())<div class="d-flex justify-content-center mb-4">{{ $messages->links() }}</div>@endif
            @foreach($messages->getCollection()->reverse() as $message)
                @php($mine = $message->sender_id === auth()->id())
                <div class="d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                    <div class="seeker-message {{ $mine ? 'seeker-message-mine' : '' }}">
                        <div class="small fw-semibold mb-1">{{ $message->sender->name }}</div>
                        <div class="seeker-message-content">{!! nl2br(e($message->content)) !!}</div>
                        <div class="small {{ $mine ? 'text-white-50' : 'text-muted' }} text-end mt-1">{{ format_date($message->created_at, true) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card-footer p-3">
            <form method="POST" action="{{ route('seeker.conversations.messages.store', $conversation) }}">
                @csrf
                <label class="visually-hidden" for="conversationMessage">@lang('seeker::messages.conversations.reply')</label>
                <div class="input-group">
                    <textarea id="conversationMessage" name="content" rows="2" maxlength="2000" class="form-control @error('content') is-invalid @enderror" placeholder="@lang('seeker::messages.conversations.reply')" required>{{ old('content') }}</textarea>
                    <button class="btn btn-primary px-4"><i class="bi bi-send" aria-hidden="true"></i><span class="visually-hidden">@lang('seeker::messages.conversations.send')</span></button>
                    @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </form>
        </div>
    </div>
@endsection
