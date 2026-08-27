<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\ConversationReport;
use Azuriom\Plugin\Seeker\Requests\StoreConversationReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConversationReportController extends Controller
{
    public function create(Request $request, Conversation $conversation): View|RedirectResponse
    {
        $this->ensureParticipant($conversation, $request);

        if ($conversation->reports()->where('reporter_id', $request->user()->id)->exists()) {
            return to_route('seeker.conversations.show', $conversation)
                ->with('warning', trans('seeker::messages.reports.already_sent'));
        }

        $conversation->load(['client', 'author', 'publication']);
        $reportedUser = $conversation->otherParticipant($request->user());

        return view('seeker::conversations.report', compact('conversation', 'reportedUser'));
    }

    public function store(
        StoreConversationReportRequest $request,
        Conversation $conversation
    ): RedirectResponse {
        $created = DB::transaction(function () use ($request, $conversation) {
            $lockedConversation = Conversation::query()
                ->lockForUpdate()
                ->findOrFail($conversation->id);
            abort_unless($lockedConversation->includes($request->user()), 403);

            if ($lockedConversation->reports()->where('reporter_id', $request->user()->id)->exists()) {
                return false;
            }

            $reportedUserId = $lockedConversation->client_id === $request->user()->id
                ? $lockedConversation->author_id
                : $lockedConversation->client_id;

            ConversationReport::create([
                'conversation_id' => $lockedConversation->id,
                'reporter_id' => $request->user()->id,
                'reported_user_id' => $reportedUserId,
                'reported_through_message_id' => $lockedConversation->messages()->max('id'),
                'reason' => $request->validated('reason'),
                'details' => $request->validated('details'),
                'status' => ConversationReport::STATUS_PENDING,
            ]);

            return true;
        }, 3);

        return to_route('seeker.conversations.show', $conversation)
            ->with($created ? 'success' : 'warning', trans($created
                ? 'seeker::messages.reports.sent'
                : 'seeker::messages.reports.already_sent'));
    }

    private function ensureParticipant(Conversation $conversation, Request $request): void
    {
        abort_unless($conversation->includes($request->user()), 403);
    }
}
