<?php

namespace Azuriom\Plugin\Seeker\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;

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
        ];
    }
}
