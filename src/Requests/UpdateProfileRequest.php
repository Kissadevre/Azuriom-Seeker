<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Support\SeekerPermissions;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && $this->user() !== null
            && $user->id === $this->user()->id
            && $this->user()->can(SeekerPermissions::EDIT_OWN_BIOGRAPHY);
    }

    public function rules(): array
    {
        return [
            'bio' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
