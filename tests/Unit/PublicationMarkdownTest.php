<?php

namespace Tests\Unit;

use Azuriom\Plugin\Seeker\Services\PublicationMarkdown;
use Tests\TestCase;

class PublicationMarkdownTest extends TestCase
{
    private PublicationMarkdown $markdown;

    protected function setUp(): void
    {
        parent::setUp();

        $this->markdown = new PublicationMarkdown;
    }

    public function test_it_renders_markdown_with_azuriom_commonmark(): void
    {
        $rendered = $this->markdown->render("## Creative services\n\nA **safe** description with [Zibuu](https://zibuu.net).\n\n- One\n- Two");

        $this->assertStringContainsString('<h2>Creative services</h2>', $rendered);
        $this->assertStringContainsString('<strong>safe</strong>', $rendered);
        $this->assertStringContainsString('<ul>', $rendered);
        $this->assertStringContainsString('href="https://zibuu.net"', $rendered);
    }

    public function test_raw_html_unsafe_links_and_embedded_images_are_not_rendered(): void
    {
        $rendered = $this->markdown->render('<script>alert(1)</script> [Bad](javascript:alert(2)) ![Image](https://example.com/image.jpg)');

        $this->assertStringNotContainsString('<script>', $rendered);
        $this->assertStringContainsString('&lt;script&gt;', $rendered);
        $this->assertStringNotContainsString('href="javascript:', $rendered);
        $this->assertStringNotContainsString('<img', $rendered);
    }

    public function test_plain_text_excludes_markdown_markup_and_removed_media(): void
    {
        $markdown = "## Creative services\n\nLogos **and branding**\n\n![Hidden](https://example.com/image.jpg)";

        $this->assertSame('Creative services Logos and branding', $this->markdown->plainText($markdown));
    }

    public function test_normalize_removes_null_bytes_and_normalizes_line_endings(): void
    {
        $this->assertSame("First\nSecond", $this->markdown->normalize(" First\r\nSec\0ond\r "));
    }
}
