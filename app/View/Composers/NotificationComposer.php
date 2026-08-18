<?php

namespace App\View\Composers;

use App\Models\KnowledgeBaseDocument;
use App\Models\UnansweredEscalation;
use Illuminate\View\View;

class NotificationComposer
{
    public function compose(View $view): void
    {
        $unansweredCount = UnansweredEscalation::where('status', '!=', 'resolved')->count();

        $failedDocsCount = KnowledgeBaseDocument::where('status', 'Failed')->count();

        $view->with('notifications', [
            'unanswered_count' => $unansweredCount,
            'failed_docs_count' => $failedDocsCount,
            'total' => $unansweredCount + $failedDocsCount,
        ]);
    }
}
