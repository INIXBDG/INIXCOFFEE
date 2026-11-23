<?php

namespace App\Http\Controllers;

use App\Models\karyawan;
use App\Models\Perusahaan;
use App\Models\PesertaWebinarSales;
use App\Models\YearMapping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PesertaWebinarSalesController extends Controller
{
    private function authorizeAccess(): void
    {
        $user = auth()->user();

        if (!$user || !$user->can('edit-timeline-item')) {
            abort(response()->json(['message' => 'Akses Ditolak: Anda tidak memiliki izin untuk mengelola peserta webinar.'], 403));
        }
    }

    /**
     * Data pendukung tab peserta: semua webinar (YearMapping) dan daftar sales aktif.
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
        ]);
    }

    /**
     * Pencarian perusahaan untuk Select2 (id = value, nama = tampilan).
     */
    public function perusahaan(Request $request)
    {
        $this->authorizeAccess();

        $q = trim((string) $request->input('q', ''));

        $paginator = Perusahaan::select('id', 'nama_perusahaan')
            ->when($q !== '', fn ($query) => $query->where('nama_perusahaan', 'like', '%' . $q . '%'))
            ->orderBy('nama_perusahaan')
            ->paginate(20);

        return response()->json([
            'results' => $paginator->getCollection()
                ->map(fn ($p) => ['id' => $p->id, 'text' => $p->nama_perusahaan])
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

        $rows = PesertaWebinarSales::with(['perusahaan:id,nama_perusahaan', 'sales'])
            ->where('year_mapping_id', $request->year_mapping_id)
            ->orderBy('nama_peserta')
            ->get()
            ->map(fn ($p) => [
                'id'              => $p->id,
                'year_mapping_id' => $p->year_mapping_id,
                'nama_peserta'    => $p->nama_peserta,
                'email_peserta'   => $p->email_peserta,
                'no_hp'           => $p->no_hp,
                'id_perusahaan'   => $p->id_perusahaan,
                'perusahaan_nama' => $p->perusahaan->nama_perusahaan ?? null,
                'id_sales'        => $p->id_sales,
                'sales_nama'      => $p->sales->nama_lengkap ?? null,
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
            'id_perusahaan'   => [
                'nullable',
                Rule::exists((new Perusahaan)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'id_sales'        => [
                'nullable', 'string', 'max:255',
                Rule::exists((new karyawan)->getTable(), 'kode_karyawan'),
            ],
        ], [
            'email_peserta.unique' => 'Email ini sudah terdaftar di webinar tersebut.',
        ]);

        $peserta = PesertaWebinarSales::create([
            'year_mapping_id' => $validated['year_mapping_id'],
            'email_peserta'   => strtolower(trim($validated['email_peserta'])),
            'nama_peserta'    => trim($validated['nama_peserta']),
            'no_hp'           => $validated['no_hp'] ?? null,
            'id_perusahaan'   => $validated['id_perusahaan'] ?? null,
            'id_sales'        => $validated['id_sales'] ?? null,
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
            'id_perusahaan' => [
                'nullable',
                Rule::exists((new Perusahaan)->getTable(), 'id')->whereNull('deleted_at'),
            ],
            'id_sales'      => [
                'nullable', 'string', 'max:255',
                Rule::exists((new karyawan)->getTable(), 'kode_karyawan'),
            ],
        ], [
            'email_peserta.unique' => 'Email ini sudah terdaftar di webinar tersebut.',
        ]);

        $peserta->update([
            'email_peserta' => strtolower(trim($validated['email_peserta'])),
            'nama_peserta'  => trim($validated['nama_peserta']),
            'no_hp'         => $validated['no_hp'] ?? null,
            'id_perusahaan' => $validated['id_perusahaan'] ?? null,
            'id_sales'      => $validated['id_sales'] ?? null,
        ]);

        return response()->json(['status' => 'updated', 'data' => $peserta]);
    }

    public function destroy($id)
    {
        $this->authorizeAccess();

        PesertaWebinarSales::findOrFail($id)->delete();

        return response()->json(['status' => 'deleted']);
    }
}