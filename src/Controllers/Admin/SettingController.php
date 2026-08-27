<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show(SeekerSettings $settings): View
    {
        return view('seeker::admin.settings', [
            'publicationsEnabled' => $settings->publicationsEnabled(),
            'newConversationsEnabled' => $settings->newConversationsEnabled(),
            'biographiesEnabled' => $settings->biographiesEnabled(),
            'messageImagesEnabled' => $settings->messageImagesEnabled(),
            'rateLimits' => $settings->allRateLimits(),
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $rules = [
            'publications_enabled' => ['required', 'boolean'],
            'new_conversations_enabled' => ['required', 'boolean'],
            'biographies_enabled' => ['required', 'boolean'],
            'message_images_enabled' => ['required', 'boolean'],
            'limits' => ['required', 'array'],
        ];

        foreach (array_keys(SeekerSettings::RATE_LIMITS) as $name) {
            $rules['limits.'.$name.'.attempts'] = ['required', 'integer', 'min:0', 'max:10000'];
            $rules['limits.'.$name.'.window'] = ['required', 'integer', 'min:1', 'max:10080'];
        }

        $validated = $request->validate($rules);
        $values = [
            SeekerSettings::PUBLICATIONS_ENABLED_KEY => (bool) $validated['publications_enabled'],
            SeekerSettings::NEW_CONVERSATIONS_ENABLED_KEY => (bool) $validated['new_conversations_enabled'],
            SeekerSettings::BIOGRAPHIES_ENABLED_KEY => (bool) $validated['biographies_enabled'],
            SeekerSettings::MESSAGE_IMAGES_ENABLED_KEY => (bool) $validated['message_images_enabled'],
        ];

        foreach (SeekerSettings::RATE_LIMITS as $name => $definition) {
            $values[$definition['attempts_key']] = (int) $validated['limits'][$name]['attempts'];
            $values[$definition['window_key']] = (int) $validated['limits'][$name]['window'];
        }

        Setting::updateSettings($values);
        ActionLog::log('seeker.settings.updated');

        return to_route('seeker.admin.settings')
            ->with('success', trans('seeker::admin.settings.updated'));
    }
}
