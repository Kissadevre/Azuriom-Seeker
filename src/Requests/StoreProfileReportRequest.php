<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Models\User;
use Azuriom\Plugin\Seeker\Models\ProfileReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProfileReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->route('user');

        return $user instanceof User
            && $this->user() !== null
            && $user->id !== $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(ProfileReport::reasons())],
            'details' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }
}
