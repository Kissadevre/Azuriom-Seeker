<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;

class ConversationReport extends Model
{
    use HasTablePrefix;

    public const STATUS_PENDING = 'pending';

    public const STATUS_REVIEWED = 'reviewed';

    public const STATUS_DISMISSED = 'dismissed';

    public const REASON_HARASSMENT = 'harassment';

    public const REASON_SPAM = 'spam';

    public const REASON_FRAUD = 'fraud';

    public const REASON_PAYMENT = 'payment';

    public const REASON_INAPPROPRIATE = 'inappropriate';

    public const REASON_OTHER = 'other';

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'conversation_id',
        'reporter_id',
        'reported_user_id',
        'reported_through_message_id',
        'reason',
        'details',
        'status',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    public static function reasons(): array
    {
        return [
            self::REASON_HARASSMENT,
            self::REASON_SPAM,
            self::REASON_FRAUD,
            self::REASON_PAYMENT,
            self::REASON_INAPPROPRIATE,
            self::REASON_OTHER,
        ];
    }
}
