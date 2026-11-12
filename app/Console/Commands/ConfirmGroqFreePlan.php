<?php

namespace App\Console\Commands;

use App\Services\FreeGroqPolicy;
use Illuminate\Console\Command;

class ConfirmGroqFreePlan extends Command
{
    protected $signature = 'chatbot:confirm-free-plan {--revoke : Disable external AI and clear its account confirmation}';

    protected $description = 'Bind an operator-confirmed institution-owned Groq Free Plan to the current key, or revoke access';

    public function handle(): int
    {
        $values = ['GROQ_ENABLED' => 'false', 'GROQ_ACCOUNT_OWNER' => 'unset', 'GROQ_FREE_PLAN_CONFIRMED' => 'false', 'GROQ_FREE_PLAN_KEY_SHA256' => ''];
        if (! $this->option('revoke')) {
            $key = config('services.groq.api_key');
            if (! is_string($key) || trim($key) === '' || config('services.groq.model') !== FreeGroqPolicy::MODEL) {
                $this->error('Isi API key akun dinas dan model yang didukung pada konfigurasi, lalu jalankan config:clear.');

                return self::FAILURE;
            }
            $this->warn('API tidak membuktikan kepemilikan atau paket billing. Periksa dashboard Groq sebelum mengonfirmasi.');
            foreach (['Apakah akun Groq ini milik dinas, bukan akun pribadi?',
                'Apakah akun ini pada Free Plan, tanpa paket billing berbayar?',
                'Aktifkan Groq untuk akun dinas yang sudah Anda periksa ini?'] as $question) {
                if (! $this->confirm($question, false)) {
                    return self::FAILURE;
                }
            }
            $values = ['GROQ_ENABLED' => 'true', 'GROQ_ACCOUNT_OWNER' => 'dkp', 'GROQ_FREE_PLAN_CONFIRMED' => 'true',
                'GROQ_FREE_PLAN_KEY_SHA256' => hash('sha256', $key)];
        }
        $path = app()->environmentFilePath();
        if (! is_file($path)) {
            $this->error('File konfigurasi environment belum tersedia.');

            return self::FAILURE;
        }
        $contents = file_get_contents($path);
        foreach ($values as $name => $value) {
            $pattern = '/^'.preg_quote($name, '/').'=.*$/m';
            $contents = preg_match($pattern, $contents) ? preg_replace($pattern, $name.'='.$value, $contents)
                : rtrim($contents)."\n".$name.'='.$value."\n";
        }
        if (file_put_contents($path, $contents, LOCK_EX) === false) {
            $this->error('Konfigurasi tidak dapat disimpan.');

            return self::FAILURE;
        }
        $this->call('config:clear');
        $this->info($this->option('revoke') ? 'Akses AI eksternal dinonaktifkan. Jawaban KB tetap tersedia.'
            : 'Konfirmasi tersimpan untuk key ini. Bangun ulang config cache dan restart proses aplikasi.');

        return self::SUCCESS;
    }
}
