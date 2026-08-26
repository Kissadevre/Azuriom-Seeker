<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationImage;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicationImageController extends Controller
{
    public function show(PublicationImage $image): StreamedResponse
    {
        $publication = $image->publication;
        $isVisible = $publication->status === Publication::STATUS_ACTIVE
            && $publication->published_at !== null
            && $publication->published_at->isPast();
        $canPreview = auth()->check()
            && (auth()->id() === $publication->user_id || auth()->user()->can('seeker.moderate'));
        $canViewPublished = $isVisible && ($publication->is_guest_visible || auth()->check());

        abort_unless($canViewPublished || $canPreview, 404);
        abort_unless(Storage::disk('local')->exists($image->path), 404);

        return Storage::disk('local')->response($image->path, $image->original_name, [
            'Cache-Control' => $isVisible && $publication->is_guest_visible
                ? 'public, max-age=86400'
                : 'private, no-store',
            'Content-Type' => $image->mime_type,
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
