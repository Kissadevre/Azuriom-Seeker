<?php

namespace Azuriom\Plugin\Seeker\Services;

use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\UserRestriction;

class RestrictionService
{
    private array $cache = [];

    public function restricted(?User $user, string $type): bool
    {
        if ($user === null) {
            return false;
        }

        $key = $user->id.':'.$type;

        return $this->cache[$key] ??= UserRestriction::query()
            ->active()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->exists();
    }

    public function clear(User $user): void
    {
        foreach (UserRestriction::types() as $type) {
            unset($this->cache[$user->id.':'.$type]);
        }
    }
}
