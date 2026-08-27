<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class RequestCommissionCompletionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $conversation = $this->route('conversation');

        return $conversation instanceof Conversation
            && $this->user() !== null
            && $conversation->author_id === $this->user()->id;
    }

    public function rules(): array
    {
        /** @var Conversation $conversation */
        $conversation = $this->route('conversation');
        $hourly = $conversation->loadMissing('publication')->isHourlyCommission();

        return [
            'hours' => [
                $hourly ? 'required' : 'prohibited',
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:999999.99',
            ],
        ];
    }
}
