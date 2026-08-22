<?php

namespace App\Http\Requests\Peserta;

use Illuminate\Foundation\Http\FormRequest;

final class UploadWoppsFormProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('peserta') !== null;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'wopps_form_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'wopps_form_declaration' => ['accepted'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'wopps_form_proof.required' => 'Bukti pengisian Form WOPPS wajib diunggah.',
            'wopps_form_proof.mimes' => 'Bukti harus berupa JPG, PNG, atau PDF.',
            'wopps_form_proof.max' => 'Ukuran bukti maksimal 5 MB.',
            'wopps_form_declaration.accepted' => 'Konfirmasi pengisian Form WOPPS wajib disetujui.',
        ];
    }
}
