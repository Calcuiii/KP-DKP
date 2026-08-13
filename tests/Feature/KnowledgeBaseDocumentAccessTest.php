<?php

namespace Tests\Feature;

use App\Models\KnowledgeBaseDocument;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KnowledgeBaseDocumentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_super_admin_can_view_document_details_and_download_the_original_file(): void
    {
        Storage::fake('public');
        $superAdmin = User::factory()->create(['role' => 'superadmin']);
        $document = $this->documentFor($superAdmin);
        Storage::disk('public')->put($document->file_path, 'Dokumen panduan magang.');

        $this->actingAs($superAdmin)
            ->get(route('admin.knowledge-base.show', $document))
            ->assertOk()
            ->assertSee($document->name)
            ->assertSee('Unduh File Asli')
            ->assertSee('Teks Hasil Pemrosesan');

        $this->actingAs($superAdmin)
            ->get(route('admin.knowledge-base.download', $document))
            ->assertOk()
            ->assertDownload('panduan-magang.docx');
    }

    public function test_a_regular_admin_cannot_access_document_details_or_downloads(): void
    {
        $regularAdmin = User::factory()->create(['role' => 'admin']);
        $document = $this->documentFor($regularAdmin);

        $this->actingAs($regularAdmin)
            ->get(route('admin.knowledge-base.show', $document))
            ->assertForbidden();

        $this->actingAs($regularAdmin)
            ->get(route('admin.knowledge-base.download', $document))
            ->assertForbidden();
    }

    private function documentFor(User $user): KnowledgeBaseDocument
    {
        return KnowledgeBaseDocument::query()->create([
            'name' => 'Panduan Magang',
            'category' => 'Panduan',
            'type' => 'DOCX',
            'version' => '1.0',
            'file_path' => 'knowledge-base/panduan.docx',
            'status' => 'Ready',
            'index_status' => 'Ready',
            'chunks_count' => 4,
            'uploaded_by' => $user->id,
        ]);
    }
}
