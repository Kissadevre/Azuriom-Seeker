<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class PublicStyleTest extends TestCase
{
    public function test_base_styles_use_theme_tokens_instead_of_the_zibuu_palette(): void
    {
        $styles = file_get_contents(dirname(__DIR__, 2).'/assets/css/style.css');

        $this->assertIsString($styles);
        $this->assertStringNotContainsString('#ec6fa9', strtolower($styles));
        $this->assertStringNotContainsString('#9b7cf7', strtolower($styles));
        $this->assertStringNotContainsString('#45c9ed', strtolower($styles));
        $this->assertStringNotContainsString('rgba(236, 111, 169', $styles);
        $this->assertStringNotContainsString('rgba(155, 124, 247', $styles);
        $this->assertStringNotContainsString('rgba(69, 201, 237', $styles);
        $this->assertStringContainsString('--seeker-brand-gradient: var(--bs-primary);', $styles);
        $this->assertStringContainsString('--seeker-border: var(--bs-border-color);', $styles);
    }
}
