<?php

namespace Tests\Unit;

use Tests\TestCase;

class BreadcrumbTest extends TestCase
{
    public function test_breadcrumb_component_is_semantic_and_accessible(): void
    {
        $view = view('seeker::_breadcrumb', [
            'breadcrumbs' => [
                ['label' => 'Messages', 'url' => '/seeker/conversations'],
                ['label' => '<Current page>'],
            ],
        ])->render();

        $this->assertStringContainsString('<nav class="seeker-breadcrumb"', $view);
        $this->assertStringContainsString('aria-label="Breadcrumb"', $view);
        $this->assertStringContainsString('<ol class="breadcrumb mb-0">', $view);
        $this->assertStringContainsString('aria-current="page"', $view);
        $this->assertStringContainsString('href="/seeker/conversations"', $view);
        $this->assertStringContainsString('&lt;Current page&gt;', $view);
        $this->assertStringNotContainsString('<Current page>', $view);
    }

    public function test_every_public_subpage_uses_the_breadcrumb_component(): void
    {
        $views = [
            'publications/mine.blade.php',
            'publications/create.blade.php',
            'publications/edit.blade.php',
            'publications/show.blade.php',
            'publications/report.blade.php',
            'profiles/show.blade.php',
            'profiles/edit.blade.php',
            'profiles/report.blade.php',
            'conversations/index.blade.php',
            'conversations/create.blade.php',
            'conversations/show.blade.php',
            'conversations/completion.blade.php',
            'conversations/review.blade.php',
            'conversations/report.blade.php',
            'restrictions/show.blade.php',
        ];

        foreach ($views as $viewPath) {
            $view = file_get_contents(dirname(__DIR__, 2).'/resources/views/'.$viewPath);

            $this->assertIsString($view, $viewPath);
            $this->assertStringContainsString('breadcrumbs', $view, $viewPath);
        }
    }
}
