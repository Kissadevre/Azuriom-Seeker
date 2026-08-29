<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Setting;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function show(SeekerSettings $settings): View
    {
        return view('seeker::admin.settings', [
            'seekerEnabled' => $settings->enabled(),
            'publicationsEnabled' => $settings->publicationsEnabled(),
            'newConversationsEnabled' => $settings->newConversationsEnabled(),
            'biographiesEnabled' => $settings->biographiesEnabled(),
            'messageImagesEnabled' => $settings->messageImagesEnabled(),
            'userMenuItems' => $settings->userMenuItems(),
            'portfolioTypes' => $settings->portfolioTypes(),
            'rateLimits' => $settings->allRateLimits(),
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $rules = [
            'seeker_enabled' => ['required', 'boolean'],
            'publications_enabled' => ['required', 'boolean'],
            'new_conversations_enabled' => ['required', 'boolean'],
            'biographies_enabled' => ['required', 'boolean'],
            'message_images_enabled' => ['required', 'boolean'],
            'user_menu' => ['required', 'array:'.implode(',', array_keys(SeekerSettings::USER_MENU_ITEMS))],
            'portfolio_types' => [
                'required',
                'array:'.implode(',', Publication::portfolioTypes()),
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $hasEnabledType = collect(Publication::portfolioTypes())
                        ->contains(fn (string $type) => filter_var($value[$type] ?? false, FILTER_VALIDATE_BOOL));

                    if (! $hasEnabledType) {
                        $fail(trans('seeker::admin.settings.portfolio_types.at_least_one'));
                    }
                },
            ],
            'limits' => ['required', 'array'],
        ];

        foreach (Publication::portfolioTypes() as $type) {
            $rules['portfolio_types.'.$type] = ['required', 'boolean'];
        }

        foreach (array_keys(SeekerSettings::USER_MENU_ITEMS) as $item) {
            $rules['user_menu.'.$item.'.enabled'] = ['required', 'boolean'];
            $rules['user_menu.'.$item.'.icon'] = [
                'required',
                'string',
                'max:64',
                'regex:/\Abi-[a-z0-9]+(?:-[a-z0-9]+)*\z/',
            ];
        }

        foreach (array_keys(SeekerSettings::RATE_LIMITS) as $name) {
            $rules['limits.'.$name.'.attempts'] = ['required', 'integer', 'min:0', 'max:10000'];
            $rules['limits.'.$name.'.window'] = ['required', 'integer', 'min:1', 'max:10080'];
        }

        $validated = $request->validate($rules);
        $values = [
            SeekerSettings::ENABLED_KEY => (bool) $validated['seeker_enabled'],
            SeekerSettings::PUBLICATIONS_ENABLED_KEY => (bool) $validated['publications_enabled'],
            SeekerSettings::NEW_CONVERSATIONS_ENABLED_KEY => (bool) $validated['new_conversations_enabled'],
            SeekerSettings::BIOGRAPHIES_ENABLED_KEY => (bool) $validated['biographies_enabled'],
            SeekerSettings::MESSAGE_IMAGES_ENABLED_KEY => (bool) $validated['message_images_enabled'],
        ];

        foreach (SeekerSettings::USER_MENU_ITEMS as $item => $definition) {
            $values[$definition['enabled_key']] = (bool) $validated['user_menu'][$item]['enabled'];
            $values[$definition['icon_key']] = $validated['user_menu'][$item]['icon'];
        }

        foreach (SeekerSettings::PORTFOLIO_TYPE_KEYS as $type => $key) {
            $values[$key] = (bool) $validated['portfolio_types'][$type];
        }

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
