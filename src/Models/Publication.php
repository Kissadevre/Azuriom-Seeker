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

    public const PORTFOLIO_EXTERNAL = 'external';

    public const PORTFOLIO_IMAGES = 'images';

    public const PRICING_POINTS = 'points';

    public const PRICING_FREE = 'free';

    public const PRICING_NEGOTIABLE = 'negotiable';

    public const PRICE_BASIS_FIXED = 'fixed';

    public const PRICE_BASIS_HOURLY = 'hourly';

    public const STATUS_ACTIVE = 'active';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_HIDDEN = 'hidden';

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'type',
        'title',
        'description',
        'portfolio_type',
        'portfolio_url',
        'is_guest_visible',
        'pricing_type',
        'price',
        'price_basis',
        'status',
        'published_at',
    ];

    protected $casts = [
        'is_guest_visible' => 'boolean',
        'price' => 'decimal:2',
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

    public function conversations()
    {
        return $this->hasMany(Conversation::class);
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

    public static function portfolioTypes(): array
    {
        return [self::PORTFOLIO_EXTERNAL, self::PORTFOLIO_IMAGES];
    }

    public static function pricingTypes(): array
    {
        return [self::PRICING_POINTS, self::PRICING_FREE, self::PRICING_NEGOTIABLE];
    }

    public static function priceBases(): array
    {
        return [self::PRICE_BASIS_FIXED, self::PRICE_BASIS_HOURLY];
    }

    public function requiresPointHold(): bool
    {
        return $this->type === self::TYPE_COMMISSION
            && $this->pricing_type === self::PRICING_POINTS
            && $this->price_basis === self::PRICE_BASIS_FIXED;
    }
}
