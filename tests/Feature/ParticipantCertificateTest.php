<?php

namespace Tests\Feature;

use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ParticipantCertificateTest extends TestCase
{
    use RefreshDatabase;

    public function test_certificate_management_requires_an_active_superadmin(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'Aktif']);
        $this->actingAs($admin)->get(route('admin.sertifikat'))->assertForbidden();
        $admin->update(['role' => 'superadmin']);
        $this->get(route('admin.sertifikat'))->assertOk();
        $admin->update(['status' => 'Nonaktif']);
        $this->get(route('admin.sertifikat'))->assertForbidden();
    }

    public function test_existing_certificate_remains_available_only_to_its_owner_after_completion(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('certificates/existing.pdf', 'test');
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => 'magang_pkl', 'decision' => 'accepted',
            'presentation_submitted_at' => now(), 'completion_form_submitted_at' => now(),
            'certificate_path' => 'certificates/existing.pdf',
        ]);
        $this->actingAs($participant, 'peserta')->get(route('peserta.certificate.download', $application))->assertDownload('sertifikat-magang.pdf');
        $other = Participant::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($other, 'peserta')->get(route('peserta.certificate.download', $application))->assertForbidden();
    }
}
