<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Conversation;
use Azuriom\Plugin\Seeker\Models\ConversationReport;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreConversationReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = $this->route('conversation');

        return $conversation instanceof Conversation
            && $this->user() !== null
            && $conversation->includes($this->user());
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', Rule::in(ConversationReport::reasons())],
            'details' => ['required', 'string', 'min:20', 'max:2000'],
        ];
    }
}
