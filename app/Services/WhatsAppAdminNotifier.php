<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\UnansweredEscalation;
use Illuminate\Support\Facades\Http;
use Throwable;

final class WhatsAppAdminNotifier
{
    public function send(UnansweredEscalation $escalation): void
    {
        if (! config('services.whatsapp.enabled')) {
            $escalation->update(['whatsapp_status' => 'skipped']);

            return;
        }

        $token = (string) config('services.whatsapp.access_token');
        $phoneNumberId = (string) config('services.whatsapp.phone_number_id');
        $recipient = (string) config('services.whatsapp.admin_recipient');
        $template = (string) config('services.whatsapp.template_name');

        if ($token === '' || $phoneNumberId === '' || $recipient === '' || $template === '') {
            $escalation->update([
                'whatsapp_status' => 'failed',
                'whatsapp_error' => 'Konfigurasi WhatsApp Cloud API belum lengkap.',
            ]);

            return;
        }

        try {
            $response = Http::withToken($token)
                ->timeout(8)
                ->post('https://graph.facebook.com/'.config('services.whatsapp.graph_version').'/'.$phoneNumberId.'/messages', [
                    'messaging_product' => 'whatsapp',
                    'to' => $recipient,
                    'type' => 'template',
                    'template' => [
                        'name' => $template,
                        'language' => ['code' => config('services.whatsapp.template_language', 'id')],
                        'components' => [[
                            'type' => 'body',
                            'parameters' => [
                                ['type' => 'text', 'text' => $escalation->ticket_code],
                                ['type' => 'text', 'text' => mb_substr($escalation->userMessage->content, 0, 500)],
                            ],
                        ]],
                    ],
                ]);

            $response->throw();

            $escalation->update([
                'whatsapp_status' => 'sent',
                'whatsapp_error' => null,
                'whatsapp_sent_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);
            $escalation->update([
                'whatsapp_status' => 'failed',
                'whatsapp_error' => mb_substr($exception->getMessage(), 0, 1000),
            ]);
        }
    }
}
