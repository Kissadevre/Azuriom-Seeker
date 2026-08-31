<?php

namespace Azuriom\Plugin\Seeker\Services;

use Azuriom\Support\Markdown;

class PublicationMarkdown
{
    public function normalize(string $markdown): string
    {
        $markdown = str_replace(["\r\n", "\r"], "\n", $markdown);

        return trim(str_replace("\0", '', $markdown));
    }

    public function render(string $markdown): string
    {
        return Markdown::parse($markdown, true);
    }

    public function plainText(string $markdown): string
    {
        $html = $this->render($markdown);
        $html = preg_replace('/<br\s*\/?\s*>/i', ' ', $html);
        $html = preg_replace('/<\/(?:p|h[1-6]|blockquote|li|pre)>/i', '$0 ', (string) $html);
        $text = html_entity_decode(strip_tags((string) $html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
