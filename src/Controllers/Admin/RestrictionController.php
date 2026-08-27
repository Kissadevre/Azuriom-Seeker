<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
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
            'selectedUserProfileRestricted'
        ));
    }

    public function store(Request $request, RestrictionService $restrictionService): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'integer', Rule::exists(User::class, 'id')],
            'type' => ['required', Rule::in(UserRestriction::types())],
            'duration' => ['required', Rule::in(['indefinite', 'until'])],
            'expires_at' => ['nullable', 'required_if:duration,until', 'date', 'after:now'],
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $user = User::query()->findOrFail($validated['user_id']);
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

        return to_route('seeker.admin.restrictions.index', ['user_id' => $user->id])
            ->with('success', trans('seeker::admin.restrictions.created'));
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
