<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Publication;
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
        ];
    }
}
