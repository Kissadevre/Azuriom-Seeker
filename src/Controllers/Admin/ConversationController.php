<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Plugin\Seeker\Models\Conversation;
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
            ->withCount(['messages', 'reports'])
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
            ->latest()
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
}
