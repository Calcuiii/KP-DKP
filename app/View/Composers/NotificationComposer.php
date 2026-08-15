<?php

namespace App\View\Composers;

use App\Models\ChatMessage;
use App\Models\KnowledgeBaseDocument;
use Illuminate\View\View;

class NotificationComposer
{
    public function compose(View $view): void
    {
        $unansweredCount = ChatMessage::where('role', 'assistant')
            ->where('status', 'insufficient_information')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $failedDocsCount = KnowledgeBaseDocument::where('status', 'Failed')->count();

        $view->with('notifications', [
            'unanswered_count' => $unansweredCount,
            'failed_docs_count' => $failedDocsCount,
            'total' => $unansweredCount + $failedDocsCount,
        ]);
    }
}