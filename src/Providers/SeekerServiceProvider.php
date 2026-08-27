<?php

namespace Azuriom\Plugin\Seeker\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Message;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Azuriom\Plugin\Seeker\Support\SeekerPermissions;
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
            SeekerPermissions::ACCESS => 'seeker::admin.permissions.access',
            SeekerPermissions::CREATE_PUBLICATIONS => 'seeker::admin.permissions.publications_create',
            SeekerPermissions::DELETE_OWN_PUBLICATIONS => 'seeker::admin.permissions.publications_delete',
            SeekerPermissions::EDIT_OWN_BIOGRAPHY => 'seeker::admin.permissions.biography_edit',
            SeekerPermissions::MODERATE => 'seeker::admin.permissions.moderate',
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

        ActionLog::registerLogs('seeker.conversations.reopened', [
            'icon' => 'unlock',
            'color' => 'success',
            'message' => 'seeker::admin.logs.conversation_reopened',
            'model' => Conversation::class,
        ]);

        ActionLog::registerLogs([
            'seeker.messages.hidden' => [
                'icon' => 'eye-slash',
                'color' => 'warning',
                'message' => 'seeker::admin.logs.message_hidden',
                'model' => Message::class,
            ],
            'seeker.messages.restored' => [
                'icon' => 'eye',
                'color' => 'success',
                'message' => 'seeker::admin.logs.message_restored',
                'model' => Message::class,
            ],
        ]);

        ActionLog::registerLogs([
            'seeker.publications.pinned' => [
                'icon' => 'pin-angle-fill',
                'color' => 'warning',
                'message' => 'seeker::admin.logs.publication_pinned',
                'model' => Publication::class,
            ],
            'seeker.publications.unpinned' => [
                'icon' => 'pin-angle',
                'color' => 'secondary',
                'message' => 'seeker::admin.logs.publication_unpinned',
                'model' => Publication::class,
            ],
        ]);

        ActionLog::registerLogs('seeker.reports.updated', [
            'icon' => 'flag',
            'color' => 'warning',
            'message' => 'seeker::admin.logs.report_updated',
        ]);

        ActionLog::registerLogs([
            'seeker.restrictions.created' => [
                'icon' => 'person-lock',
                'color' => 'danger',
                'message' => 'seeker::admin.logs.restriction_created',
                'model' => UserRestriction::class,
            ],
            'seeker.restrictions.revoked' => [
                'icon' => 'unlock',
                'color' => 'success',
                'message' => 'seeker::admin.logs.restriction_revoked',
                'model' => UserRestriction::class,
            ],
            'seeker.publications.removed_for_user' => [
                'icon' => 'trash',
                'color' => 'danger',
                'message' => 'seeker::admin.logs.publications_removed',
                'model' => User::class,
            ],
            'seeker.profiles.biography_removed' => [
                'icon' => 'person-x',
                'color' => 'warning',
                'message' => 'seeker::admin.logs.biography_removed',
                'model' => User::class,
            ],
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
                    'seeker.admin.reports.index' => trans('seeker::admin.nav.reports'),
                    'seeker.admin.restrictions.index' => trans('seeker::admin.nav.restrictions'),
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
                'permission' => SeekerPermissions::ACCESS,
            ],
            'seeker-messages' => [
                'name' => trans('seeker::messages.conversations.title'),
                'icon' => 'bi bi-chat-dots',
                'route' => 'seeker.conversations.index',
                'permission' => SeekerPermissions::ACCESS,
            ],
        ];
    }
}
