<?php

namespace App\Http\Controllers\Peserta;

use App\Http\Controllers\Controller;
use App\Models\ParticipantApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final class WoppsContactConfirmationController extends Controller
{
    public function store(Request $request, ParticipantApplication $application): RedirectResponse
    {
        abort_unless($application->participant_id === $request->user('peserta')->id, 403);
        abort_unless($application->service_type === ParticipantApplication::SERVICE_WOPPS, 404);

        DB::transaction(function () use ($application): void {
            $current = ParticipantApplication::query()->lockForUpdate()->findOrFail($application->id);
            abort_unless($current->decision === 'accepted' && $current->completed_at === null, 422, 'Konfirmasi tersedia setelah pengajuan WOPPS diterima dan selama layanan belum ditutup.');

            if ($current->pic_contacted_at === null) {
                $current->update(['pic_contacted_at' => now(), 'status' => 'wopps_contacted']);
            }
        });

        $request->session()->put('participant_active_application_id', $application->id);

        return redirect()->to(route('peserta.dashboard').'#penyelesaian-wopps')
            ->with('status', 'Konfirmasi sudah dihubungi berhasil disimpan. Silakan mengikuti arahan petugas Dinas.');
    }
}
