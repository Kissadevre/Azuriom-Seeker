<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PublicationMedia extends Model
{
    use HasTablePrefix;

    public const TYPE_VIDEO = 'video';

    public const TYPE_AUDIO = 'audio';

    public const MAX_SIZE_KILOBYTES = 10240;

    public const VIDEO_EXTENSIONS = ['mp4', 'webm'];

    public const VIDEO_MIME_TYPES = ['video/mp4', 'video/webm'];

    public const AUDIO_EXTENSIONS = ['mp3', 'wav', 'ogg', 'm4a'];

    public const AUDIO_MIME_TYPES = [
        'audio/mpeg',
        'audio/wav',
        'audio/x-wav',
        'audio/ogg',
        'audio/mp4',
        'audio/x-m4a',
    ];

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'type',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $media) {
            Storage::disk('local')->delete($media->path);
        });
    }

    public function publication()
    {
        return $this->belongsTo(Publication::class)->withTrashed();
    }

    public static function extensionsFor(string $type): array
    {
        return $type === self::TYPE_VIDEO ? self::VIDEO_EXTENSIONS : self::AUDIO_EXTENSIONS;
    }

    public static function mimeTypesFor(string $type): array
    {
        return $type === self::TYPE_VIDEO ? self::VIDEO_MIME_TYPES : self::AUDIO_MIME_TYPES;
    }
}
