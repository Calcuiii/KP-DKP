<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\ParticipantApplication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

final class VerifyAttendanceParticipantController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $email = $request->input('email');
        $email = is_string($email) ? mb_strtolower(trim($email)) : $email;
        if (Validator::make(['email' => $email], ['email' => ['required', 'string', 'email:rfc', 'max:255']])->fails()) {
            return $this->error('INVALID_EMAIL', 'A valid email address is required.', 422);
        }
        // Legacy registration preserves case; do not strip dots or plus aliases.
        $participants = Participant::query()->whereRaw('LOWER(email) = ?', [$email])
            ->limit(2)->get(['id', 'email', 'name']);
        if ($participants->isEmpty()) {
            return $this->error('PARTICIPANT_NOT_FOUND', 'Participant not found.', 404);
        }
        if ($participants->count() !== 1) {
            return $this->error('AMBIGUOUS_PARTICIPANT', 'Multiple participants match this email.', 409);
        }
        $participant = $participants->first();
        $applications = $participant->applications()->where('service_type', ParticipantApplication::SERVICE_MAGANG_PKL)
            ->limit(2)->get();
        if ($applications->isEmpty()) {
            return $this->error('INTERNSHIP_APPLICATION_NOT_FOUND', 'No internship application found.', 404);
        }
        if ($applications->count() !== 1) {
            return $this->error('AMBIGUOUS_APPLICATION', 'Multiple internship applications require an explicit selection rule.', 409);
        }
        $application = $applications->first();
        $status = match ($application->decision) {
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            null, '' => 'pending',
            default => null,
        };
        if ($status === null) {
            return $this->error('UNSUPPORTED_DECISION', 'The official admission decision is not recognized.', 409);
        }
        if (! $participant->name || $application->attendance_active === null
            || ! $application->official_started_at || ! $application->official_ended_at) {
            return $this->error('PARTICIPANT_DATA_INCOMPLETE', 'Official identity, activity decision or period is incomplete.', 409);
        }
        $start = $application->official_started_at->copy()->timezone('Asia/Jakarta')->toDateString();
        $end = $application->official_ended_at->copy()->timezone('Asia/Jakarta')->toDateString();
        if ($end < $start) {
            return $this->error('INVALID_PERIOD', 'The official end date precedes the start date.', 409);
        }

        return response()->json(['participant' => [
            'id' => (string) $application->id,
            'email' => mb_strtolower(trim($participant->email)),
            'full_name' => $participant->name,
            'status' => $status,
            'is_active' => $status === 'accepted' && $application->attendance_active && $application->completed_at === null,
            'start_date' => $start,
            'end_date' => $end,
        ]])->header('Cache-Control', 'no-store');
    }

    private function error(string $code, string $message, int $status): JsonResponse
    {
        return response()->json(['error' => compact('code', 'message')], $status)->header('Cache-Control', 'no-store');
    }
}
