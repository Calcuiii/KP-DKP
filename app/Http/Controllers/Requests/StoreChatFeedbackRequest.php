<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreChatFeedbackRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_key' => ['required', 'uuid'],
            'rating' => ['required', 'in:positive,negative'],
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }
}
