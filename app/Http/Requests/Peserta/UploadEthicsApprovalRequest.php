<?php

namespace App\Http\Requests\Peserta;

use Illuminate\Foundation\Http\FormRequest;

final class UploadEthicsApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('peserta') !== null;
    }

    public function rules(): array
    {
        return [
            'ethics_approval' => ['required', 'file', 'mimes:pdf', 'max:10240'],
            'ethics_declaration' => ['accepted'],
        ];
    }
}
