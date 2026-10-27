<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\DataPassword;
use App\Models\karyawan;
use App\Models\LogGaji;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class MasterKaryawanController extends Controller
{
    private const DOC_FIELDS = [
        'KTP'           => 'KTP',
        'KK'            => 'KK',
        'NPWP'          => 'NPWP',
        'SIM_A'         => 'SIM A',
        'SIM_B'         => 'SIM B',
        'SIM_C'         => 'SIM C',
        'Ijazah'        => 'Ijazah',
        'CV'            => 'CV',
        'doc_kontrak'   => 'Dokumen kontrak',
        'doc_probation' => 'Dokumen probation',
        'skema_gaji'    => 'Skema gaji',
    ];

    private const DATE_FIELDS = [
        'tanggal_lahir',
        'awal_probation',
        'akhir_probation',
        'awal_kontrak',
        'akhir_kontrak',
        'awal_tetap',
        'akhir_tetap',
    ];

    private const FORM_FIELDS = [
        'nip',
        'nama_lengkap',
        'nik',
        'tanggal_lahir',
        'whatsapp',
        'pendidikan_terakhir',
        'kejuruan',
        'alamat_ktp',
        'alamat_lengkap',
        'divisi',
        'jabatan',
        'status_karyawan',
        'awal_probation',
        'akhir_probation',
        'awal_kontrak',
        'akhir_kontrak',
        'awal_tetap',
        'rekening_maybank',
        'rekening_bca',
        'keterangan',
    ];

    private const ORDER_COLUMNS = ['nama_lengkap', 'divisi', 'jabatan', 'status_karyawan'];

    private const REVIEW_GAJI_BULAN = 12;

    private const CACHE_TTL = 300;

    private const IDLE_MENIT = 5;
    private const SESSION_KEY = 'master_karyawan_unlocked_until';

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $mode = 'main';

        if (!$this->master()) {
            $mode = $this->isHrd() ? 'create' : 'denied';
        }

        return view('HR.master_karyawan.index', [
            'mode'     => $mode,
            'unlocked' => $this->unlocked(),
        ]);
    }

    public function storeMaster(Request $request)
    {
        abort_unless($this->isHrd(), 403);

        if ($this->master()) {
            return redirect()->route('HR.master_karyawan.index');
        }

        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'max:50', 'confirmed'],
        ], [
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.max'       => 'Password maksimal 50 karakter.',
        ]);

        DataPassword::create([
            'judul'    => DataPassword::MASTER_KARYAWAN,
            'kategori' => DataPassword::MASTER_KARYAWAN,
            'password' => Crypt::encryptString($data['password']),
        ]);

        $this->touchSession();
        Log::info('master_karyawan: password dibuat', ['user' => auth()->id()]);

        return redirect()->route('HR.master_karyawan.index');
    }

    public function unlock(Request $request): JsonResponse
    {
        $request->validate(['password' => ['required', 'string']]);

        $master = $this->master();

        if (!$master) {
            return response()->json(['message' => 'Password Master Karyawan belum dibuat.'], 409);
        }

        if (!$master->verifySecret($request->input('password'))) {
            Log::warning('master_karyawan: gagal membuka', ['user' => auth()->id()]);

            return response()->json(['message' => 'Password salah.'], 422);
        }

        $this->touchSession();
        Log::info('master_karyawan: dibuka', ['user' => auth()->id()]);

        return response()->json(['message' => 'Terbuka.']);
    }

    public function lock(): JsonResponse
    {
        session()->forget(self::SESSION_KEY);

        return response()->json(['message' => 'Terkunci.']);
    }

    private function master(): ?DataPassword
    {
        return DataPassword::where('kategori', DataPassword::MASTER_KARYAWAN)->first();
    }

    private function isHrd(): bool
    {
        return auth()->check() && auth()->user()->jabatan === 'HRD';
    }

    private function unlocked(): bool
    {
        $until = session(self::SESSION_KEY);

        return $until && now()->timestamp < $until;
    }

    private function touchSession(): void
    {
        session([self::SESSION_KEY => now()->addMinutes(self::IDLE_MENIT)->timestamp]);
    }

    private function guard(): ?JsonResponse
    {
        if (!$this->master()) {
            return response()->json(['message' => 'Password Master Karyawan belum dibuat.'], 409);
        }

        if (!$this->unlocked()) {
            return response()->json(['message' => 'Terkunci.'], 423);
        }

        $this->touchSession();

        return null;
    }

    public function get(Request $request)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $today = Carbon::today();

        if ($request->boolean('summary_only')) {
            $summary = Cache::remember('mk_summary', self::CACHE_TTL, function () use ($today) {
                return $this->ringkasan($this->baseQuery()->with($this->relasiLog())->get(), $today);
            });

            return response()->json(['summary' => $summary]);
        }

        $total = $this->baseQuery()->count();
        $query = $this->terapkanFilter($this->baseQuery(), $request);
        $filtered = (clone $query)->count();

        $indeks = (int) $request->input('order.0.column', 0);
        $arah = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';
        $kolom = self::ORDER_COLUMNS[$indeks] ?? 'nama_lengkap';

        $panjang = (int) $request->input('length', 10);
        $panjang = $panjang < 1 ? 10 : min($panjang, 100);
        $mulai = max(0, (int) $request->input('start', 0));

        $rows = $query
            ->with($this->relasiLog())
            ->orderBy($kolom, $arah)
            ->orderBy('id')
            ->skip($mulai)
            ->take($panjang)
            ->get();

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $total,
            'recordsFiltered' => $filtered,
            'data'            => $rows->map(fn ($k) => $this->baris($k, $today))->values(),
        ]);
    }

    public function detail($id)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $today = Carbon::today();
        $k = karyawan::with($this->relasiLog())->findOrFail($id);

        $raw = [];
        foreach (self::FORM_FIELDS as $f) {
            $v = $k->$f;
            $raw[$f] = in_array($f, self::DATE_FIELDS) && $v ? Carbon::parse($v)->format('Y-m-d') : $v;
        }

        $fields = [
            'nama_lengkap'        => $k->nama_lengkap,
            'nik'                 => $k->nik,
            'tanggal_lahir'       => $this->tanggal($k->tanggal_lahir),
            'pendidikan_terakhir' => $k->pendidikan_terakhir,
            'kejuruan'            => $k->kejuruan,
            'no_hp'               => $k->whatsapp ?: $k->Telepon,
            'alamat_ktp'          => $k->alamat_ktp,
            'alamat_domisili'     => $k->alamat_lengkap,
            'rekening_maybank'    => $k->rekening_maybank,
            'rekening_bca'        => $k->rekening_bca,
            'keterangan'          => $k->keterangan,
            'masa_kerja'          => $this->durasi($k->awal_probation ?: $k->awal_kontrak),
            'masa_kontrak'        => $this->durasiKontrak($k->awal_kontrak, $k->akhir_kontrak),
            'basic_salary'        => $this->rupiah($this->basicSalary($k)),
            'awal_probation'      => $this->tanggal($k->awal_probation),
            'akhir_probation'     => $this->tanggal($k->akhir_probation),
            'awal_kontrak'        => $this->tanggal($k->awal_kontrak),
            'akhir_kontrak'       => $this->tanggal($k->akhir_kontrak),
            'awal_tetap'          => $this->tanggal($k->awal_tetap),
            'akhir_tetap'         => $this->tanggal($k->akhir_tetap),
        ];

        $dokumen = [];
        foreach (self::DOC_FIELDS as $kolom => $label) {
            $dokumen[] = [
                'key'   => $kolom,
                'label' => $label,
                'file'  => $k->$kolom,
                'url'   => $k->$kolom ? asset('dokumen/' . $k->$kolom) : null,
            ];
        }

        return response()->json([
            'baris'         => $this->baris($k, $today),
            'raw'           => $raw,
            'fields'        => $fields,
            'dokumen'       => $dokumen,
            'log_gaji'      => $k->logGaji->map(fn ($l) => [
                'bulan'             => (int) $l->bulan,
                'tahun'             => (int) $l->tahun,
                'gaji'              => (int) $l->gaji,
                'tunjangan_jabatan' => (int) $l->tunjangan_jabatan,
                'created_at'        => $l->created_at?->toIso8601String(),
            ])->values(),
            'gaji_sekarang' => [
                'gaji'      => (int) ($k->gaji ?? 0),
                'tunjangan' => (int) ($k->tunjangan_jabatan ?? 0),
            ],
            'reminder'      => $this->susunReminder(collect([$k]), $today),
        ]);
    }

    public function reminder(Request $request)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $today = Carbon::today();

        $semua = Cache::remember('mk_reminder', self::CACHE_TTL, function () use ($today) {
            return $this->susunReminder($this->baseQuery()->with($this->relasiLog())->get(), $today);
        });

        $data = $semua
            ->when($request->filled('jenis'), fn ($c) => $c->where('jenis', $request->jenis))
            ->when($request->filled('status'), fn ($c) => $c->where('status', $request->status))
            ->values();

        return response()->json(['data' => $data]);
    }

    public function store(Request $request)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $request->validate($this->aturanKaryawan() + [
            'nama_lengkap' => 'required|string|max:255',
            'gaji_awal'    => 'nullable|numeric|min:0',
        ]);

        $data = $request->except(array_merge(['id', '_token', 'foto', 'gaji_awal'], array_keys(self::DOC_FIELDS)));
        $data['status_karyawan'] = $data['status_karyawan'] ?? 'Probation';

        $k = karyawan::create($data);
        $this->simpanBerkas($k, $request);
        $k->save();

        if ($request->filled('gaji_awal')) {
            $acuan = Carbon::parse($k->awal_probation ?: ($k->awal_kontrak ?: Carbon::today()));

            LogGaji::create([
                'id_karyawan'       => $k->id,
                'gaji'              => $request->gaji_awal,
                'tahun'             => $acuan->year,
                'bulan'             => $acuan->month,
                'tunjangan_jabatan' => 0,
            ]);

            $k->update(['gaji' => $request->gaji_awal]);
        }

        return $this->selesai($request, 'Karyawan berhasil ditambahkan.');
    }

    public function UpdateData(Request $request)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $request->validate($this->aturanKaryawan());

        $k = karyawan::find($request->id);

        if (!$k) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Karyawan tidak ditemukan.'], 404);
            }

            return redirect()->back()->with('error', 'Gagal memperbarui data karyawan.');
        }

        $k->update($request->except(array_merge(['id', '_token', 'foto', 'gaji_awal'], array_keys(self::DOC_FIELDS))));
        $this->simpanBerkas($k, $request);
        $k->save();

        return $this->selesai($request, 'Data karyawan berhasil diperbarui.');
    }

    public function downloadDokumen($id)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $k = karyawan::findOrFail($id);

        $files = [];
        foreach (self::DOC_FIELDS as $kolom => $label) {
            if (!empty($k->$kolom)) {
                $path = public_path('dokumen/' . $k->$kolom);
                if (file_exists($path)) {
                    $files[] = [
                        'path' => $path,
                        'name' => $label . '.' . pathinfo($k->$kolom, PATHINFO_EXTENSION),
                    ];
                }
            }
        }

        if (empty($files)) {
            return response()->json(['message' => 'Tidak ada dokumen yang bisa diunduh.'], 404);
        }

        $zipName = 'Dokumen_' . Str::slug($k->nama_lengkap) . '_' . date('Ymd_His') . '.zip';
        $folder = storage_path('app/temp');
        $zipPath = $folder . '/' . $zipName;

        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return response()->json(['message' => 'Gagal membuat file ZIP.'], 500);
        }

        foreach ($files as $file) {
            $zip->addFile($file['path'], $file['name']);
        }
        $zip->close();

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }

    public function updateReminder(Request $request)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $request->validate([
            'id'                => 'required|integer',
            'status'            => 'required|in:done,pending,kartap',
            'alasan'            => 'required_if:status,pending|nullable|string',
            'jenis_perpanjang'  => 'nullable|in:probation,kontrak',
            'lama_perpanjang'   => 'nullable|integer|min:1|required_with:jenis_perpanjang',
            'alasan_perpanjang' => 'nullable|string|required_with:jenis_perpanjang',
        ]);

        $today = Carbon::today();
        $k = karyawan::findOrFail($request->id);

        $label = ['done' => 'Sudah update', 'pending' => 'Pending', 'kartap' => 'Masuk Kartap'][$request->status];
        $baris = '[' . $today->format('d/m/Y') . '] ' . $label;

        if ($request->filled('alasan')) {
            $baris .= ': ' . $request->alasan;
        }

        if ($request->status === 'kartap') {
            $k->status_karyawan = 'Kartap';
            $k->awal_tetap = $today->toDateString();
        } elseif ($request->filled('jenis_perpanjang')) {
            $bulan = (int) $request->lama_perpanjang;

            if ($request->jenis_perpanjang === 'probation') {
                $k->akhir_probation = Carbon::parse($k->akhir_probation ?: $today)->addMonths($bulan)->toDateString();
                $baris .= ' | Probation diperpanjang ' . $bulan . ' bulan';
            } else {
                $k->akhir_kontrak = Carbon::parse($k->akhir_kontrak ?: $today)->addMonths($bulan)->toDateString();
                $baris .= ' | Kontrak diperpanjang ' . $bulan . ' bulan';
            }

            $baris .= ' (' . $request->alasan_perpanjang . ')';
        }

        $k->keterangan = trim(($k->keterangan ? $k->keterangan . "\n" : '') . $baris);
        $k->save();

        return $this->selesai($request, 'Status reminder berhasil disimpan.');
    }

    public function storeGaji(Request $request)
    {
        if ($blocked = $this->guard()) {
            return $blocked;
        }

        $request->validate([
            'id'                => 'required|integer|exists:karyawans,id',
            'gaji'              => 'required|numeric|min:0',
            'tunjangan_jabatan' => 'nullable|numeric|min:0',
            'bulan'             => 'required|integer|between:1,12',
            'tahun'             => 'required|integer|min:2000|max:2099',
        ]);

        $k = karyawan::findOrFail($request->id);

        $bulan = (int) $request->bulan;
        $tahun = (int) $request->tahun;
        $tunjangan = $request->tunjangan_jabatan ?? 0;

        $sekarang = Carbon::now();

        if ($bulan === (int) $sekarang->month && $tahun === (int) $sekarang->year) {
            $k->update([
                'gaji'              => $request->gaji,
                'tunjangan_jabatan' => $tunjangan,
            ]);
        }

        LogGaji::updateOrCreate(
            [
                'id_karyawan' => $k->id,
                'bulan'       => $bulan,
                'tahun'       => $tahun,
            ],
            [
                'gaji'              => $request->gaji,
                'tunjangan_jabatan' => $tunjangan,
            ]
        );

        return $this->selesai($request, 'Gaji berhasil diperbarui.');
    }

    private function baseQuery()
    {
        return karyawan::query()
            ->whereNull('resigned_at')
            ->where('status_aktif', '1')
            ->where(function ($q) {
                $q->whereNull('jabatan')->orWhere(function ($q) {
                    $q->where('jabatan', '!=', 'Outsource')->where('jabatan', 'not like', 'OL%');
                });
            })
            ->where(function ($q) {
                $q->whereNull('divisi')->orWhere('divisi', '!=', 'Direksi');
            });
    }

    private function terapkanFilter($query, Request $request)
    {
        $kolomDokumen = array_keys(self::DOC_FIELDS);

        return $query
            ->when($request->filled('q'), function ($q) use ($request) {
                $s = '%' . $request->q . '%';
                $q->where(function ($q) use ($s) {
                    $q->where('nama_lengkap', 'like', $s)
                        ->orWhere('nip', 'like', $s)
                        ->orWhere('nik', 'like', $s);
                });
            })
            ->when($request->filled('divisi'), fn ($q) => $q->where('divisi', $request->divisi))
            ->when($request->filled('status'), fn ($q) => $q->where('status_karyawan', $request->status))
            ->when($request->dokumen === 'lengkap', function ($q) use ($kolomDokumen) {
                foreach ($kolomDokumen as $c) {
                    $q->whereNotNull($c)->where($c, '!=', '');
                }
            })
            ->when($request->dokumen === 'belum', function ($q) use ($kolomDokumen) {
                $q->where(function ($q) use ($kolomDokumen) {
                    foreach ($kolomDokumen as $c) {
                        $q->orWhereNull($c)->orWhere($c, '');
                    }
                });
            });
    }

    private function relasiLog(): array
    {
        return ['logGaji' => fn ($q) => $q->orderBy('tahun')->orderBy('bulan')->orderBy('id')];
    }

    private function aturanKaryawan(): array
    {
        $aturan = [
            'foto'  => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'email' => 'nullable|email',
        ];

        foreach (self::DATE_FIELDS as $f) {
            $aturan[$f] = 'nullable|date';
        }

        foreach (array_keys(self::DOC_FIELDS) as $f) {
            $aturan[$f] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120';
        }

        return $aturan;
    }

    private function simpanBerkas(karyawan $k, Request $request): void
    {
        if ($request->hasFile('foto')) {
            $gambar = $request->file('foto');
            $nama = time() . '_' . Str::random(6) . '.' . $gambar->getClientOriginalExtension();
            $gambar->move(public_path('images'), $nama);
            $k->foto = $nama;
        }

        foreach (array_keys(self::DOC_FIELDS) as $kolom) {
            if (!$request->hasFile($kolom)) {
                continue;
            }

            $berkas = $request->file($kolom);
            $nama = time() . '_' . $kolom . '_' . Str::random(6) . '.' . $berkas->getClientOriginalExtension();

            if ($k->$kolom && file_exists(public_path('dokumen/' . $k->$kolom))) {
                unlink(public_path('dokumen/' . $k->$kolom));
            }

            $berkas->move(public_path('dokumen'), $nama);
            $k->$kolom = $nama;
        }
    }

    private function selesai(Request $request, string $pesan)
    {
        Cache::forget('mk_summary');
        Cache::forget('mk_reminder');

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => $pesan]);
        }

        return redirect()->back()->with('success', $pesan);
    }

    private function ringkasan($semua, Carbon $today): array
    {
        $reminder = $this->susunReminder($semua, $today);
        $tenggat = $reminder->whereIn('jenis', ['probation', 'kontrak']);

        return [
            'total'                 => $semua->count(),
            'probation'             => $semua->where('status_karyawan', 'Probation')->count(),
            'kontrak'               => $semua->filter(fn ($k) => str_starts_with((string) $k->status_karyawan, 'Kontrak'))->count(),
            'kartap'                => $semua->where('status_karyawan', 'Kartap')->count(),
            'dokumen_belum_lengkap' => $semua->filter(fn ($k) => $this->dokumenKosong($k)->isNotEmpty())->count(),
            'reminder_pending'      => $reminder->where('status', 'pending')->count(),
            'lewat_tempo'           => $tenggat->where('level', 'late')->count(),
            'jatuh_tempo_30_hari'   => $tenggat->where('level', 'soon')->count(),
            'divisi'                => $semua->pluck('divisi')->filter()->unique()->sort()->values(),
        ];
    }

    private function baris($k, Carbon $today): array
    {
        $kosong = $this->dokumenKosong($k);
        $total = count(self::DOC_FIELDS);
        $terisi = $total - $kosong->count();
        $basic = $this->basicSalary($k);

        return [
            'id'               => $k->id,
            'initials'         => $this->inisial($k->nama_lengkap),
            'foto_url'         => $k->foto ? asset('images/' . $k->foto) : null,
            'nip'              => $k->nip,
            'nama_lengkap'     => $k->nama_lengkap,
            'divisi'           => $k->divisi,
            'jabatan'          => $k->jabatan,
            'status_karyawan'  => $k->status_karyawan,
            'masa_kerja'       => $this->durasi($k->awal_probation ?: $k->awal_kontrak),
            'masa_kontrak'     => $this->durasi($k->awal_kontrak),
            'basic_salary'     => $basic,
            'basic_salary_fmt' => $this->rupiah($basic),
            'dokumen'          => [
                'terisi' => $terisi,
                'total'  => $total,
                'persen' => (int) round($terisi / $total * 100),
                'kosong' => $kosong,
            ],
            'reminder'         => $this->tenggat($k, $today),
        ];
    }

    private function basicSalary($k): ?int
    {
        $pertama = $k->logGaji->first();

        return $pertama ? (int) $pertama->gaji : null;
    }

    private function dokumenKosong($k)
    {
        return collect(self::DOC_FIELDS)
            ->filter(fn ($label, $kolom) => empty($k->$kolom))
            ->values();
    }

    private function tenggat($k, Carbon $today): array
    {
        $jenis = $deadline = null;

        if ($k->status_karyawan === 'Probation') {
            $jenis = 'Probation';
            $deadline = $k->akhir_probation;
        } elseif (str_starts_with((string) $k->status_karyawan, 'Kontrak')) {
            $jenis = 'Kontrak';
            $deadline = $k->akhir_kontrak;
        }

        $sisaHari = $label = $level = null;

        if ($deadline) {
            $sisaHari = (int) $today->diffInDays(Carbon::parse($deadline)->startOfDay(), false);

            if ($sisaHari < 0) {
                $level = 'late';
                $label = 'Lewat ' . abs($sisaHari) . ' hari';
            } elseif ($sisaHari <= 30) {
                $level = 'soon';
                $label = $sisaHari === 0 ? 'Jatuh tempo hari ini' : 'Jatuh tempo ' . $sisaHari . ' hari lagi';
            }
        }

        return [
            'jenis'       => $jenis,
            'jatuh_tempo' => $deadline,
            'sisa_hari'   => $sisaHari,
            'label'       => $label,
            'level'       => $level,
        ];
    }

    private function sudahDitindaklanjuti($k, Carbon $today): bool
    {
        if (empty($k->keterangan)) {
            return false;
        }

        $baris = collect(explode("\n", $k->keterangan))
            ->map(fn ($b) => trim($b))
            ->filter()
            ->reverse();

        foreach ($baris as $log) {
            if (preg_match('/^\[(\d{2})\/(\d{2})\/(\d{4})\]\s+(Sudah update|Pending|Masuk Kartap)/i', $log, $m)) {
                $tgl = Carbon::createFromFormat('d/m/Y', $m[1] . '/' . $m[2] . '/' . $m[3])->startOfDay();

                if ($tgl->lt($today->copy()->subDays(30))) {
                    return false;
                }

                return strtolower($m[4]) !== 'pending' || str_contains(strtolower($log), 'diperpanjang');
            }
        }

        return false;
    }

    private function susunReminder($kumpulan, Carbon $today)
    {
        $hasil = collect();

        foreach ($kumpulan as $k) {
            $dasar = [
                'id'      => $k->id,
                'nama'    => $k->nama_lengkap,
                'jabatan' => $k->jabatan,
                'divisi'  => $k->divisi,
            ];

            $t = $this->tenggat($k, $today);

            if ($t['level'] !== null && !$this->sudahDitindaklanjuti($k, $today)) {
                $hasil->push($dasar + [
                    'jenis'  => strtolower($t['jenis']),
                    'status' => 'pending',
                    'level'  => $t['level'],
                    'judul'  => $t['jenis'] . ' berakhir ' . $this->tanggal($t['jatuh_tempo']),
                    'label'  => $t['label'],
                ]);
            }

            if ($k->awal_tetap) {
                $awal = Carbon::parse($k->awal_tetap)->startOfDay();
                $sisaHari = (int) $today->diffInDays($awal, false);

                if ($sisaHari >= 0 && $sisaHari <= 30) {
                    $hasil->push($dasar + [
                        'jenis'  => 'kartap',
                        'status' => 'pending',
                        'level'  => 'soon',
                        'judul'  => 'Akan diangkat karyawan tetap per ' . $this->tanggal($k->awal_tetap),
                        'label'  => $sisaHari === 0 ? 'Hari ini diangkat Kartap' : 'Kartap dalam ' . $sisaHari . ' hari',
                    ]);
                }
            }

            $terakhir = $k->logGaji->last();

            if ($terakhir && $k->status_karyawan !== 'Probation') {
                $sejak = Carbon::create((int) $terakhir->tahun ?: $today->year, (int) $terakhir->bulan ?: 1, 1);
                $bulan = (int) $sejak->diffInMonths($today);

                if ($bulan >= self::REVIEW_GAJI_BULAN) {
                    $hasil->push($dasar + [
                        'jenis'  => 'gaji',
                        'status' => 'pending',
                        'level'  => 'soon',
                        'judul'  => 'Review gaji',
                        'label'  => 'Gaji terakhir diubah ' . $bulan . ' bulan lalu',
                    ]);
                }
            }
        }

        return $hasil
            ->sortBy(fn ($r) => ['late' => 0, 'soon' => 1, 'done' => 2][$r['level']])
            ->values();
    }

    private function inisial($nama): string
    {
        return collect(preg_split('/\s+/', trim((string) $nama)))
            ->filter()
            ->take(2)
            ->map(fn ($kata) => mb_strtoupper(mb_substr($kata, 0, 1)))
            ->implode('');
    }

    private function rupiah($angka): string
    {
        return $angka ? 'Rp ' . number_format((int) $angka, 0, ',', '.') : '-';
    }

    private function tanggal($nilai): string
    {
        return $nilai ? Carbon::parse($nilai)->locale('id')->translatedFormat('j F Y') : '-';
    }

    private function durasi($mulai): string
    {
        if (empty($mulai)) {
            return '-';
        }

        $awal = Carbon::parse($mulai);
        $akhir = Carbon::now();

        if ($awal->greaterThan($akhir)) {
            return '-';
        }

        $d = $awal->diff($akhir);

        return "{$d->y} tahun {$d->m} bulan {$d->d} hari";
    }

    private function durasiKontrak($mulai, $selesai): string
    {
        if (empty($mulai) || empty($selesai)) {
            return '-';
        }

        $awal = Carbon::parse($mulai);
        $akhir = Carbon::parse($selesai);

        if ($awal->greaterThan($akhir)) {
            return '-';
        }

        $d = $awal->diff($akhir);

        return "{$d->y} tahun {$d->m} bulan {$d->d} hari";
    }
}