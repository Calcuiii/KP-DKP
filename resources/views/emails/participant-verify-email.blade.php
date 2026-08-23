<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verifikasi akun peserta SI-MELAYUR</title>
</head>
<body style="margin:0;background:#f3f7fb;color:#0b2447;font-family:Arial,Helvetica,sans-serif;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f3f7fb;padding:32px 12px;">
    <tr>
        <td align="center">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:620px;overflow:hidden;border:1px solid #dbe7f2;border-radius:24px;background:#ffffff;box-shadow:0 12px 32px rgba(11,36,71,.08);">
                <tr>
                    <td style="padding:30px 34px;background:linear-gradient(135deg,#0b2447,#1769aa);color:#ffffff;">
                        <table role="presentation" cellspacing="0" cellpadding="0">
                            <tr>
                                <td style="width:52px;height:52px;border-radius:16px;background:#ffffff;text-align:center;vertical-align:middle;color:#0d9488;font-size:25px;font-weight:800;">S</td>
                                <td style="padding-left:16px;">
                                    <div style="font-size:22px;font-weight:800;letter-spacing:.4px;">SI-MELAYUR</div>
                                    <div style="margin-top:4px;color:#cfe8ff;font-size:13px;line-height:1.5;">Portal Peserta DKP Provinsi Jawa Timur</div>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding:36px 34px 30px;">
                        <div style="display:inline-block;padding:7px 12px;border-radius:999px;background:#e8f7f5;color:#0d9488;font-size:12px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;">Verifikasi akun</div>
                        <h1 style="margin:22px 0 12px;color:#0b2447;font-size:27px;line-height:1.25;">Selamat datang, {{ $participant->name }}!</h1>
                        <p style="margin:0;color:#567394;font-size:16px;line-height:1.75;">Terima kasih telah mendaftar sebagai peserta SI-MELAYUR. Konfirmasikan alamat email ini agar akun Anda aktif dan Portal Peserta dapat digunakan.</p>

                        <table role="presentation" cellspacing="0" cellpadding="0" style="margin:28px 0;">
                            <tr>
                                <td style="border-radius:12px;background:#1769aa;">
                                    <a href="{{ $verificationUrl }}" style="display:inline-block;padding:15px 24px;color:#ffffff;text-decoration:none;font-size:15px;font-weight:700;">Verifikasi Email Saya</a>
                                </td>
                            </tr>
                        </table>

                        <div style="padding:16px 18px;border:1px solid #d9e7f3;border-radius:14px;background:#f7fafc;color:#567394;font-size:13px;line-height:1.65;">Tautan ini berlaku selama {{ config('auth.verification.expire', 60) }} menit. Jika Anda tidak merasa membuat akun SI-MELAYUR, abaikan email ini.</div>

                        <p style="margin:28px 0 8px;color:#0b2447;font-size:14px;font-weight:700;">Tombol tidak dapat dibuka?</p>
                        <p style="margin:0;color:#6a819b;font-size:12px;line-height:1.6;word-break:break-all;">Salin tautan berikut ke browser:<br><a href="{{ $verificationUrl }}" style="color:#1769aa;">{{ $verificationUrl }}</a></p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:22px 34px;border-top:1px solid #e4edf5;background:#f8fbfd;color:#73879c;font-size:12px;line-height:1.6;">Email otomatis dari SI-MELAYUR — Sistem Informasi Magang, Penelitian, dan Data Kelautan Jawa Timur.</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
