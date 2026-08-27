<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RestrictionController extends Controller
{
    public function index(Request $request): View
    {
        $state = in_array($request->query('state'), ['active', 'history', 'all'], true)
            ? $request->query('state')
            : 'active';
        $selectedUser = $request->integer('user_id') > 0
            ? User::query()->find($request->integer('user_id'))
            : null;
        $selectedUserProfileRestricted = $selectedUser !== null
            && UserRestriction::query()
                ->active()
                ->where('user_id', $selectedUser->id)
                ->where('type', UserRestriction::TYPE_PROFILE)
                ->exists();
        $restrictionUserId = (int) $request->old('user_id', $selectedUser?->id);
        $restrictionUser = $restrictionUserId > 0
            ? User::query()->find($restrictionUserId)
            : null;

        $restrictions = UserRestriction::query()
            ->with(['user', 'createdBy', 'revokedBy'])
            ->when($state === 'active', fn ($query) => $query->active())
            ->when($state === 'history', fn ($query) => $query->where(function ($query) {
                $query->whereNotNull('revoked_at')
                    ->orWhere(fn ($query) => $query->whereNotNull('expires_at')->where('expires_at', '<=', now()));
            }))
            ->when($selectedUser, fn ($query) => $query->where('user_id', $selectedUser->id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('seeker::admin.restrictions.index', compact(
            'restrictions',
            'state',
            'selectedUser',
            'selectedUserProfileRestricted',
            'restrictionUser'
        ));
    }

    public function searchUsers(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:64'],
        ]);

        $users = User::query()
            ->registered()
            ->search(trim($validated['q']), 'name')
            ->orderBy('name')
            ->limit(10)
            ->get();

        return response()->json([
            'data' => $users->map(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'avatar' => $user->getAvatar(40),
            ]),
        ]);
    }

    public function store(Request $request, RestrictionService $restrictionService): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'type' => ['required', Rule::in(UserRestriction::types())],
            'duration' => ['required', Rule::in(['indefinite', 'until'])],
            'expires_at' => ['nullable', 'required_if:duration,until', 'date', 'after:now'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
            'conversation_id' => ['nullable', 'integer', 'prohibits:publication_id', Rule::exists(Conversation::class, 'id')],
            'publication_id' => ['nullable', 'integer', 'prohibits:conversation_id', Rule::exists(Publication::class, 'id')],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
        $conversation = filled($validated['conversation_id'] ?? null)
            ? Conversation::query()->findOrFail($validated['conversation_id'])
            : null;
        $publication = filled($validated['publication_id'] ?? null)
            ? Publication::query()->withTrashed()->findOrFail($validated['publication_id'])
            : null;

        abort_if($conversation !== null
            && ! in_array($user->id, [$conversation->author_id, $conversation->client_id], true), 403);
        abort_if($publication !== null && $publication->user_id !== $user->id, 403);

        $restriction = DB::transaction(function () use ($request, $user, $validated) {
            User::query()->lockForUpdate()->findOrFail($user->id);

            if (UserRestriction::query()
                ->active()
                ->where('user_id', $user->id)
                ->where('type', $validated['type'])
                ->exists()) {
                throw ValidationException::withMessages([
                    'type' => trans('seeker::admin.restrictions.already_active'),
                ]);
            }

            return UserRestriction::create([
                'user_id' => $user->id,
                'created_by_id' => $request->user()->id,
                'type' => $validated['type'],
                'reason' => trim($validated['reason']),
                'expires_at' => $validated['duration'] === 'until' ? $validated['expires_at'] : null,
            ]);
        }, 3);

        $restrictionService->clear($user);
        ActionLog::log('seeker.restrictions.created', $restriction, [
            'user' => $user->name,
            'type' => $restriction->type,
        ]);

        $redirect = match (true) {
            $conversation !== null => to_route('seeker.admin.conversations.show', $conversation),
            $publication !== null => to_route('seeker.admin.publications.show', $publication),
            default => to_route('seeker.admin.restrictions.index', ['user_id' => $user->id]),
        };

        return $redirect->with('success', trans('seeker::admin.restrictions.created'));
    }

    public function revoke(
        Request $request,
        UserRestriction $restriction,
        RestrictionService $restrictionService
    ): RedirectResponse {
        $revoked = DB::transaction(function () use ($request, $restriction) {
            $lockedRestriction = UserRestriction::query()->lockForUpdate()->findOrFail($restriction->id);

            if (! $lockedRestriction->isActive()) {
                return false;
            }

            $lockedRestriction->update([
                'revoked_at' => now(),
                'revoked_by_id' => $request->user()->id,
            ]);

            return true;
        }, 3);

        $restrictionService->clear($restriction->user);
        if ($revoked) {
            ActionLog::log('seeker.restrictions.revoked', $restriction, [
                'user' => $restriction->user->name,
                'type' => $restriction->type,
            ]);
        }

        return back()->with($revoked ? 'success' : 'warning', trans($revoked
            ? 'seeker::admin.restrictions.revoked'
            : 'seeker::admin.restrictions.not_active'));
    }

    public function removePublications(Request $request, User $user): RedirectResponse
    {
        $request->validate(['confirm' => ['accepted']]);

        $count = DB::transaction(function () use ($user) {
            User::query()->lockForUpdate()->findOrFail($user->id);
            $publications = Publication::query()->where('user_id', $user->id)->lockForUpdate()->get();
            $publications->each->delete();

            return $publications->count();
        }, 3);

        ActionLog::log('seeker.publications.removed_for_user', $user, [
            'user' => $user->name,
            'count' => $count,
        ]);

        return back()->with('success', trans('seeker::admin.restrictions.publications_removed', [
            'count' => $count,
        ]));
    }
}
