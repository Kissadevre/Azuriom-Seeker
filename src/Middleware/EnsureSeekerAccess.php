<?php

namespace Azuriom\Plugin\Seeker\Middleware;

use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class EnsureSeekerAccess
{
    public function __construct(
        private readonly RestrictionService $restrictions,
        private readonly SeekerSettings $settings
    ) {}

    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        abort_unless($this->settings->enabled(), 503, trans('seeker::messages.features.seeker_disabled'));

        if (! $request->routeIs('seeker.restrictions.show')
            && $this->restrictions->restricted($request->user(), UserRestriction::TYPE_ACCESS)) {
            return to_route('seeker.restrictions.show', UserRestriction::TYPE_ACCESS);
        }

        return $next($request);
    }
}
