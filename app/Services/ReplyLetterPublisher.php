<?php

namespace App\Services;

use App\Models\ParticipantApplication;
use App\Models\ReplyLetter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

final class ReplyLetterPublisher
{
    public function publish(ParticipantApplication $application, UploadedFile $file, array $attributes): ReplyLetter
    {
        $path = $file->store('reply-letters', 'local');
        abort_unless(is_string($path), 500, 'Surat balasan gagal disimpan.');
        $oldPath = null;
        try {
            $letter = DB::transaction(function () use ($application, $file, $path, $attributes, &$oldPath) {
                $application = ParticipantApplication::query()->lockForUpdate()->findOrFail($application->id);
                abort_if($application->completed_at !== null, 422, 'Pengajuan sudah selesai.');
                $oldPath = $application->replyLetter?->file_path;
                $letter = ReplyLetter::updateOrCreate(
                    ['participant_application_id' => $application->id],
                    ['participant_id' => $application->participant_id, 'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(), 'sent_at' => now()],
                );
                $application->update([...$attributes, 'response_letter_path' => $path]);

                return $letter;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }
        if ($oldPath && $oldPath !== $path) {
            Storage::disk('local')->delete($oldPath);
        }

        return $letter;
    }
}
