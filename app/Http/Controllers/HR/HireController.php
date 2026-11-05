<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Mail\EmailKustom;
use App\Mail\EmailPenolakan;
use App\Mail\NotifikasiInterview;
use App\Mail\NotifikasiTahap;
use App\Mail\OfferLetter;
use App\Models\Folder;
use App\Models\karyawan;
use App\Models\Pelamar;
use App\Models\PelamarFolder;
use App\Models\Pelamarriwayat;
use App\Services\DokumenParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use setasign\Fpdi\Tcpdf\Fpdi;

class HireController extends Controller
{
    private const BERKAS = [
        'cv' => ['cv_path', 'pelamar/cv'],
        'portofolio' => ['portofolio_path', 'pelamar/portofolio'],
        'foto' => ['foto_path', 'pelamar/foto'],
        'ktp' => ['ktp_path', 'pelamar/ktp'],
        'ijazah' => ['ijazah_path', 'pelamar/ijazah'],
    ];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $dataJabatan = karyawan::whereNotIn('jabatan', ['Pilih Jabatan'])
            ->whereNot('divisi', 'Direksi')
            ->distinct()
            ->pluck('jabatan');

        $dataDivisi = karyawan::whereNotIn('divisi', ['Pilih Divisi', 'Direksi'])
            ->distinct()
            ->pluck('divisi');

        $Interviewer = karyawan::whereIn('jabatan', ['HRD', 'GM', 'Direktur Utama', 'Direktur', 'Koordinator ITSM', 'SPV Sales', 'Education Manager'])
            ->whereNotNull('nip')
            ->get();

        $query = Pelamar::aktif()
            ->with('pelamarFolders')
            ->where(function ($q) {
                $q->whereDoesntHave('pelamarFolders')
                    ->orWhereHas('pelamarFolders', function ($q2) {
                        $q2->whereHas('folder', function ($q3) {
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
        if ($folderId === 'all') {
            $pelamars = Pelamar::aktif()
                ->with('pelamarFolders')
                ->latest('tanggal_melamar')
                ->get();
        } elseif ($folderId === 'unassigned' || $folderId === null) {
            $pelamarDiFolder = PelamarFolder::pluck('pelamar_id');
            $pelamars = Pelamar::aktif()
                ->with('pelamarFolders')
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
                ->with('pelamarFolders')
                ->whereIn('id', $pelamarIds)
                ->latest('tanggal_melamar')
                ->get();
        }

        $pelamarIds = $pelamars->pluck('id');
        $penilaians = PelamarFolder::whereIn('pelamar_id', $pelamarIds)
            ->with(['interviewer', 'folder'])
            ->get()
            ->groupBy('pelamar_id');

        $data = $pelamars->map(function ($p, $index) use ($penilaians) {
            $penilaianPelamar = $penilaians->get($p->id, collect());
            $ratings = $penilaianPelamar->whereNotNull('rating')->pluck('rating');
            $avgRating = $ratings->count() > 0 ? round($ratings->avg(), 1) : null;

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
                'avg_rating' => $avgRating,
                'total_penilai' => $ratings->count(),
                'penilaian_list' => $penilaianPelamar->map(function ($pf) {
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
            'html' => view('HR.hire._pelamar_table', ['pelamars' => $pelamars])->render(),
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

    private function aturan(?Pelamar $pelamar = null): array
    {
        return [
            'nama_lengkap' => 'required|string|max:100',
            'email' => ['required', 'email', 'max:150', Rule::unique('pelamars', 'email')->ignore($pelamar?->id)],
            'no_telepon' => 'required|string|max:20',
            'nik_ktp' => 'nullable|string|max:20',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'status_pernikahan' => 'nullable|string|max:30',
            'agama' => 'nullable|string|max:30',
            'domisili' => 'nullable|string|max:100',
            'alamat' => 'nullable|string|max:500',
            'linkedin_url' => 'nullable|string|max:255',
            'pendidikan_terakhir' => 'nullable|string|max:50',
            'jurusan' => 'nullable|string|max:100',
            'institusi' => 'nullable|string|max:150',
            'ipk' => 'nullable|numeric|min:0|max:4',
            'divisi' => 'required|string|max:100',
            'jabatan' => 'required|string|max:100',
            'detail_jabatan' => 'nullable|string|max:100',
            'tanggal_melamar' => 'required|date',
            'sumber_lamaran' => 'required|string|max:100',
            'pengalaman_tahun' => 'nullable|integer|min:0|max:50',
            'gaji_diharapkan' => 'nullable|string|max:30',
            'notice_period' => 'nullable|string|max:50',
            'keahlian' => 'nullable|string',
            'catatan_hr' => 'nullable|string',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            'portofolio' => 'nullable|file|mimes:pdf,doc,docx,zip|max:10240',
            'foto' => 'nullable|image|max:3072',
            'ktp' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'ijazah' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    private function siapkanData(array $validated): array
    {
        foreach (array_keys(self::BERKAS) as $field) {
            unset($validated[$field]);
        }

        if (array_key_exists('gaji_diharapkan', $validated)) {
            $angka = preg_replace('/[^0-9]/', '', (string) $validated['gaji_diharapkan']);
            $validated['gaji_diharapkan'] = $angka === '' ? null : (int) $angka;
        }

        if (array_key_exists('keahlian', $validated)) {
            $daftar = array_values(array_filter(array_map('trim', explode(',', (string) $validated['keahlian']))));
            $validated['keahlian'] = $daftar ?: null;
        }

        return $validated;
    }

    private function simpanBerkas(Request $request): array
    {
        $hasil = [];

        foreach (self::BERKAS as $field => [$kolom, $folder]) {
            if ($request->hasFile($field)) {
                $hasil[$kolom] = $request->file($field)->store($folder, 'public');
            }
        }

        return $hasil;
    }

    private function hapusBerkas(array $paths): void
    {
        foreach ($paths as $path) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    public function scanDokumen(Request $request, DokumenParser $parser)
    {
        $request->validate([
            'dokumen' => 'required_without:teks|nullable|file|mimes:pdf,docx,jpg,jpeg,png|max:10240',
            'teks' => 'required_without:dokumen|nullable|string|max:60000',
            'jenis' => 'nullable|in:cv,ktp,ijazah',
        ]);

        $teks = (string) $request->input('teks', '');

        if ($request->hasFile('dokumen')) {
            $teks = $parser->ekstrakTeks($request->file('dokumen'));
        }

        if (trim($teks) === '') {
            return response()->json([
                'success' => false,
                'message' => 'Teks pada dokumen tidak terbaca. Jika dokumen berupa hasil scan, unggah dalam bentuk gambar (JPG/PNG).',
            ], 422);
        }

        $data = $parser->parse($teks);

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen terbaca, tetapi tidak ada data yang dapat dikenali.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => count($data) . ' kolom berhasil dikenali dari dokumen.',
            'data' => $data,
            'total' => count($data),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->aturan());

        $berkas = [];

        DB::beginTransaction();
        try {
            $data = $this->siapkanData($validated);
            $berkas = $this->simpanBerkas($request);

            $pelamar = Pelamar::create(array_merge($data, $berkas, [
                'tahap_rekrutmen' => 'applied',
                'status_aktif' => true,
            ]));

            Pelamarriwayat::catat($pelamar->id, 'created', [
                'tahap_ke' => 'applied',
                'keterangan' => 'Pelamar ditambahkan secara manual oleh HR.',
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Pelamar berhasil ditambahkan.', 'data' => $pelamar]);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->hapusBerkas($berkas);
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

        $kolom = [
            'nama_lengkap', 'email', 'no_telepon', 'nik_ktp', 'tempat_lahir', 'jenis_kelamin',
            'status_pernikahan', 'agama', 'domisili', 'alamat', 'linkedin_url', 'pendidikan_terakhir',
            'jurusan', 'institusi', 'ipk', 'divisi', 'jabatan', 'detail_jabatan', 'sumber_lamaran',
            'pengalaman_tahun', 'gaji_diharapkan', 'notice_period', 'catatan_hr',
        ];

        $form = collect($kolom)->mapWithKeys(fn ($k) => [$k => $pelamar->{$k}])->all();
        $form['tanggal_lahir'] = $pelamar->tanggal_lahir ? Carbon::parse($pelamar->tanggal_lahir)->format('Y-m-d') : null;
        $form['tanggal_melamar'] = $pelamar->tanggal_melamar ? Carbon::parse($pelamar->tanggal_melamar)->format('Y-m-d') : null;
        $form['keahlian'] = is_array($pelamar->keahlian) ? implode(', ', $pelamar->keahlian) : ($pelamar->keahlian ?? '');

        $dokumen = [];
        foreach (self::BERKAS as $field => [$kolomPath]) {
            $dokumen[$field] = $pelamar->{$kolomPath} ? Storage::url($pelamar->{$kolomPath}) : null;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'pelamar' => $pelamar,
                'form' => $form,
                'dokumen' => $dokumen,
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
        $validated = $request->validate($this->aturan($pelamar));

        $berkas = [];
        $pathLama = $pelamar->only(array_column(self::BERKAS, 0));

        DB::beginTransaction();
        try {
            $data = $this->siapkanData($validated);
            $berkas = $this->simpanBerkas($request);

            $pelamar->update(array_merge($data, $berkas));

            Pelamarriwayat::catat($pelamar->id, 'updated', [
                'keterangan' => 'Data pelamar diperbarui.',
                'metadata' => ['kolom_berubah' => array_keys($pelamar->getChanges())],
            ]);

            DB::commit();

            foreach ($berkas as $kolom => $path) {
                if (!empty($pathLama[$kolom])) {
                    Storage::disk('public')->delete($pathLama[$kolom]);
                }
            }

            return response()->json(['success' => true, 'message' => 'Data pelamar berhasil diperbarui.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->hapusBerkas($berkas);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy(Pelamar $pelamar)
    {
        $this->hapusBerkas($pelamar->only(array_column(self::BERKAS, 0)));

        $pelamar->delete();

        return response()->json(['success' => true, 'message' => 'Pelamar berhasil dihapus.']);
    }

    public function lanjutTahap(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'tahap_ke' => ['required', Rule::in(array_keys(Pelamar::TAHAP))],
            'rating' => 'nullable|integer|min:1|max:4',
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

            Pelamarriwayat::catat($pelamar->id, 'moved', [
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
            'alasan_lainnya' => 'nullable|string',
            'catatan_internal' => 'nullable|string',
            'notif_email' => 'nullable|boolean',
            'simpan_talent_pool' => 'nullable|boolean',
        ]);

        DB::beginTransaction();
        try {
            $tahapLama = $pelamar->tahap_rekrutmen;

            $alasan = $request->alasan_penolakan;
            if ($alasan === 'lainnya' && filled($request->alasan_lainnya)) {
                $alasan = $request->alasan_lainnya;
            }

            $pelamar->update([
                'tahap_rekrutmen' => 'rejected',
                'alasan_penolakan' => $alasan,
                'catatan_internal' => $request->catatan_internal,
                'simpan_talent_pool' => $request->boolean('simpan_talent_pool'),
            ]);

            Pelamarriwayat::catat($pelamar->id, 'rejected', [
                'tahap_dari' => $tahapLama,
                'tahap_ke' => 'rejected',
                'keterangan' => $alasan ?? $request->catatan_internal,
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

            Pelamarriwayat::catat($pelamar->id, 'interview_scheduled', [
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

        $offerPath = null;

        DB::beginTransaction();
        try {
            $gaji = (int) preg_replace('/[^0-9]/', '', $request->gaji_ditawarkan);
            $tunjanganMakan = (int) preg_replace('/[^0-9]/', '', $request->tunjangan_makan);
            $tunjanganTransport = (int) preg_replace('/[^0-9]/', '', $request->tunjangan_transport);

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

            Pelamarriwayat::catat($pelamar->id, 'offer_sent', [
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
            $this->hapusBerkas([$offerPath]);
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

            Pelamarriwayat::catat($pelamar->id, 'onboarded', [
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

            Pelamarriwayat::catat($pelamar->id, 'email_sent', [
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
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim email: ' . $e->getMessage(),
            ], 500);
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

        Pelamarriwayat::catat($pelamar->id, 'note', [
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

    public function riwayat(Pelamar $pelamar)
    {
        $riwayat = $pelamar->riwayatTahap()->get();
        return response()->json(['success' => true, 'data' => $riwayat]);
    }

    public function export(Request $request)
    {
        $pelamars = Pelamar::aktif()
            ->filter($request->only(['search', 'tahap', 'divisi', 'jabatan', 'sumber', 'tanggal_dari', 'tanggal_sampai']))
            ->latest('tanggal_melamar')
            ->get();

        $namaFile = 'pelamar_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($pelamars) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'Nama Lengkap', 'Email', 'No. Telepon', 'NIK', 'Tempat Lahir', 'Tanggal Lahir', 'Jenis Kelamin',
                'Status Pernikahan', 'Agama', 'Domisili', 'Alamat', 'LinkedIn', 'Pendidikan', 'Institusi',
                'Jurusan', 'IPK', 'Divisi', 'Posisi', 'Detail Posisi', 'Tanggal Melamar', 'Sumber',
                'Pengalaman (Tahun)', 'Gaji Diharapkan', 'Notice Period', 'Keahlian', 'Tahapan', 'Catatan HR',
            ]);

            foreach ($pelamars as $p) {
                fputcsv($out, [
                    $p->nama_lengkap,
                    $p->email,
                    $p->no_telepon,
                    $p->nik_ktp,
                    $p->tempat_lahir,
                    $p->tanggal_lahir ? Carbon::parse($p->tanggal_lahir)->format('Y-m-d') : '',
                    $p->jenis_kelamin,
                    $p->status_pernikahan,
                    $p->agama,
                    $p->domisili,
                    $p->alamat,
                    $p->linkedin_url,
                    $p->pendidikan_terakhir,
                    $p->institusi,
                    $p->jurusan,
                    $p->ipk,
                    $p->divisi,
                    $p->jabatan,
                    $p->detail_jabatan,
                    $p->tanggal_melamar?->format('Y-m-d'),
                    $p->sumber_lamaran,
                    $p->pengalaman_tahun,
                    $p->gaji_diharapkan,
                    $p->notice_period,
                    is_array($p->keahlian) ? implode(', ', $p->keahlian) : $p->keahlian,
                    $p->tahap_label,
                    $p->catatan_hr,
                ]);
            }

            fclose($out);
        }, $namaFile, ['Content-Type' => 'text/csv; charset=UTF-8']);
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

            Mail::to($pelamar->email)->send(new NotifikasiInterview(
                pelamar: $pelamar,
                jadwal: $pelamar->jadwal_interview,
                metode: $metodeLabel,
                linkMeeting: $pelamar->link_meeting,
                lokasi: $pelamar->lokasi_interview,
                interviewer: $pelamar->interviewer,
                tahapInterview: $tahapLabel,
                catatan: null
            ));
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

        $folder = Folder::find($request->folder_id);
        if (!$folder || $folder->is_archived) {
            return response()->json([
                'success' => false,
                'message' => 'Folder tidak ditemukan atau sudah diarsipkan.',
            ], 422);
        }

        $filePath = null;

        DB::beginTransaction();
        try {
            if ($request->hasFile('file_penilaian')) {
                $filePath = $request->file('file_penilaian')->store('penilaian', 'public');
            }

            $pelamarFolder = PelamarFolder::where('pelamar_id', $request->pelamar_id)->first();

            if ($pelamarFolder) {
                $pelamarFolder->update([
                    'folder_id' => $request->folder_id,
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

            Pelamarriwayat::catat($request->pelamar_id, 'rated', [
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
            $this->hapusBerkas([$filePath]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan penilaian: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function lihatCV(Pelamar $pelamar)
    {
        if (!$pelamar->cv_path) {
            return response()->json([
                'success' => false,
                'message' => 'Pelamar belum mengupload CV.',
            ], 404);
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