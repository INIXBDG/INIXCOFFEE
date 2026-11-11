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
use App\Models\PelamarPenilaian;
use App\Models\PelamarPsikotest;
use App\Models\Pelamarriwayat;
use Carbon\Carbon;

class FolderController extends Controller
{
    public function index()
    {
        $auth = auth()->user()->id;
        $divisi = karyawan::where('id', $auth)->first();

        $title = 'Inixindo';

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
                ->with(['pelamars.pelamar.penilaians:id,pelamar_id,rating',])
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
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal mengarsipkan folder: ' . $e->getMessage(),
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
            'folder_id' => 'nullable|exists:folders,id',
            'rating' => 'required|integer|min:1|max:4',
            'catatan' => 'nullable|string',
            'file_penilaian' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $pelamar = Pelamar::findOrFail($request->pelamar_id);
        $this->pastikanBolehAkses($pelamar);

        $filePath = null;
        $fileLama = null;

        DB::beginTransaction();
        try {
            if ($request->hasFile('file_penilaian')) {
                $filePath = $request->file('file_penilaian')->store('penilaian', 'public');
            }

            $folderId = $request->input('folder_id')
                ?: PelamarFolder::where('pelamar_id', $pelamar->id)->value('folder_id');

            if ($request->filled('folder_id')) {
                $keanggotaan = PelamarFolder::where('pelamar_id', $pelamar->id)->first();
                if ($keanggotaan) {
                    $keanggotaan->update(['folder_id' => $request->folder_id]);
                } else {
                    PelamarFolder::create([
                        'folder_id' => $request->folder_id,
                        'pelamar_id' => $pelamar->id,
                        'dinilai_oleh' => auth()->id(),
                        'tanggal_dinilai' => now(),
                        'is_archived' => false,
                    ]);
                }
            }

            $kunci = [
                'pelamar_id' => $pelamar->id,
                'dinilai_oleh' => auth()->id(),
                'tahap_rekrutmen' => $pelamar->tahap_rekrutmen,
            ];

            $lama = PelamarPenilaian::where($kunci)->first();
            $fileLama = $filePath ? $lama?->file_penilaian : null;

            PelamarPenilaian::updateOrCreate($kunci, [
                'folder_id' => $folder_id,
                'tahap_interview' => $pelamar->tahap_rekrutmen === 'interview' ? $pelamar->tahap_interview : null,
                'rating' => $request->rating,
                'catatan' => $request->catatan,
                'file_penilaian' => $filePath ?? $lama?->file_penilaian,
                'tanggal_dinilai' => now(),
            ]);

            Pelamarriwayat::catat($pelamar->id, 'rated', [
                'tahap_ke' => $pelamar->tahap_rekrutmen,
                'rating' => $request->rating,
                'keterangan' => 'Penilaian disimpan oleh ' . auth()->user()->nama_lengkap . '. Catatan: ' . ($request->catatan ?? '-'),
            ]);

            DB::commit();

            if ($fileLama) {
                Storage::disk('public')->delete($fileLama);
            }

            return response()->json([
                'success' => true,
                'message' => $lama ? 'Penilaian Anda untuk tahap ini diperbarui' : 'Penilaian berhasil disimpan',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
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
        $this->pastikanBolehAkses($pelamar);
        $pelamar->load('psikotests');

        $penilaians = $pelamar->penilaians()->with(['folder', 'penilai'])->orderByDesc('tanggal_dinilai')->get();

        $daftar = $penilaians
            ->map(fn ($n) => $n->ringkas() + ['milik_saya' => $n->dinilai_oleh == auth()->id()])
            ->values();

        $saya = $penilaians->first(fn ($n) => $n->dinilai_oleh == auth()->id()
            && $n->tahap_rekrutmen === $pelamar->tahap_rekrutmen);

        $ratings = $daftar->pluck('rating');

        $psikotest = collect(PelamarPsikotest::KATEGORI)->map(function ($label, $kode) use ($pelamar) {
            $t = $pelamar->psikotests->firstWhere('kategori', $kode);

            return [
                'kode' => $kode,
                'label' => $label,
                'ada' => (bool) $t,
                'nilai' => $t ? ($t->hasil ?: ($t->skor !== null ? (float) $t->skor : null)) : null,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => [
                'pelamar' => $pelamar,
                'avg_rating' => $ratings->count() ? round($ratings->avg(), 1) : null,
                'total_penilai' => $ratings->count(),
                'psikotest' => $psikotest,
                'cv_url' => $pelamar->cv_url,
                'portofolio_url' => $pelamar->portofolio_url,
                'usia' => $pelamar->usia,
                'inisial' => $pelamar->inisial,
                'tahap_label' => $pelamar->tahap_label,
                'penilaians' => $daftar,
                'penilaian_saya' => $saya?->ringkas(),
                'folder_id' => PelamarFolder::where('pelamar_id', $pelamar->id)->value('folder_id'),
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

    private function pastikanBolehAkses(Pelamar $pelamar): void
    {
        $user = auth()->user();

        if (in_array($user->jabatan, ['HRD', 'GM', 'Direktur Utama', 'Direktur'])) {
            return;
        }

        $divisi = karyawan::where('id', $user->id)->value('divisi');
        abort_unless($divisi && $pelamar->divisi === $divisi, 403, 'Anda tidak memiliki akses ke pelamar ini.');
    }

    public function rekapPenilaian(Pelamar $pelamar)
    {
        $this->pastikanBolehAkses($pelamar);
        $pelamar->load(['psikotests', 'riwayatTahap']);

        $penilaian = $pelamar
            ->penilaians()
            ->with(['folder', 'penilai'])
            ->orderBy('tanggal_dinilai')
            ->get()
            ->map(fn(PelamarPenilaian $n) => $n->ringkas())
            ->values();

        $riwayat = $pelamar->riwayatTahap;
        $meta = fn($r) => is_array($r->metadata) ? $r->metadata : (json_decode((string) $r->metadata, true) ?: []);
        $tes = $pelamar->psikotests->keyBy('kategori');

        $tahap = [];
        foreach (Pelamar::TAHAP as $kode => $label) {
            $daftar = $penilaian->where('tahap', $kode)->values();

            // Rating dan catatan yang diisi HR saat memindahkan tahap, serta alasan penolakan.
            $feedback = $riwayat
                ->filter(fn($r) => (($r->aksi === 'moved' && $r->tahap_dari === $kode) || ($r->aksi === 'rejected' && $kode === 'rejected')) && (filled($r->keterangan) || $r->rating))
                ->map(
                    fn($r) => [
                        'jenis' => $r->aksi === 'rejected' ? 'Alasan penolakan' : 'Catatan saat pindah tahap',
                        'oleh' => $r->oleh ?? '-',
                        'rating' => $r->rating ?: null,
                        'keterangan' => $r->keterangan,
                        'ke' => $r->aksi === 'moved' ? Pelamar::TAHAP[$r->tahap_ke] ?? $r->tahap_ke : null,
                        'tanggal' => $r->created_at?->format('d M Y H:i'),
                    ],
                )
                ->values();

            $jadwal =
                $kode === 'interview'
                    ? $riwayat
                        ->where('aksi', 'interview_scheduled')
                        ->map(function ($r) use ($meta) {
                            $m = $meta($r);

                            return [
                                'jadwal' => !empty($m['jadwal']) ? Carbon::parse($m['jadwal'])->format('d M Y H:i') : null,
                                'metode' => Pelamar::METODE_INTERVIEW[$m['metode'] ?? ''] ?? ($m['metode'] ?? null),
                                'tahap_interview' => Pelamar::TAHAP_INTERVIEW[$m['tahap'] ?? ''] ?? ($m['tahap'] ?? null),
                                'interviewer' => implode(', ', (array) ($m['interviewer'] ?? [])),
                                'link' => $m['link'] ?? null,
                                'lokasi' => $m['lokasi'] ?? null,
                                'oleh' => $r->oleh ?? '-',
                            ];
                        })
                        ->values()
                    : collect();

            $psikotest =
                $kode === 'psikotest'
                    ? collect(PelamarPsikotest::KATEGORI)
                        ->map(
                            fn($lbl, $k) => [
                                'kode' => $k,
                                'label' => $lbl,
                                'data' => $tes->get($k)?->ringkas(),
                            ],
                        )
                        ->values()
                    : collect();

            $tahap[] = [
                'kode' => $kode,
                'label' => $label,
                'ada' => $daftar->isNotEmpty() || $feedback->isNotEmpty() || $jadwal->isNotEmpty() || $psikotest->contains(fn($x) => $x['data']),
                'rata' => $daftar->count() ? round($daftar->avg('rating'), 1) : null,
                'penilaian' => $daftar,
                'feedback' => $feedback,
                'jadwal' => $jadwal,
                'psikotest' => $psikotest,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => [
                'pelamar' => [
                    'id' => $pelamar->id,
                    'nama_lengkap' => $pelamar->nama_lengkap,
                    'jabatan' => $pelamar->jabatan,
                    'divisi' => $pelamar->divisi,
                    'tahap' => $pelamar->tahap_rekrutmen,
                    'tahap_label' => $pelamar->tahap_label,
                ],
                'folder_id' => PelamarFolder::where('pelamar_id', $pelamar->id)->value('folder_id'),
                'total' => $penilaian->count(),
                'rata' => $penilaian->count() ? round($penilaian->avg('rating'), 1) : null,
                'tahap' => $tahap,
            ],
        ]);
    }
}
