<?php

namespace Azuriom\Plugin\Seeker\Providers;

use Azuriom\Extensions\Plugin\BaseRouteServiceProvider;
use Azuriom\Plugin\Seeker\Middleware\EnsureSeekerAccess;
use Azuriom\Plugin\Seeker\Middleware\LogSeekerRequest;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends BaseRouteServiceProvider
{
    public function loadRoutes(): void
    {
        Route::middleware(['web', LogSeekerRequest::class, EnsureSeekerAccess::class])
            ->prefix($this->plugin->id)
            ->name($this->plugin->id.'.')
            ->group(plugin_path($this->plugin->id.'/routes/web.php'));

        Route::middleware(['admin-access', LogSeekerRequest::class, 'can:seeker.moderate'])
            ->prefix('admin/'.$this->plugin->id)
            ->name($this->plugin->id.'.admin.')
            ->group(plugin_path($this->plugin->id.'/routes/admin.php'));
    }
}
