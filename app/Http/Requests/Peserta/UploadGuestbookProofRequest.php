<?php

namespace App\Http\Requests\Peserta;

use Illuminate\Foundation\Http\FormRequest;

final class UploadGuestbookProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('peserta') !== null;
    }

    public function rules(): array
    {
        return [
            'guestbook_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'guestbook_declaration' => ['accepted'],
        ];
    }
}
