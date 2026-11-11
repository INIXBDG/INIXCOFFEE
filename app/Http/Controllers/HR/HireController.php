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
use App\Models\PelamarPsikotest;
use App\Models\Pelamarriwayat;
use App\Services\DokumenParser;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use setasign\Fpdi\Tcpdf\Fpdi;
use App\Models\PelamarPenilaian;

class HireController extends Controller
{
    private const BERKAS = [
        'cv' => ['cv_path', 'pelamar/cv'],
        'portofolio' => ['portofolio_path', 'pelamar/portofolio'],
        'foto' => ['foto_path', 'pelamar/foto'],
        'ktp' => ['ktp_path', 'pelamar/ktp'],
        'ijazah' => ['ijazah_path', 'pelamar/ijazah'],
    ];

    private const FILTER = ['search', 'divisi', 'jabatan', 'tahap', 'sumber', 'tanggal_dari', 'tanggal_sampai', 'talent_pool'];

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
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

        return view('HR/hire/index', [
            'dataJabatan' => $dataJabatan,
            'dataDivisi' => $dataDivisi,
            'Interviewer' => $Interviewer,
            'sumberList' => Pelamar::SUMBER,
            'tahapList' => Pelamar::TAHAP,
            'kategoriPsikotest' => PelamarPsikotest::KATEGORI,
            'infoPsikotest' => PelamarPsikotest::info(),
        ]);
    }

    public function data(Request $request)
    {
        $request->validate([
            'folder' => 'nullable|string|max:20',
            'per_page' => 'nullable|integer|min:5|max:100',
        ]);

        $folder = (string) $request->input('folder', 'all');

        $query = Pelamar::aktif()
            ->with(['pelamarFolders', 'psikotests', 'penilaians'])
            ->filter($request->only(self::FILTER));

        if ($folder === 'unassigned') {
            $query->whereDoesntHave('pelamarFolders');
        } elseif ($folder === 'all') {
            $query->where(function ($q) {
                $q->whereDoesntHave('pelamarFolders')
                    ->orWhereHas('pelamarFolders', function ($q2) {
                        $q2->whereHas('folder', function ($q3) {
                            $q3->where('is_archived', false);
                        });
                    });
            });
        } else {
            $target = Folder::find($folder);

            if (!$target || $target->is_archived) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder tidak ditemukan atau sudah diarsipkan',
                    'data' => [],
                ], 404);
            }

            $query->whereHas('pelamarFolders', function ($q) use ($target) {
                $q->where('folder_id', $target->id);
            });
        }

        $paginator = $query
            ->latest('tanggal_melamar')
            ->orderByDesc('id')
            ->paginate((int) $request->input('per_page', 10));

        return response()->json([
            'success' => true,
            'data' => $paginator->getCollection()->map(fn (Pelamar $p) => $this->barisPelamar($p))->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ],
            'funnel' => Pelamar::statsFunnel(),
        ]);
    }

    private function barisPelamar(Pelamar $p): array
    {
        $ratings = $p->penilaians->pluck('rating');

        return [
            'id' => $p->id,
            'inisial' => $p->inisial,
            'nama_lengkap' => $p->nama_lengkap,
            'email' => $p->email,
            'jabatan' => $p->jabatan,
            'divisi' => $p->divisi,
            'detail_jabatan' => $p->detail_jabatan,
            'sumber_lamaran' => $p->sumber_lamaran,
            'tanggal_melamar' => $p->tanggal_melamar?->format('d M Y'),
            'tahap_rekrutmen' => $p->tahap_rekrutmen,
            'tahap_label' => $p->tahap_label,
            'tahap_berikutnya' => $p->tahapBerikutnya(),
            'bisa_lanjut' => $p->bisaLanjut(),
            'mulai_kerja' => $p->tanggal_mulai_kerja?->format('Y-m-d'),
            'cv_url' => $p->cv_url,
            'avg_rating' => $ratings->count() > 0 ? round($ratings->avg(), 1) : null,
            'total_penilai' => $ratings->count(),
            'psikotest' => $p->psikotests->pluck('kategori')->values()->all(),
        ];
    }

    public function analitik(Request $request)
    {
        $request->validate([
            'tanggal_dari' => 'nullable|date',
            'tanggal_sampai' => 'nullable|date',
        ]);

        $pelamars = Pelamar::aktif()
            ->when($request->filled('tanggal_dari'), fn ($q) => $q->whereDate('tanggal_melamar', '>=', $request->tanggal_dari))
            ->when($request->filled('tanggal_sampai'), fn ($q) => $q->whereDate('tanggal_melamar', '<=', $request->tanggal_sampai))
            ->get(['id', 'divisi', 'sumber_lamaran', 'tahap_rekrutmen', 'tanggal_melamar', 'pendidikan_terakhir']);

        $total = $pelamars->count();

        $kartu = ['total' => $total];
        foreach (Pelamar::TAHAP as $kode => $label) {
            $kartu[$kode] = $pelamars->where('tahap_rekrutmen', $kode)->count();
        }
        $kartu['rasio_diterima'] = $total > 0 ? round($kartu['hired'] / $total * 100, 1) : 0;

        $funnel = [
            'label' => array_values(Pelamar::TAHAP),
            'nilai' => array_map(fn ($kode) => $kartu[$kode], array_keys(Pelamar::TAHAP)),
        ];

        $perBulan = $pelamars
            ->filter(fn ($p) => $p->tanggal_melamar)
            ->groupBy(fn ($p) => $p->tanggal_melamar->format('Y-m'))
            ->sortKeys()
            ->slice(-12);

        $trend = ['label' => [], 'lamaran' => [], 'diterima' => [], 'ditolak' => []];
        foreach ($perBulan as $bulan => $grup) {
            $trend['label'][] = Carbon::parse($bulan . '-01')->format('M Y');
            $trend['lamaran'][] = $grup->count();
            $trend['diterima'][] = $grup->where('tahap_rekrutmen', 'hired')->count();
            $trend['ditolak'][] = $grup->where('tahap_rekrutmen', 'rejected')->count();
        }

        $semua = PelamarPsikotest::whereIn('pelamar_id', $pelamars->pluck('id'))->get();

        return response()->json([
            'success' => true,
            'data' => [
                'kartu' => $kartu,
                'funnel' => $funnel,
                'trend' => $trend,
                'sumber' => $this->hitung($pelamars, 'sumber_lamaran'),
                'divisi' => $this->hitung($pelamars, 'divisi'),
                'pendidikan' => $this->hitung($pelamars, 'pendidikan_terakhir'),
                'psikotest' => $this->analitikPsikotest($semua, $total),
            ],
        ]);
    }

    private function hitung(Collection $data, string $kolom, int $batas = 8): array
    {
        $hitung = $data
            ->groupBy(fn ($p) => filled($p->{$kolom}) ? $p->{$kolom} : 'Tidak diketahui')
            ->map->count()
            ->sortDesc()
            ->take($batas);

        return [
            'label' => $hitung->keys()->values()->all(),
            'nilai' => $hitung->values()->all(),
        ];
    }

    private function analitikPsikotest(Collection $semua, int $totalPelamar): array
    {
        $perKategori = $semua->groupBy('kategori');
        $jumlahKategori = count(PelamarPsikotest::KATEGORI);

        $cakupan = [];
        foreach (PelamarPsikotest::KATEGORI as $kode => $label) {
            $cakupan[] = [
                'kode' => $kode,
                'label' => $label,
                'jumlah' => $perKategori->get($kode, collect())->count(),
            ];
        }

        $lengkap = $semua
            ->groupBy('pelamar_id')
            ->filter(fn ($grup) => $grup->pluck('kategori')->unique()->count() === $jumlahKategori)
            ->count();

        $mbti = $perKategori->get('mbti', collect())->filter(fn ($t) => filled($t->hasil));

        $tipe = $mbti
            ->groupBy('hasil')
            ->map->count()
            ->sortDesc()
            ->map(fn ($jumlah, $kode) => [
                'kode' => $kode,
                'nama' => PelamarPsikotest::MBTI_TIPE[$kode]['nama'] ?? '-',
                'ringkas' => PelamarPsikotest::MBTI_TIPE[$kode]['ringkas'] ?? '',
                'jumlah' => $jumlah,
            ])
            ->values()
            ->all();

        $hitungHuruf = fn (string $h) => $mbti->filter(fn ($t) => str_contains($t->hasil, $h))->count();

        $dimensi = [
            ['nama' => 'Extraversion vs Introversion', 'kiri' => 'E', 'kanan' => 'I'],
            ['nama' => 'Sensing vs Intuition', 'kiri' => 'S', 'kanan' => 'N'],
            ['nama' => 'Thinking vs Feeling', 'kiri' => 'T', 'kanan' => 'F'],
            ['nama' => 'Judging vs Perceiving', 'kiri' => 'J', 'kanan' => 'P'],
        ];

        foreach ($dimensi as $i => $d) {
            $dimensi[$i]['jumlah_kiri'] = $hitungHuruf($d['kiri']);
            $dimensi[$i]['jumlah_kanan'] = $hitungHuruf($d['kanan']);
        }

        $riasec = $perKategori->get('riasec', collect())->filter(fn ($t) => filled($t->hasil));
        $dominan = $riasec->groupBy(fn ($t) => substr($t->hasil, 0, 1))->map->count();

        $huruf = [];
        foreach (PelamarPsikotest::RIASEC_HURUF as $kode => $info) {
            $nilai = $riasec
                ->map(fn ($t) => $t->skor_detail[strtolower($kode)] ?? null)
                ->filter(fn ($v) => $v !== null && $v !== '')
                ->map(fn ($v) => (float) $v);

            $huruf[] = [
                'kode' => $kode,
                'nama' => $info['nama'],
                'ringkas' => $info['ringkas'],
                'dominan' => $dominan->get($kode, 0),
                'rata' => $nilai->count() > 0 ? round($nilai->avg(), 1) : null,
            ];
        }

        $trenSkor = ['label' => [], 'logika_numerik' => [], 'analogi_verbal' => []];
        $perBulan = $semua
            ->whereIn('kategori', ['logika_numerik', 'analogi_verbal'])
            ->filter(fn ($t) => $t->skor !== null)
            ->groupBy(fn ($t) => ($t->tanggal_tes ?? $t->created_at)->format('Y-m'))
            ->sortKeys()
            ->slice(-12);

        foreach ($perBulan as $bulan => $grup) {
            $trenSkor['label'][] = Carbon::parse($bulan . '-01')->format('M Y');
            foreach (['logika_numerik', 'analogi_verbal'] as $kode) {
                $skor = $grup->where('kategori', $kode);
                $trenSkor[$kode][] = $skor->count() > 0 ? round($skor->avg('skor'), 1) : null;
            }
        }

        return [
            'total_pelamar' => $totalPelamar,
            'lengkap' => $lengkap,
            'cakupan' => $cakupan,
            'mbti' => [
                'jumlah' => $mbti->count(),
                'tipe' => $tipe,
                'dimensi' => $dimensi,
            ],
            'riasec' => [
                'jumlah' => $riasec->count(),
                'huruf' => $huruf,
            ],
            'logika_numerik' => $this->ringkasSkor($perKategori->get('logika_numerik', collect())),
            'analogi_verbal' => $this->ringkasSkor($perKategori->get('analogi_verbal', collect())),
            'tren_skor' => $trenSkor,
        ];
    }

    private function ringkasSkor(Collection $koleksi): array
    {
        $skor = $koleksi
            ->pluck('skor')
            ->filter(fn ($s) => $s !== null)
            ->map(fn ($s) => (float) $s)
            ->values();

        $level = [];
        foreach (PelamarPsikotest::LEVEL_SKOR as $l) {
            $level[$l['label']] = 0;
        }
        foreach ($skor as $s) {
            $level[PelamarPsikotest::levelSkor($s)]++;
        }

        return [
            'jumlah' => $skor->count(),
            'rata' => $skor->count() > 0 ? round($skor->avg(), 1) : null,
            'tertinggi' => $skor->count() > 0 ? $skor->max() : null,
            'terendah' => $skor->count() > 0 ? $skor->min() : null,
            'level' => [
                'label' => array_keys($level),
                'nilai' => array_values($level),
            ],
        ];
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
        $pelamar->load(['riwayatTahap', 'psikotests']);

        $penilaians = $pelamar->penilaians()
            ->with(['folder', 'penilai'])
            ->orderByDesc('tanggal_dinilai')
            ->get()
            ->map(fn (PelamarPenilaian $n) => $n->ringkas());

        $ratings = $penilaians->pluck('rating');
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
                'psikotests' => $this->psikotestPelamar($pelamar),
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

        foreach ($pelamar->psikotests as $tes) {
            if ($tes->file_path) {
                Storage::disk('local')->delete($tes->file_path);
            }
        }
        $pelamar->psikotests()->delete();
        
        $this->hapusBerkas($pelamar->penilaians()->pluck('file_penilaian')->all());

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
            ->with('psikotests')
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
                'MBTI', 'RIASEC', 'Skor Logika Numerik', 'Skor Analogi Verbal',
            ]);

            foreach ($pelamars as $p) {
                $tes = $p->psikotests->keyBy('kategori');

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
                    $tes->get('mbti')?->hasil,
                    $tes->get('riasec')?->hasil,
                    $tes->get('logika_numerik')?->skor,
                    $tes->get('analogi_verbal')?->skor,
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

    private function psikotestPelamar(Pelamar $pelamar): array
    {
        $tes = $pelamar->psikotests->keyBy('kategori');
        $hasil = [];

        foreach (PelamarPsikotest::KATEGORI as $kode => $label) {
            $hasil[$kode] = $tes->get($kode)?->ringkas();
        }

        return $hasil;
    }

    public function penilaianIndex(Pelamar $pelamar)
    {
        $items = $pelamar->penilaians()
            ->with(['folder', 'penilai'])
            ->orderByDesc('tanggal_dinilai')
            ->get()
            ->map(fn (PelamarPenilaian $n) => $n->ringkas())
            ->values();

        $perTahap = $items
            ->groupBy(fn ($i) => $i['tahap'] ?? 'tidak_tercatat')
            ->map(fn ($grup, $kode) => [
                'tahap' => $kode,
                'label' => $grup->first()['tahap_label'],
                'jumlah' => $grup->count(),
                'rata' => round($grup->avg('rating'), 1),
            ])
            ->values();

        return response()->json([
            'success' => true,
            'pelamar' => ['id' => $pelamar->id, 'nama_lengkap' => $pelamar->nama_lengkap],
            'rata' => $items->count() ? round($items->avg('rating'), 1) : null,
            'total' => $items->count(),
            'per_tahap' => $perTahap,
            'items' => $items,
        ]);
    }

    public function psikotestIndex(Pelamar $pelamar)
    {
        $pelamar->load('psikotests');

        return response()->json([
            'success' => true,
            'pelamar' => ['id' => $pelamar->id, 'nama_lengkap' => $pelamar->nama_lengkap],
            'data' => $this->psikotestPelamar($pelamar),
        ]);
    }

    public function psikotestSimpan(Request $request, Pelamar $pelamar)
    {
        $request->validate([
            'kategori' => ['required', Rule::in(array_keys(PelamarPsikotest::KATEGORI))],
        ]);

        $kategori = $request->input('kategori');
        $lama = $pelamar->psikotests()->where('kategori', $kategori)->first();

        $aturan = [
            'tanggal_tes' => 'nullable|date',
            'catatan' => 'nullable|string|max:2000',
            'file' => [
                $lama && $lama->file_path ? 'nullable' : 'required',
                'file',
                'mimes:pdf,jpg,jpeg,png,doc,docx',
                'max:10240',
            ],
        ];

        if ($kategori === 'mbti') {
            $aturan['hasil'] = ['required', 'regex:/^[EI][SN][TF][JP]$/i'];
        } elseif ($kategori === 'riasec') {
            $aturan['hasil'] = [
                'required',
                'regex:/^[RIASEC]{1,6}$/i',
                function ($atribut, $nilai, $gagal) {
                    if (count(array_unique(str_split(strtoupper($nilai)))) !== strlen($nilai)) {
                        $gagal('Kode RIASEC tidak boleh mengulang huruf.');
                    }
                },
            ];
            $aturan['skor_detail'] = 'nullable|array';
            $aturan['skor_detail.*'] = 'nullable|numeric|min:0|max:100';
        } else {
            $aturan['skor'] = 'required|numeric|min:0|max:100';
        }

        $request->validate($aturan, [
            'hasil.regex' => 'Format hasil tidak sesuai untuk kategori ini.',
            'file.required' => 'Dokumen hasil psikotest wajib diunggah.',
        ]);

        $fileBaru = null;
        $fileLama = $lama?->file_path;

        DB::beginTransaction();
        try {
            $payload = [
                'hasil' => in_array($kategori, ['mbti', 'riasec'], true) ? strtoupper((string) $request->input('hasil')) : null,
                'skor' => in_array($kategori, ['logika_numerik', 'analogi_verbal'], true) ? $request->input('skor') : null,
                'skor_detail' => $kategori === 'riasec' ? $this->bersihkanDetail((array) $request->input('skor_detail', [])) : null,
                'tanggal_tes' => $request->input('tanggal_tes'),
                'catatan' => $request->input('catatan'),
                'diinput_oleh' => auth()->id(),
            ];

            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileBaru = $file->store('pelamar/psikotest', 'local');

                $payload['file_path'] = $fileBaru;
                $payload['file_nama'] = $file->getClientOriginalName();
                $payload['file_mime'] = $file->getMimeType() ?: $file->getClientMimeType();
            }

            $tes = PelamarPsikotest::updateOrCreate(
                ['pelamar_id' => $pelamar->id, 'kategori' => $kategori],
                $payload
            );

            $nilai = $tes->hasil ?: ($tes->skor !== null ? rtrim(rtrim(number_format($tes->skor, 2, '.', ''), '0'), '.') : '-');

            Pelamarriwayat::catat($pelamar->id, 'psikotest', [
                'keterangan' => sprintf(
                    'Hasil psikotest %s %s: %s',
                    PelamarPsikotest::KATEGORI[$kategori],
                    $lama ? 'diperbarui' : 'diunggah',
                    $nilai
                ),
                'metadata' => ['kategori' => $kategori, 'hasil' => $tes->hasil, 'skor' => $tes->skor],
            ]);

            DB::commit();

            if ($fileBaru && $fileLama) {
                Storage::disk('local')->delete($fileLama);
            }

            return response()->json([
                'success' => true,
                'message' => 'Hasil psikotest ' . PelamarPsikotest::KATEGORI[$kategori] . ' berhasil disimpan.',
                'data' => $tes->ringkas(),
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            if ($fileBaru) {
                Storage::disk('local')->delete($fileBaru);
            }

            return response()->json(['success' => false, 'message' => 'Gagal menyimpan psikotest: ' . $e->getMessage()], 500);
        }
    }

    private function bersihkanDetail(array $detail): ?array
    {
        $hasil = [];

        foreach (['r', 'i', 'a', 's', 'e', 'c'] as $huruf) {
            if (isset($detail[$huruf]) && $detail[$huruf] !== '') {
                $hasil[$huruf] = (float) $detail[$huruf];
            }
        }

        return $hasil ?: null;
    }

    public function psikotestHapus(Pelamar $pelamar, string $kategori)
    {
        $tes = $pelamar->psikotests()->where('kategori', $kategori)->firstOrFail();

        DB::beginTransaction();
        try {
            $path = $tes->file_path;
            $tes->delete();

            Pelamarriwayat::catat($pelamar->id, 'psikotest_dihapus', [
                'keterangan' => 'Hasil psikotest ' . (PelamarPsikotest::KATEGORI[$kategori] ?? $kategori) . ' dihapus.',
                'metadata' => ['kategori' => $kategori],
            ]);

            DB::commit();

            if ($path) {
                Storage::disk('local')->delete($path);
            }

            return response()->json(['success' => true, 'message' => 'Hasil psikotest berhasil dihapus.']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function psikotestFile(Request $request, Pelamar $pelamar, string $kategori)
    {
        $tes = $pelamar->psikotests()->where('kategori', $kategori)->firstOrFail();

        abort_unless($tes->file_path && Storage::disk('local')->exists($tes->file_path), 404);

        $path = Storage::disk('local')->path($tes->file_path);
        $nama = $tes->file_nama ?: basename($path);

        if ($request->boolean('unduh')) {
            return response()->download($path, $nama);
        }

        return response()->file($path, [
            'Content-Type' => $tes->file_mime ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . addslashes($nama) . '"',
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

        $pelamar = Pelamar::findOrFail($request->pelamar_id);
        $filePath = null;
        $fileLama = null;

        DB::beginTransaction();
        try {
            if ($request->hasFile('file_penilaian')) {
                $filePath = $request->file('file_penilaian')->store('penilaian', 'public');
            }

            // Folder hanya menyimpan keanggotaan. Penilaian disimpan di tabel sendiri.
            $keanggotaan = PelamarFolder::where('pelamar_id', $pelamar->id)->first();
            if ($keanggotaan) {
                $keanggotaan->update(['folder_id' => $request->folder_id]);
            } else {
                PelamarFolder::create([
                    'folder_id' => $request->folder_id,
                    'pelamar_id' => $pelamar->id,
                    'dinilai_oleh' => auth()->id(),
                    'tanggal_dinilai' => now(),
                ]);
            }

            // Satu penilaian per penilai per tahap. Menilai ulang di tahap yang sama memperbarui penilaian lama.
            $kunci = [
                'pelamar_id' => $pelamar->id,
                'dinilai_oleh' => auth()->id(),
                'tahap_rekrutmen' => $pelamar->tahap_rekrutmen,
            ];

            $lama = PelamarPenilaian::where($kunci)->first();
            $fileLama = $filePath ? $lama?->file_penilaian : null;

            PelamarPenilaian::updateOrCreate($kunci, [
                'folder_id' => $request->folder_id,
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
                'message' => $lama
                    ? 'Penilaian Anda untuk tahap ini berhasil diperbarui.'
                    : 'Penilaian berhasil disimpan.',
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