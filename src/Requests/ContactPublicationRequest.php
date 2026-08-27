<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactPublicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'content' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }
}
