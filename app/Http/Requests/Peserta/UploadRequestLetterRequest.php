<?php

namespace App\Http\Requests\Peserta;

use Illuminate\Foundation\Http\FormRequest;

final class UploadRequestLetterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('peserta') !== null;
    }

    public function rules(): array
    {
        return [
            'request_letter' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'letter_declaration' => ['accepted'],
        ];
    }
}
