<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\perhitunganNetSales;
use App\Models\Peluang;
use App\Models\Perusahaan;
use App\Models\trackingNetSales;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use App\Models\ApprovalPendapatan;
use App\Models\ApprovalPendapatanSales;
use App\Models\KelengkapanDataLog;
use App\Models\outstanding;
use App\Models\RKM;
use Illuminate\Validation\ValidationException;

class KelengkapanDataController extends Controller
{
    private const JABATAN_AKSES = 'Adm Sales';
    private const BARIS_DATA_PERTAMA = 4;
    private const CACHE_MENIT = 60;
    private const BATAS_HARI_SETELAH_KELAS = 90;
    private const TAHAP_WAJIB_PA = ['merah'];

    private const KOLOM_BIAYA = [
        'transportasi', 'akomodasi_peserta', 'akomodasi_tim', 'fresh_money',
        'entertaint', 'souvenir', 'cashback', 'oleh_oleh', 'biaya_lain_lain',
        'biaya_exam', 'sewa_laptop',
    ];

    private const KOLOM_DB = [
        'biaya_exam' => 'exam',
    ];

    // Indeks kolom Excel (A=0) untuk isian utama dan biaya pada template import.
    private const INDEKS_FORM = [
        'perusahaan' => 0, 'materi' => 1, 'mulai' => 2, 'selesai' => 3,
        'id_sales' => 4, 'nama_sales' => 5, 'tgl_pa' => 6, 'tipe' => 7,
    ];

    private const INDEKS_BIAYA = [
        'transportasi' => 8, 'akomodasi_peserta' => 10, 'akomodasi_tim' => 11, 'fresh_money' => 13,
        'entertaint' => 14, 'souvenir' => 16, 'cashback' => 17, 'sewa_laptop' => 18,
        'oleh_oleh' => 20, 'biaya_lain_lain' => 21, 'biaya_exam' => 23,
    ];

    private const LABEL_FORM = [
        'perusahaan' => 'Perusahaan', 'materi' => 'Materi', 'mulai' => 'Tgl mulai kelas',
        'selesai' => 'Tgl selesai kelas', 'id_sales' => 'ID Sales', 'nama_sales' => 'Nama Sales',
        'tgl_pa' => 'Tgl PA', 'tipe' => 'Tipe pembayaran',
        'transportasi' => 'Transportasi', 'akomodasi_peserta' => 'Akomodasi peserta',
        'akomodasi_tim' => 'Akomodasi tim', 'fresh_money' => 'Fresh money',
        'entertaint' => 'Entertain', 'souvenir' => 'Souvenir', 'cashback' => 'Cashback',
        'sewa_laptop' => 'Sewa laptop', 'oleh_oleh' => 'Oleh-oleh',
        'biaya_lain_lain' => 'Biaya lain-lain', 'biaya_exam' => 'Exam',
    ];

    private const KOLOM_TANGGAL_FORM = ['mulai', 'selesai', 'tgl_pa'];

    private const JABATAN_FINANCE = 'Finance & Accounting';
    private const AKSI_SINKRON = ['sinkronData', 'sinkronKonfirmasi', 'sinkronBatal', 'sinkronSimpan', 'sinkronCatatan'];
    private const MAKS_HARI_SINKRON = 370;
    private const MAKS_CATATAN = 100;

    private const FIELD_SINKRON = [
        'materi'            => ['Materi', 'materi', 'materi', 'materi'],
        'perusahaan'        => ['Perusahaan', 'perusahaan', 'perusahaan', 'perusahaan'],
        'tanggal_mulai'     => ['Tanggal mulai', 'tanggal_mulai', 'tanggal_mulai', 'tanggal'],
        'tanggal_selesai'   => ['Tanggal selesai', 'tanggal_selesai', 'tanggal_selesai', 'tanggal'],
        'harga_net'         => ['Harga net', 'harga_net', 'harga_net', 'uang'],
        'pax'               => ['Pax', 'pax', 'pax', 'angka'],
        'total_kotor'       => ['Total penjualan kotor', 'total_penjualan_kotor', 'total_penjualan_kotor', 'uang'],
        'diskon'            => ['Diskon / PA', 'diskon', 'diskon', 'uang'],
        'total_diskon'      => ['Total diskon', 'total_diskon', 'total_diskon', 'uang'],
        'total_pa'          => ['Total PA', 'total_pa', 'total_pa', 'uang'],
        'total_cashback'    => ['Cashback', 'total_cashback', 'total_cashback', 'uang'],
        'total_uang_saku'   => ['Uang saku', 'total_uang_saku', 'total_uang_saku', 'uang'],
        'total_akomodasi'   => ['Akomodasi', 'total_akomodasi', 'total_akomodasi', 'uang'],
        'oleh_oleh'         => ['Oleh-oleh peserta', 'oleh_oleh', 'oleh_oleh', 'uang'],
        'biaya_lain_lain'   => ['Biaya lain-lain', 'biaya_lain_lain', 'biaya_lain_lain', 'uang'],
        'entertainment'     => ['Entertainment', 'entertainment', 'entertainment', 'uang'],
        'jenis_transport'   => ['Jenis transport', 'jenis_transport', 'jenis_transport', 'teks'],
        'biaya_transport'   => ['Biaya transport', 'biaya_transport', 'biaya_transport', 'uang'],
        'bersih'            => ['Total penjualan bersih', 'total_penjualan_sales', 'total_penjualan_bersih', 'uang'],
    ];

    private const KHUSUS_FINANCE = [
        'no_faktur'          => ['No faktur', 'teks'],
        'no_invoice'         => ['No invoice', 'teks'],
        'PPN'                => ['PPN', 'uang'],
        'PPH'                => ['PPH', 'uang'],
        'jumlah_pembayaran'  => ['Jumlah pembayaran', 'uang'],
        'tanggal_pembayaran' => ['Tanggal pembayaran', 'teks'],
        'biaya_admin'        => ['Biaya admin', 'uang'],
    ];

    private array $memoNama = [];

    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware(function ($request, $next) {
            if (Auth::user()->jabatan !== self::JABATAN_AKSES) {
                abort(403, 'Anda tidak memiliki akses ke fitur ini.');
            }
            return $next($request);
        })->except(array_merge(['index'], self::AKSI_SINKRON));

        $this->middleware(function ($request, $next) {
            if (!in_array(Auth::user()->jabatan, [self::JABATAN_AKSES, self::JABATAN_FINANCE], true)) {
                abort(403, 'Anda tidak memiliki akses ke fitur ini.');
            }
            return $next($request);
        })->only(self::AKSI_SINKRON);
    }

    public function index()
    {
        $data = [
            'urlApprovalSales'   => url('/crm/approval-pendapatan-sales'),
            'urlApprovalFinance' => url('/office/approval-pendapatan'),
            'daftarMateri'       => [],
            'daftarPerusahaan'   => [],
        ];

        if (in_array(Auth::user()->jabatan, [self::JABATAN_AKSES, self::JABATAN_FINANCE], true)) {
            $data['daftarMateri'] = Materi::orderBy('nama_materi')->get(['id', 'nama_materi'])
                ->map(fn ($m) => [$m->id, $m->nama_materi])->values()->all();
            $data['daftarPerusahaan'] = Perusahaan::orderBy('nama_perusahaan')->get(['id', 'nama_perusahaan'])
                ->map(fn ($p) => [$p->id, $p->nama_perusahaan])->values()->all();
        }

        return view('office.KelengkapanData.index', $data);
    }

    public function paData()
    {
        $paPerRkm = perhitunganNetSales::whereNotNull('tgl_pa')
            ->whereNull('deleted_at')
            ->orderByDesc('tgl_pa')
            ->orderByDesc('id')
            ->get()
            ->groupBy('id_rkm');

        $peluang = Peluang::with(['perusahaan:id,nama_perusahaan', 'materiRelation:id,nama_materi'])
            ->whereNull('deleted_at')
            ->where('tahap', '!=', 'lost')
            ->orderByDesc('id')
            ->get();

        $data = $peluang->map(function ($p) use ($paPerRkm) {
            $daftarPa = $p->id_rkm ? $paPerRkm->get($p->id_rkm, collect()) : collect();
            $punyaPa = $daftarPa->isNotEmpty();
            $perluLengkapi = !$punyaPa && in_array($p->tahap, self::TAHAP_WAJIB_PA, true);

            $bruto = (float) $p->harga * (int) $p->pax;
            $totalPa = (float) $daftarPa->sum(fn ($r) => $this->hitungTotal($r));

            return [
                'id_peluang'     => $p->id,
                'perusahaan'     => $p->perusahaan->nama_perusahaan ?? '-',
                'materi'         => $p->materiRelation->nama_materi ?? '-',
                'periode'        => $p->periode_mulai . ' s/d ' . $p->periode_selesai,
                'tgl_kelas'      => $p->periode_mulai ? Carbon::parse($p->periode_mulai)->format('Y-m-d') : null,
                'sales'          => $p->id_sales ?? '-',
                'tahap'          => $p->tahap,
                'harga'          => (float) $p->harga,
                'pax'            => (int) $p->pax,
                'bruto'          => $bruto,
                'netsales'       => (float) $p->netsales,
                'tgl_closed'     => $p->merah ? Carbon::parse($p->merah)->format('Y-m-d') : null,
                'umur_hari'      => $p->merah ? (int) Carbon::parse($p->merah)->startOfDay()->diffInDays(now()->startOfDay()) : null,
                'punya_pa'       => $punyaPa,
                'perlu_lengkapi' => $perluLengkapi,
                'jumlah_pa'      => $daftarPa->count(),
                'total_pa'       => $totalPa,
                'melebihi'       => $totalPa > $bruto,
                'daftar_pa'      => $daftarPa->map(fn ($r) => $this->petaPA($r))->values(),
            ];
        })->values();

        $sudah = $data->where('punya_pa', true)->count();
        $belum = $data->count() - $sudah;

        return response()->json([
            'data'      => $data,
            'ringkasan' => [
                'total'          => $data->count(),
                'sudah'          => $sudah,
                'belum'          => $belum,
                'perlu_lengkapi' => $data->where('perlu_lengkapi', true)->count(),
            ],
        ]);
    }

    private function petaPA($r): array
    {
        return [
            'id'                       => $r->id,
            'tgl_pa'                   => $r->tgl_pa ? Carbon::parse($r->tgl_pa)->format('Y-m-d') : null,
            'tipe_pembayaran'          => $r->tipe_pembayaran,
            'total'                    => $this->hitungTotal($r),
            'rincian' => [
                'Transportasi'      => (float) $r->transportasi,
                'Akomodasi peserta' => (float) $r->akomodasi_peserta,
                'Akomodasi tim'     => (float) $r->akomodasi_tim,
                'Fresh money'       => (float) $r->fresh_money,
                'Entertain'         => (float) $r->entertaint,
                'Souvenir'          => (float) $r->souvenir,
                'Cashback'          => (float) $r->cashback,
                'Oleh-oleh'         => (float) $r->oleh_oleh,
                'Biaya lain-lain'   => (float) $r->biaya_lain_lain,
                'Exam'              => (float) ($r->{$this->kolomDb('biaya_exam')} ?? 0),
                'Sewa laptop'       => (float) $r->sewa_laptop,
            ],
            'jenis_transportasi'       => $r->jenis_transportasi,
            'keterangan_akomodasi_tim' => $r->keterangan_akomodasi_tim,
            'keterangan_entertaint'    => $r->keterangan_entertaint,
            'keterangan_biaya_lain'    => $r->keterangan_biaya_lain,
            'deskripsi_tambahan'       => $r->deskripsi_tambahan,
            'bukti_url'                => $r->bukti ? Storage::disk('public')->url($r->bukti) : null,
        ];
    }

    public function downloadTemplate()
    {
        $path = storage_path('app/templates/template_import_net_sales.xlsx');
        abort_unless(is_file($path), 404, 'File template belum tersedia di server.');

        return response()->download($path, 'Template Import Payment Advance.xlsx');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx',
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'File harus berformat .xlsx.',
        ]);

        try {
            $sheet = IOFactory::load($request->file('file')->getRealPath())->getSheet(0);
        } catch (\Throwable $e) {
            return response()->json(['message' => 'File tidak dapat dibaca. Pastikan file tidak rusak.'], 422);
        }

        if ($this->norm($sheet->getCell('A1')->getValue()) !== 'nama perusahaan'
            || $this->norm($sheet->getCell('T1')->getValue()) !== 'catatan tambahan') {
            return response()->json([
                'message' => 'File ini bukan template yang benar. Unduh template terbaru lalu isi ulang.',
            ], 422);
        }

        $barisTerakhir = $sheet->getHighestDataRow();

        $petaPerusahaan = $this->petaPerusahaan();
        $petaMateri     = $this->petaMateri();

        $hasil = [];
        $terlihat = [];
        $berjalan = [];

        for ($r = self::BARIS_DATA_PERTAMA; $r <= $barisTerakhir; $r++) {
            $c = $sheet->rangeToArray("A{$r}:X{$r}", null, true, false, false)[0];
            if (collect($c)->every(fn ($v) => $v === null || trim((string) $v) === '')) {
                continue;
            }

            $hasil[] = $this->periksaBaris($r, $c, $petaPerusahaan, $petaMateri, $terlihat, $berjalan);
        }

        if (empty($hasil)) {
            return response()->json(['message' => 'Tidak ada data yang terbaca. Isi data mulai baris 4.'], 422);
        }

        $token = (string) Str::uuid();
        Cache::put($this->kunciCache($token), $hasil, now()->addMinutes(self::CACHE_MENIT));

        $ringkasan = collect($hasil)->countBy('status')->all();

        return response()->json([
            'token'     => $token,
            'ringkasan' => array_merge(['siap' => 0, 'duplikat' => 0, 'tinjau' => 0, 'gagal' => 0], $ringkasan),
            'baris'     => collect($hasil)->map(fn ($b) => $this->untukKlien($b))->values(),
        ]);
    }

    private function untukKlien(array $b): array
    {
        return collect($b)->except(['data', 'mentah', 'asli', 'override'])->all();
    }

    private function periksaBaris(int $no, array $c, $petaPerusahaan, $petaMateri, array &$terlihat, array &$berjalan, array $override = [], ?array $asli = null): array
    {
        $asli = $asli ?? $c;
        $gagal = [];
        $tinjau = [];
        $bedaSales = false;

        $namaPerusahaan = trim((string) $c[0]);
        $namaMateri     = trim((string) $c[1]);
        $mulai          = $this->parseTanggal($c[2]);
        $selesai        = $this->parseTanggal($c[3]);
        $idSales        = trim((string) $c[4]);
        $namaSales      = trim((string) $c[5]);
        $tglPaIsi       = trim((string) $c[6]) !== '';
        $tglPaTerbaca   = $this->parseTanggal($c[6]);
        $tglPa          = $tglPaTerbaca ?? now()->format('Y-m-d');
        $tipe           = trim((string) $c[7]);

        $baris = [
            'no' => $no, 'status' => 'gagal', 'alasan' => [],
            'perusahaan' => $namaPerusahaan, 'materi' => $namaMateri,
            'periode' => ($mulai ?? '?') . ' s/d ' . ($selesai ?? '?'),
            'sales' => $idSales !== '' ? $idSales : $namaSales,
            'tgl_pa' => $tglPa, 'total' => 0, 'peluang_id' => null,
            'beda_sales' => false, 'ada_alasan_lain' => false, 'id_sales_prospek' => null,
            'data' => null,
            'mentah' => $c,
            'asli' => $asli,
            'override' => $override,
            'form' => $this->bentukForm($c),
            'diubah' => !empty($override) || $this->bentukForm($asli) !== $this->bentukForm($c),
            'manual' => [],
            'saran' => null,
        ];

        if ($namaPerusahaan === '') $gagal[] = 'Nama Perusahaan kosong.';
        if ($namaMateri === '')     $gagal[] = 'Nama Materi / Kelas kosong.';
        if (!$mulai)                $gagal[] = 'Tanggal Mulai Kelas kosong atau formatnya salah.';
        if (!$selesai)              $gagal[] = 'Tanggal Selesai Kelas kosong atau formatnya salah.';
        if ($tglPaIsi && !$tglPaTerbaca) $gagal[] = 'Tanggal Pengajuan PA formatnya salah.';

        $biaya = [];
        foreach (self::INDEKS_BIAYA as $nama => $idx) {
            $v = $c[$idx] ?? null;
            if ($v === null || trim((string) $v) === '') { $biaya[$nama] = null; continue; }
            $angka = $this->parseUang($v);
            if ($angka === null || $angka < 0) {
                $gagal[] = "Kolom biaya \"{$nama}\" harus berupa angka 0 atau lebih (contoh: 400000 atau Rp 400.000).";
                $biaya[$nama] = null;
                continue;
            }
            $biaya[$nama] = $angka;
        }
        $total = array_sum(array_map(fn ($v) => (float) $v, $biaya));
        $baris['total'] = $total;
        if ($total <= 0 && empty($gagal)) $gagal[] = 'Semua kolom biaya kosong atau 0.';

        // ---- pencarian perusahaan / materi / prospect (bisa diperbaiki manual) ----
        $peluang = null;
        $idP = [];
        $idM = [];
        $tahapCari = empty($gagal);
        $butuh = ['perusahaan' => false, 'materi' => false, 'peluang' => false];

        if ($tahapCari) {
            $idP = !empty($override['perusahaan_id'])
                ? [(int) $override['perusahaan_id']]
                : $petaPerusahaan->get($this->norm($namaPerusahaan), []);
            $idM = !empty($override['materi_id'])
                ? [(int) $override['materi_id']]
                : $petaMateri->get($this->norm($namaMateri), []);

            if (empty($idP)) { $gagal[] = 'Perusahaan tidak ditemukan di sistem.'; $butuh['perusahaan'] = true; }
            if (empty($idM)) { $gagal[] = 'Materi tidak ditemukan di sistem.';     $butuh['materi'] = true; }
        }

        if ($tahapCari && empty($gagal)) {
            $disaring = false;

            if (!empty($override['peluang_id'])) {
                $kandidat = Peluang::whereKey((int) $override['peluang_id'])
                    ->whereNull('deleted_at')
                    ->where('tahap', '!=', 'lost')
                    ->get();
                if ($kandidat->isEmpty()) {
                    $gagal[] = 'Prospect yang dipilih sudah tidak ada atau berstatus lost.';
                }
            } else {
                $kandidat = Peluang::whereIn('id_contact', $idP)
                    ->whereIn('materi', $idM)
                    ->whereDate('periode_mulai', $mulai)
                    ->whereDate('periode_selesai', $selesai)
                    ->whereNull('deleted_at')
                    ->where('tahap', '!=', 'lost')
                    ->get();

                $awal = $kandidat->count();

                if ($awal > 1) {
                    $disaring = true;
                    $hasil = collect();
                    if ($idSales !== '') {
                        $hasil = $kandidat->filter(fn ($p) => $this->norm($p->id_sales) === $this->norm($idSales));
                    }
                    if ($hasil->isEmpty() && $namaSales !== '') {
                        $ids = $this->idSalesDariNama($namaSales);
                        $hasil = $kandidat->filter(fn ($p) => in_array($this->norm($p->id_sales), $ids, true));
                    }
                    if ($idSales === '' && $namaSales === '') {
                        $gagal[] = "Ditemukan {$awal} peluang yang sama. Isi ID Sales atau Nama Lengkap Sales untuk membedakan, atau pilih prospect manual.";
                    } elseif ($hasil->isEmpty()) {
                        $gagal[] = "Ditemukan {$awal} peluang yang sama, tetapi ID/Nama Sales tidak cocok dengan salah satunya.";
                    } elseif ($hasil->count() > 1) {
                        $gagal[] = "Masih ditemukan {$hasil->count()} peluang setelah dicocokkan dengan sales. Tidak dapat ditentukan.";
                    }
                    $kandidat = $hasil->values();
                } elseif ($awal === 0) {
                    $gagal[] = 'Peluang tidak ditemukan (periksa perusahaan, materi, dan tanggal kelas; peluang lost tidak ikut dicari).';
                }
            }

            if (!empty($gagal)) $butuh['peluang'] = true;
            if (empty($gagal)) $peluang = $kandidat->first();

            if ($peluang && !$disaring && empty($override['peluang_id']) && $idSales !== '' && $this->norm($peluang->id_sales) !== $this->norm($idSales)) {
                $tinjau[] = "ID Sales di file ({$idSales}) berbeda dengan sales pada prospect ({$peluang->id_sales}). ID Sales tidak akan diubah dari file.";
                $bedaSales = true;
            }
        }

        if (!empty($gagal)) {
            $baris['alasan'] = $gagal;
            if ($tahapCari) {
                $baris['saran'] = $this->susunSaran($butuh, $namaPerusahaan, $namaMateri, $idP, $idM, $mulai);
            }
            return $baris;
        }

        $baris['peluang_id'] = $peluang->id;

        // ---- jejak perbaikan manual (tidak menahan status; hanya dicatat) ----
        $manual = $this->ringkasPerubahan($asli, $c);
        if (!empty($override['perusahaan_id'])) {
            $namaDb = Perusahaan::whereKey((int) $override['perusahaan_id'])->value('nama_perusahaan');
            $baris['perusahaan'] = $namaDb ?: $namaPerusahaan;
            $manual[] = "Perusahaan dipilih dari database: \"{$namaDb}\" (di file: \"{$namaPerusahaan}\")";
        }
        if (!empty($override['materi_id'])) {
            $namaDb = Materi::whereKey((int) $override['materi_id'])->value('nama_materi');
            $baris['materi'] = $namaDb ?: $namaMateri;
            $manual[] = "Materi dipilih dari database: \"{$namaDb}\" (di file: \"{$namaMateri}\")";
        }
        if (!empty($override['peluang_id'])) {
            $manual[] = 'Prospect dipilih manual (ID ' . $peluang->id . ', periode ' . substr((string) $peluang->periode_mulai, 0, 10)
                . ' s/d ' . substr((string) $peluang->periode_selesai, 0, 10) . ')';
        }
        $baris['manual'] = $manual;

        if ($peluang->tahap !== 'merah') {
            $tinjau[] = "Peluang belum berstatus closed win (tahap saat ini: {$peluang->tahap}).";
        }
        if ($tglPaIsi && Carbon::parse($tglPa)->isFuture()) {
            $tinjau[] = 'Tanggal pengajuan PA berada di masa depan.';
        }
        if ($tglPaIsi && Carbon::parse($tglPa)->gt(Carbon::parse($selesai)->addDays(self::BATAS_HARI_SETELAH_KELAS))) {
            $tinjau[] = 'Tanggal pengajuan PA lebih dari ' . self::BATAS_HARI_SETELAH_KELAS . ' hari setelah kelas selesai.';
        }

        $sisa = (float) $peluang->netsales - ($berjalan[$peluang->id] ?? 0);
        if ($total > $sisa) {
            $tinjau[] = 'Total PA (Rp ' . number_format($total, 0, ',', '.') . ') melebihi sisa net sales peluang (Rp '
                . number_format($sisa, 0, ',', '.') . ').';
        }

        $duplikat = [];
        $kunci = $peluang->id_rkm . '|' . $tglPa . '|' . round($total, 2);
        if (isset($terlihat[$kunci])) {
            $duplikat[] = "Sama dengan baris {$terlihat[$kunci]} pada file ini.";
        }
        $sudahAda = perhitunganNetSales::where('id_rkm', $peluang->id_rkm)
            ->whereDate('tgl_pa', $tglPa)
            ->whereNull('deleted_at')
            ->get()
            ->contains(fn ($x) => abs($this->hitungTotal($x) - $total) < 0.01);
        if ($sudahAda) {
            $duplikat[] = 'Sudah ada Payment Advance dengan tanggal dan total yang sama untuk peluang ini.';
        }
        $terlihat[$kunci] = $terlihat[$kunci] ?? $no;

        $baris['beda_sales'] = $bedaSales;
        $baris['ada_alasan_lain'] = count($tinjau) > ($bedaSales ? 1 : 0);
        $baris['id_sales_prospek'] = $peluang->id_sales;

        $baris['alasan'] = array_merge($duplikat, $tinjau);
        $baris['status'] = !empty($duplikat) ? 'duplikat' : (!empty($tinjau) ? 'tinjau' : 'siap');

        $berjalan[$peluang->id] = ($berjalan[$peluang->id] ?? 0) + ($baris['status'] === 'duplikat' ? 0 : $total);

        $baris['data'] = array_merge($biaya, [
            'id_rkm'                   => $peluang->id_rkm,
            'id_peluang'               => $peluang->id,
            'tgl_pa'                   => $tglPa,
            'tipe_pembayaran'          => $tipe !== '' ? $tipe : null,
            'id_sales_prospek'         => $peluang->id_sales,
            'id_sales_file'            => $idSales !== '' ? $idSales : null,
            'jenis_transportasi'       => trim((string) $c[9]) ?: null,
            'keterangan_akomodasi_tim' => trim((string) $c[12]) ?: null,
            'keterangan_entertaint'    => trim((string) $c[15]) ?: null,
            'keterangan_biaya_lain'    => trim((string) ($c[22] ?? '')) ?: null,
            'deskripsi_tambahan'       => trim((string) $c[19]) ?: null,
            'jejak_manual'             => $manual ? Str::limit(implode('; ', $manual), 400, '...') : null,
            'total'                    => $total,
        ]);

        return $baris;
    }

    /**
     * Perbaiki satu baris hasil pemeriksaan: pilih data dari database dan/atau edit isian baris,
     * lalu periksa ulang baris itu saja. Baris yang lolos langsung berstatus siap/duplikat/tinjau
     * menurut aturan biasa (tanpa tinjauan tambahan hanya karena dipilih manual).
     */
    public function perbaiki(Request $request)
    {
        $request->validate([
            'token'         => 'required|string',
            'no'            => 'required|integer',
            'reset'         => 'nullable|boolean',
            'edit'          => 'nullable|array',
            'edit.*'        => 'nullable|string|max:255',
            'perusahaan_id' => 'nullable|integer|min:1',
            'materi_id'     => 'nullable|integer|min:1',
            'peluang_id'    => 'nullable|integer|min:1',
        ]);

        $kunci = $this->kunciCache($request->token);
        $lock = Cache::lock('import_pa_perbaiki:' . $request->token, 15);
        if (!$lock->get()) {
            return response()->json(['message' => 'Masih ada proses lain. Coba lagi sebentar.'], 409);
        }

        try {
            $rows = Cache::get($kunci);
            if (!$rows) {
                return response()->json(['message' => 'Sesi pemeriksaan sudah berakhir. Unggah ulang file Anda.'], 410);
            }

            $no = (int) $request->input('no');
            $idx = null;
            foreach ($rows as $i => $b) {
                if ((int) $b['no'] === $no) { $idx = $i; break; }
            }
            if ($idx === null) {
                return response()->json(['message' => 'Baris tidak ditemukan.'], 404);
            }

            $lama = $rows[$idx];
            if (empty($lama['mentah'])) {
                return response()->json(['message' => 'Baris ini tidak dapat diperbaiki dari sini.'], 422);
            }

            $asli = $lama['asli'] ?? $lama['mentah'];

            if ($request->boolean('reset')) {
                $mentah = $asli;
                $override = [];
            } else {
                $mentah = $lama['mentah'];
                $override = (array) ($lama['override'] ?? []);
                $formLama = $this->bentukForm($mentah);
                $indeks = self::INDEKS_FORM + self::INDEKS_BIAYA;

                foreach ((array) $request->input('edit', []) as $k => $v) {
                    if (!isset($indeks[$k])) continue;
                    $v = trim((string) $v);
                    if ($v === ($formLama[$k] ?? '')) continue;
                    $mentah[$indeks[$k]] = $v === '' ? null : $v;
                }

                $formBaru = $this->bentukForm($mentah);
                if ($formBaru['perusahaan'] !== $formLama['perusahaan']) {
                    unset($override['perusahaan_id'], $override['peluang_id']);
                }
                if ($formBaru['materi'] !== $formLama['materi']) {
                    unset($override['materi_id'], $override['peluang_id']);
                }
                if ($formBaru['mulai'] !== $formLama['mulai'] || $formBaru['selesai'] !== $formLama['selesai']) {
                    unset($override['peluang_id']);
                }

                // pilihan baru dari dropdown; memilih perusahaan/materi baru membatalkan pilihan prospek lama
                if ($request->filled('perusahaan_id') || $request->filled('materi_id')) {
                    if (!$request->filled('peluang_id')) unset($override['peluang_id']);
                }
                foreach (['perusahaan_id', 'materi_id', 'peluang_id'] as $k) {
                    if ($request->filled($k)) $override[$k] = (int) $request->input($k);
                }
            }

            // bangun ulang state duplikat / sisa net sales dari baris lain
            $terlihat = [];
            $berjalan = [];
            foreach ($rows as $b) {
                if ((int) $b['no'] === $no || empty($b['data'])) continue;
                $k = $b['data']['id_rkm'] . '|' . $b['data']['tgl_pa'] . '|' . round($b['data']['total'], 2);
                $terlihat[$k] = $terlihat[$k] ?? $b['no'];
                if ($b['status'] !== 'duplikat') {
                    $berjalan[$b['peluang_id']] = ($berjalan[$b['peluang_id']] ?? 0) + $b['data']['total'];
                }
            }

            $baru = $this->periksaBaris(
                $no, $mentah, $this->petaPerusahaan(), $this->petaMateri(),
                $terlihat, $berjalan, $override, $asli
            );

            $rows[$idx] = $baru;
            Cache::put($kunci, $rows, now()->addMinutes(self::CACHE_MENIT));

            return response()->json([
                'baris'     => $this->untukKlien($baru),
                'ringkasan' => array_merge(
                    ['siap' => 0, 'duplikat' => 0, 'tinjau' => 0, 'gagal' => 0],
                    collect($rows)->countBy('status')->all()
                ),
            ]);
        } finally {
            optional($lock)->release();
        }
    }

    public function commit(Request $request)
    {
        $request->validate([
            'token'     => 'required|string',
            'keputusan' => 'nullable|array',
        ]);

        $rows = Cache::get($this->kunciCache($request->token));
        if (!$rows) {
            return response()->json(['message' => 'Sesi pemeriksaan sudah berakhir. Unggah ulang file Anda.'], 410);
        }

        $lock = Cache::lock('import_pa_commit:' . $request->token, 120);
        if (!$lock->get()) {
            return response()->json(['message' => 'Import sedang diproses. Mohon tunggu.'], 409);
        }

        try {
            $keputusan = $request->input('keputusan', []);
            $rencana = [];
            $galat = [];
            $dilewati = 0;

            foreach ($rows as $b) {
                $k = $keputusan[$b['no']] ?? [];
                $aksi = $k['aksi'] ?? null;

                switch ($b['status']) {
                    case 'siap':
                        $aksi === 'impor' ? $rencana[] = [$b, true, null, false] : $dilewati++;
                        break;

                    case 'duplikat':
                        $aksi === 'tambah' ? $rencana[] = [$b, true, null, false] : $dilewati++;
                        break;

                    case 'tinjau':
                        $dariProspek = $aksi === 'prospek';
                        if ($dariProspek && empty($b['beda_sales'])) {
                            $galat[] = "Baris {$b['no']}: pilihan data dari prospect hanya berlaku jika ID Sales berbeda.";
                        } elseif ($aksi === 'pakai' || $dariProspek) {
                            $perluPerlakuan = !$dariProspek || !empty($b['ada_alasan_lain']);
                            $perlakuan = $perluPerlakuan ? ($k['perlakuan'] ?? null) : 'potong';
                            $alasan = trim((string) ($k['alasan'] ?? ''));
                            $alasan = $alasan !== '' ? $alasan : null;
                            if (!in_array($perlakuan, ['potong', 'catat'], true)) {
                                $galat[] = "Baris {$b['no']}: pilih perlakuan data.";
                            } else {
                                $rencana[] = [$b, $perlakuan === 'potong', $alasan, $dariProspek];
                            }
                        } elseif ($aksi === 'lewati') {
                            $dilewati++;
                        } else {
                            $galat[] = "Baris {$b['no']}: belum ada keputusan.";
                        }
                        break;

                    default:
                        $dilewati++;
                }
            }

            if (!empty($galat)) {
                return response()->json(['message' => 'Masih ada keputusan yang belum lengkap.', 'errors' => $galat], 422);
            }
            if (empty($rencana)) {
                return response()->json(['message' => 'Tidak ada data yang dipilih untuk diimpor.'], 422);
            }

            $diimpor = 0;
            $totalDipotong = 0.0;
            try {
                DB::transaction(function () use ($rencana, &$diimpor, &$totalDipotong) {
                    foreach ($rencana as [$b, $potong, $alasan, $dariProspek]) {
                        try {
                            $this->simpanPA($b['data'], $potong, $alasan, $dariProspek);
                        } catch (\Throwable $e) {
                            throw new \RuntimeException("Baris {$b['no']}: " . $e->getMessage(), 0, $e);
                        }
                        $diimpor++;
                        if ($potong) $totalDipotong += $b['data']['total'];
                    }
                });
            } catch (\Throwable $e) {
                Log::error('[PA Import] Gagal commit', ['error' => $e->getMessage()]);
                return response()->json(['message' => 'Import dibatalkan, tidak ada data yang tersimpan. ' . $e->getMessage()], 500);
            }

            Cache::forget($this->kunciCache($request->token));
            Log::info('[PA Import] Selesai', ['user' => Auth::id(), 'diimpor' => $diimpor, 'dilewati' => $dilewati]);

            return response()->json([
                'diimpor'        => $diimpor,
                'dilewati'       => $dilewati,
                'total_dipotong' => $totalDipotong,
            ]);
        } finally {
            optional($lock)->release();
        }
    }

    private function simpanPA(array $d, bool $potong, ?string $alasan, bool $dariProspek = false): void
    {
        $existing = $this->punyaKolomFinal()
            ? perhitunganNetSales::where('id_rkm', $d['id_rkm'])->first()
            : null;

        $tracking = trackingNetSales::where('id_rkm', $d['id_rkm'])->first();
        if (!$tracking) {
            $tracking = new trackingNetSales();
            $tracking->id_rkm = $d['id_rkm'];
            $tracking->save();
        }

        $jejak = '[Import ' . now()->format('d/m/Y H:i') . ' oleh ' . (Auth::user()->username ?? Auth::id()) . ']'
            . ($alasan ? " Dipakai karena: {$alasan}" : '')
            . ($potong ? '' : ' (net sales tidak dikurangi)')
            . ($dariProspek ? ' Data dari prospect: ID Sales ' . ($d['id_sales_prospek'] ?? '-') . ' dipakai, ID Sales file (' . ($d['id_sales_file'] ?? '-') . ') tidak dipakai.' : '')
            . (!empty($d['jejak_manual']) ? ' Diperbaiki manual saat import: ' . $d['jejak_manual'] . '.' : '');

        $n = new perhitunganNetSales();
        $n->id_rkm = $d['id_rkm'];
        foreach (self::KOLOM_BIAYA as $k) {
            $n->{$this->kolomDb($k)} = ($d[$k] ?? null) ?: null;
        }
        $n->jenis_transportasi = $d['jenis_transportasi'];
        $n->keterangan_akomodasi_tim = $d['keterangan_akomodasi_tim'];
        $n->keterangan_entertaint = $d['keterangan_entertaint'];
        $n->keterangan_biaya_lain = $d['keterangan_biaya_lain'] ?? null;
        $n->tipe_pembayaran = $d['tipe_pembayaran'];
        $n->tgl_pa = $d['tgl_pa'];
        $n->deskripsi_tambahan = trim(($d['deskripsi_tambahan'] ?? '') . ' ' . $jejak);
        $n->id_tracking = $tracking->id;
        $n->save();

        KelengkapanDataLog::catatTeks('pa', $d['id_rkm'], 'Dibuat (import) | tgl_pa: ' . $d['tgl_pa'] . '; total: ' . (int) $d['total'] . '; ' . ($potong ? 'net sales dikurangi' : 'net sales tidak dikurangi') . ($alasan ? '; alasan: ' . $alasan : '') . ($dariProspek ? '; ID Sales dari prospect: ' . ($d['id_sales_prospek'] ?? '-') : '') . (!empty($d['jejak_manual']) ? '; perbaikan manual: ' . $d['jejak_manual'] : ''));

        if ($potong) {
            if ($existing) {
                $existing->final = (float) $existing->final - $d['total'];
                $existing->save();
            }
            $p = Peluang::find($d['id_peluang']);
            if ($p) {
                $p->netsales = (float) $p->netsales - $d['total'];
                $p->save();
            }
        }
    }

    public function sinkronData(Request $request)
    {
        [$mulai, $selesai] = $this->validasiPeriode($request);
        $hasil = $this->susunSinkron($mulai, $selesai);

        return response()->json([
            'periode'   => ['mulai' => $mulai, 'selesai' => $selesai],
            'peran'     => $this->peranSinkron(),
            'ringkasan' => $hasil['ringkasan'],
            'sidik'     => $hasil['sidik'],
            'baris'     => $hasil['baris'],
            'sesi'      => $this->ringkasSesi($mulai, $selesai, $hasil),
        ]);
    }

    public function sinkronKonfirmasi(Request $request)
    {
        [$mulai, $selesai] = $this->validasiPeriode($request);
        $request->validate(['sidik' => 'required|string']);

        $hasil = $this->susunSinkron($mulai, $selesai);

        if ($hasil['sidik'] !== $request->input('sidik')) {
            return response()->json(['message' => 'Data berubah sejak layar ini dibuka. Muat ulang lalu periksa kembali.'], 409);
        }
        if ($hasil['ringkasan']['total'] === 0) {
            return response()->json(['message' => 'Tidak ada data pada periode ini.'], 422);
        }
        if ($hasil['ringkasan']['belum_sinkron'] > 0) {
            return response()->json(['message' => 'Masih ada ' . $hasil['ringkasan']['belum_sinkron'] . ' data yang belum sama. Perbaiki lewat halaman approval lalu muat ulang.'], 422);
        }

        $peran = $this->peranSinkron();
        $kunci = ['periode_mulai' => $mulai, 'periode_selesai' => $selesai];

        DB::table('kelengkapan_data_syncs')->insertOrIgnore($kunci + ['created_at' => now(), 'updated_at' => now()]);
        DB::table('kelengkapan_data_syncs')->where($kunci)->update([
            $peran . '_by'   => Auth::id(),
            $peran . '_at'   => now(),
            $peran . '_hash' => $hasil['sidik'],
            'updated_at'     => now(),
        ]);

        return response()->json(['sesi' => $this->ringkasSesi($mulai, $selesai, $hasil)]);
    }

    public function sinkronBatal(Request $request)
    {
        [$mulai, $selesai] = $this->validasiPeriode($request);
        $peran = $this->peranSinkron();

        DB::table('kelengkapan_data_syncs')
            ->where('periode_mulai', $mulai)
            ->where('periode_selesai', $selesai)
            ->update([
                $peran . '_by'   => null,
                $peran . '_at'   => null,
                $peran . '_hash' => null,
                'updated_at'     => now(),
            ]);

        return response()->json(['sesi' => $this->ringkasSesi($mulai, $selesai, $this->susunSinkron($mulai, $selesai))]);
    }

    public function sinkronCatatan(Request $request)
    {
        $request->validate([
            'id_rkm' => 'required|integer',
            'teks'   => 'required|string|min:2|max:500',
        ], [
            'teks.required' => 'Catatan tidak boleh kosong.',
            'teks.min'      => 'Catatan minimal 2 karakter.',
            'teks.max'      => 'Catatan maksimal 500 karakter.',
        ]);

        $idRkm = (int) $request->input('id_rkm');
        $peran = $this->peranSinkron();
        $teks = trim((string) $request->input('teks'));
        $tabel = (new ApprovalPendapatanSales())->getTable();

        try {
            $daftar = DB::transaction(function () use ($idRkm, $peran, $teks, $tabel) {
                $row = DB::table($tabel)->where('id_rkm', $idRkm)->lockForUpdate()->first();
                if (!$row) {
                    throw new \DomainException('Catatan dapat ditambahkan setelah data Adm Sales untuk RKM ini dibuat.');
                }

                $daftar = $this->bacaCatatan($row->catatan_sinkron ?? null);
                $daftar[] = [
                    'user_id' => Auth::id(),
                    'nama'    => $this->namaUser(Auth::id()) ?? (string) Auth::id(),
                    'peran'   => $peran,
                    'teks'    => $teks,
                    'waktu'   => now()->format('d/m/Y H:i'),
                ];
                $daftar = array_slice($daftar, -self::MAKS_CATATAN);

                DB::table($tabel)->where('id_rkm', $idRkm)->update([
                    'catatan_sinkron' => json_encode($daftar, JSON_UNESCAPED_UNICODE),
                ]);

                return $daftar;
            });
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (\Throwable $e) {
            Log::error('[Sinkron] Gagal simpan catatan', ['rkm' => $idRkm, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menyimpan catatan.'], 500);
        }

        try {
            KelengkapanDataLog::catatTeks($peran, $idRkm, 'Catatan sinkron oleh ' . (Auth::user()->username ?? Auth::id()) . ' | ' . $teks);
        } catch (\Throwable $e) {
            Log::warning('[Sinkron] Log catatan gagal dicatat', ['rkm' => $idRkm, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'pesan'   => 'Catatan ditambahkan.',
            'catatan' => $daftar,
        ]);
    }

    public function sinkronSimpan(Request $request)
    {
        $peran = $this->peranSinkron();
        $lawan = $peran === 'sales' ? 'finance' : 'sales';

        $request->validate([
            'id_rkm' => 'required|integer',
            'versi'  => 'required|string',
            'data'   => 'nullable|array',
        ]);

        $idRkm = (int) $request->input('id_rkm');
        $rkm = RKM::find($idRkm);
        if (!$rkm) {
            return response()->json(['message' => 'RKM tidak ditemukan.'], 404);
        }

        $bidang = $this->bidangEditable($peran);
        $kiriman = array_intersect_key((array) $request->input('data', []), $bidang);

        $aturan = [];
        $label = [];
        foreach ($kiriman as $k => $_) {
            $aturan[$k] = $this->aturanBidang($bidang[$k]['tipe']);
            $label[$k] = self::FIELD_SINKRON[$k][0];
        }
        if (!empty($aturan)) {
            Validator::make($kiriman, $aturan, [], $label)->validate();
        }

        if (!empty($kiriman['materi']) && !Materi::whereKey($kiriman['materi'])->exists()) {
            throw ValidationException::withMessages(['materi' => 'Materi tidak ditemukan.']);
        }
        if (!empty($kiriman['perusahaan']) && !Perusahaan::whereKey($kiriman['perusahaan'])->exists()) {
            throw ValidationException::withMessages(['perusahaan' => 'Perusahaan tidak ditemukan.']);
        }

        $kelasSendiri = $peran === 'sales' ? ApprovalPendapatanSales::class : ApprovalPendapatan::class;
        $kelasLawan = $peran === 'sales' ? ApprovalPendapatan::class : ApprovalPendapatanSales::class;
        $versiDilihat = (string) $request->input('versi');

        try {
            $hasil = DB::transaction(function () use ($idRkm, $rkm, $peran, $lawan, $bidang, $kiriman, $kelasSendiri, $kelasLawan, $versiDilihat) {
                $rec = $kelasSendiri::where('id_rkm', $idRkm)->lockForUpdate()->first();
                $recLawan = $kelasLawan::where('id_rkm', $idRkm)->first();

                $nilaiSendiri = $rec ? $this->nilaiSisi($rec, $peran) : null;
                $nilaiLawan = $recLawan ? $this->nilaiSisi($recLawan, $lawan) : null;

                $nilaiS = $peran === 'sales' ? $nilaiSendiri : $nilaiLawan;
                $nilaiF = $peran === 'sales' ? $nilaiLawan : $nilaiSendiri;

                if ($this->versiBaris($nilaiS, $nilaiF) !== $versiDilihat) {
                    throw new \DomainException('Data RKM ini sudah berubah sejak layar dibuka (diubah oleh pihak lain atau dari halaman lain). Tampilan dimuat ulang, periksa lalu ubah kembali.');
                }

                $dibuat = !$rec;
                if ($dibuat) {
                    $rec = new $kelasSendiri();
                    $rec->id_rkm = $idRkm;
                    $dasar = $this->nilaiAwal($rkm, $nilaiLawan);
                } else {
                    $dasar = $nilaiSendiri;
                }

                $normal = [];
                foreach ($kiriman as $k => $v) {
                    $normal[$k] = $this->normNilai($v, $bidang[$k]['tipe']);
                }

                $berubah = [];
                foreach ($normal as $k => $v) {
                    if (!array_key_exists($k, $dasar) || $dasar[$k] !== $v) {
                        $berubah[$k] = $v;
                    }
                }

                if (!$dibuat && empty($berubah)) {
                    throw new \UnexpectedValueException('Tidak ada perubahan untuk disimpan.');
                }

                $akhir = array_merge($dasar, $normal);
                if (!empty($akhir['tanggal_mulai']) && !empty($akhir['tanggal_selesai']) && $akhir['tanggal_selesai'] < $akhir['tanggal_mulai']) {
                    throw ValidationException::withMessages(['tanggal_selesai' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.']);
                }

                $tulis = $dibuat ? array_merge(array_intersect_key($dasar, $bidang), $normal) : $berubah;
                foreach ($tulis as $k => $nilai) {
                    $rec->{$bidang[$k]['kolom']} = $this->nilaiSimpan($nilai, $bidang[$k]['tipe']);
                }
                $rec->status = 'valid';
                $rec->save();

                if ($peran === 'finance' && array_key_exists('harga_net', $tulis)) {
                    outstanding::where('id_rkm', $idRkm)->update(['net_sales' => (float) $rec->harga_net]);
                }

                $ringkas = [];
                foreach ($berubah as $k => $baru) {
                    $ringkas[] = self::FIELD_SINKRON[$k][0] . ': ' . ($dibuat ? '-' : $this->teksLog($dasar[$k] ?? null)) . ' -> ' . $this->teksLog($baru);
                }

                return ['dibuat' => $dibuat, 'jumlah' => count($berubah), 'ringkas' => $ringkas];
            });
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        } catch (\UnexpectedValueException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('[Sinkron] Gagal simpan', ['rkm' => $idRkm, 'peran' => $peran, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Gagal menyimpan. Tidak ada data yang berubah.'], 500);
        }

        try {
            $teks = 'Perbaikan dari Kelengkapan Data (' . ($hasil['dibuat'] ? 'data baru' : 'ubah') . ') oleh '
                . (Auth::user()->username ?? Auth::id()) . ' | ' . implode('; ', $hasil['ringkas']);
            KelengkapanDataLog::catatTeks($peran, $idRkm, $teks);
        } catch (\Throwable $e) {
            Log::warning('[Sinkron] Log perubahan gagal dicatat', ['rkm' => $idRkm, 'error' => $e->getMessage()]);
        }

        return response()->json([
            'pesan'  => $hasil['dibuat']
                ? 'Data ' . ($peran === 'sales' ? 'Adm Sales' : 'Finance') . ' berhasil dibuat. Konfirmasi sebelumnya perlu diulang.'
                : 'Perubahan tersimpan (' . $hasil['jumlah'] . ' bidang). Konfirmasi sebelumnya perlu diulang.',
            'dibuat' => $hasil['dibuat'],
        ]);
    }

    private function validasiPeriode(Request $request): array
    {
        $v = $request->validate([
            'mulai'   => 'required|date_format:Y-m-d',
            'selesai' => 'required|date_format:Y-m-d|after_or_equal:mulai',
        ], [
            'mulai.required'          => 'Tanggal mulai periode wajib diisi.',
            'selesai.required'        => 'Tanggal selesai periode wajib diisi.',
            'selesai.after_or_equal'  => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ]);

        if (Carbon::parse($v['mulai'])->diffInDays(Carbon::parse($v['selesai'])) > self::MAKS_HARI_SINKRON) {
            throw ValidationException::withMessages([
                'selesai' => 'Rentang periode maksimal ' . self::MAKS_HARI_SINKRON . ' hari.',
            ]);
        }

        return [$v['mulai'], $v['selesai']];
    }

    private function peranSinkron(): string
    {
        return Auth::user()->jabatan === self::JABATAN_AKSES ? 'sales' : 'finance';
    }

    private function bidangEditable(string $peran): array
    {
        $indeks = $peran === 'sales' ? 1 : 2;
        $hasil = [];

        foreach (self::FIELD_SINKRON as $kunci => $def) {
            $hasil[$kunci] = ['kolom' => $def[$indeks], 'tipe' => $def[3]];
        }

        return $hasil;
    }

    private function aturanBidang(string $tipe): array
    {
        switch ($tipe) {
            case 'uang':
                return ['nullable', 'numeric', 'min:0'];
            case 'angka':
                return ['nullable', 'integer', 'min:0'];
            case 'materi':
            case 'perusahaan':
                return ['nullable', 'integer', 'min:0'];
            case 'tanggal':
                return ['nullable', 'date_format:Y-m-d'];
            default:
                return ['nullable', 'string', 'max:255'];
        }
    }

    private function nilaiSimpan($nilai, string $tipe)
    {
        switch ($tipe) {
            case 'uang':
                return (float) $nilai;
            case 'angka':
                return (int) $nilai;
            case 'materi':
            case 'perusahaan':
                return $nilai > 0 ? (int) $nilai : null;
            case 'tanggal':
                return $nilai !== '' ? $nilai : null;
            default:
                return $nilai !== '' ? $nilai : null;
        }
    }

    private function nilaiAwal($rkm, ?array $lawan): array
    {
        if ($lawan) {
            return $lawan;
        }

        $hasil = [];
        foreach (self::FIELD_SINKRON as $kunci => $def) {
            $hasil[$kunci] = $this->normNilai(null, $def[3]);
        }

        $harga = (int) round((float) ($rkm->harga_jual ?? 0));
        $pax = (int) ($rkm->pax ?? 0);

        $hasil['materi'] = (int) ($rkm->materi_key ?? 0);
        $hasil['perusahaan'] = (int) ($rkm->perusahaan_key ?? 0);
        $hasil['tanggal_mulai'] = $this->normNilai($rkm->tanggal_awal, 'tanggal');
        $hasil['tanggal_selesai'] = $this->normNilai($rkm->tanggal_akhir, 'tanggal');
        $hasil['harga_net'] = $harga;
        $hasil['pax'] = $pax;
        $hasil['total_kotor'] = $harga * $pax;

        return $hasil;
    }

    private function versiBaris(?array $nilaiS, ?array $nilaiF): string
    {
        return sha1(json_encode([$nilaiS, $nilaiF]));
    }

    private function teksLog($v): string
    {
        if ($v === null || $v === '') {
            return '-';
        }
        return is_scalar($v) ? (string) $v : json_encode($v);
    }

    private function bacaCatatan($mentah): array
    {
        if (is_string($mentah) && $mentah !== '') {
            $mentah = json_decode($mentah, true);
        }
        if (!is_array($mentah)) {
            return [];
        }

        return array_values(array_filter($mentah, fn ($x) => is_array($x) && isset($x['teks'])));
    }

    private function susunSinkron(string $mulai, string $selesai): array
    {
        $peran = $this->peranSinkron();

        $rkms = RKM::with(['materi', 'perusahaan', 'sales'])
            ->whereDate('tanggal_awal', '>=', $mulai)
            ->whereDate('tanggal_awal', '<=', $selesai)
            ->whereDate('tanggal_awal', '<=', now()->toDateString())
            ->orderBy('tanggal_awal')
            ->get();

        $ids = $rkms->pluck('id')->all();

        $finance = ApprovalPendapatan::whereIn('id_rkm', $ids)->get()->keyBy('id_rkm');
        $sales = ApprovalPendapatanSales::whereIn('id_rkm', $ids)->get()->keyBy('id_rkm');
        $pa = perhitunganNetSales::whereIn('id_rkm', $ids)->whereNull('deleted_at')->get()->groupBy('id_rkm');

        $idMateri = $finance->pluck('materi')->merge($sales->pluck('materi'))->filter()->unique()->values()->all();
        $idPerusahaan = $finance->pluck('perusahaan')->merge($sales->pluck('perusahaan'))->filter()->unique()->values()->all();
        $namaMateri = Materi::whereIn('id', $idMateri)->pluck('nama_materi', 'id')->all();
        $namaPerusahaan = Perusahaan::whereIn('id', $idPerusahaan)->pluck('nama_perusahaan', 'id')->all();

        $baris = [];
        $untukHash = [];
        $hitung = ['sama' => 0, 'selisih' => 0, 'hanya_finance' => 0, 'hanya_sales' => 0, 'belum_keduanya' => 0];
        $crmBedaTotal = 0;

        foreach ($rkms as $r) {
            $f = $finance->get($r->id);
            $s = $sales->get($r->id);
            $daftarPa = $pa->get($r->id, collect());

            if (!$f && !$s && $daftarPa->isEmpty()) {
                continue;
            }

            $nilaiS = $s ? $this->nilaiSisi($s, 'sales') : null;
            $nilaiF = $f ? $this->nilaiSisi($f, 'finance') : null;
            $crm = (int) round($daftarPa->sum(fn ($x) => $this->hitungTotal($x)));

            $rincian = [];
            $jumlahBeda = 0;
            foreach (self::FIELD_SINKRON as $kunci => $def) {
                $beda = $s && $f && !$this->samaNilai($nilaiS[$kunci], $nilaiF[$kunci]);
                if ($beda) {
                    $jumlahBeda++;
                }
                $rincian[] = [
                    'kunci'   => $kunci,
                    'jenis'   => $def[3],
                    'label'   => $def[0],
                    'tipe'    => in_array($def[3], ['uang', 'angka'], true) ? $def[3] : 'teks',
                    'sales'   => $s ? $this->tampilNilai($nilaiS[$kunci], $def[3], $namaMateri, $namaPerusahaan) : null,
                    'finance' => $f ? $this->tampilNilai($nilaiF[$kunci], $def[3], $namaMateri, $namaPerusahaan) : null,
                    'beda'    => $beda,
                ];
            }

            if ($f && $s) {
                $status = $jumlahBeda === 0 ? 'sama' : 'selisih';
            } elseif ($f) {
                $status = 'hanya_finance';
            } elseif ($s) {
                $status = 'hanya_sales';
            } else {
                $status = 'belum_keduanya';
            }
            $hitung[$status]++;

            $crmBeda = [];
            if ($s && $nilaiS['total_pa'] !== $crm) {
                $crmBeda[] = 'sales';
            }
            if ($f && $nilaiF['total_pa'] !== $crm) {
                $crmBeda[] = 'finance';
            }
            if (!empty($crmBeda)) {
                $crmBedaTotal++;
            }

            $khusus = [];
            if ($f && $peran === 'finance') {
                foreach (self::KHUSUS_FINANCE as $kol => [$label, $tipe]) {
                    $mentah = $f->{$kol} ?? null;
                    if ($tipe === 'uang') {
                        $nilai = (int) round((float) ($mentah ?? 0));
                    } else {
                        $nilai = trim((string) ($mentah ?? ''));
                        $nilai = $nilai === '' ? '-' : $nilai;
                    }
                    $khusus[] = ['label' => $label, 'tipe' => $tipe, 'nilai' => $nilai];
                }
            }

            $punyaSendiri = $peran === 'sales' ? (bool) $s : (bool) $f;
            $nilaiSendiri = $peran === 'sales' ? $nilaiS : $nilaiF;
            $nilaiLawan = $peran === 'sales' ? $nilaiF : $nilaiS;
            $catatan = $s ? $this->bacaCatatan($s->getRawOriginal('catatan_sinkron')) : [];

            $baris[] = [
                'id_rkm'         => $r->id,
                'perusahaan'     => $r->perusahaan->nama_perusahaan ?? '-',
                'materi'         => $r->materi->nama_materi ?? '-',
                'sales'          => $r->sales->nama_lengkap ?? '-',
                'tanggal'        => Carbon::parse($r->tanggal_awal)->format('Y-m-d') . ' s/d ' . Carbon::parse($r->tanggal_akhir)->format('Y-m-d'),
                'status'         => $status,
                'jumlah_beda'    => $jumlahBeda,
                'bersih_sales'   => $nilaiS['bersih'] ?? null,
                'bersih_finance' => $nilaiF['bersih'] ?? null,
                'total_pa_crm'   => $crm,
                'jumlah_pa_crm'  => $daftarPa->count(),
                'crm_beda'       => $crmBeda,
                'rincian'        => $rincian,
                'khusus_finance' => $khusus,
                'catatan'        => $catatan,
                'jumlah_catatan' => count($catatan),
                'catatan_bisa'   => (bool) $s,
                'versi'          => $this->versiBaris($nilaiS, $nilaiF),
                'lawan'          => $nilaiLawan,
                'edit'           => [
                    'ada'    => $punyaSendiri,
                    'sumber' => $punyaSendiri ? 'sendiri' : ($nilaiLawan ? 'lawan' : 'rkm'),
                    'nilai'  => $punyaSendiri ? $nilaiSendiri : $this->nilaiAwal($r, $nilaiLawan),
                ],
            ];

            $untukHash[] = [$r->id, $status, $nilaiS, $nilaiF, $crm];
        }

        $total = count($baris);

        return [
            'baris'     => $baris,
            'sidik'     => sha1(json_encode($untukHash)),
            'ringkasan' => [
                'total'          => $total,
                'sama'           => $hitung['sama'],
                'belum_sinkron'  => $total - $hitung['sama'],
                'selisih'        => $hitung['selisih'],
                'hanya_finance'  => $hitung['hanya_finance'],
                'hanya_sales'    => $hitung['hanya_sales'],
                'belum_keduanya' => $hitung['belum_keduanya'],
                'crm_beda'       => $crmBedaTotal,
            ],
        ];
    }

    private function nilaiSisi($row, string $sisi): array
    {
        $indeks = $sisi === 'sales' ? 1 : 2;
        $hasil = [];

        foreach (self::FIELD_SINKRON as $kunci => $def) {
            $kolom = $def[$indeks];
            $nilai = $row->{$kolom} ?? null;

            if ($kunci === 'total_kotor' && $nilai === null) {
                $hasil[$kunci] = (int) round((float) ($row->harga_net ?? 0) * (int) ($row->pax ?? 0));
                continue;
            }

            $hasil[$kunci] = $this->normNilai($nilai, $def[3]);
        }

        return $hasil;
    }

    private function normNilai($v, string $tipe)
    {
        switch ($tipe) {
            case 'uang':
                return (int) round((float) ($v ?? 0));
            case 'angka':
            case 'materi':
            case 'perusahaan':
                return (int) ($v ?? 0);
            case 'tanggal':
                if (!$v) {
                    return '';
                }
                return $v instanceof \DateTimeInterface ? $v->format('Y-m-d') : substr((string) $v, 0, 10);
            default:
                $t = trim((string) ($v ?? ''));
                return $t === '-' ? '' : $t;
        }
    }

    private function samaNilai($a, $b): bool
    {
        if (is_string($a) || is_string($b)) {
            return $this->norm((string) $a) === $this->norm((string) $b);
        }
        return $a === $b;
    }

    private function tampilNilai($v, string $tipe, array $namaMateri, array $namaPerusahaan)
    {
        if ($tipe === 'uang' || $tipe === 'angka') {
            return $v;
        }
        if ($tipe === 'materi') {
            return $v ? ($namaMateri[$v] ?? ('#' . $v)) : '-';
        }
        if ($tipe === 'perusahaan') {
            return $v ? ($namaPerusahaan[$v] ?? ('#' . $v)) : '-';
        }
        return $v === '' ? '-' : $v;
    }

    private function ringkasSesi(string $mulai, string $selesai, array $hasil): array
    {
        $sesi = DB::table('kelengkapan_data_syncs')
            ->where('periode_mulai', $mulai)
            ->where('periode_selesai', $selesai)
            ->first();

        $bagian = function (string $peran) use ($sesi, $hasil) {
            $at = $sesi->{$peran . '_at'} ?? null;
            $hash = $sesi->{$peran . '_hash'} ?? null;
            $sudah = $at && $hash === $hasil['sidik'];

            return [
                'sudah' => (bool) $sudah,
                'usang' => (bool) ($at && !$sudah),
                'oleh'  => $at ? $this->namaUser($sesi->{$peran . '_by'}) : null,
                'pada'  => $at ? Carbon::parse($at)->format('d/m/Y H:i') : null,
            ];
        };

        $sales = $bagian('sales');
        $finance = $bagian('finance');

        return [
            'sales'   => $sales,
            'finance' => $finance,
            'selesai' => $hasil['ringkasan']['total'] > 0
                && $hasil['ringkasan']['belum_sinkron'] === 0
                && $sales['sudah']
                && $finance['sudah'],
        ];
    }

    private function namaUser($id): ?string
    {
        if (!$id) {
            return null;
        }
        $u = User::with('karyawan')->find($id);
        if (!$u) {
            return (string) $id;
        }
        return $u->karyawan?->nama_lengkap ?? ($u->username ?? (string) $id);
    }

    private function punyaKolomFinal(): bool
    {
        static $ada = null;

        if ($ada === null) {
            $model = new perhitunganNetSales();
            $ada = Schema::hasColumn($model->getTable(), 'final');
        }

        return $ada;
    }

    private function hitungTotal($r): float
    {
        return array_sum(array_map(fn ($k) => (float) ($r->{$this->kolomDb($k)} ?? 0), self::KOLOM_BIAYA));
    }

    private function kolomDb(string $k): string
    {
        return self::KOLOM_DB[$k] ?? $k;
    }

    private function norm($v): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $v)));
    }

    private function kunciCache(string $token): string
    {
        return 'import_pa:' . Auth::id() . ':' . $token;
    }

    private function idSalesDariNama(string $nama): array
    {
        return User::whereHas('karyawan', function ($q) use ($nama) {
            $q->whereRaw('LOWER(TRIM(nama_lengkap)) = ?', [$this->norm($nama)]);
        })->pluck('id_sales')->map(fn ($v) => $this->norm($v))->all();
    }

    private function parseUang($v): ?float
    {
        if (is_int($v) || is_float($v)) {
            return (float) $v;
        }

        $s = trim((string) $v);
        if ($s === '') {
            return null;
        }

        $negatif = str_starts_with($s, '-') || (str_starts_with($s, '(') && str_ends_with($s, ')'));
        $s = preg_replace('/[^0-9.,]/', '', preg_replace('/rp\.?/iu', '', $s));
        if ($s === '' || !preg_match('/\d/', $s)) {
            return null;
        }

        $titik = substr_count($s, '.');
        $koma = substr_count($s, ',');

        if ($titik > 0 && $koma > 0) {
            $desimal = strrpos($s, '.') > strrpos($s, ',') ? '.' : ',';
            $ribuan = $desimal === '.' ? ',' : '.';
            $s = str_replace($ribuan, '', $s);
            $s = str_replace($desimal, '.', $s);
        } elseif ($titik > 0 || $koma > 0) {
            $pemisah = $titik > 0 ? '.' : ',';
            $jumlah = max($titik, $koma);
            $bagian = explode($pemisah, $s);
            $belakang = end($bagian);

            if ($jumlah > 1 || strlen($belakang) === 3) {
                $s = str_replace($pemisah, '', $s);
            } else {
                $s = str_replace($pemisah, '.', $s);
            }
        }

        if (!is_numeric($s)) {
            return null;
        }

        $hasil = (float) $s;

        return $negatif ? -$hasil : $hasil;
    }

    private function parseTanggal($v): ?string
    {
        if ($v === null || trim((string) $v) === '') return null;

        try {
            if (is_numeric($v)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($v))->format('Y-m-d');
            }
            return Carbon::createFromFormat('Y-m-d', trim((string) $v))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    // ------------------------------------------------------------------
    // Helper perbaikan import
    // ------------------------------------------------------------------

    /** Isian baris dalam bentuk teks, siap ditampilkan di form edit (tanggal dinormalkan ke Y-m-d). */
    private function bentukForm(array $c): array
    {
        $f = [];
        foreach (self::INDEKS_FORM as $k => $i) {
            $v = $c[$i] ?? null;
            $f[$k] = in_array($k, self::KOLOM_TANGGAL_FORM, true)
                ? ($this->parseTanggal($v) ?? trim((string) $v))
                : trim((string) $v);
        }
        foreach (self::INDEKS_BIAYA as $k => $i) {
            $f[$k] = trim((string) ($c[$i] ?? ''));
        }

        return $f;
    }

    private function ringkasPerubahan(array $asli, array $c): array
    {
        $a = $this->bentukForm($asli);
        $b = $this->bentukForm($c);
        $hasil = [];

        foreach (self::LABEL_FORM as $k => $label) {
            $x = $a[$k] ?? '';
            $y = $b[$k] ?? '';
            if ($x !== $y) {
                $hasil[] = $label . ': "' . ($x !== '' ? $x : '-') . '" -> "' . ($y !== '' ? $y : '-') . '"';
            }
        }

        return $hasil;
    }

    private function petaPerusahaan()
    {
        return Perusahaan::select('id', 'nama_perusahaan')->get()
            ->groupBy(fn ($x) => $this->norm($x->nama_perusahaan))
            ->map(fn ($g) => $g->pluck('id')->all());
    }

    private function petaMateri()
    {
        return Materi::select('id', 'nama_materi')->get()
            ->groupBy(fn ($x) => $this->norm($x->nama_materi))
            ->map(fn ($g) => $g->pluck('id')->all());
    }

    private function skorMirip(string $a, string $b): float
    {
        $bersih = fn (string $s) => trim(preg_replace('/\s+/u', ' ',
            preg_replace('/\b(pt|cv|tbk|persero|ud)\b|[^\p{L}\p{N}\s]/u', ' ', $this->norm($s))));

        $a = $bersih($a);
        $b = $bersih($b);
        if ($a === '' || $b === '') return 0;

        similar_text($a, $b, $persen);
        if (str_contains($a, $b) || str_contains($b, $a)) $persen = max($persen, 85);

        $ta = array_filter(explode(' ', $a));
        $tb = array_filter(explode(' ', $b));
        $token = count(array_intersect($ta, $tb)) / max(1, min(count($ta), count($tb))) * 100;

        return max($persen, $token * 0.9);
    }

    private function rekomendasi(string $nama, string $jenis, int $limit = 5): array
    {
        if (!isset($this->memoNama[$jenis])) {
            $this->memoNama[$jenis] = $jenis === 'perusahaan'
                ? Perusahaan::get(['id', 'nama_perusahaan'])
                    ->map(fn ($x) => ['id' => $x->id, 'nama' => (string) $x->nama_perusahaan])->all()
                : Materi::get(['id', 'nama_materi'])
                    ->map(fn ($x) => ['id' => $x->id, 'nama' => (string) $x->nama_materi])->all();
        }

        $hasil = [];
        foreach ($this->memoNama[$jenis] as $x) {
            $skor = $this->skorMirip($nama, $x['nama']);
            if ($skor >= 40) $hasil[] = $x + ['skor' => (int) round($skor)];
        }
        usort($hasil, fn ($p, $q) => $q['skor'] <=> $p['skor']);

        return array_slice($hasil, 0, $limit);
    }

    private function saranPeluang(array $idP, array $idM, ?string $mulai, int $limit = 10): array
    {
        $tm = $mulai ? Carbon::parse($mulai)->startOfDay() : null;

        return Peluang::with('materiRelation:id,nama_materi')
            ->whereIn('id_contact', $idP)
            ->whereNull('deleted_at')
            ->where('tahap', '!=', 'lost')
            ->get()
            ->map(function ($p) use ($idM, $tm) {
                $selisih = ($tm && $p->periode_mulai)
                    ? abs((int) Carbon::parse($p->periode_mulai)->startOfDay()->diffInDays($tm))
                    : 999;
                $skor = (in_array($p->materi, $idM) ? 50 : 0) + (int) round(50 * max(0, 1 - $selisih / 90));

                return [
                    'id'    => $p->id,
                    'skor'  => $skor,
                    'label' => ($p->materiRelation->nama_materi ?? '-') . ' | '
                        . substr((string) $p->periode_mulai, 0, 10) . ' s/d ' . substr((string) $p->periode_selesai, 0, 10)
                        . ' | Sales ' . ($p->id_sales ?? '-') . ' | ' . $p->tahap,
                ];
            })
            ->sortByDesc('skor')->take($limit)->values()->all();
    }

    private function susunSaran(array $butuh, string $namaPerusahaan, string $namaMateri, array $idP, array $idM, ?string $mulai): array
    {
        return [
            'butuh'      => $butuh,
            'perusahaan' => $butuh['perusahaan'] ? $this->rekomendasi($namaPerusahaan, 'perusahaan') : [],
            'materi'     => $butuh['materi'] ? $this->rekomendasi($namaMateri, 'materi') : [],
            'peluang'    => ($butuh['peluang'] && !empty($idP) && !empty($idM)) ? $this->saranPeluang($idP, $idM, $mulai) : [],
        ];
    }
}