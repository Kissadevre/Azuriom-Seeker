<?php

namespace Azuriom\Plugin\Seeker\Services;

use Azuriom\Plugin\Seeker\Models\Publication;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class DiscordWebhookNotifier
{
    public function __construct(
        private readonly SeekerSettings $settings,
        private readonly PublicationMarkdown $markdown
    ) {}

    public function publicationCreated(Publication $publication): bool
    {
        if (! $this->settings->discordWebhookEnabled()
            || ! $this->settings->discordWebhookTypeEnabled($publication->type)) {
            return false;
        }

        $url = $this->settings->discordWebhookUrl();

        if (! self::isValidUrl($url)) {
            Log::warning('Seeker skipped a Discord publication notification because the webhook URL is invalid.');

            return false;
        }

        $publication->loadMissing('user');
        $description = $this->markdown->plainText($publication->description);

        return $this->deliver($url, [
            'username' => 'Seeker',
            'allowed_mentions' => ['parse' => []],
            'embeds' => [[
                'title' => mb_substr($publication->title, 0, 256),
                'description' => mb_substr($description, 0, 1000),
                'url' => route('seeker.publications.show', $publication),
                'color' => $publication->type === Publication::TYPE_COMMISSION ? 0x5865F2 : 0x57F287,
                'fields' => [
                    [
                        'name' => trans('seeker::messages.fields.type'),
                        'value' => trans('seeker::messages.types.'.$publication->type),
                        'inline' => true,
                    ],
                    [
                        'name' => trans('seeker::admin.settings.discord_webhook.author'),
                        'value' => $publication->user->name,
                        'inline' => true,
                    ],
                ],
                'timestamp' => ($publication->published_at ?? $publication->created_at ?? now())->toIso8601String(),
            ]],
        ]);
    }

    public function test(string $url): bool
    {
        if (! self::isValidUrl($url)) {
            return false;
        }

        return $this->deliver($url, [
            'username' => 'Seeker',
            'allowed_mentions' => ['parse' => []],
            'embeds' => [[
                'title' => trans('seeker::admin.settings.discord_webhook.test_title'),
                'description' => trans('seeker::admin.settings.discord_webhook.test_description'),
                'color' => 0x5865F2,
                'timestamp' => now()->toIso8601String(),
            ]],
        ]);
    }

    public static function isValidUrl(string $url): bool
    {
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        $parts = parse_url($url);
        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';

        return ($parts['scheme'] ?? '') === 'https'
            && in_array($host, ['discord.com', 'discordapp.com'], true)
            && preg_match('~\A/api(?:/v\d+)?/webhooks/\d+/[^/]+/?\z~', $path) === 1
            && ! isset($parts['user'])
            && ! isset($parts['pass']);
    }

    private function deliver(string $url, array $payload): bool
    {
        try {
            $response = Http::asJson()
                ->acceptJson()
                ->connectTimeout(3)
                ->timeout(5)
                ->post($url, $payload);

            if (! $response->successful()) {
                Log::warning('Discord rejected a Seeker webhook notification.', [
                    'status' => $response->status(),
                ]);

                return false;
            }

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
