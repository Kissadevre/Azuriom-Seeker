<?php

namespace Azuriom\Plugin\Seeker\Middleware;

use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSeekerAccess
{
    public function __construct(
        private readonly RestrictionService $restrictions,
        private readonly SeekerSettings $settings
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($this->settings->enabled(), 503, trans('seeker::messages.features.seeker_disabled'));

        abort_if(
            $this->restrictions->restricted($request->user(), UserRestriction::TYPE_ACCESS),
            403,
            trans('seeker::messages.restrictions.access')
        );

        return $next($request);
    }
}
