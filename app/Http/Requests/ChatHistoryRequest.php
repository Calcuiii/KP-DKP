<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ChatHistoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'session_key' => ['required', 'uuid'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->hasHeader('X-Chat-Session')) {
            $this->merge(['session_key' => $this->header('X-Chat-Session')]);
        }
    }
}
