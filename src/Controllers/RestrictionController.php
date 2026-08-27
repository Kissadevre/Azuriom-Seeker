<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RestrictionController extends Controller
{
    public function show(Request $request, string $type, RestrictionService $restrictions): View
    {
        abort_unless(in_array($type, UserRestriction::types(), true), 404);

        $restriction = $restrictions->active($request->user(), $type);
        abort_if($restriction === null, 404);

        return view('seeker::restrictions.show', compact('restriction'));
    }
}
