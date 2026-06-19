<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use App\Models\karyawan;
use App\Models\Pelamar;
use App\Models\PelamarFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ArsipRekrutmenController extends Controller
{
    public function index(Request $request)
    {
        $layout = $request->layout ?? 'app';

        $backRoute = $layout == 'hr'
            ? route('HR.hire.index')
            : route('HR.folders.index');

        return view('rekrutan.arsip.index', compact('layout', 'backRoute'));
    }

    public function getArsipFolders()
    {
        $userId = auth()->id();
        $user = auth()->user();

        $query = Folder::where('is_archived', true)
            ->with(['children', 'pelamars.pelamar'])
            ->withCount(['pelamars']);

        if (!in_array($user->jabatan, ['HRD', 'GM', 'Direktur Utama', 'Direktur'])) {
            $datakaryawan = karyawan::where('id', $userId)->first();
            if ($datakaryawan && $datakaryawan->divisi) {
                $query->where(function ($q) use ($datakaryawan) {
                    $q->whereHas('pelamars.pelamar', function ($q2) use ($datakaryawan) {
                        $q2->where('divisi', $datakaryawan->divisi);
                    })->orWhereDoesntHave('pelamars');
                });
            }
        }

        $folders = $query->orderBy('updated_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $folders,
        ]);
    }

    public function getArsipPelamarByFolder($folderId)
    {
        $folder = Folder::where('id', $folderId)->where('is_archived', true)->first();

        if (!$folder) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder tidak ditemukan atau bukan folder arsip',
                ],
                404,
            );
        }

        $pelamars = PelamarFolder::where('folder_id', $folderId)
            ->with(['pelamar', 'folder'])
            ->orderBy('updated_at', 'desc')
            ->get()
            ->map(function ($pf) {
                return [
                    'id' => $pf->id,
                    'pelamar' => $pf->pelamar,
                    'folder_id' => $pf->folder_id,
                    'rating' => $pf->rating,
                    'catatan' => $pf->catatan,
                    'file_penilaian' => $pf->file_penilaian,
                    'tanggal_dinilai' => $pf->tanggal_dinilai,
                    'diarsipkan' => $pf->updated_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $pelamars,
        ]);
    }

    public function movePelamarArsip(Request $request)
    {
        $request->validate([
            'pelamar_id' => 'required|exists:pelamars,id',
            'folder_id' => 'required|exists:folders,id',
        ]);

        $targetFolder = Folder::where('id', $request->folder_id)->where('is_archived', true)->first();

        if (!$targetFolder) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder tujuan bukan folder arsip',
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $pelamarFolder = PelamarFolder::where('pelamar_id', $request->pelamar_id)->first();

            if ($pelamarFolder) {
                $pelamarFolder->update([
                    'folder_id' => $request->folder_id,
                    'updated_at' => now(),
                ]);
            } else {
                PelamarFolder::create([
                    'folder_id' => $request->folder_id,
                    'pelamar_id' => $request->pelamar_id,
                ]);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pelamar berhasil dipindahkan ke folder arsip',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal memindahkan pelamar: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function moveFolderArsip(Request $request, Folder $folder)
    {
        $request->validate([
            'parent_id' => 'nullable|exists:folders,id',
        ]);

        if (!$folder->is_archived) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder ini bukan folder arsip',
                ],
                422,
            );
        }

        if ($request->parent_id == $folder->id) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder tidak bisa dipindahkan ke dirinya sendiri',
                ],
                422,
            );
        }

        if ($request->parent_id && $this->isDescendant($folder->id, $request->parent_id)) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder tidak bisa dipindahkan ke dalam child-nya sendiri',
                ],
                422,
            );
        }

        if ($request->parent_id) {
            $parent = Folder::find($request->parent_id);
            if (!$parent || !$parent->is_archived) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Folder tujuan bukan folder arsip',
                    ],
                    422,
                );
            }
        }

        $folder->update(['parent_id' => $request->parent_id]);

        return response()->json([
            'success' => true,
            'message' => 'Folder berhasil dipindahkan',
        ]);
    }

    private function isDescendant($parentId, $targetId)
    {
        if ($parentId == $targetId) {
            return true;
        }

        $children = Folder::where('parent_id', $parentId)->get();
        foreach ($children as $child) {
            if ($this->isDescendant($child->id, $targetId)) {
                return true;
            }
        }
        return false;
    }

    public function restoreFolder(Folder $folder)
    {
        if (!$folder->is_archived) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder ini tidak berada di arsip',
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $folderIds = $this->getAllDescendantIds($folder->id);
            $folderIds[] = $folder->id;

            Folder::whereIn('id', $folderIds)->update([
                'is_archived' => false,
                'updated_at' => now(),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Folder beserta ' . (count($folderIds) - 1) . ' subfolder berhasil dipulihkan',
                'restored_count' => count($folderIds),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal memulihkan folder: ' . $e->getMessage(),
                ],
                500,
            );
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

    public function permanentDeleteFolder(Folder $folder)
    {
        if (!$folder->is_archived) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Folder ini tidak berada di arsip',
                ],
                422,
            );
        }

        DB::beginTransaction();
        try {
            $folderIds = $this->getAllDescendantIds($folder->id);
            $folderIds[] = $folder->id;

            PelamarFolder::whereIn('folder_id', $folderIds)->delete();

            Folder::whereIn('id', $folderIds)->delete();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Folder beserta ' . (count($folderIds) - 1) . ' subfolder berhasil dihapus permanen',
                'deleted_count' => count($folderIds),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menghapus folder: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function restorePelamar($pelamarFolderId)
    {
        $pf = PelamarFolder::findOrFail($pelamarFolderId);

        if (!$pf->folder || !$pf->folder->is_archived) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Pelamar ini tidak berada di folder arsip',
                ],
                422,
            );
        }

        $pf->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pelamar dikeluarkan dari folder arsip',
        ]);
    }
}
