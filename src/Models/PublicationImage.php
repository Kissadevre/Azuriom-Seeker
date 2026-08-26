<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PublicationImage extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'path',
        'original_name',
        'mime_type',
        'position',
    ];

    protected static function booted(): void
    {
        static::deleted(function (self $image) {
            Storage::disk('local')->delete($image->path);
        });
    }

    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }
}
