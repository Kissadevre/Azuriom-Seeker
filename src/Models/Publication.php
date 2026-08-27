<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Publication extends Model
{
    use HasTablePrefix;
    use SoftDeletes;

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
        'is_pinned',
        'pinned_at',
        'published_at',
    ];

    protected $casts = [
        'is_guest_visible' => 'boolean',
        'is_pinned' => 'boolean',
        'price' => 'decimal:2',
        'pinned_at' => 'datetime',
        'published_at' => 'datetime',
        'author_rating' => 'float',
        'author_reviews_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::deleting(function (self $publication) {
            if ($publication->isForceDeleting()) {
                $publication->images()->get()->each->delete();
            }
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

    public function reports()
    {
        return $this->hasMany(PublicationReport::class);
    }

    public function scopeVisible(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeWithAuthorReputation(Builder $query): Builder
    {
        return $query->select('seeker_publications.*')->addSelect([
            'author_rating' => Review::query()
                ->selectRaw('AVG(rating)')
                ->whereColumn('reviewed_user_id', 'seeker_publications.user_id')
                ->where('is_visible', true),
            'author_reviews_count' => Review::query()
                ->selectRaw('COUNT(*)')
                ->whereColumn('reviewed_user_id', 'seeker_publications.user_id')
                ->where('is_visible', true),
        ]);
    }

    public function scopeForListing(Builder $query): Builder
    {
        return $query
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
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
