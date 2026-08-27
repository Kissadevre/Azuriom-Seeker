<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationMedia;
use Azuriom\Plugin\Seeker\Services\PublicationRichText;
use Azuriom\Plugin\Seeker\Services\SeekerSettings;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicationRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $description = $this->input('description');

        if (is_string($description)) {
            $this->merge([
                'description' => app(PublicationRichText::class)->sanitize($description),
            ]);
        }
    }

    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(Publication::types())],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'description' => [
                'required',
                'string',
                'max:50000',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $length = mb_strlen(app(PublicationRichText::class)->plainText((string) $value));

                    if ($length < 20) {
                        $fail(trans('seeker::messages.validation.description_min'));
                    } elseif ($length > 10000) {
                        $fail(trans('seeker::messages.validation.description_max'));
                    }
                },
            ],
            'portfolio_type' => ['required', Rule::in($this->allowedPortfolioTypes())],
            'portfolio_url' => ['required_if:portfolio_type,'.Publication::PORTFOLIO_EXTERNAL, 'prohibited_unless:portfolio_type,'.Publication::PORTFOLIO_EXTERNAL, 'nullable', 'url:http,https', 'max:2048'],
            'images' => ['required_if:portfolio_type,'.Publication::PORTFOLIO_IMAGES, 'prohibited_unless:portfolio_type,'.Publication::PORTFOLIO_IMAGES, 'array', 'min:1', 'max:6'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
            'video' => $this->mediaRules(Publication::PORTFOLIO_VIDEO, true),
            'audio' => $this->mediaRules(Publication::PORTFOLIO_AUDIO, true),
            'is_guest_visible' => ['required', 'boolean'],
            'pricing_type' => ['required', Rule::in(Publication::pricingTypes())],
            'price' => ['required_if:pricing_type,'.Publication::PRICING_POINTS, 'prohibited_unless:pricing_type,'.Publication::PRICING_POINTS, 'nullable', 'numeric', 'decimal:0,2', 'min:0.01', 'max:999999999999.99'],
            'price_basis' => ['required_if:pricing_type,'.Publication::PRICING_POINTS, 'prohibited_unless:pricing_type,'.Publication::PRICING_POINTS, 'nullable', Rule::in(Publication::priceBases())],
        ];
    }

    protected function mediaRules(string $type, bool $required): array
    {
        $extensions = PublicationMedia::extensionsFor($type);
        $rules = [
            'prohibited_unless:portfolio_type,'.$type,
            'nullable',
            'file',
            'extensions:'.implode(',', $extensions),
            'mimes:'.implode(',', $extensions),
            'mimetypes:'.implode(',', PublicationMedia::mimeTypesFor($type)),
            'max:'.PublicationMedia::MAX_SIZE_KILOBYTES,
        ];

        if ($required) {
            array_unshift($rules, 'required_if:portfolio_type,'.$type);
        }

        return $rules;
    }

    protected function allowedPortfolioTypes(): array
    {
        return app(SeekerSettings::class)->enabledPortfolioTypes();
    }

    public function messages(): array
    {
        return [
            'portfolio_type.in' => trans('seeker::messages.validation.portfolio_type_unavailable'),
        ];
    }
}
