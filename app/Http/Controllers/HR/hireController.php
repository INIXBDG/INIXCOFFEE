<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Mail\EmailKustom;
use App\Mail\EmailPenolakan;
use App\Mail\NotifikasiInterview;
use App\Mail\NotifikasiTahap;
use App\Mail\OfferLetter;
use App\Models\Folder;
use App\Models\Karyawan;
use App\Models\Pelamar;
use App\Models\PelamarFolder;
use App\Models\PelamarRiwayat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class HireController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $dataJabatan = Karyawan::whereNotIn('jabatan', ['Pilih Jabatan'])
            ->whereNot('divisi', 'Direksi')
            ->distinct()
            ->pluck('jabatan');

        $dataDivisi = Karyawan::whereNotIn('divisi', ['Pilih Divisi', 'Direksi'])
            ->distinct()
            ->pluck('divisi');

        $Interviewer = Karyawan::whereIn('jabatan', ['HRD', 'GM', 'Direktur Utama', 'Direktur', 'Koordinator ITSM', 'SPV Sales', 'Education Manager'])
            ->whereNotNull('nip')
            ->get();

        $query = Pelamar::aktif()
            ->where(function($q) {
                $q->whereDoesntHave('pelamarFolders')
                ->orWhereHas('pelamarFolders', function($q2) {
                    $q2->whereHas('folder', function($q3) {
                        $q3->where('is_archived', false);
                    });
                });
            })
            ->filter($request->only(['search', 'divisi', 'jabatan', 'tahap', 'sumber', 'tanggal_dari', 'tanggal_sampai', 'talent_pool']))
            ->latest('tanggal_melamar');

        $pelamars = $query->paginate(10)->withQueryString();
        $funnel = Pelamar::statsFunnel();
        $sumberList = Pelamar::SUMBER;
        $tahapList = Pelamar::TAHAP;

        return view('HR/hire/index', compact('pelamars', 'funnel', 'dataJabatan', 'dataDivisi', 'sumberList', 'tahapList', 'Interviewer'));
    }

    public function getPelamarByFolder($folderId = null)
    {
        if ($folderId === 'unassigned' || $folderId === null) {
            $pelamarDiFolder = PelamarFolder::pluck('pelamar_id');
            $pelamars = Pelamar::aktif()
                ->whereNotIn('id', $pelamarDiFolder)
                ->latest('tanggal_melamar')
                ->get();
        } else {
            $folder = Folder::find($folderId);
            if (!$folder || $folder->is_archived) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder tidak ditemukan atau sudah diarsipkan',
                    'data' => [],
                ], 404);
            }
            
            $pelamarIds = PelamarFolder::where('folder_id', $folderId)->pluck('pelamar_id');
            $pelamars = Pelamar::aktif()
                ->whereIn('id', $pelamarIds)
                ->latest('tanggal_melamar')
                ->get();
        }

        $pelamarIds = $pelamars->pluck('id');
        $penilaians = PelamarFolder::whereIn('pelamar_id', $pelamarIds)
            ->with('interviewer')
            ->get()
            ->groupBy('pelamar_id');

        $data = $pelamars->map(function($p, $index) use ($penilaians) {
            $penilaianPelamar = $penilaians->get($p->id, collect());
            $ratings = $penilaianPelamar->whereNotNull('rating')->pluck('rating');
            $avgRating = $ratings->count() > 0 ? round($ratings->avg(), 1) : null;
            $totalPenilai = $ratings->count();

            return [
                'id' => $p->id,
                'no' => $index + 1,
                'inisial' => $p->inisial,
                'nama_lengkap' => $p->nama_lengkap,
                'email' => $p->email,
                'jabatan' => $p->jabatan,
                'divisi' => $p->divisi,
                'sumber_lamaran' => $p->sumber_lamaran,
                'tanggal_melamar' => $p->tanggal_melamar?->format('d M Y'),
                'tahap_rekrutmen' => $p->tahap_rekrutmen,
                'tahap_label' => $p->tahap_label,
                'cv_path' => $p->cv_path,
                'avClass' => 'av-' . (($index % 6) + 1),
                'avg_rating' => $avgRating,
                'total_penilai' => $totalPenilai,
                'penilaian_list' => $penilaianPelamar->map(function($pf) {
                    return [
                        'id' => $pf->id,
                        'rating' => $pf->rating,
                        'catatan' => $pf->catatan,
                        'tanggal_dinilai' => $pf->tanggal_dinilai?->format('d M Y H:i'),
                        'folder_nama' => $pf->folder?->nama ?? '-',
                        'interviewer_nama' => $pf->interviewer?->name ?? 'Unknown',
                        'interviewer_jabatan' => $pf->interviewer?->jabatan ?? '-',
                        'file_penilaian' => $pf->file_penilaian,
                    ];
                })->values()->all(),
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $data->count(),
        ]);
    }

    public function getFolderList()
    {
        $folders = Folder::where('is_archived', false)
            ->with(['children', 'children.children'])
            ->withCount(['pelamars'])
            ->orderBy('is_pinned', 'desc')
            ->orderBy('nama', 'asc')
            ->get(['id', 'nama', 'is_pinned', 'parent_id']);

        $pelamarDiFolder = PelamarFolder::pluck('pelamar_id')->unique();
        $unassignedCount = Pelamar::aktif()->whereNotIn('id', $pelamarDiFolder)->count();

        return response()->json([
            'success' => true,
            'data' => $folders,
            'unassigned_count' => $unassignedCount,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:100',
            'email' => 'required|email|unique:pelamars,email',
            'no_telepon' => 'required|string|max:20',
            'domisili' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'pendidikan_terakhir' => 'nullable|string',
            'jurusan' => 'nullable|string|max:100',
            'institusi' => 'nullable|string|max:150',
            'ipk' => 'nullable|numeric|min:0|max:4',
            'divisi' => 'required|string',
            'jabatan' => 'required|string',
            'detail_jabatan' => 'nullable|string|max:100',
            'tanggal_melamar' => 'required|date',
            'sumber_lamaran' => 'required|string|max:100',
            'pengalaman_tahun' => 'nullable|integer|min:0|max:50',
            'gaji_diharapkan' => 'nullable|string',
            'keahlian' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'portofolio' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
            'catatan_hr' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $gajiDiharapkan = isset($validated['gaji_diharapkan']) ? (int) preg_replace('/[^0-9]/', '', $validated['gaji_diharapkan']) : null;

            $keahlian = null;
            if (!empty($validated['keahlian'])) {
                $keahlian = array_values(array_filter(array_map('trim', explode(',', $validated['keahlian']))));
            }

            $cvPath = null;
            if ($request->hasFile('cv')) {
                $cvPath = $request->file('cv')->store('pelamar/cv', 'public');
            }

            $portofolioPath = null;
            if ($request->hasFile('portofolio')) {
                $portofolioPath = $request->file('portofolio')->store('pelamar/portofolio', 'public');
            }

            $pelamar = Pelamar::create([...$validated, 'gaji_diharapkan' => $gajiDiharapkan, 'keahlian' => $keahlian, 'cv_path' => $cvPath, 'portofolio_path' => $portofolioPath, 'tahap_rekrutmen' => 'applied', 'status_aktif' => true]);

            PelamarRiwayat::catat($pelamar->id, 'created', [
                'tahap_ke' => 'applied',
                'keterangan' => 'Pelamar ditambahkan secara manual oleh HR.',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pelamar berhasil ditambahkan.', 'data' => $pelamar]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }

    public function show(Pelamar $pelamar)
    {
        $pelamar->load('riwayatTahap');

        $penilaians = PelamarFolder::where('pelamar_id', $pelamar->id)
            ->with(['folder', 'interviewer'])
            ->orderBy('tanggal_dinilai', 'desc')
            ->get()
            ->map(function ($pf) {
                return [
                    'id' => $pf->id,
                    'rating' => $pf->rating,
                    'catatan' => $pf->catatan,
                    'tanggal_dinilai' => $pf->tanggal_dinilai?->format('d M Y H:i'),
                    'folder_nama' => $pf->folder?->nama ?? '-',
                    'interviewer_nama' => $pf->interviewer?->name ?? 'Unknown',
                    'interviewer_jabatan' => $pf->interviewer?->jabatan ?? '-',
                    'file_penilaian' => $pf->file_penilaian,
                ];
            });

        $ratings = $penilaians->whereNotNull('rating')->pluck('rating');
        $avgRating = $ratings->count() > 0 ? round($ratings->avg(), 1) : null;

        return response()->json([
            'success' => true,
            'data' => [
                'pelamar' => $pelamar,
                'riwayat' => $pelamar->riwayatTahap,
                'cv_url' => $pelamar->cv_url,
                'portofolio_url' => $pelamar->portofolio_url,
                'usia' => $pelamar->usia,
                'inisial' => $pelamar->inisial,
                'tahap_label' => $pelamar->tahap_label,
                'progress' => $pelamar->progressPersen(),
                'bisa_lanjut' => $pelamar->bisaLanjut(),
                'tahap_berikutnya' => $pelamar->tahapBerikutnya(),
                'estimasi_bulanan' => $pelamar->estimasi_pendapatan_bulanan_format,
                'penilaians' => $penilaians,
                'avg_rating' => $avgRating,
                'total_penilai' => $ratings->count(),
            ],
        ]);
    }

    public function update(Request $request, Pelamar $pelamar)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'sometimes|required|string|max:100',
            'email' => ['sometimes', 'required', 'email', Rule::unique('pelamars', 'email')->ignore($pelamar->id)],
            'no_telepon' => 'nullable|string|max:20',
            'domisili' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'pendidikan_terakhir' => 'nullable|string',
            'jurusan' => 'nullable|string|max:100',
            'institusi' => 'nullable|string|max:150',
            'ipk' => 'nullable|numeric|min:0|max:4',
            'divisi' => 'nullable|string',
            'jabatan' => 'nullable|string',
            'detail_jabatan' => 'nullable|string|max:100',
            'sumber_lamaran' => 'nullable|string|max:100',
            'pengalaman_tahun' => 'nullable|integer|min:0|max:50',
            'gaji_diharapkan' => 'nullable|string',
            'keahlian' => 'nullable|string',
            'catatan_hr' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        DB::beginTransaction();
        try {
            if (!empty($validated['gaji_diharapkan'])) {
                $validated['gaji_diharapkan'] = (int) preg_replace('/[^0-9]/', '', $validated['gaji_diharapkan']);
            }

            if (isset($validated['keahlian'])) {
                $validated['keahlian'] = array_values(array_filter(array_map('trim', explode(',', $validated['keahlian']))));
            }

            if ($request->hasFile('cv')) {
                if ($pelamar->cv_path) {
                    Storage::disk('public')->delete($pelamar->cv_path);
                }
                $validated['cv_path'] = $request->file('cv')->store('pelamar/cv', 'public');
            }
            unset($validated['cv']);

            $pelamar->update($validated);

            PelamarRiwayat::catat($pelamar->id, 'updated', [
                'keterangan' => 'Data pelamar diperbarui.',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Data pelamar berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Pelamar $pelamar)
    {
        if ($pelamar->cv_path) {
            Storage::disk('public')->delete($pelamar->cv_path);
        }
        if ($pelamar->portofolio_path) {
            Storage::disk('public')->delete($pelamar->portofolio_path);
        }

        $pelamar->delete();

        return response()->json(['success' => true, 'message' => 'Pelamar berhasil dihapus.']);
    }

    public function lanjutTahap(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'tahap_ke' => ['required', Rule::in(array_keys(Pelamar::TAHAP))],
            'rating' => 'nullable|integer|min:1|max:5',
            'keterangan' => 'nullable|string',
            'notif_email' => 'nullable|boolean',
        ]);

        if (!$pelamar->bisaLanjut()) {
            return response()->json(['success' => false, 'message' => 'Pelamar sudah pada tahap akhir.'], 422);
        }

        DB::beginTransaction();
        try {
            $tahapLama = $pelamar->tahap_rekrutmen;

            $pelamar->update([
                'tahap_rekrutmen' => $request->tahap_ke,
                'rating' => $request->rating ?? $pelamar->rating,
            ]);

            PelamarRiwayat::catat($pelamar->id, 'moved', [
                'tahap_dari' => $tahapLama,
                'tahap_ke' => $request->tahap_ke,
                'rating' => $request->rating,
                'keterangan' => $request->keterangan,
            ]);

            if ($request->boolean('notif_email')) {
                $this->kirimNotifikasiTahap($pelamar, $request->tahap_ke);
            }

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Pelamar berhasil dilanjutkan ke tahap ' . Pelamar::TAHAP[$request->tahap_ke] . '.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function tolak(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'alasan_penolakan' => 'nullable|string',
            'catatan_internal' => 'nullable|string',
            'notif_email' => 'nullable|boolean',
            'simpan_talent_pool' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $tahapLama = $pelamar->tahap_rekrutmen;

            $pelamar->update([
                'tahap_rekrutmen' => 'rejected',
                'alasan_penolakan' => $request->alasan_penolakan,
                'catatan_internal' => $request->catatan_internal,
                'simpan_talent_pool' => $request->boolean('simpan_talent_pool'),
            ]);

            PelamarRiwayat::catat($pelamar->id, 'rejected', [
                'tahap_dari' => $tahapLama,
                'tahap_ke' => 'rejected',
                'keterangan' => $request->alasan_penolakan ?? $request->catatan_internal,
            ]);

            if ($request->boolean('notif_email')) {
                $this->kirimEmailPenolakan($pelamar);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pelamar berhasil ditolak.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function jadwalkanInterview(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'tahap_interview' => ['required', Rule::in(array_keys(Pelamar::TAHAP_INTERVIEW))],
            'tanggal' => 'required|date|after_or_equal:today',
            'waktu' => 'required|date_format:H:i',
            'metode_interview' => ['required', Rule::in(array_keys(Pelamar::METODE_INTERVIEW))],
            'link_meeting' => 'nullable|url',
            'lokasi_interview' => 'nullable|string|max:200',
            'interviewer' => 'required|array|min:1',
            'interviewer.*' => 'required|string|max:100',
            'catatan' => 'nullable|string',
            'notif_email' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $jadwal = $request->tanggal . ' ' . $request->waktu . ':00';

            $interviewers = json_encode($request->interviewer);

            $pelamar->update([
                'jadwal_interview' => $jadwal,
                'metode_interview' => $request->metode_interview,
                'link_meeting' => $request->link_meeting,
                'lokasi_interview' => $request->lokasi_interview,
                'interviewer' => $interviewers,
                'tahap_interview' => $request->tahap_interview,
                'tahap_rekrutmen' => 'interview',
            ]);

            PelamarRiwayat::catat($pelamar->id, 'interview_scheduled', [
                'tahap_ke' => 'interview',
                'keterangan' => $request->catatan,
                'metadata' => [
                    'jadwal' => $jadwal,
                    'metode' => $request->metode_interview,
                    'link' => $request->link_meeting,
                    'lokasi' => $request->lokasi_interview,
                    'interviewer' => $request->interviewer,
                    'tahap' => $request->tahap_interview,
                ],
            ]);

            if ($request->boolean('notif_email')) {
                $this->kirimNotifikasiInterview($pelamar);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Jadwal interview berhasil disimpan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function kirimOffer(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'gaji_ditawarkan' => 'required|string',
            'tunjangan_makan' => 'required|string',
            'tunjangan_transport' => 'required|string',
            'tanggal_mulai_kerja' => 'required|date|after_or_equal:today',
            'status_kepegawaian' => ['required', Rule::in(array_keys(Pelamar::STATUS_KEPEGAWAIAN))],
            'benefit_lainnya' => 'nullable|string',
            'pesan_tambahan' => 'nullable|string',
            'lampiran_offer' => 'required|file|mimes:pdf|max:5120',
            'password_offer' => 'required|string|min:4|max:20',
        ]);

        DB::beginTransaction();
        try {
            $gaji = (int) preg_replace('/[^0-9]/', '', $request->gaji_ditawarkan);
            $tunjanganMakan = (int) preg_replace('/[^0-9]/', '', $request->tunjangan_makan);
            $tunjanganTransport = (int) preg_replace('/[^0-9]/', '', $request->tunjangan_transport);

            $offerPath = null;
            if ($request->hasFile('lampiran_offer')) {
                $offerPath = $request->file('lampiran_offer')->store('pelamar/offer', 'public');
            }

            $pelamar->update([
                'gaji_ditawarkan' => $gaji,
                'tunjangan_makan' => $tunjanganMakan,
                'tunjangan_transport' => $tunjanganTransport,
                'tanggal_mulai_kerja' => $request->tanggal_mulai_kerja,
                'status_kepegawaian' => $request->status_kepegawaian,
                'benefit_lainnya' => $request->benefit_lainnya,
                'tahap_rekrutmen' => 'offer',
                'status_offer' => 'pending',
                'tanggal_offer_dikirim' => now(),
            ]);

            $totalTunjanganHarian = $tunjanganMakan + $tunjanganTransport;

            PelamarRiwayat::catat($pelamar->id, 'offer_sent', [
                'tahap_ke' => 'offer',
                'keterangan' => $request->pesan_tambahan,
                'metadata' => [
                    'gaji_pokok' => $gaji,
                    'tunjangan_makan' => $tunjanganMakan,
                    'tunjangan_transport' => $tunjanganTransport,
                    'total_tunjangan_harian' => $totalTunjanganHarian,
                    'tanggal_mulai' => $request->tanggal_mulai_kerja,
                    'status_kepegawaian' => $request->status_kepegawaian,
                    'offer_path' => $offerPath,
                    'offer_password' => $request->password_offer,
                ],
            ]);

            $this->kirimEmailOffer(
                $pelamar, 
                $request->pesan_tambahan, 
                $offerPath, 
                $request->password_offer
            );

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Penawaran berhasil dikirim ke pelamar.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function protectPdf($sourcePath, $password, $pelamarId)
    {
        $pdf = new Fpdi();

        $pdf->setProtection(
            ['print', 'copy'],  
            $password,          
            $password,          
            2                   
        );

        $pageCount = $pdf->setSourceFile($sourcePath);
        
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($templateId);
            
            $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
            $pdf->useTemplate($templateId);
        }

        $outputDir = storage_path('app/public/pelamar/offer');
        if (!file_exists($outputDir)) {
            mkdir($outputDir, 0755, true);
        }
        
        $outputPath = $outputDir . '/offer_' . $pelamarId . '_' . time() . '.pdf';
        
        $pdf->Output($outputPath, 'F');

        return $outputPath;
    }

    public function onboarding(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'nik_karyawan' => 'required|string|max:30',
            'tanggal_mulai_kerja' => 'required|date',
            'atasan_langsung' => 'required|string|max:100',
            'status_kepegawaian' => ['required', Rule::in(array_keys(Pelamar::STATUS_KEPEGAWAIAN))],
            'checklist_onboarding' => 'nullable|array',
        ]);

        if (!$pelamar->sudahDiterima() && $pelamar->tahap_rekrutmen !== 'offer') {
            return response()->json(['success' => false, 'message' => 'Pelamar belum pada tahap yang sesuai untuk onboarding.'], 422);
        }

        DB::beginTransaction();
        try {
            $pelamar->update([
                'tahap_rekrutmen' => 'hired',
                'nik_karyawan' => $request->nik_karyawan,
                'tanggal_mulai_kerja' => $request->tanggal_mulai_kerja,
                'atasan_langsung' => $request->atasan_langsung,
                'status_kepegawaian' => $request->status_kepegawaian,
                'checklist_onboarding' => $request->checklist_onboarding ?? [],
                'status_offer' => 'accepted',
            ]);

            PelamarRiwayat::catat($pelamar->id, 'onboarded', [
                'tahap_ke' => 'hired',
                'keterangan' => 'Pelamar resmi dijadikan karyawan.',
                'metadata' => [
                    'nik' => $request->nik_karyawan,
                    'tanggal_mulai' => $request->tanggal_mulai_kerja,
                    'atasan' => $request->atasan_langsung,
                    'status' => $request->status_kepegawaian,
                    'gaji_pokok' => $pelamar->gaji_ditawarkan,
                    'tunjangan_makan' => $pelamar->tunjangan_makan,
                    'tunjangan_transport' => $pelamar->tunjangan_transport,
                ],
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pelamar berhasil diproses menjadi karyawan.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function kirimEmail(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'subjek' => 'required|string|max:200',
            'isi_email' => 'required|string',
            'lampiran' => 'nullable|array',
            'lampiran.*' => 'nullable|file|max:5120',
        ]);

        try {
            $lampiranPaths = [];
            if ($request->hasFile('lampiran')) {
                foreach ($request->file('lampiran') as $file) {
                    $lampiranPaths[] = $file->store('pelamar/email-lampiran', 'public');
                }
            }

            Mail::to($pelamar->email)->send(new EmailKustom($pelamar, $request->subjek, $request->isi_email, $lampiranPaths));

            PelamarRiwayat::catat($pelamar->id, 'email_sent', [
                'keterangan' => 'Email kustom dikirim: ' . $request->subjek,
                'metadata' => [
                    'subjek' => $request->subjek,
                    'lampiran' => $lampiranPaths,
                    'penerima' => $pelamar->email,
                ],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Email berhasil dikirim ke ' . $pelamar->email . '.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Gagal kirim email kustom: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal mengirim email: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function tambahCatatan(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'catatan' => 'required|string',
            'tipe' => 'in:hr,internal',
        ]);

        $field = $request->tipe === 'internal' ? 'catatan_internal' : 'catatan_hr';
        $pelamar->update([$field => $request->catatan]);

        PelamarRiwayat::catat($pelamar->id, 'note', [
            'keterangan' => $request->catatan,
            'metadata' => ['tipe' => $request->tipe],
        ]);

        return response()->json(['success' => true, 'message' => 'Catatan berhasil disimpan.']);
    }

    public function toggleTalentPool(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'simpan' => 'required|boolean',
            'catatan' => 'nullable|string',
        ]);

        $pelamar->update([
            'simpan_talent_pool' => $request->simpan,
            'talent_pool_catatan' => $request->catatan,
        ]);

        $msg = $request->simpan ? 'Pelamar disimpan ke talent pool.' : 'Pelamar dihapus dari talent pool.';
        return response()->json(['success' => true, 'message' => $msg]);
    }

    public function talentPool(Request $request)
    {
        $pelamars = Pelamar::talentPool()
            ->filter($request->only(['search', 'divisi', 'jabatan']))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $dataJabatan = Karyawan::distinct()->pluck('jabatan');
        $dataDivisi = Karyawan::distinct()->pluck('divisi');

        return view('HR/hire/talent_pool', compact('pelamars', 'dataJabatan', 'dataDivisi'));
    }

    public function riwayat(Pelamar $pelamar)
    {
        $riwayat = $pelamar->riwayatTahap()->get();
        return response()->json(['success' => true, 'data' => $riwayat]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'format' => 'required|in:xlsx,pdf,csv',
            'tahap' => 'nullable|string',
        ]);

        $pelamars = Pelamar::aktif()
            ->filter($request->only(['tahap', 'divisi', 'jabatan', 'tanggal_dari', 'tanggal_sampai']))
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Export disiapkan.',
            'total' => $pelamars->count(),
        ]);
    }

    public function funnelStats()
    {
        return response()->json([
            'success' => true,
            'data' => Pelamar::statsFunnel(),
        ]);
    }

    private function kirimNotifikasiTahap(Pelamar $pelamar, string $tahap): void
    {
        try {
            Mail::to($pelamar->email)->send(new NotifikasiTahap($pelamar, Pelamar::TAHAP[$tahap] ?? $tahap));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi tahap: ' . $e->getMessage());
        }
    }

    private function kirimNotifikasiInterview(Pelamar $pelamar): void
    {
        try {
            $metodeLabel = Pelamar::METODE_INTERVIEW[$pelamar->metode_interview] ?? $pelamar->metode_interview;
            $tahapLabel = Pelamar::TAHAP_INTERVIEW[$pelamar->tahap_interview] ?? $pelamar->tahap_interview;

            Mail::to($pelamar->email)->send(new NotifikasiInterview(pelamar: $pelamar, jadwal: $pelamar->jadwal_interview, metode: $metodeLabel, linkMeeting: $pelamar->link_meeting, lokasi: $pelamar->lokasi_interview, interviewer: $pelamar->interviewer, tahapInterview: $tahapLabel, catatan: null));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim notifikasi interview: ' . $e->getMessage());
        }
    }

    private function kirimEmailOffer(Pelamar $pelamar, ?string $pesanTambahan, ?string $offerPath, string $offerPassword): void
    {
        try {
            $totalTunjanganHarian = ($pelamar->tunjangan_makan ?? 0) + ($pelamar->tunjangan_transport ?? 0);
            $estimasiBulanan = ($pelamar->gaji_ditawarkan ?? 0) + $totalTunjanganHarian * Pelamar::HARI_KERJA_PER_BULAN;

            Mail::to($pelamar->email)->send(new OfferLetter(
                pelamar: $pelamar,
                gaji: $pelamar->gaji_ditawarkan ?? 0,
                tunjanganMakan: $pelamar->tunjangan_makan ?? 0,
                tunjanganTransport: $pelamar->tunjangan_transport ?? 0,
                estimasiBulanan: $estimasiBulanan,
                tanggalMulai: $pelamar->tanggal_mulai_kerja->format('Y-m-d'),
                statusKepegawaian: Pelamar::STATUS_KEPEGAWAIAN[$pelamar->status_kepegawaian] ?? $pelamar->status_kepegawaian,
                benefitLainnya: $pelamar->benefit_lainnya,
                pesanTambahan: $pesanTambahan,
                offerPassword: $offerPassword,    
                offerPath: $offerPath              
            ));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim email offer: ' . $e->getMessage());
        }
    }

    private function kirimEmailPenolakan(Pelamar $pelamar): void
    {
        try {
            $alasanLabel = $pelamar->alasan_penolakan;
            if (isset(Pelamar::ALASAN_PENOLAKAN[$pelamar->alasan_penolakan])) {
                $alasanLabel = Pelamar::ALASAN_PENOLAKAN[$pelamar->alasan_penolakan];
            }

            Mail::to($pelamar->email)->send(new EmailPenolakan($pelamar, $alasanLabel));
        } catch (\Throwable $e) {
            Log::error('Gagal kirim email penolakan: ' . $e->getMessage());
        }
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
                ]);
            }

            PelamarRiwayat::catat($request->pelamar_id, 'rated', [
                'tahap_ke' => Pelamar::where('id', $request->pelamar_id)->value('tahap_rekrutmen'),
                'rating' => $request->rating,
                'keterangan' => 'Penilaian disimpan oleh ' . auth()->user()->nama_lengkap . '. Catatan: ' . ($request->catatan ?? '-'),
            ]);

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Penilaian berhasil disimpan dan pelamar dimasukkan ke folder.',
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menyimpan penilaian: ' . $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function lihatCV(Pelamar $pelamar)
    {
        if (!$pelamar->cv_path) {
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Pelamar belum mengupload CV.',
                ],
                404,
            );
        }

        $cvUrl = asset('storage/' . $pelamar->cv_path);
        $fileExtension = pathinfo($pelamar->cv_path, PATHINFO_EXTENSION);

        return response()->json([
            'success' => true,
            'data' => [
                'cv_url' => $cvUrl,
                'file_extension' => $fileExtension,
                'nama_pelamar' => $pelamar->nama_lengkap,
                'is_pdf' => strtolower($fileExtension) === 'pdf',
            ],
        ]);
    }
}
