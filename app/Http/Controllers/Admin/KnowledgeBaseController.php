<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreKnowledgeBaseDocumentRequest;
use App\Models\ActivityLog;
use App\Models\KnowledgeBaseDocument;
use App\Services\KnowledgeBaseDocumentIndexer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class KnowledgeBaseController extends Controller
{
    public function index(Request $request): View
    {
        $documents = KnowledgeBaseDocument::query()
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category, fn ($q) => $q->where('category', $request->category))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $metrics = [
            'total' => KnowledgeBaseDocument::count(),
            'active' => KnowledgeBaseDocument::where('status', 'Ready')->count(),
            'chunks' => KnowledgeBaseDocument::sum('chunks_count'),
            'failed' => KnowledgeBaseDocument::where('status', 'Failed')->count(),
        ];

        return view('pages.admin.knowledge-base', compact('documents', 'metrics'));
    }

    public function store(
        StoreKnowledgeBaseDocumentRequest $request,
        KnowledgeBaseDocumentIndexer $indexer,
    ): RedirectResponse {
        $file = $request->file('file');
        $path = $file->store('knowledge-base', 'public');

        $document = KnowledgeBaseDocument::create([
            'name' => $request->name,
            'category' => $request->category,
            'type' => strtoupper($file->getClientOriginalExtension()),
            'version' => $request->version,
            'description' => $request->description,
            'effective_date' => $request->effective_date,
            'file_path' => $path,
            'status' => 'Pending',
            'index_status' => 'Pending',
            'chunks_count' => 0,
            'uploaded_by' => auth()->id(),
        ]);

        ActivityLog::record('Upload', 'Knowledge Base', "Mengunggah dokumen \"{$request->name}\"");

        return $this->process($document, $indexer, 'Dokumen berhasil diunggah');
    }

    public function destroy(KnowledgeBaseDocument $document): RedirectResponse
    {
        Storage::disk('public')->delete($document->file_path);
        ActivityLog::record('Delete', 'Knowledge Base', "Menghapus dokumen \"{$document->name}\"");
        $document->delete();

        return redirect()->route('admin.knowledge-base')
            ->with('status', 'Dokumen berhasil dihapus.');
    }

    public function reindex(
        KnowledgeBaseDocument $document,
        KnowledgeBaseDocumentIndexer $indexer,
    ): RedirectResponse {
        return $this->process($document, $indexer, 'Dokumen berhasil diproses ulang');
    }

    private function process(
        KnowledgeBaseDocument $document,
        KnowledgeBaseDocumentIndexer $indexer,
        string $successMessage,
    ): RedirectResponse {
        $document->update(['status' => 'Processing', 'index_status' => 'Processing']);

        try {
            $chunksCount = $indexer->index($document);

            $document->update([
                'status' => 'Ready',
                'index_status' => 'Ready',
                'chunks_count' => $chunksCount,
            ]);

            return redirect()->route('admin.knowledge-base')
                ->with('status', $successMessage.' dan siap digunakan oleh chatbot.');
        } catch (Throwable $exception) {
            report($exception);

            $document->update([
                'status' => 'Failed',
                'index_status' => 'Failed',
                'chunks_count' => 0,
            ]);

            return redirect()->route('admin.knowledge-base')
                ->with('error', 'Dokumen gagal diproses: '.$exception->getMessage());
        }
    }
}
