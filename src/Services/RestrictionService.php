<?php

namespace Azuriom\Plugin\Seeker\Services;

use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\UserRestriction;

class RestrictionService
{
    private array $cache = [];

    public function active(?User $user, string $type): ?UserRestriction
    {
        if ($user === null) {
            return null;
        }

        $key = $user->id.':'.$type;

        if (! array_key_exists($key, $this->cache)) {
            $this->cache[$key] = UserRestriction::query()
                ->active()
                ->where('user_id', $user->id)
                ->where('type', $type)
                ->latest('id')
                ->first();
        }

        return $this->cache[$key];
    }

    public function restricted(?User $user, string $type): bool
    {
        return $this->active($user, $type) !== null;
    }

    public function clear(User $user): void
    {
        foreach (UserRestriction::types() as $type) {
            unset($this->cache[$user->id.':'.$type]);
        }
    }
}
