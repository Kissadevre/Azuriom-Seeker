<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationMedia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(Publication::types())],
            'title' => ['required', 'string', 'min:5', 'max:120'],
            'description' => ['required', 'string', 'min:20', 'max:10000'],
            'portfolio_type' => ['required', Rule::in(Publication::portfolioTypes())],
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
}
