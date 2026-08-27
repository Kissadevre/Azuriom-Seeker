<?php

namespace Azuriom\Plugin\Seeker\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class SeekerServiceProvider extends BasePluginServiceProvider
{
    public function boot(): void
    {
        $this->loadViews();
        $this->loadTranslations();
        $this->loadMigrations();
        $this->registerRouteDescriptions();
        $this->registerAdminNavigation();
        $this->registerUserNavigation();
        $this->registerRateLimiters();

        Permission::registerPermissions([
            'seeker.moderate' => 'seeker::admin.permissions.moderate',
        ]);

        ActionLog::registerLogs('seeker.settings.updated', [
            'icon' => 'sliders',
            'color' => 'info',
            'message' => 'seeker::admin.logs.settings_updated',
        ]);

        ActionLog::registerLogs('seeker.conversations.closed', [
            'icon' => 'lock',
            'color' => 'danger',
            'message' => 'seeker::admin.logs.conversation_closed',
            'model' => Conversation::class,
        ]);
    }

    protected function routeDescriptions(): array
    {
        return [
            'seeker.index' => trans('seeker::messages.title'),
        ];
    }

    protected function registerRateLimiters(): void
    {
        RateLimiter::for('seeker.publications.create', fn (Request $request) => $this->publicationLimits($request, 'create'));
        RateLimiter::for('seeker.publications.edit', fn (Request $request) => $this->publicationLimits($request, 'edit'));
    }

    private function publicationLimits(Request $request, string $action): Limit|array
    {
        $definitions = $this->app->make(SeekerSettings::class)->rateLimits($action);
        $limits = [];

        foreach ($definitions as $definition) {
            if ($definition['attempts'] === 0) {
                continue;
            }

            $identifier = $definition['by_user']
                ? (string) $request->user()?->getAuthIdentifier()
                : (string) $request->ip();

            $limits[] = $this->publicationLimit(
                Limit::perMinutes($definition['window'], $definition['attempts']),
                $request,
                $definition['scope'].':'.$identifier.':'.$definition['attempts'].':'.$definition['window']
            );
        }

        return $limits === [] ? Limit::none() : $limits;
    }

    private function publicationLimit(Limit $limit, Request $request, string $key): Limit
    {
        return $limit
            ->by('seeker:'.$key)
            ->response(fn (Request $request, array $headers) => back()
                ->with('error', trans('seeker::messages.security.rate_limited', [
                    'seconds' => $headers['Retry-After'] ?? 60,
                ]))
                ->withInput($request->except([
                    'g-recaptcha-response',
                    'h-captcha-response',
                    'cf-turnstile-response',
                ]))
                ->withHeaders($headers));
    }

    protected function adminNavigation(): array
    {
        return [
            'seeker' => [
                'name' => trans('seeker::admin.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-people',
                'permission' => 'seeker.moderate',
                'route' => 'seeker.admin.*',
                'items' => [
                    'seeker.admin.settings' => trans('seeker::admin.nav.settings'),
                    'seeker.admin.publications.index' => trans('seeker::admin.nav.publications'),
                    'seeker.admin.conversations.index' => trans('seeker::admin.nav.conversations'),
                    'seeker.admin.profile-reports.index' => trans('seeker::admin.nav.reports'),
                    'seeker.admin.transactions.index' => trans('seeker::admin.nav.transactions'),
                ],
            ],
        ];
    }

    protected function userNavigation(): array
    {
        return [
            'seeker' => [
                'name' => trans('seeker::messages.my_publications'),
                'icon' => 'bi bi-briefcase',
                'route' => 'seeker.publications.mine',
            ],
            'seeker-messages' => [
                'name' => trans('seeker::messages.conversations.title'),
                'icon' => 'bi bi-chat-dots',
                'route' => 'seeker.conversations.index',
            ],
        ];
    }
}
