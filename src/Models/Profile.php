<?php

namespace Azuriom\Plugin\Seeker\Models;

use Azuriom\Models\Traits\HasTablePrefix;
use Azuriom\Models\User;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasTablePrefix;

    protected string $prefix = 'seeker_';

    protected $fillable = ['bio'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
