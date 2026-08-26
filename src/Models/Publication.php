<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Publication extends Model
{
    use HasTablePrefix;

    public const TYPE_COMMISSION = 'commission';

    public const TYPE_TALENT = 'talent';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_HIDDEN = 'hidden';

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'type',
        'title',
        'description',
        'portfolio_url',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $publication) {
            $publication->images()->get()->each->delete();
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function images()
    {
        return $this->hasMany(PublicationImage::class)->orderBy('position');
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public static function types(): array
    {
        return [self::TYPE_COMMISSION, self::TYPE_TALENT];
    }

    public static function statuses(): array
    {
        return [self::STATUS_ACTIVE, self::STATUS_CLOSED, self::STATUS_HIDDEN];
    }
}
