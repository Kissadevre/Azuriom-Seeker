<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    use HasTablePrefix;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const COMPLETION_NONE = 'none';

    public const COMPLETION_PENDING = 'pending';

    public const COMPLETION_REJECTED = 'rejected';

    public const COMPLETION_ACCEPTED = 'accepted';

    public const ESCROW_NONE = 'none';

    public const ESCROW_HELD = 'held';

    public const ESCROW_RELEASED = 'released';

    public const ESCROW_REFUNDED = 'refunded';

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'publication_id',
        'client_id',
        'author_id',
        'status',
        'completion_status',
        'delivery_attempts',
        'escrow_status',
        'held_points',
        'proposed_hours',
        'service_points',
        'tip_points',
        'final_message',
        'last_message_at',
        'completion_requested_at',
        'completion_responded_at',
        'completed_at',
    ];

    protected $casts = [
        'delivery_attempts' => 'integer',
        'held_points' => 'decimal:2',
        'proposed_hours' => 'decimal:2',
        'service_points' => 'decimal:2',
        'tip_points' => 'decimal:2',
        'last_message_at' => 'datetime',
        'completion_requested_at' => 'datetime',
        'completion_responded_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function publication()
    {
        return $this->belongsTo(Publication::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function messages()
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage()
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function reports()
    {
        return $this->hasMany(ConversationReport::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $query) use ($user) {
            $query->where('client_id', $user->id)
                ->orWhere('author_id', $user->id);
        });
    }

    public function includes(User $user): bool
    {
        return $this->client_id === $user->id || $this->author_id === $user->id;
    }

    public function otherParticipant(User $user): User
    {
        return $this->client_id === $user->id ? $this->author : $this->client;
    }

    public function isPaidCommission(): bool
    {
        return $this->publication->type === Publication::TYPE_COMMISSION
            && $this->publication->pricing_type === Publication::PRICING_POINTS;
    }

    public function isHourlyCommission(): bool
    {
        return $this->isPaidCommission()
            && $this->publication->price_basis === Publication::PRICE_BASIS_HOURLY;
    }
}
