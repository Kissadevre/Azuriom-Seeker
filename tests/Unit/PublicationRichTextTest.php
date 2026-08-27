<?php

namespace Tests\Unit;

use Azuriom\Plugin\Seeker\Services\PublicationRichText;
use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2).'/src/Services/PublicationRichText.php';

class PublicationRichTextTest extends TestCase
{
    private PublicationRichText $richText;

    protected function setUp(): void
    {
        parent::setUp();

        $this->richText = new PublicationRichText;
    }

    public function test_it_keeps_only_supported_rich_text(): void
    {
        $html = '<h2 class="title">Sample</h2><p style="color:red" onclick="alert(1)">A <strong>safe</strong> description.</p>'
            .'<blockquote><em>Details</em></blockquote><ul><li>One</li></ul>';

        $sanitized = $this->richText->sanitize($html);

        $this->assertStringContainsString('<h2>Sample</h2>', $sanitized);
        $this->assertStringContainsString('<strong>safe</strong>', $sanitized);
        $this->assertStringContainsString('<blockquote><em>Details</em></blockquote>', $sanitized);
        $this->assertStringNotContainsString('class=', $sanitized);
        $this->assertStringNotContainsString('style=', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
    }

    public function test_it_removes_embedded_media_and_active_content(): void
    {
        $html = '<p>Visible text</p><img src="https://example.com/image.jpg">'
            .'<picture><source srcset="x"><img src="x"></picture>'
            .'<video><source src="movie.mp4"></video><audio src="sound.mp3"></audio>'
            .'<iframe src="https://example.com"></iframe><svg onload="alert(1)"><text>bad</text></svg>'
            .'<script>alert(1)</script><form><input value="bad"></form>';

        $sanitized = $this->richText->sanitize($html);

        $this->assertSame('<p>Visible text</p>', $sanitized);
    }

    public function test_it_rejects_unsafe_links_and_link_attributes(): void
    {
        $html = '<p><a href="javascript:alert(1)" target="_blank" rel="opener">Bad</a> '
            .'<a href="https://zibuu.net/path" target="_blank" onclick="alert(1)">Good</a></p>';

        $sanitized = $this->richText->sanitize($html);

        $this->assertStringContainsString('<a>Bad</a>', $sanitized);
        $this->assertStringContainsString('<a href="https://zibuu.net/path">Good</a>', $sanitized);
        $this->assertStringNotContainsString('target=', $sanitized);
        $this->assertStringNotContainsString('onclick=', $sanitized);
    }

    public function test_plain_text_excludes_markup_and_forbidden_content(): void
    {
        $html = '<h2>Creative services</h2><p>Logos &amp; branding</p><video>hidden caption</video>';

        $this->assertSame('Creative services Logos & branding', $this->richText->plainText($html));
    }

    public function test_render_preserves_legacy_plain_text_line_breaks_safely(): void
    {
        $rendered = $this->richText->render("First line\nSecond < line");

        $this->assertSame("First line<br>\nSecond &lt; line", $rendered);
    }
}
