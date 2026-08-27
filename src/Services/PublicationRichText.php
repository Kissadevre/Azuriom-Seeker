<?php

namespace Azuriom\Plugin\Seeker\Services;

use DOMDocument;
use DOMElement;
use DOMNode;

class PublicationRichText
{
    /** @var array<string, array<int, string>> */
    private const ALLOWED_ELEMENTS = [
        'p' => [], 'br' => [],
        'h2' => [], 'h3' => [], 'h4' => [],
        'strong' => [], 'b' => [], 'em' => [], 'i' => [], 'u' => [], 's' => [],
        'blockquote' => [], 'ul' => [], 'ol' => [], 'li' => [],
        'a' => ['href', 'title'],
    ];

    /** @var array<int, string> */
    private const DROP_WITH_CONTENT = [
        'script', 'style', 'noscript', 'template',
        'img', 'picture', 'video', 'audio', 'source', 'track',
        'iframe', 'object', 'embed', 'svg', 'math', 'canvas',
        'form', 'input', 'button', 'textarea', 'select', 'option',
        'meta', 'link', 'base',
    ];

    public function sanitize(string $html): string
    {
        if (trim($html) === '') {
            return '';
        }

        if (preg_match('/<\/?[a-z][^>]*>/i', $html) !== 1) {
            return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
        }

        $document = new DOMDocument('1.0', 'UTF-8');
        $previous = libxml_use_internal_errors(true);
        $loaded = $document->loadHTML(
            '<?xml encoding="utf-8" ?><div id="seeker-publication-content">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if (! $loaded) {
            return '';
        }

        $root = $document->getElementById('seeker-publication-content');
        if (! $root instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($root);

        $result = '';
        foreach (iterator_to_array($root->childNodes) as $child) {
            $result .= $document->saveHTML($child);
        }

        return trim($result);
    }

    public function plainText(string $html): string
    {
        $sanitized = $this->sanitize($html);
        $sanitized = preg_replace('/<br\s*\/?\s*>/i', ' ', $sanitized);
        $sanitized = preg_replace('/<\/(?:p|h2|h3|h4|blockquote|li)>/i', '$0 ', (string) $sanitized);
        $text = html_entity_decode(strip_tags((string) $sanitized), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    public function render(string $html): string
    {
        $sanitized = $this->sanitize($html);

        if ($sanitized === '' || preg_match('/<\/?[a-z][^>]*>/i', $sanitized) === 1) {
            return $sanitized;
        }

        return nl2br($sanitized, false);
    }

    private function sanitizeChildren(DOMNode $parent): void
    {
        foreach (iterator_to_array($parent->childNodes) as $node) {
            if (! $node instanceof DOMElement) {
                if (! in_array($node->nodeType, [XML_TEXT_NODE, XML_CDATA_SECTION_NODE], true)) {
                    $node->parentNode?->removeChild($node);
                }

                continue;
            }

            $tag = strtolower($node->tagName);
            if (in_array($tag, self::DROP_WITH_CONTENT, true)) {
                $node->parentNode?->removeChild($node);

                continue;
            }

            if (! array_key_exists($tag, self::ALLOWED_ELEMENTS)) {
                $this->sanitizeChildren($node);
                $this->unwrap($node);

                continue;
            }

            $this->sanitizeAttributes($node, self::ALLOWED_ELEMENTS[$tag]);
            $this->sanitizeChildren($node);
        }
    }

    /** @param array<int, string> $allowed */
    private function sanitizeAttributes(DOMElement $element, array $allowed): void
    {
        foreach (iterator_to_array($element->attributes) as $attribute) {
            if (! in_array(strtolower($attribute->name), $allowed, true)) {
                $element->removeAttribute($attribute->name);
            }
        }

        if ($element->tagName === 'a' && ! $this->isSafeLink($element->getAttribute('href'))) {
            $element->removeAttribute('href');
        }
    }

    private function isSafeLink(string $url): bool
    {
        $url = trim($url);
        if ($url === '' || preg_match('/[\x00-\x20\x7f]/', $url) === 1) {
            return false;
        }

        if (str_starts_with($url, '#')) {
            return true;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if ($scheme === '') {
            return str_starts_with($url, '/') && ! str_starts_with($url, '//');
        }

        return in_array($scheme, ['http', 'https', 'mailto'], true);
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if ($parent === null) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
