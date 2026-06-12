<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\karyawan;
use App\Models\Pelamar;
use App\Models\PelamarFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FolderController extends Controller
{
    public function index()
    {
        $auth = auth()->user()->id;
        $divisi = karyawan::where('id', $auth)->first();

        $title = "Inixindo";

        if (in_array(auth()->user()->jabatan, ['HRD', 'Direktur Utama', 'Direktur', 'GM'])) {
            $title = 'Inixindo';
        } else {
            $title = 'Divisi ' . $divisi->divisi;
        }
        

        return view('rekrutan.index', compact('title'));
    }

    public function getFolders()
    {
        $userId = auth()->id();
        $user = auth()->user();

        if (in_array($user->jabatan, ['HRD', 'GM', 'Direktur Utama', 'Direktur'])) {
            $folders = Folder::where('is_archived', false)
                ->with(['children', 'pelamars.pelamar'])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            $datakaryawan = karyawan::where('id', $userId)->first();

            $folders = Folder::where('is_archived', false)
                ->with([
                    'children',
                    'pelamars' => function ($q) use ($datakaryawan) {
                        $q->whereHas('pelamar', function ($q2) use ($datakaryawan) {
                            $q2->where('divisi', $datakaryawan->divisi);
                        });
                    },
                    'pelamars.pelamar',
                ])
                ->orderBy('is_pinned', 'desc')
                ->orderBy('sort_order', 'asc')
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'success' => true,
            'data' => $folders,
        ]);
    }

    public function storeFolder(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        $folder = Folder::create([
            'nama' => $request->nama,
            'parent_id' => $request->parent_id,
            'user_id' => null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Folder berhasil dibuat',
            'data' => $folder,
        ]);
    }

    public function renameFolder(Request $request, Folder $folder)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
        ]);

        $folder->update(['nama' => $request->nama]);

        return response()->json([
            'success' => true,
            'message' => 'Folder berhasil diubah nama',
        ]);
    }

    public function togglePin(Folder $folder)
    {
        $folder->update(['is_pinned' => !$folder->is_pinned]);

        return response()->json([
            'success' => true,
            'message' => $folder->is_pinned ? 'Folder di-pin' : 'Folder di-unpin',
            'is_pinned' => $folder->is_pinned,
        ]);
    }

    public function archiveFolder(Folder $folder)
    {
        DB::beginTransaction();
        try {
            $folderIds = $this->getAllDescendantIds($folder->id);
            $folderIds[] = $folder->id;

            Folder::whereIn('id', $folderIds)->update([
                'is_archived' => true,
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Folder beserta ' . (count($folderIds) - 1) . ' subfolder berhasil diarsipkan',
                'archived_count' => count($folderIds),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengarsipkan folder: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function getAllDescendantIds($parentId)
    {
        $ids = [];
        $children = Folder::where('parent_id', $parentId)->get();
        
        foreach ($children as $child) {
            $ids[] = $child->id;
            $ids = array_merge($ids, $this->getAllDescendantIds($child->id));
        }
        
        return $ids;
    }

    public function destroyFolder(Folder $folder)
    {
        $folder->delete();

        return response()->json([
            'success' => true,
            'message' => 'Folder berhasil dihapus',
        ]);
    }

    public function moveFolder(Request $request, Folder $folder)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        if ($request->parent_id == $folder->id) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder tidak bisa dipindahkan ke dirinya sendiri',
                ],
                422,
            );
        }

        if ($this->isDescendant($folder->id, $request->parent_id)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder tidak bisa dipindahkan ke dalam child-nya sendiri',
                ],
                422,
            );
        }

        $folder->update(['parent_id' => $request->parent_id]);

        return response()->json([
            'success' => true,
            'message' => 'Folder berhasil dipindahkan',
        ]);
    }

    private function isDescendant($parentId, $childId)
    {
        if ($parentId == $childId) {
            return true;
        }

        $children = Folder::where('parent_id', $parentId)->get();
        foreach ($children as $child) {
            if ($this->isDescendant($child->id, $childId)) {
                return true;
            }
        }

        return false;
    }

    public function addPelamar(Request $request)
    {
        $request->validate([
            'folder_id' => 'required|exists:folders,id',
            'pelamar_id' => 'required|exists:pelamars,id',
        ]);

        $existing = PelamarFolder::where('pelamar_id', $request->pelamar_id)->first();

        if ($existing) {
            $existing->update(['folder_id' => $request->folder_id]);
        } else {
            PelamarFolder::create([
                'folder_id' => $request->folder_id,
                'pelamar_id' => $request->pelamar_id,
                'is_archived' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelamar berhasil dimasukkan ke folder',
        ]);
    }

    public function movePelamar(Request $request)
    {
        $request->validate([
            'pelamar_id' => 'required|exists:pelamars,id',
            'folder_id' => 'required|exists:folders,id',
        ]);

        $pelamarFolder = PelamarFolder::where('pelamar_id', $request->pelamar_id)->first();

        if ($pelamarFolder) {
            $pelamarFolder->update(['folder_id' => $request->folder_id]);
        } else {
            PelamarFolder::create([
                'folder_id' => $request->folder_id,
                'pelamar_id' => $request->pelamar_id,
                'is_archived' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Pelamar berhasil dipindahkan',
        ]);
    }

    public function storePenilaian(Request $request)
    {
        $request->validate([
            'pelamar_id' => 'required|exists:pelamars,id',
            'folder_id' => 'required|exists:folders,id',
            'rating' => 'required|integer|min:1|max:4',
            'catatan' => 'nullable|string',
            'file_penilaian' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $filePath = null;
            if ($request->hasFile('file_penilaian')) {
                $filePath = $request->file('file_penilaian')->store('penilaian', 'public');
            }

            $pelamarFolder = PelamarFolder::where('pelamar_id', $request->pelamar_id)->where('folder_id', $request->folder_id)->first();

            if ($pelamarFolder) {
                $pelamarFolder->update([
                    'rating' => $request->rating,
                    'catatan' => $request->catatan,
                    'file_penilaian' => $filePath ?? $pelamarFolder->file_penilaian,
                    'dinilai_oleh' => auth()->id(),
                    'tanggal_dinilai' => now(),
                ]);
            } else {
                PelamarFolder::create([
                    'folder_id' => $request->folder_id,
                    'pelamar_id' => $request->pelamar_id,
                    'rating' => $request->rating,
                    'catatan' => $request->catatan,
                    'file_penilaian' => $filePath,
                    'dinilai_oleh' => auth()->id(),
                    'tanggal_dinilai' => now(),
                    'is_archived' => false,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function getPenilaian(Pelamar $pelamar)
    {
        $penilaian = PelamarFolder::where('pelamar_id', $pelamar->id)->with('folder', 'interviewer')->first();

        return response()->json([
            'success' => true,
            'data' => $penilaian,
        ]);
    }

    public function getDetailPelamar(Pelamar $pelamar)
    {
        $penilaians = PelamarFolder::where('pelamar_id', $pelamar->id)
            ->with(['folder', 'interviewer'])
            ->orderBy('tanggal_dinilai', 'desc')
            ->get();

        $avgRating = $penilaians->whereNotNull('rating')->avg('rating');

        return response()->json([
            'success' => true,
            'data' => [
                'pelamar' => $pelamar,
                'penilaians' => $penilaians,
                'avg_rating' => $avgRating ? round($avgRating, 1) : null,
                'total_penilai' => $penilaians->whereNotNull('rating')->count(),
                'cv_url' => $pelamar->cv_url,
                'usia' => $pelamar->usia,
                'inisial' => $pelamar->inisial,
                'tahap_label' => $pelamar->tahap_label,
            ],
        ]);
    }

    public function removePelamar($pelamarId)
    {
        PelamarFolder::where('pelamar_id', $pelamarId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pelamar berhasil dihapus dari folder',
        ]);
    }

    public function getPelamarBelumFolder()
    {
        $pelamarDiFolder = PelamarFolder::pluck('pelamar_id');

        $pelamar = Pelamar::whereNotIn('id', $pelamarDiFolder)->get();

        return response()->json([
            'success' => true,
            'data' => $pelamar,
        ]);
    }
}