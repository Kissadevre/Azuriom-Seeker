<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'seeker_';

    protected $fillable = [
        'sender_id',
        'content',
        'image_path',
        'image_original_name',
        'image_mime_type',
        'read_at',
        'hidden_at',
        'hidden_by_id',
    ];

    protected $casts = [
        'read_at' => 'datetime',
        'hidden_at' => 'datetime',
    ];

    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function hiddenBy()
    {
        return $this->belongsTo(User::class, 'hidden_by_id');
    }

    public function isHidden(): bool
    {
        return $this->hidden_at !== null;
    }
}
