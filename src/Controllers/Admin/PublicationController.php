<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Requests\PublicationStatusRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicationController extends Controller
{
    public function index(Request $request): View
    {
        $status = in_array($request->query('status'), Publication::statuses(), true)
            ? $request->query('status')
            : null;

        $publications = Publication::query()
            ->with('user')
            ->withCount('conversations')
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('seeker::admin.publications.index', compact('publications', 'status'));
    }

    public function show(Publication $publication): View
    {
        $publication->load(['user', 'images'])->loadCount('conversations');
        $conversations = $publication->conversations()
            ->with('client')
            ->withCount(['messages', 'reports'])
            ->latest()
            ->paginate(20);

        return view('seeker::admin.publications.show', compact('publication', 'conversations'));
    }

    public function updateStatus(PublicationStatusRequest $request, Publication $publication): RedirectResponse
    {
        $publication->status = $request->validated('status');
        $publication->published_at ??= now();
        $publication->save();

        return back()->with('success', trans('seeker::admin.alerts.status_updated'));
    }
}
