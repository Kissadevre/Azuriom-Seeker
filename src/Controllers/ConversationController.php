<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Requests\ContactPublicationRequest;
use Azuriom\Plugin\Seeker\Services\ConversationStarter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $conversations = Conversation::query()
            ->forUser($user)
            ->with(['publication', 'client', 'author', 'latestMessage'])
            ->withCount(['messages as unread_count' => fn ($query) => $query
                ->where('sender_id', '!=', $user->id)
                ->whereNull('read_at')])
            ->orderByDesc('last_message_at')
            ->paginate(20);

        return view('seeker::conversations.index', compact('conversations', 'user'));
    }

    public function create(Request $request, Publication $publication): View|RedirectResponse
    {
        $this->ensureContactable($publication, $request);

        $existing = Conversation::query()
            ->where('publication_id', $publication->id)
            ->where('client_id', $request->user()->id)
            ->first();

        if ($existing !== null) {
            return to_route('seeker.conversations.show', $existing);
        }

        $publication->load('user');

        return view('seeker::conversations.create', compact('publication'));
    }

    public function store(
        ContactPublicationRequest $request,
        Publication $publication,
        ConversationStarter $starter
    ): RedirectResponse {
        $this->ensureContactable($publication, $request);

        $result = $starter->start($publication, $request->user(), $request->validated('content'));
        $conversation = $result['conversation'];

        if ($result['created']) {
            rescue(function () use ($request, $publication, $conversation) {
                (new AlertNotification(trans('seeker::messages.notifications.new_conversation', [
                    'user' => $request->user()->name,
                    'publication' => $publication->title,
                ])))
                    ->link(route('seeker.conversations.show', $conversation, false))
                    ->from($request->user())
                    ->send($publication->user);
            }, report: true);
        }

        return to_route('seeker.conversations.show', $conversation)
            ->with('success', trans($result['created']
                ? 'seeker::messages.contact.created'
                : 'seeker::messages.contact.already_exists'));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $this->ensureParticipant($conversation, $request);

        $conversation->load(['publication', 'client', 'author']);
        $messages = $conversation->messages()
            ->with('sender')
            ->latest('id')
            ->paginate(50);

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('seeker::conversations.show', compact('conversation', 'messages'));
    }

    private function ensureContactable(Publication $publication, Request $request): void
    {
        abort_if($publication->user_id === $request->user()->id, 403);
        abort_unless(
            $publication->status === Publication::STATUS_ACTIVE
            && $publication->published_at !== null
            && $publication->published_at->isPast(),
            404
        );
    }

    private function ensureParticipant(Conversation $conversation, Request $request): void
    {
        abort_unless($conversation->includes($request->user()), 403);
    }
}
