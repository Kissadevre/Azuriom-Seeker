<?php

namespace Tests\Unit;

use Azuriom\Plugin\Seeker\Providers\SeekerServiceProvider;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Azuriom\Support\SettingsRepository;
use Tests\TestCase;

class UserMenuSettingTest extends TestCase
{
    public function test_user_menu_shortcut_is_disabled_by_default(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository);

        $this->assertFalse($this->app->make(SeekerSettings::class)->userMenuEnabled());
        $this->assertSame([], $this->provider()->shortcut());
    }

    public function test_user_menu_shortcut_links_to_seeker_when_enabled(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository(collect([
            SeekerSettings::USER_MENU_ENABLED_KEY => true,
        ])));

        $shortcut = $this->provider()->shortcut();

        $this->assertSame('seeker.index', $shortcut['seeker-portal']['route']);
        $this->assertSame('seeker.access', $shortcut['seeker-portal']['permission']);
        $this->assertSame('Seeker', $shortcut['seeker-portal']['name']);
    }

    private function provider(): object
    {
        return new class($this->app) extends SeekerServiceProvider
        {
            public function shortcut(): array
            {
                return $this->userMenuShortcut();
            }
        };
    }
}
