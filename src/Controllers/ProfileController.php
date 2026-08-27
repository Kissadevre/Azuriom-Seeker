<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Profile;
use Azuriom\Plugin\Seeker\Models\ProfileReport;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\Review;
use Azuriom\Plugin\Seeker\Requests\UpdateProfileRequest;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(Request $request, User $user, SeekerSettings $settings): View
    {
        $profile = Profile::query()->where('user_id', $user->id)->first();
        abort_unless($this->hasSeekerPresence($user, $profile) || $request->user()?->id === $user->id, 404);

        $authorConversations = Conversation::query()
            ->where('author_id', $user->id)
            ->whereHas('publication', fn ($query) => $query->where('type', Publication::TYPE_COMMISSION));
        $clientConversations = Conversation::query()
            ->where('client_id', $user->id)
            ->whereHas('publication', fn ($query) => $query->where('type', Publication::TYPE_COMMISSION));

        $statistics = [
            'active_publications' => Publication::query()
                ->where('user_id', $user->id)
                ->visible()
                ->count(),
            'author_commissions' => (clone $authorConversations)->count(),
            'author_completed' => (clone $authorConversations)->where('status', Conversation::STATUS_COMPLETED)->count(),
            'client_commissions' => (clone $clientConversations)->count(),
            'client_completed' => (clone $clientConversations)->where('status', Conversation::STATUS_COMPLETED)->count(),
        ];

        $reputation = [
            'overall' => $this->reputationFor($user),
            'author' => $this->reputationFor($user, 'author_id'),
            'client' => $this->reputationFor($user, 'client_id'),
        ];

        $reviews = Review::query()
            ->where('reviewed_user_id', $user->id)
            ->where('is_visible', true)
            ->whereNotNull('conversation_id')
            ->with(['reviewer', 'conversation'])
            ->latest()
            ->paginate(10, ['*'], 'reviews_page');

        $publications = Publication::query()
            ->where('user_id', $user->id)
            ->visible()
            ->when($request->user() === null, fn ($query) => $query->where('is_guest_visible', true))
            ->with('images')
            ->latest('published_at')
            ->limit(6)
            ->get();

        $profileReport = $request->user() !== null && $request->user()->id !== $user->id
            ? ProfileReport::query()
                ->where('profile_user_id', $user->id)
                ->where('reporter_id', $request->user()->id)
                ->first()
            : null;
        $biographiesEnabled = $settings->biographiesEnabled();

        return view('seeker::profiles.show', compact(
            'user',
            'profile',
            'statistics',
            'reputation',
            'reviews',
            'publications',
            'profileReport',
            'biographiesEnabled'
        ));
    }

    public function edit(Request $request, User $user, SeekerSettings $settings): View|RedirectResponse
    {
        abort_unless($request->user()->id === $user->id, 403);

        if (! $settings->biographiesEnabled()) {
            return to_route('seeker.profiles.show', $user)
                ->with('error', trans('seeker::messages.features.biographies_disabled'));
        }

        $profile = Profile::query()->firstOrNew(['user_id' => $user->id]);

        return view('seeker::profiles.edit', compact('user', 'profile'));
    }

    public function update(UpdateProfileRequest $request, User $user, SeekerSettings $settings): RedirectResponse
    {
        if (! $settings->biographiesEnabled()) {
            return to_route('seeker.profiles.show', $user)
                ->with('error', trans('seeker::messages.features.biographies_disabled'));
        }

        $profile = Profile::query()->firstOrNew(['user_id' => $user->id]);
        $profile->user_id = $user->id;
        $profile->bio = $request->filled('bio') ? trim($request->validated('bio')) : null;
        $profile->save();

        return to_route('seeker.profiles.show', $user)
            ->with('success', trans('seeker::messages.profiles.updated'));
    }

    private function reputationFor(User $user, ?string $conversationRole = null): object
    {
        return Review::query()
            ->where('reviewed_user_id', $user->id)
            ->where('is_visible', true)
            ->when($conversationRole !== null, fn ($query) => $query
                ->whereHas('conversation', fn ($conversation) => $conversation
                    ->where($conversationRole, $user->id)))
            ->selectRaw('AVG(rating) as rating, COUNT(*) as reviews_count')
            ->first();
    }

    private function hasSeekerPresence(User $user, ?Profile $profile): bool
    {
        return $profile !== null
            || Publication::query()->where('user_id', $user->id)->exists()
            || Conversation::query()->where('client_id', $user->id)->orWhere('author_id', $user->id)->exists()
            || Review::query()->where('reviewed_user_id', $user->id)->exists();
    }
}
