<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeBase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class KnowledgeBaseController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    private function isHRD()
    {
        return Auth::user()->jabatan === 'HRD';
    }

    public function index()
    {
        $divisiGroups = KnowledgeBase::select('id', 'divisi', 'subdivisi', 'title', 'file_path', 'file_type')
            ->get()
            ->groupBy('divisi')
            ->map(function ($divisiGroup) {
                return $divisiGroup->groupBy('subdivisi');
            });

        return view('knowledgebase.index', compact('divisiGroups'));
    }

    public function store(Request $request)
    {
        if (!$this->isHRD()) {
            abort(403, 'Hanya HRD yang dapat menambah dokumen.');
        }

        $validated = $request->validate([
            'divisi' => 'required|string|max:255',
            'subdivisi' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'file' => 'required|file|mimes:pdf,xlsx,xls|max:10240',
        ], [
            'file.required' => 'File wajib diunggah',
            'file.mimes' => 'Format file harus PDF, XLS, atau XLSX',
            'file.max' => 'Ukuran file maksimal 10MB'
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();
            $sanitizedTitle = Str::slug($validated['title'], '_');
            $filename = $sanitizedTitle . '_' . time() . '.' . $extension;
            $path = $file->storeAs('knowledgebase', $filename, 'public');

            KnowledgeBase::create([
                'divisi' => $validated['divisi'],
                'subdivisi' => $validated['subdivisi'],
                'title' => $validated['title'],
                'file_path' => $path,
                'file_type' => $extension,
                'uploaded_by' => Auth::user()->name ?? Auth::user()->email,
            ]);

            return redirect()->route('knowledgebase.index')
                ->with('success', 'Dokumen berhasil ditambahkan!');


        } catch (\Exception $e) {
            
            if (isset($path) && Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }

            \Log::error('KnowledgeBase Store Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan dokumen.');
        }
    }

    public function update(Request $request, KnowledgeBase $knowledgeBase)
    {
        if (!$this->isHRD()) {
            abort(403, 'Hanya HRD yang dapat mengedit dokumen.');
        }

        $validated = $request->validate([
            'divisi' => 'required|string|max:255',
            'subdivisi' => 'required|string|max:255',
            'title' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:pdf,xlsx,xls|max:10240',
        ]);

        try {
            $updateData = [
                'divisi' => $validated['divisi'],
                'subdivisi' => $validated['subdivisi'],
                'title' => $validated['title'],
            ];

            if ($request->hasFile('file')) {
                if ($knowledgeBase->file_path && Storage::disk('public')->exists($knowledgeBase->file_path)) {
                    Storage::disk('public')->delete($knowledgeBase->file_path);
                }

                $file = $request->file('file');
                $extension = $file->getClientOriginalExtension();
                $sanitizedTitle = Str::slug($validated['title'], '_');
                $filename = $sanitizedTitle . '_' . time() . '.' . $extension;
                $path = $file->storeAs('knowledgebase', $filename, 'public');

                $updateData['file_path'] = $path;
                $updateData['file_type'] = $extension;
            }

            $knowledgeBase->update($updateData);

            return redirect()->route('knowledgebase.index')
                ->with('success', 'Dokumen berhasil diperbarui!');


        } catch (\Exception $e) {
            \Log::error('KnowledgeBase Update Error: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan saat memperbarui dokumen.');
        }
    }

    public function destroy(KnowledgeBase $knowledgeBase)
    {
        if (!$this->isHRD()) {
            abort(403, 'Hanya HRD yang dapat menghapus dokumen.');
        }

        try {
            if ($knowledgeBase->file_path && Storage::disk('public')->exists($knowledgeBase->file_path)) {
                Storage::disk('public')->delete($knowledgeBase->file_path);
            }

            $knowledgeBase->delete();

            return redirect()->route('knowledgebase.index')
                ->with('success', 'Dokumen berhasil dihapus!');


        } catch (\Exception $e) {
            \Log::error('KnowledgeBase Destroy Error: ' . $e->getMessage());
            return redirect()->route('knowledgebase.index')
                ->with('error', 'Gagal menghapus dokumen');
        }
    }

    public function download(KnowledgeBase $knowledgeBase)
    {
        if (!$knowledgeBase->file_path || !Storage::disk('public')->exists($knowledgeBase->file_path)) {
            abort(404, 'File tidak ditemukan.');
        }

        $originalName = pathinfo($knowledgeBase->file_path, PATHINFO_FILENAME);
        $downloadName = Str::slug($knowledgeBase->title ?: $originalName, '_') . '.' . $knowledgeBase->file_type;

        return response()->download(
            storage_path('app/public/' . $knowledgeBase->file_path),
            $downloadName,
            [
                'Content-Type' => $this->getMimeType($knowledgeBase->file_type),
            ]
        );
    }

    private function getMimeType(string $extension): string
    {
        $mimes = [
            'pdf' => 'application/pdf',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xls' => 'application/vnd.ms-excel',
        ];

        return $mimes[strtolower($extension)] ?? 'application/octet-stream';
    }
}