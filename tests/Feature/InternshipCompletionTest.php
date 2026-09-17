<?php

namespace Tests\Feature;

use App\Models\Participant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternshipCompletionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guide_replaces_checklist_without_changing_existing_records(): void
    {
        $participant = Participant::factory()->create(['email_verified_at' => now()]);
        $application = $participant->applications()->create([
            'service_type' => 'magang_pkl', 'status' => 'accepted', 'decision' => 'accepted',
            'official_started_at' => now()->subMonth(), 'official_ended_at' => now()->subDay(),
            'completion_checklist' => ['report', 'slides'],
            'presentation_date' => now()->subDay(), 'presentation_submitted_at' => now(),
        ]);
        $this->actingAs($participant, 'peserta')->get(route('peserta.dashboard'))
            ->assertOk()->assertSee('Selesaikan magang Anda')
            ->assertSee('SelesaiMagangPKL-SM')->assertSee('SelesaiMagangPKL-PT')
            ->assertDontSee('https://bit.ly/Surat_Permohonan_DKP')
            ->assertSee('Penerimaan sertifikat')
            ->assertSee('Permohonan sertifikat tidak dapat diajukan setelah magang selesai.')
            ->assertSee('File presentasi paparan hasil kegiatan')
            ->assertSee('Foto atau dokumentasi selama kegiatan')
            ->assertSee('Foto atau dokumentasi saat paparan')
            ->assertSee('Laporan kegiatan magang/PKL/penelitian.')
            ->assertSee('yang dikeluarkan Fakultas')
            ->assertSee('(dari Sekolah)')
            ->assertSee('id="form-selesai"', false)
            ->assertSee('id="penerimaan-sertifikat"', false)
            ->assertDontSee('Nama kolom di atas mengikuti formulir')
            ->assertDontSee('Simpan checklist saya')->assertDontSee('name="steps[]"', false);
        $this->assertSame(['report', 'slides'], $application->fresh()->completion_checklist);
        $this->assertSame('accepted', $application->fresh()->status);
        $this->assertNull($application->fresh()->completed_at);
        $this->patch('/peserta/pengajuan/'.$application->id.'/penyelesaian', ['steps' => []])->assertNotFound();
    }
}
