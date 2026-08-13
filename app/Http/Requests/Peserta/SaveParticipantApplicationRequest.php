<?php

namespace App\Http\Requests\Peserta;

use App\Models\ParticipantApplication;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class SaveParticipantApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('peserta') !== null;
    }

    /**
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'service_type' => [
                'required',
                'string',
                Rule::in(array_keys(ParticipantApplication::serviceOptions())),
            ],
        ];
    }
}
