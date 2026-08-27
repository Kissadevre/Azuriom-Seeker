<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;

class ProfileReport extends Model
{
    use HasTablePrefix;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_DISMISSED = 'dismissed';

    public const REASON_IMPERSONATION = 'impersonation';

    public const REASON_SPAM = 'spam';

    public const REASON_HARASSMENT = 'harassment';

    public const REASON_INAPPROPRIATE = 'inappropriate';

    public const REASON_FRAUD = 'fraud';

    public const REASON_OTHER = 'other';

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'profile_user_id',
        'reporter_id',
        'reason',
        'details',
        'reported_bio',
        'status',
    ];

    public function profileUser()
    {
        return $this->belongsTo(User::class, 'profile_user_id');
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public static function reasons(): array
    {
        return [
            self::REASON_IMPERSONATION,
            self::REASON_SPAM,
            self::REASON_HARASSMENT,
            self::REASON_INAPPROPRIATE,
            self::REASON_FRAUD,
            self::REASON_OTHER,
        ];
    }

    public static function statuses(): array
    {
        return [self::STATUS_PENDING, self::STATUS_REVIEWED, self::STATUS_DISMISSED];
    }
}
