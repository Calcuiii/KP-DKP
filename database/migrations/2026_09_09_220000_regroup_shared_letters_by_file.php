<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('participant_application_documents')
            ->where('type', 'request_letter')
            ->whereNotNull('letter_institution')
            ->orderBy('id')->each(function ($document) {
                $path = Storage::disk('local')->path($document->file_path);
                $institution = mb_strtolower(preg_replace('/\s+/u', ' ', trim($document->letter_institution)));
                $key = is_file($path) ? hash('sha256', json_encode(
                    ['file-v1', $institution, hash_file('sha256', $path)], JSON_UNESCAPED_UNICODE
                )) : null;
                DB::table('participant_application_documents')->where('id', $document->id)
                    ->update(['letter_group_key' => $key]);
            });
    }

    public function down(): void
    {
        DB::table('participant_application_documents')->where('type', 'request_letter')
            ->whereNotNull('letter_institution')->orderBy('id')->each(function ($document) {
                $normalize = fn ($value) => mb_strtolower(preg_replace('/\s+/u', ' ', trim($value)));
                DB::table('participant_application_documents')->where('id', $document->id)->update([
                    'letter_group_key' => filled($document->letter_number) ? hash('sha256', json_encode(
                        [$normalize($document->letter_institution), $normalize($document->letter_number)], JSON_UNESCAPED_UNICODE
                    )) : null,
                ]);
            });
    }
};
