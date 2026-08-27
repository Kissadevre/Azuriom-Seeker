<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationReport;
use Azuriom\Plugin\Seeker\Models\Review;
use Azuriom\Plugin\Seeker\Requests\PublicationStatusRequest;
use Azuriom\Plugin\Seeker\Requests\StorePublicationRequest;
use Azuriom\Plugin\Seeker\Requests\UpdatePublicationRequest;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PublicationController extends Controller
{
    public function index(Request $request, SeekerSettings $settings): View
    {
        $type = in_array($request->query('type'), Publication::types(), true) ? $request->query('type') : null;
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);

        $publications = Publication::query()
            ->visible()
            ->withAuthorReputation()
            ->when($request->user() === null, fn ($query) => $query->where('is_guest_visible', true))
            ->with(['user', 'images'])
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        $publicationsEnabled = $settings->publicationsEnabled();

        return view('seeker::publications.index', compact('publications', 'type', 'search', 'publicationsEnabled'));
    }

    public function show(Publication $publication, SeekerSettings $settings): View
    {
        $this->ensureVisibleToCurrentUser($publication);
        $publication->load(['user', 'images']);
        $reputation = Review::query()
            ->where('reviewed_user_id', $publication->user_id)
            ->where('is_visible', true)
            ->selectRaw('AVG(rating) as rating, COUNT(*) as reviews_count')
            ->first();
        $authorReviews = Review::query()
            ->where('reviewed_user_id', $publication->user_id)
            ->where('is_visible', true)
            ->with('reviewer')
            ->latest()
            ->paginate(6, ['*'], 'reviews_page');
        $contactConversation = auth()->check() && auth()->id() !== $publication->user_id
            ? Conversation::query()
                ->where('publication_id', $publication->id)
                ->where('client_id', auth()->id())
                ->first()
            : null;
        $publicationReport = auth()->check() && auth()->id() !== $publication->user_id
            ? PublicationReport::query()
                ->where('publication_id', $publication->id)
                ->where('reporter_id', auth()->id())
                ->first()
            : null;
        $newConversationsEnabled = $settings->newConversationsEnabled();

        return view('seeker::publications.show', compact(
            'publication',
            'contactConversation',
            'reputation',
            'authorReviews',
            'newConversationsEnabled',
            'publicationReport'
        ));
    }

    public function mine(Request $request, SeekerSettings $settings): View
    {
        $publications = Publication::query()
            ->where('user_id', $request->user()->id)
            ->withAuthorReputation()
            ->with('images')
            ->latest()
            ->paginate(12);

        $publicationsEnabled = $settings->publicationsEnabled();

        return view('seeker::publications.mine', compact('publications', 'publicationsEnabled'));
    }

    public function create(SeekerSettings $settings): View|RedirectResponse
    {
        if (! $settings->publicationsEnabled()) {
            return to_route('seeker.publications.mine')
                ->with('error', trans('seeker::messages.features.publications_disabled'));
        }

        return view('seeker::publications.create');
    }

    public function store(StorePublicationRequest $request, SeekerSettings $settings): RedirectResponse
    {
        if (! $settings->publicationsEnabled()) {
            return to_route('seeker.publications.mine')
                ->with('error', trans('seeker::messages.features.publications_disabled'));
        }

        $storedPaths = [];

        try {
            $publication = DB::transaction(function () use ($request, &$storedPaths) {
                $publication = new Publication($request->safe()->only([
                    'type', 'title', 'description', 'portfolio_type', 'portfolio_url',
                    'is_guest_visible', 'pricing_type', 'price', 'price_basis',
                ]));
                if ($publication->portfolio_type === Publication::PORTFOLIO_IMAGES) {
                    $publication->portfolio_url = null;
                }
                if ($publication->pricing_type !== Publication::PRICING_POINTS) {
                    $publication->price = null;
                    $publication->price_basis = null;
                }
                $publication->user_id = $request->user()->id;
                $publication->status = Publication::STATUS_ACTIVE;
                $publication->published_at = now();
                $publication->save();

                $this->storeImages($publication, $request->file('images', []), $storedPaths);

                return $publication;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        return redirect()->route('seeker.publications.show', $publication)
            ->with('success', trans('seeker::messages.alerts.created'));
    }

    public function edit(Publication $publication): View
    {
        $this->ensureOwner($publication);
        $publication->load('images');

        return view('seeker::publications.edit', compact('publication'));
    }

    public function update(UpdatePublicationRequest $request, Publication $publication): RedirectResponse
    {
        $storedPaths = [];
        $removedPaths = [];

        try {
            DB::transaction(function () use ($request, $publication, &$storedPaths, &$removedPaths) {
                $publication->fill($request->safe()->only([
                    'type', 'title', 'description', 'portfolio_type', 'portfolio_url',
                    'is_guest_visible', 'pricing_type', 'price', 'price_basis',
                ]));

                if ($publication->conversations()->exists()
                    && $publication->isDirty(['type', 'pricing_type', 'price', 'price_basis'])) {
                    throw ValidationException::withMessages([
                        'pricing_type' => trans('seeker::messages.validation.pricing_locked'),
                    ]);
                }

                if ($publication->portfolio_type === Publication::PORTFOLIO_IMAGES) {
                    $publication->portfolio_url = null;
                }
                if ($publication->pricing_type !== Publication::PRICING_POINTS) {
                    $publication->price = null;
                    $publication->price_basis = null;
                }
                $publication->save();

                $imagesToRemove = $publication->portfolio_type === Publication::PORTFOLIO_EXTERNAL
                    ? $publication->images()
                    : $publication->images()->whereIn('id', array_unique($request->input('remove_images', [])));
                $removedPaths = $imagesToRemove->pluck('path')->all();
                $imagesToRemove->delete();

                $this->storeImages($publication, $request->file('images', []), $storedPaths);
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        Storage::disk('local')->delete($removedPaths);

        return redirect()->route('seeker.publications.show', $publication)
            ->with('success', trans('seeker::messages.alerts.updated'));
    }

    public function updateStatus(PublicationStatusRequest $request, Publication $publication): RedirectResponse
    {
        $publication->status = $request->validated('status');
        $publication->published_at ??= now();
        $publication->save();

        return back()->with('success', trans('seeker::messages.alerts.status_updated'));
    }

    public function destroy(Request $request, Publication $publication): RedirectResponse
    {
        $this->ensureOwner($publication);

        if ($publication->conversations()->exists() || $publication->reports()->exists()) {
            return back()->with('error', trans('seeker::messages.alerts.cannot_delete_with_conversations'));
        }

        $publication->delete();

        return redirect()->route('seeker.publications.mine')
            ->with('success', trans('seeker::messages.alerts.deleted'));
    }

    private function ensureOwner(Publication $publication): void
    {
        abort_unless(auth()->id() === $publication->user_id, 403);
    }

    private function ensureVisibleToCurrentUser(Publication $publication): void
    {
        $isVisible = $publication->status === Publication::STATUS_ACTIVE
            && $publication->published_at !== null
            && $publication->published_at->isPast();
        $canPreview = auth()->check()
            && (auth()->id() === $publication->user_id || auth()->user()->can('seeker.moderate'));

        abort_unless($isVisible || $canPreview, 404);

        if ($isVisible && ! $publication->is_guest_visible && auth()->guest()) {
            throw new AuthenticationException;
        }
    }

    private function storeImages(Publication $publication, array $files, array &$storedPaths): void
    {
        $position = (int) $publication->images()->max('position');

        foreach ($files as $file) {
            $path = $file->store('seeker/publications/'.$publication->id, 'local');
            $storedPaths[] = $path;

            $publication->images()->create([
                'path' => $path,
                'original_name' => mb_substr($file->getClientOriginalName(), 0, 255),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'position' => ++$position,
            ]);
        }
    }
}
