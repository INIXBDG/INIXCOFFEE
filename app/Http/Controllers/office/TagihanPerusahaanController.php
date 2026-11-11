<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\karyawan;
use App\Models\tagihanPerusahaan;
use App\Models\trackingTagihanPerusahaan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class TagihanPerusahaanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Tagihan Perusahaan', ['only' => ['index', 'detailTagihanPerusahaan', 'dataTagihan', 'detailJson']]);
        $this->middleware('permission:Store Tagihan Perusahaan', ['only' => ['storeTagihanPerusahaan', 'importTagihanPerusahaan', 'storeManualBanyak']]);
        $this->middleware('permission:Update Tagihan Perusahaan', ['only' => ['updateTagihanPerusahaan']]);
        $this->middleware('permission:Delete Tagihan Perusahaan', ['only' => ['hapusTagihanPerusahaan', 'hapusSemuaTagihanPerusahaan']]);
    }

    public function index(Request $request)
    {
        $tahun = $request->get('tahun', now()->year);
        $bulan = $request->get('bulan');

        $query = trackingTagihanPerusahaan::with('tagihanPerusahaan')->whereYear('tanggal_perkiraan_mulai', $tahun);

        if ($bulan) {
            $query->whereMonth('tanggal_perkiraan_mulai', $bulan);
        }

        $trackingTagihanPerusahaans = $query->orderByDesc('is_urgent')->orderBy('urgent_order')->orderBy('tanggal_perkiraan_mulai', 'desc')->get();

        $listTahun = range(now()->year - 3, now()->year + 1);

        return view('office.tagihanPerusahaan.index', compact('trackingTagihanPerusahaans', 'tahun', 'bulan', 'listTahun'));
    }

    public function toggleUrgent($id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);

        if (!$tracking->is_urgent) {
            $minOrder = trackingTagihanPerusahaan::where('is_urgent', true)->min('urgent_order');

            $tracking->is_urgent = true;
            $tracking->urgent_order = is_null($minOrder) ? 1 : $minOrder - 1;
        } else {
            $tracking->is_urgent = false;
            $tracking->urgent_order = null;
        }

        $tracking->save();
        Cache::forget('office_dashboard_tagihan');

        return response()->json([
            'success' => true,
            'is_urgent' => (bool) $tracking->is_urgent,
        ]);
    }

    public function reorderUrgent(Request $request)
    {
        $orders = $request->orders;

        foreach ($orders as $id => $order) {
            trackingTagihanPerusahaan::where('id', $id)
                ->where('is_urgent', true)
                ->update(['urgent_order' => $order]);
        }

        Cache::forget('office_dashboard_tagihan');

        return response()->json(['success' => true]);
    }

    public function updateInline(Request $request, $id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);
        $tagihan = tagihanPerusahaan::find($tracking->id_tagihan_perusahaan);

        $field = $request->field;

        if ($field === 'due_date') {
            $mulai = $request->tanggal_perkiraan_mulai;
            $selesai = $request->tanggal_perkiraan_selesai ?: $mulai;

            if (!$mulai) {
                return response()->json(['success' => false, 'message' => 'Tanggal mulai wajib'], 422);
            }

            $tracking->tanggal_perkiraan_mulai = $mulai;
            $tracking->tanggal_perkiraan_selesai = $selesai;
            $tracking->save();

            if ($tagihan) {
                $tagihan->tanggal_perkiraan_mulai = $mulai;
                $tagihan->tanggal_perkiraan_selesai = $selesai;
                $tagihan->save();
            }

            if ($tracking->tanggal_selesai && $tracking->status !== 'on_time') {
                $due = Carbon::parse($selesai);
                $realisasi = Carbon::parse($tracking->tanggal_selesai);
                if ($realisasi->gt($due) && $tracking->status !== 'selesai') {
                    $tracking->status = 'telat';
                    $tracking->save();
                } elseif ($realisasi->lte($due)) {
                    $tracking->status = 'selesai';
                    $tracking->save();
                }
            }

            Cache::forget('office_dashboard_tagihan');

            return response()->json(['success' => true]);
        }

        $value = $request->value;

        $allowed = ['kegiatan', 'tipe', 'nominal', 'tracking', 'status', 'keterangan', 'tanggal_selesai'];

        if (!in_array($field, $allowed)) {
            return response()->json(['success' => false, 'message' => 'Field tidak diizinkan'], 422);
        }

        $tracking->$field = $value === '' || $value === null ? null : $value;

        if (in_array($field, ['kegiatan', 'tipe', 'nominal']) && $tagihan) {
            $tagihan->$field = $tracking->$field;
            $tagihan->save();
        }

        if (in_array($field, ['tanggal_selesai', 'status']) && $tracking->status !== 'on_time') {
            $due = $tracking->tanggal_perkiraan_selesai ? Carbon::parse($tracking->tanggal_perkiraan_selesai) : Carbon::parse($tracking->tanggal_perkiraan_mulai);

            if ($tracking->tanggal_selesai) {
                $selesai = Carbon::parse($tracking->tanggal_selesai);
                if ($selesai->gt($due) && $tracking->status !== 'selesai') {
                    $tracking->status = 'telat';
                } elseif ($selesai->lte($due)) {
                    $tracking->status = 'selesai';
                }
            }
        }

        $tracking->save();
        Cache::forget('office_dashboard_tagihan');

        return response()->json([
            'success' => true,
            'status' => $tracking->status,
            'value' => $tracking->$field,
        ]);
    }

    public function storeTagihanPerusahaan(Request $request)
    {
        $request->validate([
            'kegiatan' => 'required|string',
            'nominal' => 'nullable|numeric|min:0',
            'tipe' => 'required|in:bulanan,tahunan',
            'tanggal_perkiraan_mulai' => 'required|date',
            'tanggal_perkiraan_selesai' => 'nullable|date|after_or_equal:tanggal_perkiraan_mulai',
            'keterangan' => 'nullable|string',
        ]);

        $tagihan = tagihanPerusahaan::create([
            'kegiatan' => $request->kegiatan,
            'tipe' => $request->tipe,
            'nominal' => $request->nominal ?? null,
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $request->tanggal_perkiraan_mulai,
            'last_generate' => now(),
        ]);

        trackingTagihanPerusahaan::create([
            'kegiatan' => $request->kegiatan,
            'tipe' => $request->tipe,
            'id_tagihan_perusahaan' => $tagihan->id,
            'nominal' => $request->nominal ?? null,
            'keterangan' => $request->keterangan ?? null,
            'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $request->tanggal_perkiraan_mulai,
        ]);

        Cache::forget('office_dashboard_tagihan');

        return back()->with('success_tagihan', 'Tagihan perusahaan berhasil dibuat.');
    }

    public function storeManualBanyak(Request $request)
    {
        $request->validate(
            [
                'rows' => 'required|array|min:1|max:100',
                'rows.*.kegiatan' => 'required|string|max:255',
                'rows.*.tipe' => 'required|in:bulanan,tahunan',
                'rows.*.nominal' => 'nullable|string|max:100',
                'rows.*.tanggal_perkiraan_mulai' => 'required|date',
                'rows.*.tanggal_perkiraan_selesai' => 'nullable|date',
                'rows.*.keterangan' => 'nullable|string',
                'rows.*.status' => 'nullable|in:pending,proses,selesai,telat,on_time',
            ],
            [
                'rows.required' => 'Minimal isi satu baris.',
                'rows.*.kegiatan.required' => 'Kolom Kegiatan wajib diisi di semua baris.',
                'rows.*.tanggal_perkiraan_mulai.required' => 'Kolom Due Date wajib diisi di semua baris.',
                'rows.*.tanggal_perkiraan_mulai.date' => 'Format Due Date tidak valid.',
            ],
        );

        foreach ($request->rows as $i => $r) {
            if (!empty($r['tanggal_perkiraan_selesai']) && $r['tanggal_perkiraan_selesai'] < $r['tanggal_perkiraan_mulai']) {
                return response()->json(['success' => false, 'message' => 'Baris ke-' . ($i + 1) . ': tanggal selesai tidak boleh sebelum tanggal mulai.'], 422);
            }
        }

        DB::beginTransaction();

        try {
            foreach ($request->rows as $r) {
                $mulai = $r['tanggal_perkiraan_mulai'];
                $selesai = $r['tanggal_perkiraan_selesai'] ?: $mulai;

                [$nominal, $teksNominal] = $this->parseNominal($r['nominal'] ?? null);

                $keterangan = trim($r['keterangan'] ?? '');
                if ($teksNominal !== null) {
                    $keterangan = trim($keterangan . ' (Nominal: ' . $teksNominal . ')');
                }

                $tagihan = tagihanPerusahaan::create([
                    'kegiatan' => $r['kegiatan'],
                    'tipe' => $r['tipe'],
                    'nominal' => $nominal,
                    'tanggal_perkiraan_mulai' => $mulai,
                    'tanggal_perkiraan_selesai' => $selesai,
                    'last_generate' => now(),
                ]);

                trackingTagihanPerusahaan::create([
                    'kegiatan' => $r['kegiatan'],
                    'tipe' => $r['tipe'],
                    'id_tagihan_perusahaan' => $tagihan->id,
                    'nominal' => $nominal,
                    'keterangan' => $keterangan !== '' ? $keterangan : null,
                    'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
                    'status' => $r['status'] ?? 'pending',
                    'tanggal_perkiraan_mulai' => $mulai,
                    'tanggal_perkiraan_selesai' => $selesai,
                ]);
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menyimpan: ' . $e->getMessage()], 500);
        }

        Cache::forget('office_dashboard_tagihan');

        return response()->json([
            'success' => true,
            'message' => count($request->rows) . ' tagihan berhasil ditambahkan.',
        ]);
    }

    public function importTagihanPerusahaan(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            'tipe' => 'nullable|in:bulanan,tahunan',
        ]);

        $tipeDefault = $request->tipe ?: 'bulanan';

        try {
            $path = $request->file('file')->getRealPath();
            $reader = IOFactory::createReaderForFile($path);
            $spreadsheet = $reader->load($path);
            $rows = $this->bacaBaris($spreadsheet->getActiveSheet());
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'File tidak dapat dibaca: ' . $e->getMessage()], 422);
        }

        $headerIndex = null;
        $map = [];

        foreach ($rows as $i => $row) {
            if ($i > 20) {
                break;
            }
            $found = [];
            foreach ($row as $col => $cell) {
                $name = strtolower(trim((string) $cell));
                if (in_array($name, ['no', 'no.', 'tagihan', 'due date', 'realisasi', 'keterangan', 'status', 'nominal'])) {
                    $found[rtrim($name, '.')] = $col;
                }
            }
            if (isset($found['tagihan']) && isset($found['due date'])) {
                $headerIndex = $i;
                $map = $found;
                break;
            }
        }

        if ($headerIndex === null) {
            return response()->json(['success' => false, 'message' => 'Header kolom (Tagihan, Due Date, Realisasi, Keterangan, status) tidak ditemukan.'], 422);
        }

        $berhasil = 0;
        $dilewati = 0;
        $detailDilewati = [];

        DB::beginTransaction();

        try {
            foreach ($rows as $i => $row) {
                if ($i <= $headerIndex) {
                    continue;
                }

                $kegiatanAsli = trim((string) ($row[$map['tagihan']] ?? ''));

                if ($kegiatanAsli === '') {
                    $dilewati++;
                    $detailDilewati[] = 'Baris ' . ($i + 1) . ': kolom Tagihan kosong';
                    continue;
                }

                $kegiatan = $kegiatanAsli;
                $catatan = [];

                if (preg_match_all('/\(([^)]*)\)/u', $kegiatanAsli, $cocok)) {
                    foreach ($cocok[1] as $isi) {
                        if (trim($isi) !== '') {
                            $catatan[] = trim($isi);
                        }
                    }
                    $bersih = trim(preg_replace('/\s*\([^)]*\)\s*/u', ' ', $kegiatanAsli));
                    if ($bersih !== '') {
                        $kegiatan = $bersih;
                    }
                }

                $dueRaw = $row[$map['due date']] ?? null;
                [$mulai, $selesai] = $this->parseRentangTanggal($dueRaw);

                if (!$mulai) {
                    $teksDue = is_scalar($dueRaw) ? trim((string) $dueRaw) : '';
                    if ($teksDue !== '' && $teksDue !== '-') {
                        $catatan[] = 'Due Date: ' . $teksDue;
                    }
                    $mulai = now()->format('Y-m-d');
                    $selesai = $mulai;
                }

                $realisasiRaw = isset($map['realisasi']) ? $row[$map['realisasi']] ?? null : null;
                $tanggalSelesai = $this->parseTanggal($realisasiRaw);

                if (!$tanggalSelesai && $realisasiRaw !== null && trim((string) $realisasiRaw) !== '' && trim((string) $realisasiRaw) !== '-') {
                    $catatan[] = 'Realisasi: ' . trim((string) $realisasiRaw);
                }

                $keteranganExcel = isset($map['keterangan']) ? trim((string) ($row[$map['keterangan']] ?? '')) : '';
                $nominalRaw = isset($map['nominal']) ? ($row[$map['nominal']] ?? null) : null;
                [$nominal, $teksNominal] = $this->parseNominal($nominalRaw);

                if ($teksNominal !== null) {
                    $catatan[] = 'Nominal: ' . $teksNominal;
                }

                $bagian = [];
                if ($keteranganExcel !== '') {
                    $bagian[] = $keteranganExcel;
                }
                foreach ($catatan as $c) {
                    $bagian[] = '(' . $c . ')';
                }
                $keterangan = implode(' ', $bagian);

                $statusRaw = isset($map['status']) ? strtolower(trim((string) ($row[$map['status']] ?? ''))) : '';
                $status = $this->normalisasiStatus($statusRaw, $tanggalSelesai, $selesai);

                $tagihan = tagihanPerusahaan::create([
                    'kegiatan' => $kegiatan,
                    'tipe' => $tipeDefault,
                    'nominal' => $nominal,
                    'tanggal_perkiraan_mulai' => $mulai,
                    'tanggal_perkiraan_selesai' => $selesai,
                    'last_generate' => now(),
                ]);

                trackingTagihanPerusahaan::create([
                    'kegiatan' => $kegiatan,
                    'tipe' => $tipeDefault,
                    'id_tagihan_perusahaan' => $tagihan->id,
                    'nominal' => $nominal,
                    'keterangan' => $keterangan !== '' ? $keterangan : null,
                    'tracking' => 'Diajukan dan Sedang Ditinjau oleh Finance',
                    'status' => $status,
                    'tanggal_selesai' => $tanggalSelesai,
                    'tanggal_perkiraan_mulai' => $mulai,
                    'tanggal_perkiraan_selesai' => $selesai,
                ]);

                $berhasil++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Import gagal: ' . $e->getMessage()], 500);
        }

        Cache::forget('office_dashboard_tagihan');

        $pesan = "Import selesai. Berhasil: {$berhasil} data, dilewati: {$dilewati} baris.";

        if (!empty($detailDilewati)) {
            $pesan .= "\n\nBaris yang dilewati:\n" . implode("\n", array_slice($detailDilewati, 0, 20));
        }

        return response()->json([
            'success' => true,
            'message' => $pesan,
        ]);
    }

    private function parseNominal($value)
    {
        if ($value === null) {
            return [null, null];
        }

        if (is_numeric($value)) {
            return [(float) $value, null];
        }

        $teks = trim((string) $value);

        if ($teks === '' || $teks === '-') {
            return [null, null];
        }

        if (!preg_match('/^(rp\.?)?\s*[\d.,\s]+$/i', $teks)) {
            return [null, $teks];
        }

        $angka = preg_replace('/[^0-9.,]/', '', $teks);

        if (str_contains($angka, ',') && str_contains($angka, '.')) {
            $angka = str_replace('.', '', $angka);
            $angka = str_replace(',', '.', $angka);
        } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $angka)) {
            $angka = str_replace('.', '', $angka);
        } elseif (str_contains($angka, ',')) {
            $angka = str_replace(',', '.', $angka);
        }

        return is_numeric($angka) ? [(float) $angka, null] : [null, $teks];
    }

    private function bacaBaris($sheet)
    {
        $rows = [];
        $maxRow = $sheet->getHighestDataRow();
        $maxCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        for ($r = 1; $r <= $maxRow; $r++) {
            $row = [];

            for ($c = 1; $c <= $maxCol; $c++) {
                $cell = $sheet->getCell(Coordinate::stringFromColumnIndex($c) . $r);

                if ($cell->isFormula()) {
                    $value = $cell->getOldCalculatedValue();
                } else {
                    $value = $cell->getValue();
                }

                if ($value instanceof RichText) {
                    $value = $value->getPlainText();
                }

                $row[] = $value;
            }

            $rows[] = $row;
        }

        return $rows;
    }

    private function parseTanggal($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            if ((float) $value < 20000 || (float) $value > 80000) {
                return null;
            }
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->format('Y-m-d');
            } catch (\Throwable $e) {
                return null;
            }
        }

        $text = strtolower(trim((string) $value));

        $bulan = [
            'januari' => 'january',
            'februari' => 'february',
            'maret' => 'march',
            'april' => 'april',
            'mei' => 'may',
            'juni' => 'june',
            'juli' => 'july',
            'agustus' => 'august',
            'september' => 'september',
            'oktober' => 'october',
            'november' => 'november',
            'desember' => 'december',
            'agu' => 'aug',
            'okt' => 'oct',
            'des' => 'dec',
        ];

        $text = str_replace(array_keys($bulan), array_values($bulan), $text);

        try {
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function parseRentangTanggal($value)
    {
        $single = $this->parseTanggal($value);

        if ($single) {
            return [$single, $single];
        }

        if (!is_string($value)) {
            return [null, null];
        }

        $parts = preg_split('/\s*(?:–|—|-|s\/d|sd|sampai)\s*/iu', trim($value));

        if (count($parts) === 2) {
            $akhir = $this->parseTanggal($parts[1]);

            if ($akhir) {
                $awalText = trim($parts[0]);
                $awal = $this->parseTanggal($awalText);

                if (!$awal && preg_match('/^\d{1,2}$/', $awalText)) {
                    $awal = Carbon::parse($akhir)->day((int) $awalText)->format('Y-m-d');
                }

                if (!$awal && preg_match('/^\d{1,2}\s+[a-zA-Z]+$/', $awalText)) {
                    $awal = $this->parseTanggal($awalText . ' ' . Carbon::parse($akhir)->year);
                }

                if ($awal) {
                    return [$awal, $akhir];
                }
            }
        }

        return [null, null];
    }

    private function normalisasiStatus($statusRaw, $tanggalSelesai, $selesai)
    {
        if ($statusRaw !== '') {
            if (preg_match('/on[\s_-]?time|tepat\s*waktu/', $statusRaw)) {
                return 'on_time';
            }
            if (preg_match('/telat|terlambat|late|overdue/', $statusRaw)) {
                return 'telat';
            }
            if (preg_match('/selesai|done|lunas|sudah|complete|paid|ok/', $statusRaw)) {
                return 'selesai';
            }
            if (preg_match('/proses|progress|berjalan/', $statusRaw)) {
                return 'proses';
            }
            if (preg_match('/pending|belum|menunggu/', $statusRaw)) {
                return 'pending';
            }
        }

        if ($tanggalSelesai) {
            return Carbon::parse($tanggalSelesai)->gt(Carbon::parse($selesai)) ? 'telat' : 'selesai';
        }

        return 'pending';
    }

    public function detailTagihanPerusahaan($id)
    {
        $tagihan = trackingTagihanPerusahaan::with('tagihanPerusahaan')->findOrFail($id);

        return view('office.tagihanPerusahaan.detail', compact('tagihan'));
    }

    public function hapusTagihanPerusahaan($id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);

        tagihanPerusahaan::where('id', $tracking->id_tagihan_perusahaan)->delete();
        $tracking->delete();

        Cache::forget('office_dashboard_tagihan');

        return response()->json(['success' => true]);
    }

    public function hapusSemuaTagihanPerusahaan(Request $request)
    {
        $request->validate(
            [
                'konfirmasi' => 'required|in:HAPUS SEMUA',
            ],
            [
                'konfirmasi.in' => 'Teks konfirmasi tidak sesuai.',
            ],
        );

        DB::beginTransaction();

        try {
            $total = trackingTagihanPerusahaan::count();
            trackingTagihanPerusahaan::query()->delete();
            tagihanPerusahaan::query()->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Gagal menghapus data: ' . $e->getMessage()], 500);
        }

        Cache::forget('office_dashboard_tagihan');

        return response()->json([
            'success' => true,
            'message' => "{$total} data tagihan berhasil dihapus.",
        ]);
    }

    public function updateTagihanPerusahaan(Request $request, $id)
    {
        $tracking = trackingTagihanPerusahaan::findOrFail($id);
        $tagihan = tagihanPerusahaan::where('id', $tracking->id_tagihan_perusahaan)->first();

        $tagihan->update([
            'kegiatan' => $request->kegiatan ?? $tagihan->kegiatan,
            'tipe' => $request->tipe ?? $tagihan->tipe,
            'nominal' => $request->nominal ?? $tagihan->nominal,
            'tanggal_perkiraan_mulai' => $request->tanggal_perkiraan_mulai ?? $tagihan->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $request->tanggal_perkiraan_selesai ?? $tagihan->tanggal_perkiraan_selesai,
        ]);

        $tracking->update([
            'nominal' => $request->nominal ?? $tracking->nominal,
            'status' => $request->status ?? $tracking->status,
            'keterangan' => $request->keterangan ?? $tracking->keterangan,
            'tracking' => $request->tracking ?? $tracking->tracking,
            'tanggal_selesai' => $request->tanggal_selesai ?? $tracking->tanggal_selesai,
            'tanggal_perkiraan_mulai' => $tagihan->tanggal_perkiraan_mulai,
            'tanggal_perkiraan_selesai' => $tagihan->tanggal_perkiraan_selesai ?? $tagihan->tanggal_perkiraan_selesai,
            'kegiatan' => $request->kegiatan ?? $tagihan->kegiatan,
            'tipe' => $request->tipe ?? $tagihan->tipe,
        ]);

        $dueDate = null;

        if ($tracking->tanggal_perkiraan_selesai) {
            $dueDate = Carbon::parse($tracking->tanggal_perkiraan_selesai);
        } else {
            $dueDate = Carbon::parse($tracking->tanggal_perkiraan_mulai);
        }

        if ($tracking->status !== 'on_time') {
            if ($request->tanggal_selesai !== null && $dueDate < $request->tanggal_selesai && !in_array($request->status, ['selesai', 'pending'])) {
                $tracking->status = 'telat';
                $tracking->save();
            } elseif ($request->tanggal_selesai !== null && $dueDate >= $request->tanggal_selesai && $tracking->status) {
                $tracking->status = 'selesai';
                $tracking->save();
            }
        }

        Cache::forget('office_dashboard_tagihan');

        return back()->with('success_tagihan', 'Tagihan berhasil diperbaharui.');
    }

    public function detailJson($id)
    {
        $t = trackingTagihanPerusahaan::with('tagihanPerusahaan')->findOrFail($id);

        $mulai = $t->tanggal_perkiraan_mulai ? Carbon::parse($t->tanggal_perkiraan_mulai) : null;
        $due = $t->tanggal_perkiraan_selesai ? Carbon::parse($t->tanggal_perkiraan_selesai) : $mulai;
        $realisasi = $t->tanggal_selesai ? Carbon::parse($t->tanggal_selesai) : null;

        $selisihText = '—';
        $selisihTone = 'secondary';

        if ($due && $realisasi) {
            $diff = (int) $due->copy()->startOfDay()->diffInDays($realisasi->copy()->startOfDay(), false);

            if ($diff > 0) {
                $selisihText = "Terlambat {$diff} hari dari due date";
                $selisihTone = 'danger';
            } elseif ($diff === 0) {
                $selisihText = 'Selesai tepat pada due date';
                $selisihTone = 'success';
            } else {
                $selisihText = abs($diff) . ' hari lebih awal dari due date';
                $selisihTone = 'success';
            }
        } elseif ($due) {
            $sisa = (int) now()->startOfDay()->diffInDays($due->copy()->startOfDay(), false);

            if ($sisa > 0) {
                $selisihText = "{$sisa} hari lagi menuju due date";
                $selisihTone = 'primary';
            } elseif ($sisa === 0) {
                $selisihText = 'Jatuh tempo hari ini';
                $selisihTone = 'warning';
            } else {
                $selisihText = 'Lewat ' . abs($sisa) . ' hari dari due date';
                $selisihTone = 'danger';
            }
        }

        $nominal = $t->nominal;
        $nominalFormat = ($nominal !== null && $nominal !== '' && is_numeric($nominal))
            ? 'Rp ' . number_format($nominal, 0, ',', '.')
            : ($nominal ?: '—');

        if ($mulai && $due && $mulai->isSameDay($due)) {
            $dueText = $mulai->translatedFormat('d F Y');
        } elseif ($mulai && $due) {
            $dueText = $mulai->translatedFormat('d F Y') . ' – ' . $due->translatedFormat('d F Y');
        } else {
            $dueText = '—';
        }

        return response()->json([
            'id' => $t->id,
            'kegiatan' => $t->tagihanPerusahaan?->kegiatan ?? $t->kegiatan,
            'tipe' => $t->tagihanPerusahaan?->tipe ?? $t->tipe,
            'nominal' => $nominalFormat,
            'status' => $t->status,
            'tracking' => $t->tracking,
            'keterangan' => $t->keterangan,
            'due_date' => $dueText,
            'tanggal_selesai' => $realisasi ? $realisasi->translatedFormat('d F Y') : null,
            'selisih_text' => $selisihText,
            'selisih_tone' => $selisihTone,
            'is_urgent' => (bool) $t->is_urgent,
            'dibuat' => $t->created_at ? $t->created_at->translatedFormat('d F Y H:i') : '—',
            'diperbarui' => $t->updated_at ? $t->updated_at->translatedFormat('d F Y H:i') : '—',
        ]);
    }

    public function dataTagihan($id)
    {
        $tagihan = trackingTagihanPerusahaan::with('tagihanPerusahaan')->findOrFail($id);

        $data = [
            'id_tagihan' => $tagihan->id,
            'data' => [
                'kegiatan' => $tagihan->tagihanPerusahaan->kegiatan,
                'tipe' => $tagihan->tagihanPerusahaan->tipe,
                'nominal' => $tagihan->nominal,
                'status' => $tagihan->status,
                'keterangan' => $tagihan->keterangan,
                'tracking' => $tagihan->tracking,
                'tanggal_selesai' => $tagihan->tanggal_selesai,
                'tanggal_perkiraan_mulai' => $tagihan->tanggal_perkiraan_mulai,
                'tanggal_perkiraan_selesai' => $tagihan->tanggal_perkiraan_selesai,
            ],
        ];

        return response()->json($data);
    }
}