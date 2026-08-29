<?php

namespace Azuriom\Plugin\Seeker\Services;

use Azuriom\Plugin\Seeker\Models\Publication;

class SeekerSettings
{
    public const ENABLED_KEY = 'seeker.enabled';

    public const PUBLICATIONS_ENABLED_KEY = 'seeker.publications_enabled';

    public const NEW_CONVERSATIONS_ENABLED_KEY = 'seeker.new_conversations_enabled';

    public const BIOGRAPHIES_ENABLED_KEY = 'seeker.biographies_enabled';

    public const MESSAGE_IMAGES_ENABLED_KEY = 'seeker.message_images_enabled';

    public const USER_MENU_ENABLED_KEY = 'seeker.user_menu_enabled';

    public const USER_MENU_ITEMS = [
        'seeker' => [
            'enabled_key' => self::USER_MENU_ENABLED_KEY,
            'enabled' => false,
            'icon_key' => 'seeker.user_menu.seeker_icon',
            'icon' => 'bi-people',
        ],
        'my_publications' => [
            'enabled_key' => 'seeker.user_menu.my_publications_enabled',
            'enabled' => true,
            'icon_key' => 'seeker.user_menu.my_publications_icon',
            'icon' => 'bi-briefcase',
        ],
        'messages' => [
            'enabled_key' => 'seeker.user_menu.messages_enabled',
            'enabled' => true,
            'icon_key' => 'seeker.user_menu.messages_icon',
            'icon' => 'bi-chat-dots',
        ],
    ];

    public const PORTFOLIO_TYPE_KEYS = [
        Publication::PORTFOLIO_EXTERNAL => 'seeker.portfolio_types.external_enabled',
        Publication::PORTFOLIO_IMAGES => 'seeker.portfolio_types.images_enabled',
        Publication::PORTFOLIO_VIDEO => 'seeker.portfolio_types.video_enabled',
        Publication::PORTFOLIO_AUDIO => 'seeker.portfolio_types.audio_enabled',
    ];

    public const RATE_LIMITS = [
        'create_user_short' => [
            'attempts_key' => 'seeker.rate_limits.create_user_short.attempts',
            'window_key' => 'seeker.rate_limits.create_user_short.window',
            'attempts' => 3,
            'window' => 60,
            'scope' => 'create:user:short',
            'by_user' => true,
        ],
        'create_ip_short' => [
            'attempts_key' => 'seeker.rate_limits.create_ip_short.attempts',
            'window_key' => 'seeker.rate_limits.create_ip_short.window',
            'attempts' => 15,
            'window' => 60,
            'scope' => 'create:ip:short',
            'by_user' => false,
        ],
        'create_user_long' => [
            'attempts_key' => 'seeker.rate_limits.create_user_long.attempts',
            'window_key' => 'seeker.rate_limits.create_user_long.window',
            'attempts' => 10,
            'window' => 1440,
            'scope' => 'create:user:long',
            'by_user' => true,
        ],
        'edit_user_short' => [
            'attempts_key' => 'seeker.rate_limits.edit_user_short.attempts',
            'window_key' => 'seeker.rate_limits.edit_user_short.window',
            'attempts' => 12,
            'window' => 60,
            'scope' => 'edit:user:short',
            'by_user' => true,
        ],
        'edit_ip_short' => [
            'attempts_key' => 'seeker.rate_limits.edit_ip_short.attempts',
            'window_key' => 'seeker.rate_limits.edit_ip_short.window',
            'attempts' => 60,
            'window' => 60,
            'scope' => 'edit:ip:short',
            'by_user' => false,
        ],
        'edit_user_long' => [
            'attempts_key' => 'seeker.rate_limits.edit_user_long.attempts',
            'window_key' => 'seeker.rate_limits.edit_user_long.window',
            'attempts' => 30,
            'window' => 1440,
            'scope' => 'edit:user:long',
            'by_user' => true,
        ],
    ];

    public function enabled(): bool
    {
        return $this->boolean(self::ENABLED_KEY, true);
    }

    public function publicationsEnabled(): bool
    {
        return $this->boolean(self::PUBLICATIONS_ENABLED_KEY, true);
    }

    public function newConversationsEnabled(): bool
    {
        return $this->boolean(self::NEW_CONVERSATIONS_ENABLED_KEY, true);
    }

    public function biographiesEnabled(): bool
    {
        return $this->boolean(self::BIOGRAPHIES_ENABLED_KEY, true);
    }

    public function messageImagesEnabled(): bool
    {
        return $this->boolean(self::MESSAGE_IMAGES_ENABLED_KEY, true);
    }

    public function userMenuEnabled(): bool
    {
        return $this->userMenuItemEnabled('seeker');
    }

    public function userMenuItemEnabled(string $item): bool
    {
        $definition = self::USER_MENU_ITEMS[$item] ?? null;

        return $definition !== null
            && $this->boolean($definition['enabled_key'], $definition['enabled']);
    }

    public function userMenuIcon(string $item): string
    {
        $definition = self::USER_MENU_ITEMS[$item] ?? null;

        if ($definition === null) {
            return 'bi-question-circle';
        }

        $icon = (string) setting($definition['icon_key'], $definition['icon']);

        return preg_match('/\Abi-[a-z0-9]+(?:-[a-z0-9]+)*\z/', $icon) === 1
            ? $icon
            : $definition['icon'];
    }

    public function userMenuItems(): array
    {
        return collect(self::USER_MENU_ITEMS)->map(fn (array $definition, string $item) => [
            'enabled' => $this->userMenuItemEnabled($item),
            'icon' => $this->userMenuIcon($item),
        ])->all();
    }

    public function portfolioTypes(): array
    {
        return collect(self::PORTFOLIO_TYPE_KEYS)
            ->mapWithKeys(fn (string $key, string $type) => [$type => $this->boolean($key, true)])
            ->all();
    }

    public function enabledPortfolioTypes(): array
    {
        return array_keys(array_filter($this->portfolioTypes()));
    }

    public function portfolioTypeEnabled(string $type): bool
    {
        return $this->portfolioTypes()[$type] ?? false;
    }

    public function rateLimits(string $action): array
    {
        return collect(self::RATE_LIMITS)
            ->filter(fn (array $definition) => str_starts_with($definition['scope'], $action.':'))
            ->map(fn (array $definition) => [
                ...$definition,
                'attempts' => $this->integer($definition['attempts_key'], $definition['attempts'], 0, 10000),
                'window' => $this->integer($definition['window_key'], $definition['window'], 1, 10080),
            ])
            ->values()
            ->all();
    }

    public function allRateLimits(): array
    {
        return collect(self::RATE_LIMITS)->map(fn (array $definition) => [
            ...$definition,
            'attempts' => $this->integer($definition['attempts_key'], $definition['attempts'], 0, 10000),
            'window' => $this->integer($definition['window_key'], $definition['window'], 1, 10080),
        ])->all();
    }

    private function boolean(string $key, bool $default): bool
    {
        return filter_var(setting($key, $default), FILTER_VALIDATE_BOOL);
    }

    private function integer(string $key, int $default, int $minimum, int $maximum): int
    {
        return max($minimum, min($maximum, (int) setting($key, $default)));
    }
}
