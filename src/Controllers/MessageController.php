<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Requests\StoreMessageRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    public function store(StoreMessageRequest $request, Conversation $conversation): RedirectResponse
    {
        $message = DB::transaction(function () use ($request, $conversation) {
            $lockedConversation = Conversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);

            abort_unless($lockedConversation->includes($request->user()), 403);
            abort_unless($lockedConversation->status === Conversation::STATUS_ACTIVE, 409);

            $message = $lockedConversation->messages()->create([
                'sender_id' => $request->user()->id,
                'content' => $request->validated('content'),
            ]);
            $lockedConversation->update(['last_message_at' => $message->created_at]);

            return $message;
        }, 3);

        $conversation->loadMissing(['client', 'author']);
        $recipient = $conversation->otherParticipant($request->user());

        rescue(function () use ($request, $conversation, $recipient) {
            (new AlertNotification(trans('seeker::messages.notifications.new_message', [
                'user' => $request->user()->name,
            ])))
                ->link(route('seeker.conversations.show', $conversation, false))
                ->from($request->user())
                ->send($recipient);
        }, report: true);

        return to_route('seeker.conversations.show', $conversation)
            ->with('success', trans('seeker::messages.conversations.message_sent'));
    }
}
