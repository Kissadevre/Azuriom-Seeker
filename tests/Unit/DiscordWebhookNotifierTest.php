<?php

namespace Tests\Unit;

use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Services\DiscordWebhookNotifier;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Azuriom\Support\SettingsRepository;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class DiscordWebhookNotifierTest extends TestCase
{
    private const WEBHOOK_URL = 'https://discord.com/api/webhooks/123456789/test-token';

    public function test_discord_notifications_are_disabled_by_default(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository);
        $settings = $this->app->make(SeekerSettings::class);

        $this->assertFalse($settings->discordWebhookEnabled());
        $this->assertTrue($settings->discordWebhookTypeEnabled(Publication::TYPE_COMMISSION));
        $this->assertTrue($settings->discordWebhookTypeEnabled(Publication::TYPE_TALENT));

        Http::fake();

        $this->assertFalse($this->app->make(DiscordWebhookNotifier::class)->publicationCreated(
            $this->publication(Publication::TYPE_COMMISSION)
        ));
        Http::assertNothingSent();
    }

    public function test_new_publication_notification_contains_safe_discord_embed_data(): void
    {
        $this->enableWebhook();
        Http::fake([self::WEBHOOK_URL => Http::response(status: 204)]);

        $sent = $this->app->make(DiscordWebhookNotifier::class)->publicationCreated(
            $this->publication(Publication::TYPE_COMMISSION)
        );

        $this->assertTrue($sent);
        Http::assertSent(function (Request $request): bool {
            $embed = $request->data()['embeds'][0];

            return $request->url() === self::WEBHOOK_URL
                && $request->data()['allowed_mentions'] === ['parse' => []]
                && $embed['title'] === 'A polished commission'
                && $embed['description'] === 'Safe description <script>alert(1)</script>'
                && str_ends_with($embed['url'], '/seeker/publications/42');
        });
    }

    public function test_each_publication_type_can_be_disabled_independently(): void
    {
        $this->enableWebhook([
            SeekerSettings::DISCORD_WEBHOOK_TYPE_KEYS[Publication::TYPE_TALENT] => false,
        ]);
        Http::fake();

        $this->assertFalse($this->app->make(DiscordWebhookNotifier::class)->publicationCreated(
            $this->publication(Publication::TYPE_TALENT)
        ));
        Http::assertNothingSent();
    }

    public function test_discord_failure_is_contained_and_does_not_throw(): void
    {
        $this->enableWebhook();
        Http::fake([self::WEBHOOK_URL => Http::response(['message' => 'Unavailable'], 503)]);

        $this->assertFalse($this->app->make(DiscordWebhookNotifier::class)->publicationCreated(
            $this->publication(Publication::TYPE_COMMISSION)
        ));
        Http::assertSentCount(1);
    }

    public function test_discord_connection_exception_is_contained(): void
    {
        $this->enableWebhook();
        Http::fake(fn () => Http::failedConnection('Discord is unreachable'));

        $this->assertFalse($this->app->make(DiscordWebhookNotifier::class)->publicationCreated(
            $this->publication(Publication::TYPE_COMMISSION)
        ));
    }

    public function test_only_the_creation_flow_dispatches_publication_notifications(): void
    {
        $controller = file_get_contents(dirname(__DIR__, 2).'/src/Controllers/PublicationController.php');

        $this->assertIsString($controller);
        $this->assertSame(1, substr_count($controller, '->publicationCreated($publication)'));
        $this->assertGreaterThan(
            strpos($controller, '$publication = DB::transaction'),
            strpos($controller, '->publicationCreated($publication)')
        );
        $this->assertStringNotContainsString(
            'publicationCreated',
            substr($controller, strpos($controller, 'public function update('))
        );
    }

    public function test_only_official_https_discord_webhook_urls_are_accepted(): void
    {
        $this->assertTrue(DiscordWebhookNotifier::isValidUrl(self::WEBHOOK_URL));
        $this->assertTrue(DiscordWebhookNotifier::isValidUrl('https://discordapp.com/api/v10/webhooks/1/token'));
        $this->assertFalse(DiscordWebhookNotifier::isValidUrl('http://discord.com/api/webhooks/1/token'));
        $this->assertFalse(DiscordWebhookNotifier::isValidUrl('https://discord.com.evil.test/api/webhooks/1/token'));
        $this->assertFalse(DiscordWebhookNotifier::isValidUrl('https://discord.com/channels/1/2'));
    }

    private function enableWebhook(array $overrides = []): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository(collect([
            SeekerSettings::DISCORD_WEBHOOK_ENABLED_KEY => true,
            SeekerSettings::DISCORD_WEBHOOK_URL_KEY => self::WEBHOOK_URL,
            ...$overrides,
        ])));
    }

    private function publication(string $type): Publication
    {
        $publication = new Publication([
            'type' => $type,
            'title' => 'A polished commission',
            'description' => '**Safe** description <script>alert(1)</script>',
            'status' => Publication::STATUS_ACTIVE,
        ]);
        $publication->id = 42;
        $publication->published_at = now();
        $publication->setRelation('user', new User(['name' => 'Kissadere']));

        return $publication;
    }
}
