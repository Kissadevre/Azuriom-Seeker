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
        'escrow_status',
        'held_points',
        'last_message_at',
    ];

    protected $casts = [
        'held_points' => 'decimal:2',
        'last_message_at' => 'datetime',
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
}
