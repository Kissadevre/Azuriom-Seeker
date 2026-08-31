<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationMedia;
use Azuriom\Plugin\Seeker\Models\PublicationReport;
use Azuriom\Plugin\Seeker\Models\Review;
use Azuriom\Plugin\Seeker\Models\UserRestriction;
use Azuriom\Plugin\Seeker\Requests\PublicationStatusRequest;
use Azuriom\Plugin\Seeker\Requests\StorePublicationRequest;
use Azuriom\Plugin\Seeker\Requests\UpdatePublicationRequest;
use Azuriom\Plugin\Seeker\Services\DiscordWebhookNotifier;
use Azuriom\Plugin\Seeker\Services\RestrictionService;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Azuriom\Plugin\Seeker\Support\SeekerPermissions;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class PublicationController extends Controller
{
    public function index(Request $request, SeekerSettings $settings, RestrictionService $restrictions): View
    {
        $type = in_array($request->query('type'), Publication::types(), true) ? $request->query('type') : null;
        $search = mb_substr(trim((string) $request->query('search')), 0, 100);

        $publications = Publication::query()
            ->visible()
            ->withAuthorReputation()
            ->when($request->user() === null, fn ($query) => $query->where('is_guest_visible', true))
            ->with(['user', 'images', 'media'])
            ->when($type, fn ($query) => $query->where('type', $type))
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            }))
            ->forListing()
            ->paginate(12)
            ->withQueryString();

        $publishRestriction = $restrictions->active($request->user(), UserRestriction::TYPE_PUBLISH);
        $publicationsEnabled = $request->user()?->can(SeekerPermissions::CREATE_PUBLICATIONS) === true
            && $settings->publicationsEnabled()
            && $publishRestriction === null;

        return view('seeker::publications.index', compact('publications', 'type', 'search', 'publicationsEnabled', 'publishRestriction'));
    }

    public function show(Publication $publication, SeekerSettings $settings, RestrictionService $restrictions): View
    {
        $this->ensureVisibleToCurrentUser($publication);
        $publication->load(['user', 'images', 'media']);
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
        $contactRestriction = $restrictions->active(auth()->user(), UserRestriction::TYPE_CONTACT);

        return view('seeker::publications.show', compact(
            'publication',
            'contactConversation',
            'reputation',
            'authorReviews',
            'newConversationsEnabled',
            'contactRestriction',
            'publicationReport'
        ));
    }

    public function mine(Request $request, SeekerSettings $settings, RestrictionService $restrictions): View
    {
        $publications = Publication::query()
            ->where('user_id', $request->user()->id)
            ->withAuthorReputation()
            ->with(['images', 'media'])
            ->forListing()
            ->paginate(12);

        $publishRestriction = $restrictions->active($request->user(), UserRestriction::TYPE_PUBLISH);
        $publicationsEnabled = $request->user()->can(SeekerPermissions::CREATE_PUBLICATIONS)
            && $settings->publicationsEnabled()
            && $publishRestriction === null;

        return view('seeker::publications.mine', compact('publications', 'publicationsEnabled', 'publishRestriction'));
    }

    public function create(SeekerSettings $settings, RestrictionService $restrictions): View|RedirectResponse
    {
        if (! $settings->publicationsEnabled()) {
            return to_route('seeker.publications.mine')
                ->with('error', trans('seeker::messages.features.publications_disabled'));
        }

        if ($restrictions->restricted(auth()->user(), UserRestriction::TYPE_PUBLISH)) {
            return to_route('seeker.restrictions.show', UserRestriction::TYPE_PUBLISH);
        }

        return view('seeker::publications.create', [
            'availablePortfolioTypes' => $settings->enabledPortfolioTypes(),
            'assetLimits' => $settings->assetLimits(),
        ]);
    }

    public function store(
        StorePublicationRequest $request,
        SeekerSettings $settings,
        RestrictionService $restrictions,
        DiscordWebhookNotifier $discordWebhook
    ): RedirectResponse {
        if (! $settings->publicationsEnabled()) {
            return to_route('seeker.publications.mine')
                ->with('error', trans('seeker::messages.features.publications_disabled'));
        }

        if ($restrictions->restricted($request->user(), UserRestriction::TYPE_PUBLISH)) {
            return to_route('seeker.restrictions.show', UserRestriction::TYPE_PUBLISH);
        }

        $storedPaths = [];

        try {
            $publication = DB::transaction(function () use ($request, &$storedPaths) {
                $publication = new Publication($request->safe()->only([
                    'type', 'title', 'description', 'portfolio_type', 'portfolio_url',
                    'is_guest_visible', 'pricing_type', 'price', 'price_basis',
                ]));
                if ($publication->portfolio_type !== Publication::PORTFOLIO_EXTERNAL) {
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

                if ($publication->portfolio_type === Publication::PORTFOLIO_IMAGES) {
                    $this->storeImages($publication, $request->file('images', []), $storedPaths);
                } elseif (in_array($publication->portfolio_type, Publication::uploadedPortfolioTypes(), true)) {
                    $this->storeMedia($publication, $request->file($publication->portfolio_type, []), $storedPaths);
                }

                return $publication;
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($storedPaths);
            throw $exception;
        }

        // The publication is already committed, so no notification failure can roll it back.
        try {
            $discordWebhook->publicationCreated($publication);
        } catch (Throwable $exception) {
            try {
                Log::warning('An unexpected error occurred while notifying Discord about a Seeker publication.', [
                    'publication_id' => $publication->id,
                    'exception' => $exception::class,
                ]);
            } catch (Throwable) {
                // Notification diagnostics must not affect the completed publication flow.
            }
        }

        return redirect()->route('seeker.publications.show', $publication)
            ->with('success', trans('seeker::messages.alerts.created'));
    }

    public function edit(Publication $publication, SeekerSettings $settings): View
    {
        $this->ensureOwner($publication);
        $publication->load(['images', 'media']);

        $availablePortfolioTypes = $settings->enabledPortfolioTypes();
        $portfolioTypeDisabled = ! in_array($publication->portfolio_type, $availablePortfolioTypes, true);

        if ($portfolioTypeDisabled) {
            $availablePortfolioTypes[] = $publication->portfolio_type;
        }

        $assetLimits = $settings->assetLimits();

        return view('seeker::publications.edit', compact(
            'publication',
            'availablePortfolioTypes',
            'portfolioTypeDisabled',
            'assetLimits'
        ));
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

                if ($publication->portfolio_type !== Publication::PORTFOLIO_EXTERNAL) {
                    $publication->portfolio_url = null;
                }
                if ($publication->pricing_type !== Publication::PRICING_POINTS) {
                    $publication->price = null;
                    $publication->price_basis = null;
                }
                $publication->save();

                $imagesToRemove = $publication->portfolio_type !== Publication::PORTFOLIO_IMAGES
                    ? $publication->images()
                    : $publication->images()->whereIn('id', array_unique($request->input('remove_images', [])));
                $removedPaths = array_merge($removedPaths, $imagesToRemove->pluck('path')->all());
                $imagesToRemove->delete();

                $selectedMediaType = in_array($publication->portfolio_type, Publication::uploadedPortfolioTypes(), true)
                    ? $publication->portfolio_type
                    : null;
                $mediaToRemove = $publication->media();

                if ($selectedMediaType !== null) {
                    $removeMediaIds = array_unique($request->input('remove_media', []));
                    $mediaToRemove->where(function ($query) use ($selectedMediaType, $removeMediaIds) {
                        $query->where('type', '!=', $selectedMediaType);

                        if ($removeMediaIds !== []) {
                            $query->orWhereIn('id', $removeMediaIds);
                        }
                    });
                }

                $removedPaths = array_merge($removedPaths, $mediaToRemove->pluck('path')->all());
                $mediaToRemove->delete();

                if ($publication->portfolio_type === Publication::PORTFOLIO_IMAGES) {
                    $this->storeImages($publication, $request->file('images', []), $storedPaths);
                } elseif ($selectedMediaType !== null && $request->hasFile($selectedMediaType)) {
                    $this->storeMedia($publication, $request->file($selectedMediaType, []), $storedPaths);
                }
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
        abort_unless($request->user()->can(SeekerPermissions::DELETE_OWN_PUBLICATIONS), 403);
        $this->ensureOwner($publication);

        if ($publication->conversations()->exists() || $publication->reports()->exists()) {
            return back()->with('error', trans('seeker::messages.alerts.cannot_delete_with_conversations'));
        }

        $publication->forceDelete();

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
                'original_name' => $this->sanitizeOriginalName($file),
                'mime_type' => $file->getMimeType() ?? 'application/octet-stream',
                'position' => ++$position,
            ]);
        }
    }

    private function storeMedia(Publication $publication, array $files, array &$storedPaths): void
    {
        $type = $publication->portfolio_type;

        if (! in_array($type, Publication::uploadedPortfolioTypes(), true)) {
            throw ValidationException::withMessages([
                $type => trans('seeker::messages.validation.invalid_media'),
            ]);
        }

        foreach ($files as $file) {
            $mimeType = $file->getMimeType();

            if ($mimeType === null || ! in_array($mimeType, PublicationMedia::mimeTypesFor($type), true)) {
                throw ValidationException::withMessages([
                    $type => trans('seeker::messages.validation.invalid_media'),
                ]);
            }

            $path = $file->store('seeker/publications/'.$publication->id.'/'.$type, 'local');
            $storedPaths[] = $path;

            $publication->media()->create([
                'type' => $type,
                'path' => $path,
                'original_name' => $this->sanitizeOriginalName($file),
                'mime_type' => $mimeType,
                'size' => (int) $file->getSize(),
            ]);
        }
    }

    private function sanitizeOriginalName(UploadedFile $file): string
    {
        $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
        $name = trim(preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? '');

        return $name === '' ? 'upload' : mb_substr($name, 0, 255);
    }
}
