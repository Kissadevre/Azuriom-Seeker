<?php

namespace Azuriom\Plugin\Seeker\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
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
    }

    protected function routeDescriptions(): array
    {
        return [
            'seeker.index' => trans('seeker::messages.title'),
        ];
    }

    protected function registerRateLimiters(): void
    {
        RateLimiter::for('seeker.publications.create', fn (Request $request) => [
            $this->publicationLimit(Limit::perHour(3), $request, 'create:user'),
            $this->publicationLimit(Limit::perHour(15), $request, 'create:ip', false),
            $this->publicationLimit(Limit::perDay(10), $request, 'create:daily'),
        ]);

        RateLimiter::for('seeker.publications.edit', fn (Request $request) => [
            $this->publicationLimit(Limit::perHour(12), $request, 'edit:user'),
            $this->publicationLimit(Limit::perHour(60), $request, 'edit:ip', false),
            $this->publicationLimit(Limit::perDay(30), $request, 'edit:daily'),
        ]);
    }

    private function publicationLimit(Limit $limit, Request $request, string $scope, bool $byUser = true): Limit
    {
        $identifier = $byUser
            ? (string) $request->user()->getAuthIdentifier()
            : (string) $request->ip();

        return $limit
            ->by('seeker:'.$scope.':'.$identifier)
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
                'icon' => 'bi bi-people',
                'permission' => 'seeker.moderate',
                'route' => 'seeker.admin.publications.index',
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
