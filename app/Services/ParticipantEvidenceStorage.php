<?php

namespace App\Services;

use App\Models\ParticipantApplication;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ParticipantEvidenceStorage
{
    public function store(ParticipantApplication $application, UploadedFile $file, string $stage, array $attributes = []): void
    {
        $field = $stage === 'presentation' ? 'presentation_photo_path' : 'completion_form_proof_path';
        $directory = $stage === 'presentation' ? 'presentation-proofs' : 'completion-form-proofs';
        $path = $file->store($directory.'/'.$application->id, 'local');
        abort_unless(is_string($path), 500);
        $old = null;
        try {
            DB::transaction(function () use ($application, $stage, $field, $path, $attributes, &$old) {
                $locked = ParticipantApplication::query()->lockForUpdate()->findOrFail($application->id);
                abort_unless($locked->service_type === 'magang_pkl' && in_array($locked->decision, ['accepted', 'approved', 'diterima'], true), 403);
                abort_if($locked->completed_at !== null || $locked->certificate_published_at !== null, 422, 'Pengajuan telah diselesaikan; bukti tidak dapat diubah.');
                if ($stage === 'presentation') {
                    abort_if($locked->completion_form_submitted_at !== null, 422, 'Tahap 7 sudah dikirim; bukti tahap 6 tidak dapat diubah.');
                } else {
                    abort_unless($locked->presentation_submitted_at !== null, 422, 'Selesaikan tahap 6 terlebih dahulu.');
                }
                $old = $locked->{$field};
                $locked->update([...$attributes, $field => $path,
                    ($stage === 'presentation' ? 'presentation_submitted_at' : 'completion_form_submitted_at') => now()]);
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }
        if ($old && $old !== $path) {
            Storage::disk('local')->delete($old);
        }
    }
}
