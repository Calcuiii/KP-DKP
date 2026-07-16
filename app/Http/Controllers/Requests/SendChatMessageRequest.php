<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class SendChatMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_key' => ['required', 'uuid'],
            'conversation_id' => ['nullable', 'integer', 'min:1'],
            'message' => ['required', 'string', 'max:500'],
        ];
    }
}
