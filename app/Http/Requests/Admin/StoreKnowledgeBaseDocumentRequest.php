<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreKnowledgeBaseDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'mimes:pdf,docx,xlsx', 'max:51200'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:SOP,Panduan,FAQ,Template,Peraturan'],
            'version' => ['required', 'string', 'max:20'],
            'effective_date' => ['nullable', 'date'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File dokumen wajib diunggah.',
            'file.mimes' => 'File harus berformat PDF, DOCX, atau XLSX.',
            'file.max' => 'Ukuran file maksimal 50MB.',
        ];
    }
}