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

                if ($publication->images()->count() - $removed + $uploaded > 6) {
                    $validator->errors()->add('images', trans('seeker::messages.validation.max_images'));
                }
            },
        ];
    }
}
