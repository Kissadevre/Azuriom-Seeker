<?php

namespace Tests\Unit;

use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Requests\StorePublicationRequest;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Azuriom\Support\SettingsRepository;
use Tests\TestCase;

class AssetLimitSettingTest extends TestCase
{
    public function test_asset_limits_keep_the_existing_defaults(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository);
        $settings = $this->app->make(SeekerSettings::class);

        $this->assertSame([
            Publication::PORTFOLIO_IMAGES => ['count' => 6, 'size' => 5],
            Publication::PORTFOLIO_VIDEO => ['count' => 1, 'size' => 10],
            Publication::PORTFOLIO_AUDIO => ['count' => 1, 'size' => 10],
        ], $settings->assetLimits());
    }

    public function test_custom_asset_limits_are_used_by_upload_validation(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository(collect([
            'seeker.asset_limits.images.count' => 12,
            'seeker.asset_limits.images.size_megabytes' => 8,
            'seeker.asset_limits.video.count' => 3,
            'seeker.asset_limits.video.size_megabytes' => 25,
            'seeker.asset_limits.audio.count' => 4,
            'seeker.asset_limits.audio.size_megabytes' => 15,
        ])));

        $rules = (new StorePublicationRequest)->rules();

        $this->assertContains('max:12', $rules['images']);
        $this->assertContains('max:8192', $rules['images.*']);
        $this->assertContains('max:3', $rules['video']);
        $this->assertContains('max:25600', $rules['video.*']);
        $this->assertContains('max:4', $rules['audio']);
        $this->assertContains('max:15360', $rules['audio.*']);
    }

    public function test_out_of_range_stored_limits_are_safely_clamped(): void
    {
        $this->app->instance(SettingsRepository::class, new SettingsRepository(collect([
            'seeker.asset_limits.images.count' => 0,
            'seeker.asset_limits.video.count' => 500,
            'seeker.asset_limits.audio.size_megabytes' => 9000,
        ])));
        $settings = $this->app->make(SeekerSettings::class);

        $this->assertSame(1, $settings->assetCountLimit(Publication::PORTFOLIO_IMAGES));
        $this->assertSame(100, $settings->assetCountLimit(Publication::PORTFOLIO_VIDEO));
        $this->assertSame(2048, $settings->assetSizeMegabytes(Publication::PORTFOLIO_AUDIO));
    }
}
