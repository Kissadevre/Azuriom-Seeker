<?php

namespace Azuriom\Plugin\Seeker\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\ActionLog;
use Azuriom\Plugin\Seeker\Models\ConversationReport;
use Azuriom\Plugin\Seeker\Models\ProfileReport;
use Azuriom\Plugin\Seeker\Models\PublicationReport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public const TYPES = ['publication', 'profile', 'conversation'];

    public function index(Request $request): View
    {
        $type = in_array($request->query('type'), self::TYPES, true) ? $request->query('type') : null;
        $status = in_array($request->query('status'), ProfileReport::statuses(), true)
            ? $request->query('status')
            : null;

        $union = $this->reportQuery(PublicationReport::class, 'publication', 'publication_id')
            ->unionAll($this->reportQuery(ProfileReport::class, 'profile', 'profile_user_id'))
            ->unionAll($this->reportQuery(ConversationReport::class, 'conversation', 'conversation_id'));

        $reports = DB::query()
            ->fromSub($union, 'seeker_reports')
            ->when($type, fn ($query) => $query->where('report_type', $type))
            ->when($status, fn ($query) => $query->where('status', $status))
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->orderBy('report_type')
            ->paginate(25)
            ->withQueryString();

        $this->hydrateReports($reports->items());

        $counts = [
            'publication' => PublicationReport::query()->count(),
            'profile' => ProfileReport::query()->count(),
            'conversation' => ConversationReport::query()->count(),
            'pending' => PublicationReport::query()->where('status', PublicationReport::STATUS_PENDING)->count()
                + ProfileReport::query()->where('status', ProfileReport::STATUS_PENDING)->count()
                + ConversationReport::query()->where('status', ConversationReport::STATUS_PENDING)->count(),
        ];
        $counts['total'] = $counts['publication'] + $counts['profile'] + $counts['conversation'];

        return view('seeker::admin.reports.index', compact('reports', 'type', 'status', 'counts'));
    }

    public function update(Request $request, string $type, int $report): RedirectResponse
    {
        abort_unless(in_array($type, self::TYPES, true), 404);
        $validated = $request->validate([
            'status' => ['required', Rule::in(ProfileReport::statuses())],
        ]);
        $model = $this->findReport($type, $report);
        $model->update($validated);

        ActionLog::log('seeker.reports.updated', data: [
            'type' => $type,
            'id' => $model->getKey(),
        ]);

        return back()->with('success', trans('seeker::admin.reports.status_updated'));
    }

    private function reportQuery(string $model, string $type, string $targetColumn)
    {
        return $model::query()
            ->select([
                'id',
                DB::raw("'{$type}' as report_type"),
                DB::raw("{$targetColumn} as target_id"),
                'reporter_id',
                'reason',
                'details',
                'status',
                'created_at',
                'updated_at',
            ]);
    }

    private function hydrateReports(array $items): void
    {
        $items = collect($items);
        $models = [
            'publication' => PublicationReport::query()
                ->with(['publication.user', 'reporter'])
                ->whereIn('id', $items->where('report_type', 'publication')->pluck('id'))
                ->get()->keyBy('id'),
            'profile' => ProfileReport::query()
                ->with(['profileUser', 'reporter'])
                ->whereIn('id', $items->where('report_type', 'profile')->pluck('id'))
                ->get()->keyBy('id'),
            'conversation' => ConversationReport::query()
                ->with(['conversation.publication', 'reporter', 'reportedUser'])
                ->whereIn('id', $items->where('report_type', 'conversation')->pluck('id'))
                ->get()->keyBy('id'),
        ];

        foreach ($items as $item) {
            $item->report = $models[$item->report_type]->get($item->id);
        }
    }

    private function findReport(string $type, int $id): Model
    {
        $model = match ($type) {
            'publication' => PublicationReport::class,
            'profile' => ProfileReport::class,
            'conversation' => ConversationReport::class,
        };

        return $model::query()->findOrFail($id);
    }
}
