<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Publication;
use Azuriom\Plugin\Seeker\Models\PublicationReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePublicationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $publication = $this->route('publication');

        return $publication instanceof Publication
            && $this->user() !== null
            && $publication->user_id !== $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(PublicationReport::reasons())],
            'details' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }
}
