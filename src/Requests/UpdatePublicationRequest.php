<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Publication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdatePublicationRequest extends StorePublicationRequest
{
    public function authorize(): bool
    {
        $publication = $this->route('publication');

        return $publication instanceof Publication
            && $this->user() !== null
            && $publication->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            'images' => ['nullable', 'prohibited_unless:portfolio_type,'.Publication::PORTFOLIO_IMAGES, 'array', 'max:6'],
            'remove_images' => ['nullable', 'array'],
            'remove_images.*' => ['integer', Rule::exists('seeker_publication_images', 'id')],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $publication = $this->route('publication');
                $removed = $publication->images()
                    ->whereIn('id', array_unique($this->input('remove_images', [])))
                    ->count();
                $uploaded = count($this->file('images', []));
                $remaining = $publication->images()->count() - $removed + $uploaded;

                if ($this->input('portfolio_type') === Publication::PORTFOLIO_IMAGES && $remaining < 1) {
                    $validator->errors()->add('images', trans('seeker::messages.validation.images_required'));
                }

                if ($this->input('portfolio_type') === Publication::PORTFOLIO_IMAGES && $remaining > 6) {
                    $validator->errors()->add('images', trans('seeker::messages.validation.max_images'));
                }
            },
        ];
    }
}
