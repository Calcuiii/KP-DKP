<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ParticipantNotificationController extends Controller
{
    public function read(Request $request, string $notification): RedirectResponse
    {
        $item = $request->user('peserta')->notifications()->findOrFail($notification);
        $item->markAsRead();
        $applicationId = $item->data['application_id'] ?? null;
        if ($applicationId && $request->user('peserta')->applications()->whereKey($applicationId)->exists()) {
            $request->session()->put('participant_active_application_id', (int) $applicationId);
        }

        return redirect()->to($item->data['action_url'] ?? route('peserta.dashboard'));
    }

    public function readAll(Request $request): RedirectResponse
    {
        $request->user('peserta')->unreadNotifications->markAsRead();

        return back()->with('status', 'Semua notifikasi telah ditandai sudah dibaca.');
    }
}
