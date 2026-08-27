<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationReport;
use Azuriom\Plugin\Seeker\Requests\StorePublicationReportRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicationReportController extends Controller
{
    public function create(Request $request, Publication $publication): View|RedirectResponse
    {
        $this->ensureReportable($publication);
        abort_if($publication->user_id === $request->user()->id, 403);

        if ($this->existingReport($publication, $request) !== null) {
            return to_route('seeker.publications.show', $publication)
                ->with('warning', trans('seeker::messages.publication_reports.already_sent'));
        }

        return view('seeker::publications.report', compact('publication'));
    }

    public function store(StorePublicationReportRequest $request, Publication $publication): RedirectResponse
    {
        $this->ensureReportable($publication);

        $created = DB::transaction(function () use ($request, $publication) {
            $lockedPublication = Publication::query()->lockForUpdate()->findOrFail($publication->id);
            $this->ensureReportable($lockedPublication);

            if (PublicationReport::query()
                ->where('publication_id', $lockedPublication->id)
                ->where('reporter_id', $request->user()->id)
                ->exists()) {
                return false;
            }

            PublicationReport::create([
                'publication_id' => $lockedPublication->id,
                'reporter_id' => $request->user()->id,
                'reason' => $request->validated('reason'),
                'details' => trim($request->validated('details')),
                'reported_title' => $lockedPublication->title,
                'reported_description' => $lockedPublication->description,
                'reported_portfolio_url' => $lockedPublication->portfolio_url,
                'status' => PublicationReport::STATUS_PENDING,
            ]);

            return true;
        }, 3);

        return to_route('seeker.publications.show', $publication)
            ->with($created ? 'success' : 'warning', trans($created
                ? 'seeker::messages.publication_reports.sent'
                : 'seeker::messages.publication_reports.already_sent'));
    }

    private function existingReport(Publication $publication, Request $request): ?PublicationReport
    {
        return PublicationReport::query()
            ->where('publication_id', $publication->id)
            ->where('reporter_id', $request->user()->id)
            ->first();
    }

    private function ensureReportable(Publication $publication): void
    {
        abort_unless(
            $publication->status === Publication::STATUS_ACTIVE
            && $publication->published_at !== null
            && $publication->published_at->isPast(),
            404
        );
    }
}
