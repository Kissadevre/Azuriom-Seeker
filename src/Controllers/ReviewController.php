<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Review;
use Azuriom\Plugin\Seeker\Requests\StoreReviewRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function create(Request $request, Conversation $conversation): View|RedirectResponse
    {
        $this->ensureReviewable($conversation, $request);

        if ($conversation->reviews()->where('reviewer_id', $request->user()->id)->exists()) {
            return to_route('seeker.conversations.show', $conversation)
                ->with('error', trans('seeker::messages.reviews.already_submitted'));
        }

        $conversation->load(['publication', 'client', 'author']);
        $reviewedUser = $conversation->otherParticipant($request->user());

        return view('seeker::conversations.review', compact('conversation', 'reviewedUser'));
    }

    public function store(StoreReviewRequest $request, Conversation $conversation): RedirectResponse
    {
        $review = DB::transaction(function () use ($request, $conversation) {
            $lockedConversation = Conversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);
            $this->ensureCompletedParticipant($lockedConversation, $request);

            if ($lockedConversation->reviews()->where('reviewer_id', $request->user()->id)->exists()) {
                return null;
            }

            $reviewedUserId = $lockedConversation->client_id === $request->user()->id
                ? $lockedConversation->author_id
                : $lockedConversation->client_id;

            $review = new Review([
                'rating' => $request->validated('rating'),
                'comment' => trim($request->validated('comment')),
                'is_visible' => true,
            ]);
            $review->conversation_id = $lockedConversation->id;
            $review->publication_id = $lockedConversation->publication_id;
            $review->reviewer_id = $request->user()->id;
            $review->reviewed_user_id = $reviewedUserId;
            $review->save();

            return $review;
        }, 3);

        if ($review === null) {
            return to_route('seeker.conversations.show', $conversation)
                ->with('error', trans('seeker::messages.reviews.already_submitted'));
        }

        $conversation->loadMissing(['client', 'author']);
        $reviewedUser = $conversation->otherParticipant($request->user());

        rescue(function () use ($request, $conversation, $reviewedUser) {
            (new AlertNotification(trans('seeker::messages.notifications.review_received', [
                'user' => $request->user()->name,
            ])))
                ->link(route('seeker.conversations.show', $conversation, false))
                ->from($request->user())
                ->send($reviewedUser);
        }, report: true);

        return to_route('seeker.conversations.show', $conversation)
            ->with('success', trans('seeker::messages.reviews.submitted'));
    }

    private function ensureReviewable(Conversation $conversation, Request $request): void
    {
        abort_unless($conversation->includes($request->user()), 403);
        $this->ensureCompletedParticipant($conversation, $request);
    }

    private function ensureCompletedParticipant(Conversation $conversation, Request $request): void
    {
        abort_unless(
            $conversation->includes($request->user())
            && $conversation->status === Conversation::STATUS_COMPLETED
            && $conversation->completion_status === Conversation::COMPLETION_ACCEPTED,
            409
        );
    }
}
