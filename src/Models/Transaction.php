<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasTablePrefix;

    public const TYPE_SERVICE = 'service';

    public const TYPE_TIP = 'tip';

    public const STATUS_HELD = 'held';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_REFUNDED = 'refunded';

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'conversation_id',
        'payer_id',
        'payee_id',
        'payer_name',
        'payee_name',
        'publication_title',
        'type',
        'status',
        'amount',
        'held_at',
        'completed_at',
        'refunded_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'held_at' => 'datetime',
        'completed_at' => 'datetime',
        'refunded_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function payer()
    {
        return $this->belongsTo(User::class, 'payer_id');
    }

    public function payee()
    {
        return $this->belongsTo(User::class, 'payee_id');
    }

    public static function types(): array
    {
        return [self::TYPE_SERVICE, self::TYPE_TIP];
    }

    public static function statuses(): array
    {
        return [self::STATUS_HELD, self::STATUS_COMPLETED, self::STATUS_REFUNDED];
    }
}
