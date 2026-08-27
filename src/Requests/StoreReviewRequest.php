<?php

namespace Azuriom\Plugin\Seeker\Requests;

use Azuriom\Plugin\Seeker\Models\Conversation;
use Illuminate\Foundation\Http\FormRequest;

class StoreReviewRequest extends FormRequest
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
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['required', 'string', 'min:10', 'max:500'],
        ];
    }
}
