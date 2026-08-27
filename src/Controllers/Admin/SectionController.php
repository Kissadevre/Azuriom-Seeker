<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;

class SectionController extends Controller
{
    public function transactions(): View
    {
        return view('seeker::admin.coming-soon', ['section' => 'transactions']);
    }
}
