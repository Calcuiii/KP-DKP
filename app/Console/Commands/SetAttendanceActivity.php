<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\ParticipantApplication;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

final class SetAttendanceActivity extends Command
{
    protected $signature = 'attendance:set-active {application : ID pengajuan} {state : active, inactive, atau unset} {--operator= : ID superadmin penanggung jawab} {--force : Tanpa konfirmasi interaktif}';

    protected $description = 'Tetapkan keaktifan administratif presensi per pengajuan Magang/KP/PKL';

    public function handle(): int
    {
        $operator = User::where('role', 'superadmin')->where('status', 'Aktif')->find($this->option('operator'));
        if (! $operator) {
            $this->error('Isi --operator dengan ID superadmin aktif yang bertanggung jawab.');

            return self::FAILURE;
        }
        $state = $this->argument('state');
        if (! in_array($state, ['active', 'inactive', 'unset'], true)) {
            $this->error('State harus active, inactive, atau unset.');

            return self::FAILURE;
        }
        $application = ParticipantApplication::where('service_type', ParticipantApplication::SERVICE_MAGANG_PKL)
            ->find($this->argument('application'));
        if (! $application) {
            $this->error('Pengajuan magang tidak ditemukan.');

            return self::FAILURE;
        }
        if ($state === 'active' && ($application->decision !== 'accepted' || $application->completed_at
            || ! $application->official_started_at || ! $application->official_ended_at
            || $application->official_ended_at->lt($application->official_started_at))) {
            $this->error('Aktivasi memerlukan keputusan accepted, periode valid, dan kegiatan belum ditandai selesai.');

            return self::FAILURE;
        }
        if (! $this->option('force') && ! $this->confirm('Ubah pengajuan #'.$application->id.' milik '.$application->participant->name.' menjadi '.$state.'?', false)) {
            return self::FAILURE;
        }
        DB::transaction(function () use ($application, $state, $operator): void {
            $before = $application->attendance_active;
            $application->forceFill(['attendance_active' => match ($state) {
                'active' => true, 'inactive' => false, default => null,
            }])->save();
            ActivityLog::create([
                'user_id' => $operator->id, 'action' => 'Set attendance activity', 'module' => 'Attendance integration',
                'description' => 'Application #'.$application->id.': '.json_encode($before).' -> '.$state.' (server command)',
            ]);
        });
        $this->info('Keaktifan pengajuan #'.$application->id.' diperbarui menjadi '.$state.'.');

        return self::SUCCESS;
    }
}
