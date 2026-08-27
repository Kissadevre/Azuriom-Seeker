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
            <div class="d-flex flex-wrap align-items-center gap-3">
                <a href="{{ route('seeker.profiles.show', $other) }}"><img src="{{ $other->getAvatar(48) }}" width="48" height="48" class="rounded-circle" alt=""></a>
                <div class="flex-grow-1"><h1 class="h5 mb-0"><a class="text-body text-decoration-none" href="{{ route('seeker.profiles.show', $other) }}">{{ $other->name }}</a></h1>@if($conversation->publication->trashed())<span class="small text-muted">{{ $conversation->publication->title }} · @lang('seeker::messages.publications.removed')</span>@else<a class="small text-decoration-none" href="{{ route('seeker.publications.show', $conversation->publication) }}">{{ $conversation->publication->title }}</a>@endif</div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="badge text-bg-light">@include('seeker::publications._price', ['publication' => $conversation->publication])</span>
                    @if($conversationReport === null)
                        <a class="btn btn-sm btn-outline-danger" href="{{ route('seeker.conversations.reports.create', $conversation) }}">
                            <i class="bi bi-flag me-1" aria-hidden="true"></i> @lang('seeker::messages.reports.action')
                        </a>
                    @else
                        <span class="badge text-bg-warning">
                            <i class="bi bi-flag me-1" aria-hidden="true"></i> @lang('seeker::messages.reports.statuses.'.$conversationReport->status)
                        </span>
                    @endif
                </div>
            </div>
        </div>

        @if($conversation->escrow_status === 'held')
            <div class="alert alert-warning rounded-0 border-start-0 border-end-0 mb-0">
                <i class="bi bi-shield-lock me-2" aria-hidden="true"></i>
                @lang('seeker::messages.escrow.held', ['points' => format_money((float) $conversation->held_points)])
            </div>
        @endif

        @if($conversation->status === 'closed')
            <div class="alert alert-danger rounded-0 border-start-0 border-end-0 mb-0">
                <i class="bi bi-shield-lock me-2" aria-hidden="true"></i>@lang('seeker::messages.conversations.closed_by_moderation')
            </div>
        @endif

        @if($conversation->completion_status === 'accepted')
            <div class="alert alert-success rounded-0 border-start-0 border-end-0 mb-0">
                <div class="d-flex flex-wrap justify-content-between gap-2">
                    <strong><i class="bi bi-check-circle me-2" aria-hidden="true"></i>@lang('seeker::messages.completion.completed')</strong>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge text-bg-light">@lang('seeker::messages.completion.attempt_count', ['count' => $conversation->delivery_attempts])</span>
                        <span>@lang('seeker::messages.completion.paid_total', ['points' => format_money((float) $conversation->service_points + (float) $conversation->tip_points)])</span>
                    </div>
                </div>
                @if((float) $conversation->tip_points > 0)
                    <div class="small mt-1">@lang('seeker::messages.completion.tip_included', ['points' => format_money((float) $conversation->tip_points)])</div>
                @endif
                @if($conversation->final_message)
                    <div class="border-top mt-3 pt-3">{!! nl2br(e($conversation->final_message)) !!}</div>
                @endif
            </div>
        @elseif($conversation->status === 'active' && $conversation->isPaidCommission())
            @if($conversation->completion_status === 'rejected')
                <div class="alert alert-danger rounded-0 border-start-0 border-end-0 mb-0">
                    <i class="bi bi-x-circle me-2" aria-hidden="true"></i>@lang('seeker::messages.completion.rejected_notice')
                    <span class="badge text-bg-light ms-2">@lang('seeker::messages.completion.attempt_count', ['count' => $conversation->delivery_attempts])</span>
                </div>
            @endif

            @if(auth()->id() === $conversation->author_id)
                <div class="border-bottom p-3">
                    @if($conversation->completion_status === 'pending')
                        <div class="text-muted"><i class="bi bi-hourglass-split me-2" aria-hidden="true"></i>@lang('seeker::messages.completion.awaiting_client')</div>
                        <div class="small mt-1">@lang('seeker::messages.completion.attempt_count', ['count' => $conversation->delivery_attempts])</div>
                        @if($conversation->isHourlyCommission())
                            <div class="small mt-1">@lang('seeker::messages.completion.proposed_summary', ['hours' => $conversation->proposed_hours, 'points' => format_money($completionServicePoints)])</div>
                        @endif
                    @else
                        <form method="POST" action="{{ route('seeker.conversations.completion.request', $conversation) }}" class="d-flex flex-wrap align-items-end gap-2">
                            @csrf
                            @if($conversation->isHourlyCommission())
                                <div>
                                    <label class="form-label mb-1" for="completionHours">@lang('seeker::messages.completion.hours_worked')</label>
                                    <input id="completionHours" type="number" name="hours" value="{{ old('hours', $conversation->proposed_hours) }}" min="0.01" max="999999.99" step="0.01" class="form-control @error('hours') is-invalid @enderror" required>
                                    @error('hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            @endif
                            <button class="btn btn-success" type="submit">
                                <i class="bi bi-check2-circle me-1" aria-hidden="true"></i>
                                @lang($conversation->completion_status === 'rejected'
                                    ? 'seeker::messages.completion.request_again_action'
                                    : 'seeker::messages.completion.request_action')
                            </button>
                        </form>
                    @endif
                </div>
            @elseif($conversation->completion_status === 'pending')
                <div class="border-bottom p-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <strong class="d-block">@lang('seeker::messages.completion.client_decision')</strong>
                            <span class="badge text-bg-light mb-1">@lang('seeker::messages.completion.attempt_count', ['count' => $conversation->delivery_attempts])</span>
                            @if($conversation->isHourlyCommission())
                                <span class="small text-muted">@lang('seeker::messages.completion.proposed_summary', ['hours' => $conversation->proposed_hours, 'points' => format_money($completionServicePoints)])</span>
                            @else
                                <span class="small text-muted">@lang('seeker::messages.completion.fixed_summary', ['points' => format_money($completionServicePoints)])</span>
                            @endif
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <form method="POST" action="{{ route('seeker.conversations.completion.reject', $conversation) }}" onsubmit="return confirm(@js(trans('seeker::messages.completion.reject_confirm')))">
                                @csrf
                                <button class="btn btn-outline-danger" type="submit">@lang('seeker::messages.completion.reject_action')</button>
                            </form>
                            <a class="btn btn-success" href="{{ route('seeker.conversations.completion.show', $conversation) }}">@lang('seeker::messages.completion.review_action')</a>
                        </div>
                    </div>
                </div>
            @endif
        @endif

        @if($conversation->completion_status === 'accepted')
            <div class="border-bottom p-3">
                @if($conversationReview === null)
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <div>
                            <strong class="d-block">@lang('seeker::messages.reviews.conversation_title')</strong>
                            <span class="small text-muted">@lang('seeker::messages.reviews.conversation_description', ['user' => $other->name])</span>
                        </div>
                        <a class="btn btn-primary" href="{{ route('seeker.conversations.reviews.create', $conversation) }}">
                            <i class="bi bi-star me-1" aria-hidden="true"></i>@lang('seeker::messages.reviews.action')
                        </a>
                    </div>
                @else
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <strong>@lang('seeker::messages.reviews.your_review')</strong>
                        <span class="text-warning" aria-label="@lang('seeker::messages.reviews.rating_value', ['rating' => $conversationReview->rating])">
                            @foreach(range(1, 5) as $star)<i class="bi bi-star{{ $star <= $conversationReview->rating ? '-fill' : '' }}" aria-hidden="true"></i>@endforeach
                        </span>
                    </div>
                    <div class="small text-muted mt-1">{{ $conversationReview->comment }}</div>
                @endif
            </div>
        @endif

        <div class="card-body seeker-chat-messages p-3 p-md-4">
            @if($messages->hasPages())<div class="d-flex justify-content-center mb-4">{{ $messages->links() }}</div>@endif
            @foreach($messages->getCollection()->reverse() as $message)
                @php($mine = $message->sender_id === auth()->id())
                <div class="d-flex {{ $mine ? 'justify-content-end' : 'justify-content-start' }} mb-3">
                    <div class="seeker-message {{ $mine ? 'seeker-message-mine' : '' }}">
                        <div class="small fw-semibold mb-1">{{ $message->sender->name }}</div>
                        @if($message->isHidden())
                            <div class="seeker-message-moderated"><i class="bi bi-eye-slash me-1" aria-hidden="true"></i>@lang('seeker::messages.conversations.hidden_by_moderation')</div>
                        @else
                            @if($message->image_path)
                                <a href="{{ route('seeker.messages.images.show', $message) }}" target="_blank" rel="noopener">
                                    <img class="seeker-message-image rounded" src="{{ route('seeker.messages.images.show', $message) }}" loading="lazy" alt="{{ $message->image_original_name }}">
                                </a>
                            @endif
                            @if(filled($message->content))
                                <div class="seeker-message-content {{ $message->image_path ? 'mt-2' : '' }}">{!! nl2br(e($message->content)) !!}</div>
                            @endif
                        @endif
                        <div class="small {{ $mine ? 'text-white-50' : 'text-muted' }} text-end mt-1">{{ format_date($message->created_at, true) }}</div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card-footer p-3">
            @if($conversation->status === 'active')
                <form method="POST" action="{{ route('seeker.conversations.messages.store', $conversation) }}" enctype="multipart/form-data">
                    @csrf
                    <label class="visually-hidden" for="conversationMessage">@lang('seeker::messages.conversations.reply')</label>
                    <div class="input-group">
                        <textarea id="conversationMessage" name="content" rows="2" maxlength="2000" class="form-control @error('content') is-invalid @enderror" placeholder="@lang('seeker::messages.conversations.reply')">{{ old('content') }}</textarea>
                        <button class="btn btn-primary px-4"><i class="bi bi-send" aria-hidden="true"></i><span class="visually-hidden">@lang('seeker::messages.conversations.send')</span></button>
                        @error('content')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    @if($messageImagesEnabled)
                        <div class="mt-2">
                            <label class="form-label small mb-1" for="conversationImage"><i class="bi bi-image me-1" aria-hidden="true"></i>@lang('seeker::messages.conversations.image')</label>
                            <input id="conversationImage" type="file" name="image" accept="image/jpeg,image/png,image/webp" class="form-control form-control-sm @error('image') is-invalid @enderror">
                            <div class="form-text">@lang('seeker::messages.conversations.image_help')</div>
                            @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    @endif
                </form>
            @else
                <div class="text-center text-muted py-2"><i class="bi bi-lock me-2" aria-hidden="true"></i>@lang($conversation->status === 'closed' ? 'seeker::messages.conversations.closed_by_moderation_short' : 'seeker::messages.completion.conversation_closed')</div>
            @endif
        </div>
    </div>
@endsection
