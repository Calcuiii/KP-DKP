<?php

namespace App\Http\Requests\Peserta;

use Illuminate\Foundation\Http\FormRequest;

final class UploadInternshipFormProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('peserta') !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'internship_form_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'internship_form_declaration' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'internship_form_proof.required' => 'Bukti pengisian Google Form wajib diunggah.',
            'internship_form_proof.mimes' => 'Bukti harus berupa JPG, PNG, atau PDF.',
            'internship_form_proof.max' => 'Ukuran bukti maksimal 5 MB.',
            'internship_form_declaration.accepted' => 'Konfirmasi pengisian Google Form wajib disetujui.',
        ];
    }
}
