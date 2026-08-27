<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmCommissionCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = $this->route('conversation');

        return $conversation instanceof Conversation
            && $this->user() !== null
            && $conversation->client_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'tip_points' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:999999999999.99'],
            'final_message' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
