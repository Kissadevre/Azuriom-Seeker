<?php

namespace Azuriom\Plugin\Seeker\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Seeker\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessageImageController extends Controller
{
    public function show(Request $request, Message $message): StreamedResponse
    {
        $message->loadMissing('conversation');

        abort_unless($message->conversation->includes($request->user()), 404);
        abort_if($message->image_path === null, 404);
        abort_unless(Storage::disk('local')->exists($message->image_path), 404);

        return Storage::disk('local')->response(
            $message->image_path,
            $message->image_original_name,
            [
                'Cache-Control' => 'private, no-store',
                'Content-Type' => $message->image_mime_type,
                'X-Content-Type-Options' => 'nosniff',
            ]
        );
    }
}
