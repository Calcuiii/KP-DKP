<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\FreeGroqPolicy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class CheckDeployment extends Command
{
    protected $signature = 'deployment:check';

    protected $description = 'Read-only production readiness checks; never prints credentials';

    public function handle(): int
    {
        $checks = [
            'Environment production' => app()->isProduction(),
            'Debug nonaktif' => config('app.debug') === false,
            'APP_KEY tersedia' => filled(config('app.key')),
            'Cache persisten untuk batas chatbot' => in_array(config('cache.default'), ['database', 'redis'], true),
            'URL publik HTTPS' => str_starts_with(config('app.url'), 'https://') && ! str_contains(config('app.url'), '.invalid'),
            'Cookie Secure + HttpOnly' => config('session.secure') && config('session.http_only'),
            'Build manifest tersedia' => is_file(public_path('build/manifest.json')),
            'Penanda Vite development tidak ada' => ! is_file(public_path('hot')),
            'Dokumen resmi tidak ada di disk publik' => Storage::disk('public')->allFiles('reply-letters') === [] && Storage::disk('public')->allFiles('knowledge-base') === [],
            'Transport email nyata' => in_array(config('mail.default'), ['smtp', 'ses', 'postmark', 'resend'], true),
            'Buku Tamu dikonfigurasi' => filled(config('services.google_guestbook.form_url')) && filled(config('services.google_guestbook.spreadsheet_id')),
        ];
        $checks['Superadmin aktif dengan password bukan default'] = User::where('role', 'superadmin')->where('status', 'Aktif')->get()
            ->contains(fn (User $user) => ! Hash::check('password123', $user->password));
        $checks['Tidak ada admin aktif berpassword default'] = ! User::whereIn('role', ['admin', 'superadmin'])->where('status', 'Aktif')->get()
            ->contains(fn (User $user) => Hash::check('password123', $user->password) || Hash::check('password', $user->password));
        $checks['AI eksternal nonaktif atau akun dinas Free Plan terkonfirmasi untuk key aktif'] = ! config('services.groq.enabled')
            || app(FreeGroqPolicy::class)->allows(config('services.groq.api_key'), config('services.groq.model'));
        foreach ($checks as $label => $ok) {
            $this->line(($ok ? 'OK ' : 'GAGAL ').$label);
        }

        return in_array(false, array_map(fn ($v) => (bool) $v, $checks), true) ? self::FAILURE : self::SUCCESS;
    }
}
