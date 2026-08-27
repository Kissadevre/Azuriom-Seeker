<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Profile;
use Azuriom\Plugin\Seeker\Models\ProfileReport;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\Review;
use Azuriom\Plugin\Seeker\Requests\StoreProfileReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProfileReportController extends Controller
{
    public function create(Request $request, User $user): View|RedirectResponse
    {
        abort_if($request->user()->id === $user->id, 403);
        $this->ensureSeekerProfileExists($user);

        if ($this->existingReport($user, $request) !== null) {
            return to_route('seeker.profiles.show', $user)
                ->with('error', trans('seeker::messages.profile_reports.already_sent'));
        }

        $profile = Profile::query()->where('user_id', $user->id)->first();

        return view('seeker::profiles.report', compact('user', 'profile'));
    }

    public function store(StoreProfileReportRequest $request, User $user): RedirectResponse
    {
        $this->ensureSeekerProfileExists($user);

        $created = DB::transaction(function () use ($request, $user) {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if (ProfileReport::query()
                ->where('profile_user_id', $lockedUser->id)
                ->where('reporter_id', $request->user()->id)
                ->exists()) {
                return false;
            }

            ProfileReport::create([
                'profile_user_id' => $lockedUser->id,
                'reporter_id' => $request->user()->id,
                'reason' => $request->validated('reason'),
                'details' => trim($request->validated('details')),
                'reported_bio' => Profile::query()->where('user_id', $lockedUser->id)->value('bio'),
                'status' => ProfileReport::STATUS_PENDING,
            ]);

            return true;
        }, 3);

        return to_route('seeker.profiles.show', $user)
            ->with($created ? 'success' : 'error', trans($created
                ? 'seeker::messages.profile_reports.sent'
                : 'seeker::messages.profile_reports.already_sent'));
    }

    private function existingReport(User $user, Request $request): ?ProfileReport
    {
        return ProfileReport::query()
            ->where('profile_user_id', $user->id)
            ->where('reporter_id', $request->user()->id)
            ->first();
    }

    private function ensureSeekerProfileExists(User $user): void
    {
        abort_unless(
            Profile::query()->where('user_id', $user->id)->exists()
            || Publication::query()->where('user_id', $user->id)->exists()
            || Conversation::query()->where('client_id', $user->id)->orWhere('author_id', $user->id)->exists()
            || Review::query()->where('reviewed_user_id', $user->id)->exists(),
            404
        );
    }
}
