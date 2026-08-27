<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserRestriction extends Model
{
    use HasTablePrefix;

    public const TYPE_PUBLISH = 'publish';

    public const TYPE_CONTACT = 'contact';

    public const TYPE_PROFILE = 'profile';

    public const TYPE_ACCESS = 'access';

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'user_id',
        'created_by_id',
        'revoked_by_id',
        'type',
        'reason',
        'expires_at',
        'revoked_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function revokedBy()
    {
        return $this->belongsTo(User::class, 'revoked_by_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('revoked_at')
            ->where(function (Builder $query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    public function isActive(): bool
    {
        return $this->revoked_at === null
            && ($this->expires_at === null || $this->expires_at->isFuture());
    }

    public function isExpired(): bool
    {
        return $this->revoked_at === null
            && $this->expires_at !== null
            && $this->expires_at->isPast();
    }

    public static function types(): array
    {
        return [self::TYPE_PUBLISH, self::TYPE_CONTACT, self::TYPE_PROFILE, self::TYPE_ACCESS];
    }
}
