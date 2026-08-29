<?php

namespace Tests\Unit;

use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Requests\StorePublicationRequest;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Tests\TestCase;

class PublicationEditorTest extends TestCase
{
    public function test_tinymce_removes_native_validation_from_its_hidden_textarea(): void
    {
        $editorView = file_get_contents(dirname(__DIR__, 2).'/resources/views/publications/_editor.blade.php');

        $this->assertIsString($editorView);
        $this->assertStringContainsString(
            "editor.getElement().removeAttribute('required');",
            $editorView
        );
    }

    public function test_description_remains_required_by_server_validation(): void
    {
        $settings = $this->createMock(SeekerSettings::class);
        $settings->method('enabledPortfolioTypes')->willReturn([Publication::PORTFOLIO_EXTERNAL]);
        $settings->method('assetCountLimit')->willReturnMap([
            [Publication::PORTFOLIO_IMAGES, 6],
            [Publication::PORTFOLIO_VIDEO, 1],
            [Publication::PORTFOLIO_AUDIO, 1],
        ]);
        $settings->method('assetSizeKilobytes')->willReturnMap([
            [Publication::PORTFOLIO_IMAGES, 5120],
            [Publication::PORTFOLIO_VIDEO, 10240],
            [Publication::PORTFOLIO_AUDIO, 10240],
        ]);
        $this->app->instance(SeekerSettings::class, $settings);

        $rules = (new StorePublicationRequest)->rules();

        $this->assertContains('required', $rules['description']);
    }
}
