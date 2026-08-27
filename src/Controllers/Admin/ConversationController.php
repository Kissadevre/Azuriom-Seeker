<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Message;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ConversationController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), Conversation::statuses(), true)
            ? $request->query('status')
            : null;
        $reports = in_array($request->query('reports'), ['with', 'without'], true)
            ? $request->query('reports')
            : null;

        $conversations = Conversation::query()
            ->with(['publication', 'client', 'author'])
            ->withCount('reports')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($reports === 'with', fn ($query) => $query->whereHas('reports'))
            ->when($reports === 'without', fn ($query) => $query->whereDoesntHave('reports'))
            ->latest('updated_at')
            ->paginate(20)
            ->withQueryString();

        return view('seeker::admin.conversations.index', compact('conversations', 'status', 'reports'));
    }

    public function show(Conversation $conversation): View
    {
        $conversation->load(['publication', 'client', 'author'])->loadCount(['messages', 'reports']);
        $messages = $conversation->messages()
            ->with('sender')
            ->latest('id')
            ->paginate(100, ['*'], 'messages_page');
        $reports = $conversation->reports()
            ->with(['reporter', 'reportedUser'])
            ->latest('created_at')
            ->latest('id')
            ->get();

        return view('seeker::admin.conversations.show', compact('conversation', 'messages', 'reports'));
    }

    public function close(Conversation $conversation): RedirectResponse
    {
        $closed = DB::transaction(function () use ($conversation) {
            $lockedConversation = Conversation::query()->lockForUpdate()->findOrFail($conversation->id);

            if ($lockedConversation->status !== Conversation::STATUS_ACTIVE) {
                return false;
            }

            $lockedConversation->update(['status' => Conversation::STATUS_CLOSED]);

            return true;
        }, 3);

        if ($closed) {
            ActionLog::log('seeker.conversations.closed', $conversation);
        }

        return back()->with($closed ? 'success' : 'warning', trans($closed
            ? 'seeker::admin.conversations.closed'
            : 'seeker::admin.conversations.already_read_only'));
    }

    public function reopen(Conversation $conversation): RedirectResponse
    {
        $reopened = DB::transaction(function () use ($conversation) {
            $lockedConversation = Conversation::query()->lockForUpdate()->findOrFail($conversation->id);

            if ($lockedConversation->status !== Conversation::STATUS_CLOSED) {
                return false;
            }

            $lockedConversation->update(['status' => Conversation::STATUS_ACTIVE]);

            return true;
        }, 3);

        if ($reopened) {
            ActionLog::log('seeker.conversations.reopened', $conversation);
        }

        return back()->with($reopened ? 'success' : 'warning', trans($reopened
            ? 'seeker::admin.conversations.reopened'
            : 'seeker::admin.conversations.cannot_reopen'));
    }

    public function hideMessage(Request $request, Message $message): RedirectResponse
    {
        $hidden = DB::transaction(function () use ($request, $message) {
            $lockedMessage = Message::query()->lockForUpdate()->findOrFail($message->id);

            if ($lockedMessage->isHidden()) {
                return false;
            }

            $lockedMessage->update([
                'hidden_at' => now(),
                'hidden_by_id' => $request->user()->id,
            ]);

            return true;
        }, 3);

        if ($hidden) {
            ActionLog::log('seeker.messages.hidden', $message);
        }

        return back()->with($hidden ? 'success' : 'warning', trans($hidden
            ? 'seeker::admin.conversations.message_hidden'
            : 'seeker::admin.conversations.message_already_hidden'));
    }

    public function restoreMessage(Message $message): RedirectResponse
    {
        $restored = DB::transaction(function () use ($message) {
            $lockedMessage = Message::query()->lockForUpdate()->findOrFail($message->id);

            if (! $lockedMessage->isHidden()) {
                return false;
            }

            $lockedMessage->update([
                'hidden_at' => null,
                'hidden_by_id' => null,
            ]);

            return true;
        }, 3);

        if ($restored) {
            ActionLog::log('seeker.messages.restored', $message);
        }

        return back()->with($restored ? 'success' : 'warning', trans($restored
            ? 'seeker::admin.conversations.message_restored'
            : 'seeker::admin.conversations.message_already_visible'));
    }
}
