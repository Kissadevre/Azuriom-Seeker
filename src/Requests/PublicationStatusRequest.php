<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Publication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PublicationStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $publication = $this->route('publication');

        if (! $publication instanceof Publication || $this->user() === null) {
            return false;
        }

        if ($this->user()->can('seeker.moderate')) {
            return true;
        }

        return $publication->user_id === $this->user()->id
            && $publication->status !== Publication::STATUS_HIDDEN;
    }

    public function rules(): array
    {
        $statuses = $this->user()?->can('seeker.moderate')
            ? Publication::statuses()
            : [Publication::STATUS_ACTIVE, Publication::STATUS_CLOSED];

        return [
            'status' => ['required', Rule::in($statuses)],
        ];
    }
}
