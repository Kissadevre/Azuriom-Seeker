<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Notifications\AlertNotification;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Requests\ContactPublicationRequest;
use Azuriom\Plugin\Seeker\Services\CommissionCompletionService;
use Azuriom\Plugin\Seeker\Services\ConversationStarter;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
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

    public function create(
        Request $request,
        Publication $publication,
        SeekerSettings $settings,
        RestrictionService $restrictions
    ): View|RedirectResponse {
        $this->ensureContactable($publication, $request);

        $existing = Conversation::query()
            ->where('publication_id', $publication->id)
            ->where('client_id', $request->user()->id)
            ->first();

        if ($existing !== null) {
            return to_route('seeker.conversations.show', $existing);
        }

        if ($restrictions->restricted($request->user(), UserRestriction::TYPE_CONTACT)) {
            return to_route('seeker.publications.show', $publication)
                ->with('error', trans('seeker::messages.restrictions.contact'));
        }

        if (! $settings->newConversationsEnabled()) {
            return to_route('seeker.publications.show', $publication)
                ->with('error', trans('seeker::messages.features.new_conversations_disabled'));
        }

        $publication->load('user');

        return view('seeker::conversations.create', compact('publication'));
    }

    public function store(
        ContactPublicationRequest $request,
        Publication $publication,
        ConversationStarter $starter,
        SeekerSettings $settings,
        RestrictionService $restrictions
    ): RedirectResponse {
        $this->ensureContactable($publication, $request);

        $existing = Conversation::query()
            ->where('publication_id', $publication->id)
            ->where('client_id', $request->user()->id)
            ->first();

        if ($existing !== null) {
            return to_route('seeker.conversations.show', $existing);
        }

        if ($restrictions->restricted($request->user(), UserRestriction::TYPE_CONTACT)) {
            return to_route('seeker.publications.show', $publication)
                ->with('error', trans('seeker::messages.restrictions.contact'));
        }

        if (! $settings->newConversationsEnabled()) {
            return to_route('seeker.publications.show', $publication)
                ->with('error', trans('seeker::messages.features.new_conversations_disabled'));
        }

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

    public function show(
        Request $request,
        Conversation $conversation,
        CommissionCompletionService $completionService,
        SeekerSettings $settings
    ): View {
        $this->ensureParticipant($conversation, $request);

        $conversation->load(['publication', 'client', 'author']);
        $messages = $conversation->messages()
            ->with('sender')
            ->latest('id')
            ->paginate(50);
        $conversationReport = $conversation->reports()
            ->where('reporter_id', $request->user()->id)
            ->first();
        $conversationReview = $conversation->reviews()
            ->where('reviewer_id', $request->user()->id)
            ->first();
        $completionServicePoints = null;
        $messageImagesEnabled = $settings->messageImagesEnabled();

        if ($conversation->status === Conversation::STATUS_ACTIVE
            && $conversation->completion_status === Conversation::COMPLETION_PENDING
            && $conversation->isPaidCommission()) {
            $completionServicePoints = $completionService->calculateServicePoints(
                $conversation,
                $conversation->proposed_hours === null ? null : (float) $conversation->proposed_hours
            );
        }

        $conversation->messages()
            ->where('sender_id', '!=', $request->user()->id)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return view('seeker::conversations.show', compact(
            'conversation',
            'messages',
            'conversationReport',
            'conversationReview',
            'completionServicePoints',
            'messageImagesEnabled'
        ));
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
