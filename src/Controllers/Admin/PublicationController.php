<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\ConversationReport;
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
        $type = in_array($request->query('type'), Publication::types(), true)
            ? $request->query('type')
            : null;
        $reported = $request->query('reported') === '1';

        $publications = Publication::query()
            ->withTrashed()
            ->with('user')
            ->withCount(['conversations', 'reports'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($reported, fn ($query) => $query->whereHas('reports'))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('seeker::admin.publications.index', compact('publications', 'status', 'type', 'reported'));
    }

    public function show(Publication $publication): View
    {
        $publication->load(['user', 'images'])->loadCount('conversations');
        $reports = $publication->reports()
            ->with('reporter')
            ->latest('created_at')
            ->latest('id')
            ->get();
        $conversations = $publication->conversations()
            ->with('client')
            ->withCount([
                'messages',
                'reports',
                'reports as pending_reports_count' => fn ($query) => $query->where('status', ConversationReport::STATUS_PENDING),
                'reports as reviewed_reports_count' => fn ($query) => $query->where('status', ConversationReport::STATUS_REVIEWED),
                'reports as dismissed_reports_count' => fn ($query) => $query->where('status', ConversationReport::STATUS_DISMISSED),
            ])
            ->latest()
            ->paginate(20);

        return view('seeker::admin.publications.show', compact('publication', 'reports', 'conversations'));
    }

    public function updateStatus(PublicationStatusRequest $request, Publication $publication): RedirectResponse
    {
        abort_if($publication->trashed(), 409);
        $publication->status = $request->validated('status');
        $publication->published_at ??= now();
        $publication->save();

        return back()->with('success', trans('seeker::admin.alerts.status_updated'));
    }
}
