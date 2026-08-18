<?php

namespace App\Http\Requests\Peserta;

use App\Models\ParticipantApplicationDocument;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UploadWoppsDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('peserta') !== null;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'required',
                'string',
                Rule::in([
                    ParticipantApplicationDocument::TYPE_WOPPS_IDENTITY,
                    ParticipantApplicationDocument::TYPE_WOPPS_REQUEST_LETTER,
                    ParticipantApplicationDocument::TYPE_WOPPS_PROPOSAL,
                    ParticipantApplicationDocument::TYPE_WOPPS_ETHICS,
                ]),
            ],

            'document' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'type.required' => 'Jenis dokumen wajib dipilih.',
            'type.in' => 'Jenis dokumen WOPPS tidak valid.',
            'document.required' => 'Silakan pilih dokumen terlebih dahulu.',
            'document.file' => 'File yang dipilih tidak valid.',
            'document.max' => 'Ukuran file maksimal 10 MB.',
            'document.mimes' => 'Format file yang diterima adalah PDF, DOC, DOCX, JPG, JPEG, atau PNG.',
        ];
    }
}