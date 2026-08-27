<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationMedia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class PublicationMediaController extends Controller
{
    public function show(PublicationMedia $media): BinaryFileResponse
    {
        $publication = $media->publication;
        $isVisible = ! $publication->trashed()
            && $publication->status === Publication::STATUS_ACTIVE
            && $publication->published_at !== null
            && $publication->published_at->isPast();
        $canPreview = auth()->check()
            && (auth()->user()->can('seeker.moderate')
                || (! $publication->trashed() && auth()->id() === $publication->user_id));
        $canViewPublished = $isVisible && ($publication->is_guest_visible || auth()->check());

        abort_unless($canViewPublished || $canPreview, 404);
        abort_unless($publication->portfolio_type === $media->type, 404);
        abort_unless(in_array($media->type, Publication::uploadedPortfolioTypes(), true), 404);
        abort_unless(in_array($media->mime_type, PublicationMedia::mimeTypesFor($media->type), true), 404);
        abort_unless(Storage::disk('local')->exists($media->path), 404);

        $response = response()->file(Storage::disk('local')->path($media->path), [
            'Cache-Control' => $isVisible && $publication->is_guest_visible
                ? 'public, max-age=86400'
                : 'private, no-store',
            'Content-Type' => $media->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);

        $fallbackName = trim(preg_replace('/[^A-Za-z0-9._ -]/', '_', Str::ascii($media->original_name)) ?? '');

        return $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $media->original_name,
            $fallbackName === '' ? 'media' : $fallbackName
        );
    }
}
