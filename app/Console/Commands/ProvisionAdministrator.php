<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ProvisionAdministrator extends Command
{
    protected $signature = 'admin:provision {email} {--name=} {--reset-existing}';

    protected $description = 'Provision the super administrator using a hidden password prompt';

    public function handle(): int
    {
        $email = mb_strtolower(trim($this->argument('email')));
        $existing = User::where('email', $email)->first();
        if (($existing && (! $this->option('reset-existing') || ! $existing->isSuperAdmin()))
            || (! $existing && User::where('role', 'superadmin')->exists())) {
            $this->error('Superadmin sudah tersedia atau email telah digunakan. Gunakan pengelolaan admin yang ada.');

            return self::FAILURE;
        }
        $data = ['email' => $email, 'name' => $this->option('name') ?: $this->ask('Nama administrator'),
            'password' => $this->secret('Password unik (minimal 12 karakter, huruf besar/kecil dan angka)'),
            'password_confirmation' => $this->secret('Ulangi password')];
        $validator = Validator::make($data, ['email' => ['required', 'email', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::min(12)->mixedCase()->numbers()]]);
        if ($validator->fails()) {
            $this->error('Data tidak valid. Periksa email, nama, serta panjang dan konfirmasi password.');

            return self::FAILURE;
        }
        $user = $existing ?? new User;
        $user->fill(['name' => $data['name'], 'email' => $email, 'password' => $data['password'],
            'role' => 'superadmin', 'status' => 'Aktif']);
        $user->save();
        $this->info('Superadmin tersimpan. Password tidak ditampilkan.');

        return self::SUCCESS;
    }
}
