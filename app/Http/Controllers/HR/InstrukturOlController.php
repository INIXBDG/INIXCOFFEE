<?php

namespace App\Http\Controllers\HR;

use App\Exports\ArrayExport;
use App\Http\Controllers\Controller;
use App\Models\InstrukturOl;
use App\Models\InstrukturOlMateri;
use App\Models\InstrukturOlPembayaran;
use App\Models\InstrukturOlRiwayat;
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

    /** Dipakai untuk membaca peran instruktur di sebuah RKM (informasi saja) dan filter "kelas instruktur ini". */
    private const PERAN = [
        'utama'        => ['kolom' => 'instruktur_key',  'label' => 'Instruktur Utama'],
        'instruktur-2' => ['kolom' => 'instruktur_key2', 'label' => 'Instruktur 2'],
        'asisten'      => ['kolom' => 'asisten_key',     'label' => 'Asisten'],
    ];

    /** Silabus & Materi memakai logika yang sama. */
    private const DOKUMEN = [
        'silabus' => ['relasi' => 'silabus', 'dir' => 'silabus', 'label' => 'Silabus'],
        'materi'  => ['relasi' => 'materis', 'dir' => 'materi',  'label' => 'Materi'],
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
        return view('HR.InstrukturOL.index', [
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

    // ---------- Riwayat Mengajar (dipilih manual dari RKM) ----------

    public function riwayat(InstrukturOl $instruktur): JsonResponse
    {
        return $this->responRiwayat($instruktur);
    }

    /**
     * Tabel pemilih RKM: search, filter, pagination.
     * Dipakai oleh Riwayat Mengajar (multi pilih) serta Silabus & Materi (pilih satu).
     */
    public function rkmPilihan(Request $request, InstrukturOl $instruktur): JsonResponse
    {
        $kode = trim((string) $instruktur->kode_karyawan);

        $paginator = RKM::query()
            ->with(['materi', 'perusahaan'])
            ->when($request->q, function ($q, $term) {
                $q->where(function ($w) use ($term) {
                    $w->whereHas('materi', fn ($m) => $m->where('nama_materi', 'like', "%{$term}%"))
                        ->orWhereHas('perusahaan', fn ($p) => $p->where('nama_perusahaan', 'like', "%{$term}%"));
                });
            })
            ->when($request->tahun, fn ($q, $y) => $q->whereYear('tanggal_awal', (int) $y))
            ->when($request->metode, fn ($q, $m) => $q->where('metode_kelas', $m))
            ->when($request->boolean('milik') && $kode !== '', function ($q) use ($kode) {
                $q->where(function ($w) use ($kode) {
                    foreach (self::PERAN as $meta) {
                        $w->orWhere($meta['kolom'], $kode);
                    }
                });
            })
            ->orderByDesc('tanggal_awal')
            ->orderByDesc('id')
            ->paginate(10)
            ->through(fn ($r) => $this->barisRiwayat($r, $kode));

        return response()->json(array_merge($paginator->toArray(), [
            'added'          => $instruktur->riwayats()->pluck('rkm_id')->values(),
            'tahun_options'  => RKM::query()->whereNotNull('tanggal_awal')
                ->selectRaw('YEAR(tanggal_awal) as y')->distinct()->orderByDesc('y')->pluck('y'),
            'metode_options' => RKM::query()->whereNotNull('metode_kelas')->where('metode_kelas', '!=', '')
                ->distinct()->orderBy('metode_kelas')->pluck('metode_kelas'),
        ]));
    }

    public function riwayatStore(Request $request, InstrukturOl $instruktur): JsonResponse
    {
        $data = $request->validate([
            'rkm_ids'   => ['required', 'array', 'min:1'],
            'rkm_ids.*' => ['integer', Rule::exists((new RKM)->getTable(), 'id')],
        ], ['rkm_ids.required' => 'Pilih minimal 1 kelas.']);

        $ids   = array_values(array_unique(array_map('intval', $data['rkm_ids'])));
        $sudah = $instruktur->riwayats()->whereIn('rkm_id', $ids)->pluck('rkm_id')->map(fn ($v) => (int) $v)->all();
        $baru  = array_values(array_diff($ids, $sudah));

        foreach ($baru as $id) {
            $instruktur->riwayats()->create(['rkm_id' => $id]);
        }

        return $this->responRiwayat($instruktur, count($baru) . ' riwayat mengajar berhasil ditambahkan.', 201);
    }

    public function riwayatDestroy(InstrukturOlRiwayat $riwayat): JsonResponse
    {
        $instruktur = $riwayat->instruktur;
        $riwayat->delete(); // hanya melepas tautan ke RKM, data RKM tidak disentuh

        return $this->responRiwayat($instruktur, 'Riwayat mengajar berhasil dihapus.');
    }

    private function responRiwayat(InstrukturOl $instruktur, ?string $message = null, int $status = 200): JsonResponse
    {
        $payload = ['data' => $this->riwayatItems($instruktur)];

        if ($message) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    private function riwayatItems(InstrukturOl $instruktur)
    {
        $kode = trim((string) $instruktur->kode_karyawan);

        return $instruktur->riwayats()
            ->with(['rkm.materi', 'rkm.perusahaan'])
            ->get()
            ->filter(fn ($l) => $l->rkm)
            ->sortByDesc(fn ($l) => $l->rkm->tanggal_awal?->getTimestamp() ?? 0)
            ->map(fn ($l) => $this->barisRiwayat($l->rkm, $kode, $l->id))
            ->values();
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

        $cols = $this->pilihKolom(self::KOLOM_RIWAYAT, $request->input('columns'));
        $rows = [];
        $no   = 1;

        foreach ($this->riwayatItems($instruktur) as $b) {
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

    // ---------- Silabus & Materi ----------

    public function silabus(InstrukturOl $instruktur): JsonResponse
    {
        return $this->responDokumen($instruktur, 'silabus');
    }

    public function silabusStore(Request $request, InstrukturOl $instruktur): JsonResponse
    {
        return $this->dokStore($request, $instruktur, 'silabus');
    }

    public function silabusUpdate(Request $request, InstrukturOlSilabus $silabus): JsonResponse
    {
        return $this->dokUpdate($request, $silabus, 'silabus');
    }

    public function silabusDestroy(InstrukturOlSilabus $silabus): JsonResponse
    {
        return $this->dokDestroy($silabus, 'silabus');
    }

    public function materi(InstrukturOl $instruktur): JsonResponse
    {
        return $this->responDokumen($instruktur, 'materi');
    }

    public function materiStore(Request $request, InstrukturOl $instruktur): JsonResponse
    {
        return $this->dokStore($request, $instruktur, 'materi');
    }

    public function materiUpdate(Request $request, InstrukturOlMateri $materi): JsonResponse
    {
        return $this->dokUpdate($request, $materi, 'materi');
    }

    public function materiDestroy(InstrukturOlMateri $materi): JsonResponse
    {
        return $this->dokDestroy($materi, 'materi');
    }

    private function dokRules(): array
    {
        return [
            'rkm_id'     => ['nullable', 'integer', Rule::exists((new RKM)->getTable(), 'id')],
            'judul'      => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:500'],
            'file'       => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx', 'max:10240'],
        ];
    }

    private function dokStore(Request $request, InstrukturOl $instruktur, string $jenis): JsonResponse
    {
        $meta = self::DOKUMEN[$jenis];
        $data = $request->validate($this->dokRules());

        // Wajib salah satu: pilih kelas dari RKM, atau unggah file sendiri.
        if (empty($data['rkm_id']) && !$request->hasFile('file')) {
            throw ValidationException::withMessages([
                'file' => "Pilih kelas dari RKM atau unggah file {$meta['label']}.",
            ]);
        }

        $file     = $request->file('file');
        $uploaded = [];

        $instruktur->{$meta['relasi']}()->create([
            'rkm_id'         => $data['rkm_id'] ?? null,
            'judul'          => $data['judul'],
            'keterangan'     => $data['keterangan'] ?? null,
            'file_path'      => $this->simpanFile($file, $meta['dir'], $uploaded),
            'file_nama_asli' => $file?->getClientOriginalName(),
        ]);

        return $this->responDokumen($instruktur, $jenis, "{$meta['label']} berhasil ditambahkan.", 201);
    }

    private function dokUpdate(Request $request, $dok, string $jenis): JsonResponse
    {
        $meta = self::DOKUMEN[$jenis];
        $data = $request->validate($this->dokRules());

        if (empty($data['rkm_id']) && !$request->hasFile('file') && !$dok->file_path) {
            throw ValidationException::withMessages([
                'file' => "Pilih kelas dari RKM atau unggah file {$meta['label']}.",
            ]);
        }

        $attributes = [
            'rkm_id'     => $data['rkm_id'] ?? null,
            'judul'      => $data['judul'],
            'keterangan' => $data['keterangan'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file     = $request->file('file');
            $uploaded = [];
            $old      = $dok->file_path;

            $attributes['file_path']      = $this->simpanFile($file, $meta['dir'], $uploaded);
            $attributes['file_nama_asli'] = $file->getClientOriginalName();

            if ($old) {
                $this->hapusFile($meta['dir'] . '/' . $old);
            }
        }

        $dok->update($attributes);

        return $this->responDokumen($dok->instruktur, $jenis, "{$meta['label']} berhasil diperbarui.");
    }

    private function dokDestroy($dok, string $jenis): JsonResponse
    {
        $instruktur = $dok->instruktur;
        $dok->delete(); // soft delete, file dibiarkan

        return $this->responDokumen($instruktur, $jenis, self::DOKUMEN[$jenis]['label'] . ' berhasil dihapus.');
    }

    private function responDokumen(InstrukturOl $instruktur, string $jenis, ?string $message = null, int $status = 200): JsonResponse
    {
        $items = $instruktur->{self::DOKUMEN[$jenis]['relasi']}()
            ->with(['rkm.materi', 'rkm.perusahaan'])
            ->latest()
            ->get()
            ->map(function ($d) {
                $kelas = $this->labelRkm($d->rkm);
                $row   = $d->makeHidden('rkm')->toArray();

                $row['kelas'] = $kelas;

                return $row;
            })
            ->values();

        $payload = ['data' => $items];

        if ($message) {
            $payload['message'] = $message;
        }

        return response()->json($payload, $status);
    }

    private function labelRkm(?RKM $r): ?string
    {
        if (!$r) {
            return null;
        }

        $materi     = $r->materi->nama_materi ?? '-';
        $perusahaan = $r->perusahaan->nama_perusahaan ?? '-';

        return "{$materi} — {$perusahaan} (" . $this->fmtTanggal($r->tanggal_awal?->format('Y-m-d')) . ')';
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

    /** Kelas pembayaran harus salah satu kelas yang sudah ada di riwayat mengajar instruktur. */
    private function pastikanRkmMilik(InstrukturOl $instruktur, $rkmId): void
    {
        if (!$rkmId) {
            return;
        }

        if (!$instruktur->riwayats()->where('rkm_id', $rkmId)->exists()) {
            throw ValidationException::withMessages([
                'rkm_id' => 'Kelas yang dipilih belum ada di riwayat mengajar instruktur ini.',
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

    /** Opsi kelas pada form pembayaran = riwayat mengajar yang sudah dipilih. */
    private function opsiRkm(InstrukturOl $instruktur): array
    {
        return $this->riwayatItems($instruktur)->map(fn ($b) => [
            'id'     => $b['rkm_id'],
            'materi' => $b['materi'],
            'pax'    => $b['pax'],
            'label'  => "{$b['materi']} — {$b['perusahaan']} ({$this->fmtTanggal($b['tanggal_awal'])}) · {$b['peran']}",
        ])->values()->all();
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

    private function peranDari(RKM $r, string $kode): string
    {
        if ($kode === '') {
            return '-';
        }

        $labels = [];

        foreach (self::PERAN as $meta) {
            if ((string) $r->{$meta['kolom']} === $kode) {
                $labels[] = $meta['label'];
            }
        }

        return $labels ? implode(', ', $labels) : '-';
    }

    private function barisRiwayat(RKM $r, string $kode, ?int $riwayatId = null): array
    {
        return [
            'id'            => $riwayatId, // id tautan riwayat (null pada tabel pemilih RKM)
            'rkm_id'        => $r->id,
            'materi'        => $r->materi->nama_materi ?? '-',
            'perusahaan'    => $r->perusahaan->nama_perusahaan ?? '-',
            'tanggal_awal'  => $r->tanggal_awal?->format('Y-m-d'),
            'tanggal_akhir' => $r->tanggal_akhir?->format('Y-m-d'),
            'metode_kelas'  => $r->metode_kelas ?: '-',
            'pax'           => (int) $r->pax,
            'peran'         => $this->peranDari($r, $kode),
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

        return Pdf::loadView('HR.InstrukturOL.export-pdf', compact('judul', 'headings', 'rows'))
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