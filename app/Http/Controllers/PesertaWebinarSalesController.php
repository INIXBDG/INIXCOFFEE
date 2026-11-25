<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Perusahaan;
use App\Models\PesertaWebinarSales;
use App\Models\YearMapping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Exports\PesertaWebinarSalesExport;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class PesertaWebinarSalesController extends Controller
{
    private const INFO_PRESETS = [
        'WhatsApp',
        'Sales Inixindo Bandung',
        'Teman',
        'Instagram',
        'Email',
        'Webinar Inix',
        'Lainnya',
    ];

    private const PAKET_PRESETS = [
        'Paket 1x',
        'Bundling 2x',
        'Voucher',
        'Free Premium',
        'Bundling 3x',
    ];

    private function authorizeAccess(): void
    {
        $user = auth()->user();

        if (!$user || !$user->can('edit-timeline-item')) {
            abort(response()->json(['message' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengelola peserta webinar.'], 403));
        }
    }

    /**
     * Pilihan bawaan + nilai custom yang pernah diinput (unik, tanpa membedakan huruf besar/kecil).
     */
    private function optionList(string $column, array $presets): array
    {
        $options = $presets;
        $seen    = array_map('mb_strtolower', $presets);

        $custom = PesertaWebinarSales::whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->pluck($column)
            ->sort(SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($custom as $value) {
            $value = trim($value);
            $key   = mb_strtolower($value);

            if ($key === '' || in_array($key, $seen, true)) {
                continue;
            }

            $seen[]    = $key;
            $options[] = $value;
        }

        return $options;
    }

    /**
     * Rapikan input; kalau sudah ada pilihan yang sama (tanpa membedakan huruf), pakai penulisan yang ada.
     */
    private function normalizeChoice(?string $value, string $column, array $presets): ?string
    {
        $value = trim(preg_replace('/\s+/', ' ', (string) $value));

        if ($value === '') {
            return null;
        }

        foreach ($this->optionList($column, $presets) as $option) {
            if (mb_strtolower($option) === mb_strtolower($value)) {
                return $option;
            }
        }

        return $value;
    }

    /**
     * Data pendukung tab peserta: semua webinar (YearMapping), sales aktif, dan pilihan info/paket.
     */
    public function meta()
    {
        $this->authorizeAccess();

        $webinars = YearMapping::with('eventDetail')
            ->orderByDesc('year')
            ->orderBy('month')
            ->get()
            ->map(fn ($m) => [
                'id'    => $m->id,
                'year'  => (int) $m->year,
                'month' => (int) $m->month,
                'label' => Carbon::create($m->year, $m->month, 1)->translatedFormat('F Y')
                    . ' - ' . ($m->eventDetail->title ?? 'Belum ada judul'),
            ])
            ->values();

        // Webinar default: bulan berjalan, kalau tidak ada pakai yang pertama
        $default = $webinars->first(fn ($w) => $w['year'] === now()->year && $w['month'] === now()->month)
            ?? $webinars->first();

        $sales = karyawan::where('status_aktif', "1")
            ->where('jabatan', 'Sales')
            ->get()
            ->map(fn ($k) => [
                'kode' => $k->kode_karyawan,
                'nama' => $k->nama_lengkap ?? $k->kode_karyawan,
            ])
            ->sortBy('nama')
            ->values();

        return response()->json([
            'webinars'           => $webinars,
            'default_mapping_id' => $default['id'] ?? null,
            'sales'              => $sales,
            'info_options'       => $this->optionList('info', self::INFO_PRESETS),
            'paket_options'      => $this->optionList('paket', self::PAKET_PRESETS),
        ]);
    }

    /**
     * Pencarian perusahaan untuk Select2 (id = value, nama = tampilan, kategori untuk field readonly).
     */
    public function perusahaan(Request $request)
    {
        $this->authorizeAccess();

        $q = trim((string) $request->input('q', ''));

        $paginator = Perusahaan::select('id', 'nama_perusahaan', 'kategori_perusahaan')
            ->when($q !== '', fn ($query) => $query->where('nama_perusahaan', 'like', '%' . $q . '%'))
            ->orderBy('nama_perusahaan')
            ->paginate(20);

        return response()->json([
            'results' => $paginator->getCollection()
                ->map(fn ($p) => [
                    'id'       => $p->id,
                    'text'     => $p->nama_perusahaan,
                    'kategori' => $p->kategori_perusahaan,
                ])
                ->values(),
            'more' => $paginator->hasMorePages(),
        ]);
    }

    /**
     * Daftar peserta, hanya untuk webinar (YearMapping) yang dipilih.
     */
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'year_mapping_id' => 'required|exists:year_mappings,id',
        ]);

        $rows = PesertaWebinarSales::with(['perusahaan:id,nama_perusahaan,kategori_perusahaan', 'sales'])
            ->where('year_mapping_id', $request->year_mapping_id)
            ->orderBy('nama_peserta')
            ->get()
            ->map(fn ($p) => [
                'id'                  => $p->id,
                'year_mapping_id'     => $p->year_mapping_id,
                'nama_peserta'        => $p->nama_peserta,
                'email_peserta'       => $p->email_peserta,
                'no_hp'               => $p->no_hp,
                'domisili'            => $p->domisili,
                'id_perusahaan'       => $p->id_perusahaan,
                'perusahaan_nama'     => $p->perusahaan->nama_perusahaan ?? null,
                'kategori_perusahaan' => $p->perusahaan->kategori_perusahaan ?? null,
                'id_sales'            => $p->id_sales,
                'sales_nama'          => $p->sales->nama_lengkap ?? null,
                'info'                => $p->info,
                'paket'               => $p->paket,
            ]);

        return response()->json($rows);
    }

    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'year_mapping_id' => 'required|exists:year_mappings,id',
            'email_peserta'   => [
                'required', 'email', 'max:255',
                Rule::unique('peserta_webinar_sales', 'email_peserta')
                    ->where('year_mapping_id', $request->year_mapping_id),
            ],
            'nama_peserta'    => 'required|string|max:255',
            'no_hp'           => 'nullable|string|max:50',
            'domisili'        => 'nullable|string|max:255',
            'id_perusahaan'   => [
                'nullable',
                Rule::exists((new Perusahaan)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'id_sales'        => [
                'nullable', 'string', 'max:255',
                Rule::exists((new karyawan)->getTable(), 'kode_karyawan'),
            ],
            'info'            => 'nullable|string|max:100',
            'paket'           => 'nullable|string|max:100',
        ], [
            'email_peserta.unique' => 'Email ini sudah terdaftar di webinar tersebut.',
        ]);

        $peserta = PesertaWebinarSales::create([
            'year_mapping_id' => $validated['year_mapping_id'],
            'email_peserta'   => strtolower(trim($validated['email_peserta'])),
            'nama_peserta'    => trim($validated['nama_peserta']),
            'no_hp'           => $validated['no_hp'] ?? null,
            'domisili'        => isset($validated['domisili']) ? trim($validated['domisili']) : null,
            'id_perusahaan'   => $validated['id_perusahaan'] ?? null,
            'id_sales'        => $validated['id_sales'] ?? null,
            'info'            => $this->normalizeChoice($validated['info'] ?? null, 'info', self::INFO_PRESETS),
            'paket'           => $this->normalizeChoice($validated['paket'] ?? null, 'paket', self::PAKET_PRESETS),
        ]);

        return response()->json(['status' => 'saved', 'data' => $peserta]);
    }

    public function update(Request $request, $id)
    {
        $this->authorizeAccess();

        $peserta = PesertaWebinarSales::findOrFail($id);

        $validated = $request->validate([
            'email_peserta' => [
                'required', 'email', 'max:255',
                Rule::unique('peserta_webinar_sales', 'email_peserta')
                    ->where('year_mapping_id', $peserta->year_mapping_id)
                    ->ignore($peserta->id),
            ],
            'nama_peserta'  => 'required|string|max:255',
            'no_hp'         => 'nullable|string|max:50',
            'domisili'      => 'nullable|string|max:255',
            'id_perusahaan' => [
                'nullable',
                Rule::exists((new Perusahaan)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'id_sales'      => [
                'nullable', 'string', 'max:255',
                Rule::exists((new karyawan)->getTable(), 'kode_karyawan'),
            ],
            'info'          => 'nullable|string|max:100',
            'paket'         => 'nullable|string|max:100',
        ], [
            'email_peserta.unique' => 'Email ini sudah terdaftar di webinar tersebut.',
        ]);

        $peserta->update([
            'email_peserta' => strtolower(trim($validated['email_peserta'])),
            'nama_peserta'  => trim($validated['nama_peserta']),
            'no_hp'         => $validated['no_hp'] ?? null,
            'domisili'      => isset($validated['domisili']) ? trim($validated['domisili']) : null,
            'id_perusahaan' => $validated['id_perusahaan'] ?? null,
            'id_sales'      => $validated['id_sales'] ?? null,
            'info'          => $this->normalizeChoice($validated['info'] ?? null, 'info', self::INFO_PRESETS),
            'paket'         => $this->normalizeChoice($validated['paket'] ?? null, 'paket', self::PAKET_PRESETS),
        ]);

        return response()->json(['status' => 'updated', 'data' => $peserta]);
    }

    public function destroy($id)
    {
        $this->authorizeAccess();

        PesertaWebinarSales::findOrFail($id)->delete();

        return response()->json(['status' => 'deleted']);
    }

    public function export(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'year_mapping_id' => 'required|exists:year_mappings,id',
        ]);

        $mapping = YearMapping::with('eventDetail')->findOrFail($request->year_mapping_id);

        $periode = Carbon::create($mapping->year, $mapping->month, 1)->translatedFormat('F Y');
        $judul   = $mapping->eventDetail->title ?? '';

        $filename = 'peserta-webinar-'
            . Str::slug(Str::limit($periode . ' ' . $judul, 60, ''))
            . '-' . now()->format('Ymd_His') . '.xlsx';

        return Excel::download(new PesertaWebinarSalesExport($mapping->id), $filename);
    }
}