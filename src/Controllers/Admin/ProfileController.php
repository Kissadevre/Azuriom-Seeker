<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Profile;
use Illuminate\Http\RedirectResponse;

class ProfileController extends Controller
{
    public function clearBiography(User $user): RedirectResponse
    {
        $profile = Profile::query()->where('user_id', $user->id)->first();

        if ($profile !== null && filled($profile->bio)) {
            $profile->update(['bio' => null]);

            ActionLog::log('seeker.profiles.biography_removed', $user, [
                'user' => $user->name,
            ]);
        }

        return to_route('seeker.profiles.show', $user)
            ->with('success', trans('seeker::messages.profiles.moderation.biography_removed'));
    }
}
