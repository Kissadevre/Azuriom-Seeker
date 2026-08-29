<?php

namespace Tests\Unit;

use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Requests\StorePublicationRequest;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Tests\TestCase;

class PublicationEditorTest extends TestCase
{
    public function test_publication_form_uses_the_native_azuriom_captcha_view(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $form = file_get_contents($pluginRoot.'/resources/views/publications/_form.blade.php');

        $this->assertIsString($form);
        $this->assertStringContainsString("@include('elements.captcha', ['center' => true])", $form);
        $this->assertFileDoesNotExist($pluginRoot.'/resources/views/captcha.blade.php');
        $this->assertFileDoesNotExist($pluginRoot.'/resources/views/elements/captcha.blade.php');
    }

    public function test_publication_description_uses_the_azuriom_markdown_editor(): void
    {
        $pluginRoot = dirname(__DIR__, 2);
        $form = file_get_contents($pluginRoot.'/resources/views/publications/_form.blade.php');
        $create = file_get_contents($pluginRoot.'/resources/views/publications/create.blade.php');
        $edit = file_get_contents($pluginRoot.'/resources/views/publications/edit.blade.php');

        $this->assertIsString($form);
        $this->assertStringContainsString('name="description"', $form);
        $this->assertStringContainsString('markdown-editor', $form);
        $this->assertStringContainsString('required', $form);
        $this->assertStringContainsString("@include('seeker::publications._markdown-editor')", $create);
        $this->assertStringContainsString("@include('seeker::publications._markdown-editor')", $edit);
        $this->assertStringNotContainsString('tinymce', strtolower($form.$create.$edit));

        $editor = file_get_contents($pluginRoot.'/resources/views/publications/_markdown-editor.blade.php');

        $this->assertIsString($editor);
        $this->assertStringContainsString("asset('vendor/easymde/easymde.min.js')", $editor);
        $this->assertStringContainsString("description.removeAttribute('required')", $editor);
        $this->assertStringContainsString("description.removeAttribute('minlength')", $editor);
        $this->assertStringContainsString("description.removeAttribute('maxlength')", $editor);
        $this->assertStringContainsString('.editor-toolbar .table', $editor);
        $this->assertStringContainsString('display: inline-block', $editor);
        $this->assertStringNotContainsString("'image'", $editor);
        $this->assertStringNotContainsString("'preview'", $editor);
        $this->assertStringNotContainsString('uploadImage', $editor);
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
