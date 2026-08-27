<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\ProfileReport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileReportController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), ProfileReport::statuses(), true)
            ? $request->query('status')
            : null;
        $reports = ProfileReport::query()
            ->with(['profileUser', 'reporter'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('seeker::admin.profile-reports.index', compact('reports', 'status'));
    }

    public function update(Request $request, ProfileReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(ProfileReport::statuses())],
        ]);
        $report->update($validated);

        return back()->with('success', trans('seeker::admin.profile_reports.status_updated'));
    }
}
