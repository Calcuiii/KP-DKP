<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class PrivatizeDocuments extends Command
{
    protected $signature = 'documents:privatize {--force : Confirm moving existing public official documents}';

    protected $description = 'Back up and move reply letters and uploaded knowledge documents to private storage';

    public function handle(): int
    {
        if (! $this->option('force') && ! $this->confirm('Pindahkan dokumen resmi dari publik ke privat dengan cadangan?', false)) {
            return self::FAILURE;
        }
        $public = Storage::disk('public');
        $private = Storage::disk('local');
        $backup = 'migration-backups/'.now()->format('YmdHis').'-'.bin2hex(random_bytes(4));
        $count = 0;
        foreach (['reply-letters', 'knowledge-base'] as $directory) {
            foreach ($public->allFiles($directory) as $path) {
                $contents = $public->get($path);
                $hash = hash('sha256', $contents);
                if ($private->exists($path) && hash('sha256', $private->get($path)) !== $hash) {
                    throw new RuntimeException('Konflik isi dokumen privat. Pemindahan dihentikan tanpa menimpa berkas.');
                }
                if (! $private->put($backup.'/'.$path, $contents)
                    || ! $private->put($path, $contents)
                    || hash('sha256', $private->get($path)) !== $hash
                    || hash('sha256', $private->get($backup.'/'.$path)) !== $hash) {
                    throw new RuntimeException('Verifikasi salinan gagal; sumber publik dipertahankan.');
                }
                if (! $public->delete($path)) {
                    throw new RuntimeException('Salinan privat tersimpan, tetapi sumber publik belum terhapus.');
                }
                $count++;
            }
        }
        $this->info("{$count} dokumen dipindahkan. Cadangan privat: {$backup}");

        return self::SUCCESS;
    }
}
