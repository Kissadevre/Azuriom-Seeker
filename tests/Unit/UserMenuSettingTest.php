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

        $settings = $this->app->make(SeekerSettings::class);

        $this->assertFalse($settings->userMenuEnabled());
        $this->assertTrue($settings->userMenuItemEnabled('my_publications'));
        $this->assertTrue($settings->userMenuItemEnabled('messages'));
        $this->assertSame('bi-people', $settings->userMenuIcon('seeker'));
        $this->assertSame('bi-briefcase', $settings->userMenuIcon('my_publications'));
        $this->assertSame('bi-chat-dots', $settings->userMenuIcon('messages'));
        $this->assertSame([], $this->provider()->shortcut());
    }

    public function test_user_menu_shortcut_links_to_seeker_when_enabled(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository(collect([
            SeekerSettings::USER_MENU_ENABLED_KEY => true,
            'seeker.user_menu.seeker_icon' => 'bi-stars',
        ])));

        $shortcut = $this->provider()->shortcut();

        $this->assertSame('seeker.index', $shortcut['seeker-portal']['route']);
        $this->assertSame('seeker.access', $shortcut['seeker-portal']['permission']);
        $this->assertSame('Seeker', $shortcut['seeker-portal']['name']);
        $this->assertSame('bi bi-stars', $shortcut['seeker-portal']['icon']);
    }

    public function test_publication_and_message_shortcuts_can_be_hidden_and_customized(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository(collect([
            'seeker.user_menu.my_publications_enabled' => false,
            'seeker.user_menu.messages_enabled' => true,
            'seeker.user_menu.messages_icon' => 'bi-envelope-paper',
        ])));

        $navigation = $this->provider()->navigation();

        $this->assertArrayNotHasKey('seeker', $navigation);
        $this->assertSame('bi bi-envelope-paper', $navigation['seeker-messages']['icon']);
    }

    public function test_invalid_stored_icons_fall_back_to_safe_defaults(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository(collect([
            'seeker.user_menu.messages_icon' => 'bi-chat-dots text-danger onclick=alert(1)',
        ])));

        $this->assertSame(
            'bi-chat-dots',
            $this->app->make(SeekerSettings::class)->userMenuIcon('messages')
        );
    }

    private function provider(): object
    {
        return new class($this->app) extends SeekerServiceProvider
        {
            public function shortcut(): array
            {
                return $this->userMenuShortcut();
            }

            public function navigation(): array
            {
                return $this->userNavigation();
            }
        };
    }
}
