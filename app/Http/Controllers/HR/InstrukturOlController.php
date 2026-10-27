<?php

namespace App\Http\Controllers\HR;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\InstrukturOl;
use App\Models\InstrukturOlPembayaran;
use App\Models\InstrukturOlSilabus;
use App\Models\karyawan;
use App\Models\RKM;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class InstrukturOlController extends Controller
{
    private const DIR = InstrukturOl::UPLOAD_DIR;

    private const PERAN = [
        'utama'        => ['kolom' => 'instruktur_key',  'label' => 'Instruktur Utama'],
        'instruktur-2' => ['kolom' => 'instruktur_key2', 'label' => 'Instruktur 2'],
        'asisten'      => ['kolom' => 'asisten_key',     'label' => 'Asisten'],
    ];

    private const KOLOM_LIST = [
        'kode_karyawan' => 'Kode Karyawan',
        'nama'          => 'Nama',
        'core'          => 'Core',
        'no_hp'         => 'No. HP',
        'keterangan'    => 'Keterangan',
        'cv'            => 'CV',
        'ktp'           => 'KTP',
    ];

    private const KOLOM_RIWAYAT = [
        'materi'       => 'Nama Materi',
        'perusahaan'   => 'Perusahaan',
        'periode'      => 'Periode',
        'metode_kelas' => 'Metode Kelas',
        'peran'        => 'Peran',
        'pax'          => 'Pax',
    ];

    public function index()
    {
        return view('HR.InstrukturOl.index', [
            'kolomList'    => self::KOLOM_LIST,
            'kolomRiwayat' => self::KOLOM_RIWAYAT,
        ]);
    }

    public function karyawanOl(Request $request): JsonResponse
    {
        $data = $this->kandidatKaryawan()
            ->when($request->q, fn ($q, $t) => $q->where('nama_lengkap', 'like', "%{$t}%"))
            ->orderBy('nama_lengkap')
            ->get()
            ->map(fn ($k) => [
                'id'      => $k->getKey(),
                'nama'    => $k->nama_lengkap,
                'no_hp'   => $k->Telepon,
                'jabatan' => $k->jabatan,
            ])
            ->values();

        return response()->json(['data' => $data]);
    }

    public function migrasiKaryawan(Request $request): JsonResponse
    {
        $request->validate([
            'items'               => ['required', 'array', 'min:1'],
            'items.*.karyawan_id' => ['required'],
            'items.*.core'        => ['required', 'string', 'max:255'],
        ], [
            'items.required'        => 'Pilih minimal 1 karyawan.',
            'items.*.core.required' => 'Core wajib diisi untuk setiap karyawan yang dipilih.',
        ]);

        $berhasil = 0;
        $gagal    = [];

        foreach ($request->input('items') as $item) {
            $k = $this->kandidatKaryawan()->find($item['karyawan_id']);

            if (!$k) {
                $gagal[] = "ID {$item['karyawan_id']}: bukan karyawan OL atau sudah tidak ada.";
                continue;
            }

            try {
                DB::transaction(function () use ($k, $item) {
                    InstrukturOl::create([
                        'kode_karyawan' => $k->kode_karyawan,
                        'nama'          => $k->nama_lengkap,
                        'core'          => $item['core'],
                        'no_hp'         => Str::limit(trim((string) $k->Telepon), 20, '') ?: '-',
                        'keterangan'    => '-',
                    ]);
                });

                $berhasil++;
            } catch (Throwable $e) {
                report($e);
                $gagal[] = "{$k->nama_lengkap}: gagal dimigrasi (kemungkinan masih terhubung dengan data lain).";
            }
        }

        return response()->json([
            'message'  => "{$berhasil} karyawan berhasil dimigrasi.",
            'berhasil' => $berhasil,
            'gagal'    => $gagal,
        ], $berhasil === 0 ? 422 : 200);
    }

    private function kandidatKaryawan()
    {
        return karyawan::query()->where(function ($q) {
            $q->where('jabatan', 'Outsource')
                ->orWhere('jabatan', 'like', 'OL%');
        });
    }

    public function getData(Request $request): JsonResponse
    {
        $instrukturs = InstrukturOl::query()
            ->when($request->q, fn ($q, $term) => $this->cariInstruktur($q, $term))
            ->latest()
            ->paginate(15);

        $kosong = fn ($kolom) => InstrukturOl::query()
            ->where(fn ($q) => $q->whereNull($kolom)->orWhere($kolom, ''))
            ->count();

        return response()->json(array_merge($instrukturs->toArray(), [
            'summary' => [
                'total'      => InstrukturOl::count(),
                'tanpa_cv'   => $kosong('cv_path'),
                'tanpa_ktp'  => $kosong('ktp_path'),
                'tanpa_kode' => $kosong('kode_karyawan'),
            ],
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $data     = $request->validate($this->instrukturRules());
        $uploaded = [];

        try {
            $instruktur = DB::transaction(function () use ($request, $data, &$uploaded) {
                $kode = !empty($data['kode_karyawan'])
                    ? (str_contains($data['kode_karyawan'], 'OL') ? $data['kode_karyawan'] : 'OL' . $data['kode_karyawan'])
                    : null;

                $instruktur = InstrukturOl::create([
                    'kode_karyawan' => $kode,
                    'nama'          => $data['nama'],
                    'core'          => $data['core'],
                    'no_hp'         => $data['no_hp'],
                    'keterangan'    => $data['keterangan'] ?? null,
                    'cv_path'       => $this->simpanFile($request->file('cv'), 'cv', $uploaded),
                    'ktp_path'      => $this->simpanFile($request->file('ktp'), 'ktp', $uploaded),
                ]);

                // Buat akun karyawan + user hanya jika kode karyawan belum terdaftar
                $sudahAda = $kode && karyawan::where('kode_karyawan', $kode)->exists();

                if (!$sudahAda) {
                    $addKaryawan                = new karyawan();
                    $addKaryawan->nama_lengkap  = $instruktur->nama;
                    $addKaryawan->divisi        = 'Education';
                    $addKaryawan->jabatan       = 'Outsource';
                    $addKaryawan->status_aktif  = '1';
                    $addKaryawan->kode_karyawan = $instruktur->kode_karyawan;
                    $addKaryawan->CV            = $instruktur->cv_path;
                    $addKaryawan->KTP           = $instruktur->ktp_path;
                    $addKaryawan->save();

                    $dataUser              = new User();
                    $dataUser->username    = explode(' ', trim($addKaryawan->nama_lengkap))[0];
                    $dataUser->jabatan     = $addKaryawan->jabatan;
                    $dataUser->status_akun = $addKaryawan->status_aktif;
                    $dataUser->password    = Hash::make('password');
                    $dataUser->karyawan_id = $addKaryawan->id;
                    $dataUser->save();
                }

                return $instruktur;
            });
        } catch (Throwable $e) {
            foreach ($uploaded as $path) {
                $this->hapusFile($path);
            }
            throw $e;
        }

        return response()->json([
            'message' => "Instruktur {$instruktur->nama} berhasil ditambahkan.",
            'data'    => $instruktur,
        ], 201);
    }

    public function show(InstrukturOl $instruktur): JsonResponse
    {
        return response()->json(['data' => $instruktur]);
    }

    public function update(Request $request, InstrukturOl $instruktur): JsonResponse
    {
        $data       = $request->validate($this->instrukturRules($instruktur->id));
        $attributes = collect($data)->only(['kode_karyawan', 'nama', 'core', 'no_hp', 'keterangan'])->all();
        $uploaded   = [];

        foreach (['cv', 'ktp'] as $field) {
            if ($request->hasFile($field)) {
                $old = $instruktur->{$field . '_path'};
                $attributes[$field . '_path'] = $this->simpanFile($request->file($field), $field, $uploaded);

                if ($old) {
                    $this->hapusFile($field . '/' . $old);
                }
            }
        }

        $instruktur->update($attributes);

        return response()->json([
            'message' => 'Data instruktur berhasil diperbarui.',
            'data'    => $instruktur->fresh(),
        ]);
    }

    public function destroy(InstrukturOl $instruktur): JsonResponse
    {
        $instruktur->delete();

        return response()->json(['message' => 'Data instruktur berhasil dihapus.']);
    }

    private function instrukturRules(?int $ignoreId = null): array
    {
        return [
            'kode_karyawan' => [
                'nullable', 'string', 'max:20',
                Rule::unique('instruktur_ols', 'kode_karyawan')
                    ->whereNull('deleted_at')
                    ->ignore($ignoreId),
            ],
            'nama'       => ['required', 'string', 'max:255'],
            'core'       => ['required', 'string', 'max:255'],
            'no_hp'      => ['required', 'string', 'max:20'],
            'keterangan' => ['nullable', 'string'],
            'cv'         => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
            'ktp'        => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    public function riwayat(InstrukturOl $instruktur): JsonResponse
    {
        $kode = trim((string) $instruktur->kode_karyawan);
        $data = [];

        foreach (self::PERAN as $peran => $meta) {
            $data[$peran] = $kode === ''
                ? []
                : $this->riwayatQuery($kode, $meta['kolom'])->get()
                    ->map(fn ($r) => $this->barisRiwayat($r, $meta['label']))
                    ->values();
        }

        return response()->json(['kode_kosong' => $kode === '', 'data' => $data]);
    }

    public function export(Request $request)
    {
        $request->validate([
            'format'    => ['required', 'in:pdf,xlsx'],
            'columns'   => ['required', 'array', 'min:1'],
            'columns.*' => ['in:' . implode(',', array_keys(self::KOLOM_LIST))],
        ], ['columns.required' => 'Pilih minimal 1 kolom untuk diexport.']);

        $cols = $this->pilihKolom(self::KOLOM_LIST, $request->input('columns'));

        $items = InstrukturOl::query()
            ->when($request->q, fn ($q, $term) => $this->cariInstruktur($q, $term))
            ->orderBy('nama')
            ->get();

        $rows = $items->values()->map(function ($i, $n) use ($cols) {
            $all = [
                'kode_karyawan' => $i->kode_karyawan ?: '-',
                'nama'          => $i->nama,
                'core'          => $i->core,
                'no_hp'         => $i->no_hp,
                'keterangan'    => $i->keterangan ?: '-',
                'cv'            => $i->cv_path ? 'Ada' : 'Belum',
                'ktp'           => $i->ktp_path ? 'Ada' : 'Belum',
            ];

            return array_merge([$n + 1], array_map(fn ($c) => $all[$c], $cols));
        })->all();

        $headings = array_merge(['No'], array_map(fn ($c) => self::KOLOM_LIST[$c], $cols));

        return $this->kirimExport($request->format, 'data-instruktur-ol', 'Data Instruktur OL', $headings, $rows);
    }

    public function exportRiwayat(Request $request, InstrukturOl $instruktur)
    {
        $request->validate([
            'format'    => ['required', 'in:pdf,xlsx'],
            'columns'   => ['required', 'array', 'min:1'],
            'columns.*' => ['in:' . implode(',', array_keys(self::KOLOM_RIWAYAT))],
        ], ['columns.required' => 'Pilih minimal 1 kolom untuk diexport.']);

        $kode = trim((string) $instruktur->kode_karyawan);
        abort_if($kode === '', 422, 'Kode Karyawan belum diisi.');

        $cols = $this->pilihKolom(self::KOLOM_RIWAYAT, $request->input('columns'));
        $rows = [];
        $no   = 1;

        foreach (self::PERAN as $meta) {
            foreach ($this->riwayatQuery($kode, $meta['kolom'])->get() as $r) {
                $b   = $this->barisRiwayat($r, $meta['label']);
                $all = [
                    'materi'       => $b['materi'],
                    'perusahaan'   => $b['perusahaan'],
                    'periode'      => $this->fmtTanggal($b['tanggal_awal']) . ' s/d ' . $this->fmtTanggal($b['tanggal_akhir']),
                    'metode_kelas' => $b['metode_kelas'],
                    'peran'        => $b['peran'],
                    'pax'          => $b['pax'],
                ];

                $rows[] = array_merge([$no++], array_map(fn ($c) => $all[$c], $cols));
            }
        }

        $headings = array_merge(['No'], array_map(fn ($c) => self::KOLOM_RIWAYAT[$c], $cols));
        $slug     = Str::slug($instruktur->nama);

        return $this->kirimExport(
            $request->format,
            "riwayat-mengajar-{$slug}",
            "Riwayat Mengajar: {$instruktur->nama}",
            $headings,
            $rows
        );
    }

    // ---------- Silabus ----------

    public function silabus(InstrukturOl $instruktur): JsonResponse
    {
        return $this->responSilabus($instruktur);
    }

    public function silabusStore(Request $request, InstrukturOl $instruktur): JsonResponse
    {
        $data = $request->validate($this->silabusRules(true));

        $file     = $request->file('file');
        $uploaded = [];

        $instruktur->silabus()->create([
            'judul'          => $data['judul'],
            'keterangan'     => $data['keterangan'] ?? null,
            'file_path'      => $this->simpanFile($file, 'silabus', $uploaded),
            'file_nama_asli' => $file->getClientOriginalName(),
        ]);

        return $this->responSilabus($instruktur, 'Silabus berhasil diunggah.', 201);
    }

    public function silabusUpdate(Request $request, InstrukturOlSilabus $silabus): JsonResponse
    {
        $data = $request->validate($this->silabusRules(false));

        $attributes = [
            'judul'      => $data['judul'],
            'keterangan' => $data['keterangan'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $uploaded = [];
            $old      = $silabus->file_path;

            $attributes['file_path']      = $this->simpanFile($file, 'silabus', $uploaded);
            $attributes['file_nama_asli'] = $file->getClientOriginalName();

            if ($old) {
                $this->hapusFile('silabus/' . $old);
            }
        }

        $silabus->update($attributes);

        return $this->responSilabus($silabus->instruktur, 'Silabus berhasil diperbarui.');
    }

    public function silabusDestroy(InstrukturOlSilabus $silabus): JsonResponse
    {
        $instruktur = $silabus->instruktur;
        $silabus->delete(); // soft delete, file dibiarkan

        return $this->responSilabus($instruktur, 'Silabus berhasil dihapus.');
    }

    private function silabusRules(bool $wajibFile): array
    {
        return [
            'judul'      => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'file'       => [$wajibFile ? 'required' : 'nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
        ];
    }

    private function responSilabus(InstrukturOl $instruktur, ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['data' => $instruktur->silabus()->latest()->get()];

        if ($message) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    // ---------- Riwayat Pembayaran ----------

    public function pembayaran(InstrukturOl $instruktur): JsonResponse
    {
        return $this->responPembayaran($instruktur);
    }

    public function pembayaranStore(Request $request, InstrukturOl $instruktur): JsonResponse
    {
        $data = $request->validate($this->pembayaranRules());
        $this->pastikanRkmMilik($instruktur, $data['rkm_id'] ?? null);

        $uploaded   = [];
        $attributes = $this->pembayaranAttributes($data, $request);
        $file       = $request->file('bukti');

        if ($file) {
            $attributes['bukti_path']      = $this->simpanFile($file, 'bukti-pembayaran', $uploaded);
            $attributes['bukti_nama_asli'] = $file->getClientOriginalName();
        }

        $instruktur->pembayarans()->create($attributes);

        return $this->responPembayaran($instruktur, 'Pembayaran berhasil ditambahkan.', 201);
    }

    public function pembayaranUpdate(Request $request, InstrukturOlPembayaran $pembayaran): JsonResponse
    {
        $data       = $request->validate($this->pembayaranRules());
        $instruktur = $pembayaran->instruktur;
        $this->pastikanRkmMilik($instruktur, $data['rkm_id'] ?? null);

        $attributes = $this->pembayaranAttributes($data, $request);

        if ($request->hasFile('bukti')) {
            $file     = $request->file('bukti');
            $uploaded = [];
            $old      = $pembayaran->bukti_path;

            $attributes['bukti_path']      = $this->simpanFile($file, 'bukti-pembayaran', $uploaded);
            $attributes['bukti_nama_asli'] = $file->getClientOriginalName();

            if ($old) {
                $this->hapusFile('bukti-pembayaran/' . $old);
            }
        }

        $pembayaran->update($attributes);

        return $this->responPembayaran($instruktur, 'Pembayaran berhasil diperbarui.');
    }

    public function pembayaranDestroy(InstrukturOlPembayaran $pembayaran): JsonResponse
    {
        $instruktur = $pembayaran->instruktur;
        $pembayaran->delete(); // soft delete, file bukti dibiarkan agar data bisa dipulihkan

        return $this->responPembayaran($instruktur, 'Pembayaran berhasil dihapus.');
    }

    private function pembayaranRules(): array
    {
        return [
            'rkm_id'        => ['nullable', 'integer'],
            'judul'         => ['required', 'string', 'max:255'],
            'harga'         => ['required', 'integer', 'min:0', 'max:999999999999'],
            'kali_pax'      => ['nullable', 'boolean'],
            'pax'           => ['nullable', 'integer', 'min:1', 'max:100000', 'required_if:kali_pax,1'],
            'tanggal_bayar' => ['required', 'date'],
            'keterangan'    => ['nullable', 'string', 'max:500'],
            'bukti'         => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ];
    }

    private function pembayaranAttributes(array $data, Request $request): array
    {
        $kali = $request->boolean('kali_pax');
        $pax  = $kali ? (int) $data['pax'] : null;

        return [
            'rkm_id'        => $data['rkm_id'] ?? null,
            'judul'         => $data['judul'],
            'harga'         => (int) $data['harga'],
            'kali_pax'      => $kali,
            'pax'           => $pax,
            'total'         => (int) $data['harga'] * ($kali ? $pax : 1),
            'tanggal_bayar' => $data['tanggal_bayar'],
            'keterangan'    => $data['keterangan'] ?? null,
        ];
    }

    private function pastikanRkmMilik(InstrukturOl $instruktur, $rkmId): void
    {
        if (!$rkmId) {
            return;
        }

        $kode  = trim((string) $instruktur->kode_karyawan);
        $rkm   = RKM::find($rkmId);
        $milik = $rkm && $kode !== '' && collect(self::PERAN)
            ->contains(fn ($m) => (string) $rkm->{$m['kolom']} === $kode);

        if (!$milik) {
            throw ValidationException::withMessages([
                'rkm_id' => 'Kelas yang dipilih bukan riwayat mengajar instruktur ini.',
            ]);
        }
    }

    private function responPembayaran(InstrukturOl $instruktur, ?string $message = null, int $status = 200): JsonResponse
    {
        $items = $instruktur->pembayarans()
            ->with(['rkm.materi', 'rkm.perusahaan'])
            ->orderByDesc('tanggal_bayar')
            ->orderByDesc('id')
            ->get();

        $payload = [
            'data'        => $items->map(fn ($p) => [
                'id'              => $p->id,
                'rkm_id'          => $p->rkm_id,
                'materi'          => $p->rkm?->materi?->nama_materi,
                'perusahaan'      => $p->rkm?->perusahaan?->nama_perusahaan,
                'judul'           => $p->judul,
                'harga'           => $p->harga,
                'kali_pax'        => $p->kali_pax,
                'pax'             => $p->pax,
                'total'           => $p->total,
                'tanggal_bayar'   => $p->tanggal_bayar?->format('Y-m-d'),
                'keterangan'      => $p->keterangan,
                'bukti_url'       => $p->bukti_url,
                'bukti_nama_asli' => $p->bukti_nama_asli,
            ])->values(),
            'total'       => (int) $items->sum('total'),
            'rkm_options' => $this->opsiRkm($instruktur),
        ];

        if ($message) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    private function opsiRkm(InstrukturOl $instruktur): array
    {
        $kode = trim((string) $instruktur->kode_karyawan);
        $out  = [];

        if ($kode === '') {
            return $out;
        }

        foreach (self::PERAN as $meta) {
            foreach ($this->riwayatQuery($kode, $meta['kolom'])->get() as $r) {
                $b     = $this->barisRiwayat($r, $meta['label']);
                $out[] = [
                    'id'    => $b['id'],
                    'materi' => $b['materi'],
                    'pax'   => $b['pax'],
                    'label' => "{$b['materi']} — {$b['perusahaan']} ({$this->fmtTanggal($b['tanggal_awal'])}) · {$b['peran']}",
                ];
            }
        }

        return $out;
    }

    private function cariInstruktur($query, string $term)
    {
        return $query->where(function ($w) use ($term) {
            $w->where('nama', 'like', "%{$term}%")
                ->orWhere('kode_karyawan', 'like', "%{$term}%")
                ->orWhere('core', 'like', "%{$term}%")
                ->orWhere('no_hp', 'like', "%{$term}%");
        });
    }

    private function riwayatQuery(string $kode, string $kolom)
    {
        return RKM::query()
            ->with(['materi', 'perusahaan'])
            ->where($kolom, $kode)
            ->orderByDesc('tanggal_awal');
    }

    private function barisRiwayat(RKM $r, string $peran): array
    {
        return [
            'id'            => $r->id,
            'materi'        => $r->materi->nama_materi ?? '-',
            'perusahaan'    => $r->perusahaan->nama_perusahaan ?? '-',
            'tanggal_awal'  => $r->tanggal_awal?->format('Y-m-d'),
            'tanggal_akhir' => $r->tanggal_akhir?->format('Y-m-d'),
            'metode_kelas'  => $r->metode_kelas ?: '-',
            'pax'           => (int) $r->pax,
            'peran'         => $peran,
        ];
    }

    private function fmtTanggal(?string $tanggal): string
    {
        return $tanggal ? Carbon::parse($tanggal)->format('d-m-Y') : '-';
    }

    private function pilihKolom(array $katalog, array $dipilih): array
    {
        return array_values(array_intersect(array_keys($katalog), $dipilih));
    }

    private function kirimExport(string $format, string $nama, string $judul, array $headings, array $rows)
    {
        $file = $nama . '-' . now()->format('Ymd-His');

        if ($format === 'xlsx') {
            return Excel::download(new ArrayExport($headings, $rows), $file . '.xlsx');
        }

        return Pdf::loadView('HR.InstrukturOl.export-pdf', compact('judul', 'headings', 'rows'))
            ->setPaper('a4', count($headings) > 6 ? 'landscape' : 'portrait')
            ->download($file . '.pdf');
    }

    private function simpanFile($file, string $subDir, array &$uploaded): ?string
    {
        if (!$file) {
            return null;
        }

        $nama = time() . '_' . $subDir . '_' . Str::random(6) . '.' . $file->getClientOriginalExtension();
        $dir  = public_path(self::DIR . '/' . $subDir);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file->move($dir, $nama);
        $uploaded[] = $subDir . '/' . $nama;

        return $nama;
    }

    private function hapusFile(string $relative): void
    {
        $full = public_path(self::DIR . '/' . $relative);

        if (is_file($full)) {
            unlink($full);
        }
    }
}