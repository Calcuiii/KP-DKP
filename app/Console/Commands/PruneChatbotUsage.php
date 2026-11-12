<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PruneChatbotUsage extends Command
{
    protected $signature = 'chatbot:prune-usage {--days=30}';

    protected $description = 'Prune old anonymous AI counters; never deletes participant files or conversations';

    public function handle(): int
    {
        $days = max(7, min(365, (int) $this->option('days')));
        $count = DB::table('chatbot_ai_counters')->where('expires_at', '<', now()->subDays($days))->delete();
        $this->info("{$count} penghitung lama dihapus.");

        return self::SUCCESS;
    }
}
