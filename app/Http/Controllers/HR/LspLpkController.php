<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\AsesorRiwayat;
use App\Models\Lsp;
use App\Models\LspAsesor;
use App\Models\LspLpkProgram;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LspLpkController extends Controller
{
    private const PER_PAGE = 15;

    public function index()
    {
        return view('HR.LspLpk.index');
    }

    /* ======================= HELPERS ======================= */

    private function ok(array $payload = []): JsonResponse
    {
        return response()->json(['success' => true] + $payload);
    }

    private function meta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page'    => $paginator->lastPage(),
            'total'        => $paginator->total(),
            'per_page'     => $paginator->perPage(),
        ];
    }

    /* ======================= OPTIONS ======================= */

    public function options(): JsonResponse
    {
        return $this->ok([
            'lsps' => Lsp::orderBy('nama')->get(['id', 'nama', 'jenis'])
                ->map(fn ($l) => ['id' => $l->id, 'nama' => $l->nama, 'jenis' => $l->jenis])
                ->values(),
            'asesors' => LspAsesor::orderBy('nama_lengkap')->get(['id', 'nama_lengkap'])
                ->map(fn ($a) => ['id' => $a->id, 'nama_lengkap' => $a->nama_lengkap])
                ->values(),
        ]);
    }

    /* ======================= LSP / LPK ======================= */

    public function lspList(Request $request): JsonResponse
    {
        $data = Lsp::query()
            ->withCount(['sertifikasis', 'pelatihans', 'asesors'])
            ->when($request->q, fn ($q, $s) => $q->where('nama', 'like', "%$s%"))
            ->when($request->jenis, fn ($q, $v) => $q->where('jenis', $v))
            ->when($request->status, fn ($q, $v) => $q->where('status', $v))
            ->latest()
            ->paginate(self::PER_PAGE);

        return $this->ok([
            'data'  => $data->items(),
            'meta'  => $this->meta($data),
            'stats' => [
                'total'   => Lsp::count(),
                'aktif'   => Lsp::where('status', 'Aktif')->count(),
                'segera'  => Lsp::where('status', 'Aktif')
                    ->whereBetween('tanggal_berakhir', [today(), today()->addDays(90)])
                    ->count(),
                'program' => LspLpkProgram::count(),
            ],
        ]);
    }

    public function lspShow(Lsp $lsp): JsonResponse
    {
        $attachedIds = $lsp->asesors()->pluck('lsp_asesors.id');

        return $this->ok([
            'lsp'          => $lsp,
            'sertifikasis' => $lsp->sertifikasis()->orderBy('nama_skema')->get(),
            'pelatihans'   => $lsp->pelatihans()->orderBy('nama_skema')->get(),
            'asesors'      => $lsp->asesors()->orderBy('nama_lengkap')->get()
                ->map(fn ($a) => [
                    'id'           => $a->id,
                    'nama_lengkap' => $a->nama_lengkap,
                    'no_telp'      => $a->no_telp,
                    'email'        => $a->email,
                ])
                ->values(),
            'available_asesors' => LspAsesor::whereNotIn('id', $attachedIds)
                ->orderBy('nama_lengkap')
                ->get()
                ->map(fn ($a) => ['id' => $a->id, 'nama_lengkap' => $a->nama_lengkap])
                ->values(),
        ]);
    }

    public function lspStore(Request $request): JsonResponse
    {
        $lsp = Lsp::create($request->validate($this->lspRules()));

        return $this->ok(['message' => 'Data LSP/LPK ditambahkan.', 'id' => $lsp->id]);
    }

    public function lspUpdate(Request $request, Lsp $lsp): JsonResponse
    {
        $lsp->update($request->validate($this->lspRules()));

        return $this->ok(['message' => 'Data LSP/LPK diperbarui.']);
    }

    public function lspDestroy(Lsp $lsp): JsonResponse
    {
        $lsp->delete();

        return $this->ok(['message' => 'Data LSP/LPK dihapus.']);
    }

    public function lspAttachAsesor(Request $request, Lsp $lsp): JsonResponse
    {
        $request->validate(['asesor_id' => 'required|exists:lsp_asesors,id']);
        $lsp->asesors()->syncWithoutDetaching([$request->asesor_id]);

        return $this->ok(['message' => 'Asesor dihubungkan.']);
    }

    public function lspDetachAsesor(Lsp $lsp, LspAsesor $asesor): JsonResponse
    {
        $lsp->asesors()->detach($asesor->id);

        return $this->ok(['message' => 'Asesor dilepas dari LSP/LPK.']);
    }

    /* ======================= PROGRAM (SERTIFIKASI / PELATIHAN) ======================= */

    public function programList(Request $request, string $jenis): JsonResponse
    {
        abort_unless(in_array($jenis, ['sertifikasi', 'pelatihan']), 404);

        $data = LspLpkProgram::with('lsp:id,nama,jenis')
            ->where('jenis', $jenis)
            ->when($request->lsp_id, fn ($q, $v) => $q->where('lsp_id', $v))
            ->when($request->q, fn ($q, $s) => $q->where('nama_skema', 'like', "%$s%"))
            ->latest()
            ->paginate(self::PER_PAGE);

        $col  = $jenis === 'sertifikasi' ? 'harga_sertifikasi_per_pax' : 'harga_pelatihan_per_hari';
        $base = LspLpkProgram::where('jenis', $jenis);

        return $this->ok([
            'data'  => $data->items(),
            'meta'  => $this->meta($data),
            'stats' => [
                'total' => (clone $base)->count(),
                'lsp'   => (clone $base)->distinct()->count('lsp_id'),
                'avg'   => (clone $base)->avg($col),
                'min'   => (clone $base)->min($col),
                'max'   => (clone $base)->max($col),
            ],
        ]);
    }

    public function programStore(Request $request): JsonResponse
    {
        LspLpkProgram::create($this->programData($request));

        return $this->ok(['message' => 'Data ditambahkan.']);
    }

    public function programUpdate(Request $request, LspLpkProgram $program): JsonResponse
    {
        $program->update($this->programData($request));

        return $this->ok(['message' => 'Data diperbarui.']);
    }

    public function programDestroy(LspLpkProgram $program): JsonResponse
    {
        $program->delete();

        return $this->ok(['message' => 'Data dihapus.']);
    }

    /* ======================= ASESOR ======================= */

    public function asesorList(Request $request): JsonResponse
    {
        $data = LspAsesor::with('lsps:id,nama')
            ->when($request->q, fn ($q, $s) => $q->where('nama_lengkap', 'like', "%$s%"))
            ->when($request->lsp_id, fn ($q, $v) => $q->whereHas('lsps', fn ($l) => $l->where('lsps.id', $v)))
            ->latest()
            ->paginate(self::PER_PAGE);

        return $this->ok([
            'data'  => $data->items(),
            'meta'  => $this->meta($data),
            'stats' => [
                'total'  => LspAsesor::count(),
                'dengan' => LspAsesor::has('lsps')->count(),
                'tanpa'  => LspAsesor::doesntHave('lsps')->count(),
            ],
        ]);
    }

    public function asesorShow(LspAsesor $asesor): JsonResponse
    {
        $asesor->load('lsps:id,nama');
        $riwayat = $asesor->riwayat()->orderByDesc('tanggal')->orderByDesc('id')->get();

        return $this->ok([
            'asesor'  => $asesor,
            'riwayat' => $riwayat,
            'stats'   => [
                'uji'              => $riwayat->where('jenis', 'uji_kompetensi')->count(),
                'mengajar'         => $riwayat->where('jenis', 'mengajar')->count(),
                'pembayaran_count' => $riwayat->where('jenis', 'pembayaran')->count(),
                'total_pembayaran' => (float) $riwayat->where('jenis', 'pembayaran')->sum('harga'),
            ],
        ]);
    }

    public function asesorStore(Request $request): JsonResponse
    {
        [$data, $lspIds] = $this->asesorData($request);

        $asesor = LspAsesor::create($data);
        $asesor->lsps()->sync($lspIds);

        return $this->ok(['message' => 'Asesor ditambahkan.', 'id' => $asesor->id]);
    }

    public function asesorUpdate(Request $request, LspAsesor $asesor): JsonResponse
    {
        [$data, $lspIds] = $this->asesorData($request);

        $asesor->update($data);
        $asesor->lsps()->sync($lspIds);

        return $this->ok(['message' => 'Data asesor diperbarui.']);
    }

    public function asesorDestroy(LspAsesor $asesor): JsonResponse
    {
        $asesor->delete();

        return $this->ok(['message' => 'Data asesor dihapus.']);
    }

    /* ======================= RIWAYAT ======================= */

    public function riwayatList(Request $request): JsonResponse
    {
        $data = AsesorRiwayat::with('asesor:id,nama_lengkap')
            ->when($request->jenis, fn ($q, $v) => $q->where('jenis', $v))
            ->when($request->asesor_id, fn ($q, $v) => $q->where('asesor_id', $v))
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w
                ->where('nama_manual', 'like', "%$s%")
                ->orWhereHas('asesor', fn ($a) => $a->where('nama_lengkap', 'like', "%$s%"))))
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->paginate(self::PER_PAGE);

        return $this->ok([
            'data'  => $data->items(),
            'meta'  => $this->meta($data),
            'stats' => [
                'uji'        => AsesorRiwayat::where('jenis', 'uji_kompetensi')->count(),
                'mengajar'   => AsesorRiwayat::where('jenis', 'mengajar')->count(),
                'pembayaran' => (float) AsesorRiwayat::where('jenis', 'pembayaran')->sum('harga'),
            ],
        ]);
    }

    public function riwayatStore(Request $request): JsonResponse
    {
        AsesorRiwayat::create($this->riwayatData($request));

        return $this->ok(['message' => 'Riwayat ditambahkan.']);
    }

    public function riwayatUpdate(Request $request, AsesorRiwayat $riwayat): JsonResponse
    {
        $riwayat->update($this->riwayatData($request));

        return $this->ok(['message' => 'Riwayat diperbarui.']);
    }

    public function riwayatDestroy(AsesorRiwayat $riwayat): JsonResponse
    {
        $riwayat->delete();

        return $this->ok(['message' => 'Riwayat dihapus.']);
    }

    /* ======================= VALIDATION & DATA ======================= */

    private function lspRules(): array
    {
        return [
            'nama'             => 'required|string|max:255',
            'jenis'            => 'required|in:LSP 1,LSP 3,LPK',
            'bidang'           => 'nullable|string|max:255',
            'based'            => 'nullable|string|max:255',
            'nomor_pks_mou'    => 'nullable|string|max:255',
            'tanggal_mulai'    => 'nullable|date',
            'tanggal_berakhir' => 'nullable|date|after_or_equal:tanggal_mulai',
            'pic'              => 'nullable|string|max:255',
            'no_hp'            => 'nullable|string|max:30',
            'email'            => 'nullable|email|max:255',
            'website'          => 'nullable|string|max:255',
            'status'           => 'required|in:Aktif,Nonaktif,Berakhir',
            'keterangan'       => 'nullable|string',
        ];
    }

    private function asesorRules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'alamat'       => 'nullable|string',
            'no_telp'      => 'nullable|string|max:30',
            'email'        => 'nullable|email|max:255',
            'npwp'         => 'nullable|string|max:30',
            'ktp'          => 'nullable|string|max:20',
            'lsp_ids'      => 'nullable|array',
            'lsp_ids.*'    => 'exists:lsps,id',
        ];
    }

    /** @return array{0: array, 1: array} [data asesor, id LSP/LPK yang dihubungkan] */
    private function asesorData(Request $request): array
    {
        $data   = $request->validate($this->asesorRules());
        $lspIds = $data['lsp_ids'] ?? [];
        unset($data['lsp_ids']);

        return [$data, $lspIds];
    }

    private function programData(Request $request): array
    {
        $jenis = $request->validate(['jenis' => 'required|in:sertifikasi,pelatihan'])['jenis'];
        $sert  = $jenis === 'sertifikasi';

        $data = $request->validate([
            'lsp_id'                    => 'required|exists:lsps,id',
            'nama_skema'                => 'required|string|max:255',
            'jenis_skema'               => $sert ? 'nullable|string|max:255' : 'prohibited',
            'pelaksanaan'               => 'nullable|string|max:255',
            'keterangan'                => 'nullable|string',
            'jenjang_kkni'              => $sert ? 'nullable|string|max:50' : 'prohibited',
            'harga_sertifikasi_per_pax' => $sert ? 'required|numeric|min:0' : 'prohibited',
            'harga_pelatihan_per_hari'  => $sert ? 'prohibited' : 'required|numeric|min:0',
        ]);

        return $data + ['jenis' => $jenis];
    }

    private function riwayatData(Request $request): array
    {
        $data = $request->validate([
            'jenis'       => 'required|in:uji_kompetensi,mengajar,pembayaran',
            'asesor_id'   => 'nullable|exists:lsp_asesors,id|required_without:nama_manual',
            'nama_manual' => 'nullable|string|max:255|required_without:asesor_id',
            'tanggal'     => 'required|date',
            'harga'       => 'nullable|numeric|min:0|required_if:jenis,pembayaran',
            'keterangan'  => 'nullable|string',
        ]);

        if (!empty($data['asesor_id'])) {
            $data['nama_manual'] = null;
        }
        if ($data['jenis'] !== 'pembayaran') {
            $data['harga'] = null;
        }

        return $data;
    }
}