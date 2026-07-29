<?php

namespace App\Http\Controllers;

use App\Models\KnowledgeManagement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class KnowledgeManagementController extends Controller
{
    /**
     * Display a listing of knowledge management items.
     */
    public function index(Request $request)
    {
        $category = $request->query('category', 'all');
        $search = $request->query('search');

        $query = KnowledgeManagement::with(['creator.karyawan', 'updater.karyawan']);

        if ($category !== 'all' && in_array($category, ['SOP', 'FAQ', 'TUTORIAL', 'Panduan Instalasi'])) {
            $query->where('category', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $knowledgeList = $query->latest()->paginate(10)->appends($request->all());

        $counts = [
            'total' => KnowledgeManagement::count(),
            'SOP' => KnowledgeManagement::where('category', 'SOP')->count(),
            'FAQ' => KnowledgeManagement::where('category', 'FAQ')->count(),
            'TUTORIAL' => KnowledgeManagement::where('category', 'TUTORIAL')->count(),
            'Panduan Instalasi' => KnowledgeManagement::where('category', 'Panduan Instalasi')->count(),
        ];

        return view('knowledge_management.index', compact('knowledgeList', 'category', 'search', 'counts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('knowledge_management.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:SOP,FAQ,TUTORIAL,Panduan Instalasi',
            'content' => 'required|string',
            'file' => 'nullable|file|max:20480', // max 20MB
        ]);

        $filePath = null;
        $fileName = null;

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('knowledge_management', 'public');
        }

        KnowledgeManagement::create([
            'title' => $request->title,
            'category' => $request->category,
            'content' => $request->content,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('knowledge-management.index')
            ->with('success', 'Data Knowledge Management berhasil ditambahkan.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $knowledge = KnowledgeManagement::with(['creator.karyawan', 'updater.karyawan'])->findOrFail($id);
        return view('knowledge_management.show', compact('knowledge'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $knowledge = KnowledgeManagement::findOrFail($id);
        return view('knowledge_management.edit', compact('knowledge'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $knowledge = KnowledgeManagement::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|in:SOP,FAQ,TUTORIAL,Panduan Instalasi',
            'content' => 'required|string',
            'file' => 'nullable|file|max:20480',
        ]);

        $filePath = $knowledge->file_path;
        $fileName = $knowledge->file_name;

        if ($request->hasFile('file')) {
            // Delete old file if exists
            if ($knowledge->file_path && Storage::disk('public')->exists($knowledge->file_path)) {
                Storage::disk('public')->delete($knowledge->file_path);
            }

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $filePath = $file->store('knowledge_management', 'public');
        }

        $knowledge->update([
            'title' => $request->title,
            'category' => $request->category,
            'content' => $request->content,
            'file_path' => $filePath,
            'file_name' => $fileName,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('knowledge-management.index')
            ->with('success', 'Data Knowledge Management berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $knowledge = KnowledgeManagement::findOrFail($id);

        if ($knowledge->file_path && Storage::disk('public')->exists($knowledge->file_path)) {
            Storage::disk('public')->delete($knowledge->file_path);
        }

        $knowledge->delete();

        return redirect()->route('knowledge-management.index')
            ->with('success', 'Data Knowledge Management berhasil dihapus.');
    }

    /**
     * Download attached file.
     */
    public function downloadFile($id)
    {
        $knowledge = KnowledgeManagement::findOrFail($id);

        if (!$knowledge->file_path || !Storage::disk('public')->exists($knowledge->file_path)) {
            return redirect()->back()->with('error', 'File lampiran tidak ditemukan.');
        }

        return Storage::disk('public')->download($knowledge->file_path, $knowledge->file_name);
    }
}
