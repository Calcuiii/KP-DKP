<?php

namespace App\Notifications;

use App\Models\UnansweredEscalation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

final class UnansweredQuestionEscalated extends Notification
{
    use Queueable;

    public function __construct(
        private readonly UnansweredEscalation $escalation,
    ) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        $question = $this->escalation->userMessage?->content ?? 'Pertanyaan pengguna';

        return [
            'type' => 'unanswered_question_escalated',
            'title' => 'Pertanyaan diteruskan ke petugas',
            'message' => $this->escalation->ticket_code.' · '.str($question)->limit(120),
            'ticket_code' => $this->escalation->ticket_code,
            'escalation_id' => $this->escalation->id,
            'action_url' => route('admin.unanswered-questions.show', $this->escalation),
        ];
    }
}
