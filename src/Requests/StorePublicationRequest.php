<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationMedia;
use Azuriom\Plugin\Seeker\Services\PublicationMarkdown;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Azuriom\Plugin\Seeker\Support\SeekerPermissions;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $description = $this->input('description');

        if (is_string($description)) {
            $this->merge([
                'description' => app(PublicationMarkdown::class)->normalize($description),
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->can(SeekerPermissions::CREATE_PUBLICATIONS) === true;
    }

    public function rules(): array
    {
        $settings = app(SeekerSettings::class);

        return [
            'type' => ['required', Rule::in(Publication::types())],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'description' => [
                'required',
                'string',
                'max:50000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $length = mb_strlen(app(PublicationMarkdown::class)->plainText((string) $value));

                    if ($length < 20) {
                        $fail(trans('seeker::messages.validation.description_min'));
                    } elseif ($length > 10000) {
                        $fail(trans('seeker::messages.validation.description_max'));
                    }
                },
            ],
            'portfolio_type' => ['required', Rule::in($this->allowedPortfolioTypes())],
            'portfolio_url' => ['required_if:portfolio_type,'.Publication::PORTFOLIO_EXTERNAL, 'prohibited_unless:portfolio_type,'.Publication::PORTFOLIO_EXTERNAL, 'nullable', 'url:http,https', 'max:2048'],
            'images' => ['required_if:portfolio_type,'.Publication::PORTFOLIO_IMAGES, 'prohibited_unless:portfolio_type,'.Publication::PORTFOLIO_IMAGES, 'array', 'min:1', 'max:'.$settings->assetCountLimit(Publication::PORTFOLIO_IMAGES)],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.$settings->assetSizeKilobytes(Publication::PORTFOLIO_IMAGES), 'dimensions:max_width=4096,max_height=4096'],
            'video' => $this->mediaRules(Publication::PORTFOLIO_VIDEO, true),
            'video.*' => $this->mediaFileRules(Publication::PORTFOLIO_VIDEO),
            'audio' => $this->mediaRules(Publication::PORTFOLIO_AUDIO, true),
            'audio.*' => $this->mediaFileRules(Publication::PORTFOLIO_AUDIO),
            'is_guest_visible' => ['required', 'boolean'],
            'pricing_type' => ['required', Rule::in(Publication::pricingTypes())],
            'price' => ['required_if:pricing_type,'.Publication::PRICING_POINTS, 'prohibited_unless:pricing_type,'.Publication::PRICING_POINTS, 'nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999999999.99'],
            'price_basis' => ['required_if:pricing_type,'.Publication::PRICING_POINTS, 'prohibited_unless:pricing_type,'.Publication::PRICING_POINTS, 'nullable', Rule::in(Publication::priceBases())],
        ];
    }

    protected function mediaRules(string $type, bool $required): array
    {
        $rules = [
            'prohibited_unless:portfolio_type,'.$type,
            'nullable',
            'array',
            'max:'.app(SeekerSettings::class)->assetCountLimit($type),
        ];

        if ($required) {
            array_unshift($rules, 'required_if:portfolio_type,'.$type);
        }

        return $rules;
    }

    protected function mediaFileRules(string $type): array
    {
        $extensions = PublicationMedia::extensionsFor($type);

        return [
            'file',
            'extensions:'.implode(',', $extensions),
            'mimes:'.implode(',', $extensions),
            'mimetypes:'.implode(',', PublicationMedia::mimeTypesFor($type)),
            'max:'.app(SeekerSettings::class)->assetSizeKilobytes($type),
        ];
    }

    protected function allowedPortfolioTypes(): array
    {
        return app(SeekerSettings::class)->enabledPortfolioTypes();
    }

    public function messages(): array
    {
        $settings = app(SeekerSettings::class);

        return [
            'portfolio_type.in' => trans('seeker::messages.validation.portfolio_type_unavailable'),
            'images.max' => trans('seeker::messages.validation.max_assets', [
                'count' => $settings->assetCountLimit(Publication::PORTFOLIO_IMAGES),
                'type' => trans('seeker::messages.fields.images'),
            ]),
            'images.*.max' => trans('seeker::messages.validation.asset_too_large', [
                'size' => $settings->assetSizeMegabytes(Publication::PORTFOLIO_IMAGES),
            ]),
            'video.max' => trans('seeker::messages.validation.max_assets', [
                'count' => $settings->assetCountLimit(Publication::PORTFOLIO_VIDEO),
                'type' => trans('seeker::messages.fields.video'),
            ]),
            'video.*.max' => trans('seeker::messages.validation.asset_too_large', [
                'size' => $settings->assetSizeMegabytes(Publication::PORTFOLIO_VIDEO),
            ]),
            'audio.max' => trans('seeker::messages.validation.max_assets', [
                'count' => $settings->assetCountLimit(Publication::PORTFOLIO_AUDIO),
                'type' => trans('seeker::messages.fields.audio'),
            ]),
            'audio.*.max' => trans('seeker::messages.validation.asset_too_large', [
                'size' => $settings->assetSizeMegabytes(Publication::PORTFOLIO_AUDIO),
            ]),
        ];
    }
}
