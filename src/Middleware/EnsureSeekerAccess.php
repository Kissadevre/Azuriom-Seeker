<?php

namespace Azuriom\Plugin\Seeker\Middleware;

use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSeekerAccess
{
    public function handle(Request $request, Closure $next, RestrictionService $restrictions): Response
    {
        abort_if(
            $restrictions->restricted($request->user(), UserRestriction::TYPE_ACCESS),
            403,
            trans('seeker::messages.restrictions.access')
        );

        return $next($request);
    }
}
