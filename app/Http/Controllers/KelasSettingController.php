<?php

namespace App\Http\Controllers;

use App\Models\Inventaris;
use App\Models\KelasSetting;
use App\Models\RKM;
use App\Models\Materi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class KelasSettingController extends Controller
{
    public function index()
    {
        return view('KelasSetting.index');
    }
    public function getData(Request $request): JsonResponse
    {
        $this->autoSyncFromRKM();

        $search = $request->query('search', '');

        $query = KelasSetting::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kelas', 'like', "%{$search}%")
                    ->orWhere('instruktur', 'like', "%{$search}%")
                    ->orWhere('ruangan', 'like', "%{$search}%")
                    ->orWhere('asset', 'like', "%{$search}%");
            });
        }

        $rows = $query->orderBy('dari')->get();

        $grouped = $rows->groupBy(function ($r) {
            $ws = $r->week_start ? Carbon::parse($r->week_start)->format('Y-m-d') : 'unknown';
            $we = $r->week_end ? Carbon::parse($r->week_end)->format('Y-m-d') : 'unknown';
            return $ws . '|' . $we;
        });

        $weeksData = [];
        foreach ($grouped as $key => $items) {
            [$start, $end] = explode('|', $key);

            $rowsArr = [];
            $comments = [];

            foreach ($items as $item) {
                $rowsArr[] = $this->formatRow($item);

                if (!empty($item->comments) && is_array($item->comments)) {
                    foreach ($item->comments as $field => $cmts) {
                        $comKey = $item->id . ':' . $field;
                        if (!empty($cmts)) {
                            $comments[$comKey] = $cmts;
                        }
                    }
                }
            }

            $weeksData[$start] = [
                'id' => $start,
                'start' => $start,
                'end' => $end,
                'rows' => $rowsArr,
                'comments' => $comments,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $weeksData,
            'meta' => [
                'materi' => $this->getMateriList(),
                'karyawan_instruktur' => $this->getKaryawanByJabatan(['Instruktur', 'Education Manager', 'Outsource']),
                'karyawan_ts' => $this->getKaryawanByJabatan(['Technical Support']),
                'inventaris' => $this->getInventarisList(),
            ],
        ]);
    }

    private function autoSyncFromRKM(): void
    {
        $lockKey = 'kelas_setting_auto_sync_lock';

        if (Cache::has($lockKey)) {
            return;
        }

        Cache::put($lockKey, true, now()->addMinutes(5));

        try {
            $synced = $this->syncFromRKM();
            if ($synced > 0) {
                Log::info("Auto-sync RKM: {$synced} kelas baru ditambahkan.");
            }
        } catch (\Exception $e) {
            Log::error('Auto-sync RKM gagal: ' . $e->getMessage());
            Cache::forget($lockKey);
        }
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'week_start' => 'nullable|date',
            'week_end' => 'nullable|date|after_or_equal:week_start',
            'kelas' => 'nullable|string|max:255',
            'dari' => 'nullable|date',
            'sampai' => 'nullable|date|after_or_equal:dari',
            'ruangan' => 'nullable|string|max:100',
            'device' => 'nullable|string|max:50',
            'device_instruktur' => 'nullable|string|max:50',
            'pax' => 'nullable|integer|min:0',
            'instruktur' => 'nullable|string|max:100',
            'pc_its' => 'nullable|string|max:50',
            'asset' => 'nullable|string|max:255',
            'software' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string|in:Hitam,Biru,Merah',
            'id_rkm' => 'nullable|integer',
            'asset' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = $validator->validated();
        $data['comments'] = [];

        if (!empty($data['id_rkm'])) {
            $rkm = RKM::with(['materi', 'instruktur', 'sales'])->find($data['id_rkm']);
            if ($rkm) {
                if (empty($data['kelas']) && $rkm->materi) {
                    $data['kelas'] = $rkm->materi->nama_materi ?? '';
                }
                if (empty($data['instruktur']) && $rkm->instruktur) {
                    $data['instruktur'] = $rkm->instruktur->kode_karyawan ?? ($rkm->instruktur->nama_lengkap ?? '');
                }
                if (empty($data['dari']) && $rkm->tanggal_awal) {
                    $data['dari'] = Carbon::parse($rkm->tanggal_awal)->format('Y-m-d');
                }
                if (isset($data['asset']) && is_array($data['asset'])) {
                    $data['asset'] = json_encode(array_values($data['asset']));
                }
                if (empty($data['sampai']) && $rkm->tanggal_akhir) {
                    $data['sampai'] = Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d');
                }
                if ((!isset($data['pax']) || (int) $data['pax'] === 0) && isset($rkm->pax)) {
                    $data['pax'] = (int) ($rkm->pax ?? 0);
                }
                if (empty($data['week_start']) && $rkm->tanggal_awal) {
                    $tglAwal = Carbon::parse($rkm->tanggal_awal);
                    $data['week_start'] = $tglAwal->copy()->startOfWeek()->format('Y-m-d');
                    $data['week_end'] = $tglAwal->copy()->endOfWeek()->format('Y-m-d');
                }
            }
        }

        $kelas = KelasSetting::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan.',
            'data' => $this->formatRow($kelas),
        ], 201);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $kelas = KelasSetting::findOrFail($id);

        $allowedFields = [
            'id_rkm', 'kelas', 'dari', 'sampai', 'ruangan',
            'device', 'device_instruktur', 'pax',
            'instruktur', 'pc_its', 'asset',
            'software', 'keterangan', 'status',
            'week_start', 'week_end',
        ];

        $updates = [];
        foreach ($request->all() as $field => $value) {
            if (in_array($field, $allowedFields)) {
                if (in_array($field, ['dari', 'sampai', 'week_start', 'week_end'])) {
                    $value = $value ?: null;
                }
                if ($field === 'pax') {
                    $value = (int) $value;
                }
                if ($field === 'id_rkm') {
                    $value = $value ? (int) $value : null;
                }
                if ($field === 'status' && !in_array($value, ['Hitam', 'Biru', 'Merah'])) {
                    continue;
                }
                if ($field === 'asset' && is_array($value)) {
                    $value = json_encode(array_values($value));
                }
                $updates[$field] = $value;
            }
        }

        if (empty($updates)) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada field yang valid diupdate.',
            ], 422);
        }

        $kelas->update($updates);

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate.',
            'data' => $this->formatRow($kelas->fresh()),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $kelas = KelasSetting::findOrFail($id);
        $kelasName = $this->formatRow($kelas)['kelas'];
        $kelas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kelas "' . ($kelasName ?: 'tanpa nama') . '" berhasil dihapus.',
            'data' => ['id' => $id],
        ]);
    }

    public function restore($id): JsonResponse
    {
        $kelas = KelasSetting::withTrashed()->findOrFail($id);
        $kelas->restore();

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dipulihkan.',
            'data' => $this->formatRow($kelas->fresh()),
        ]);
    }

    public function addComment(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'field' => 'required|string',
            'author' => 'nullable|string|max:100',
            'text' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $kelas = KelasSetting::findOrFail($id);

        $author = $request->author
            ?? (auth()->check() && auth()->user() ? (auth()->user()->name ?? 'User') : null)
            ?? 'Anonymous';

        $kelas->addComment(
            $request->field,
            (string) $author,
            $request->text
        );
        $kelas->save();

        return response()->json([
            'success' => true,
            'message' => 'Dikirim.',
            'data' => [
                'rowId' => (string) $kelas->id,
                'field' => $request->field,
                'comments' => $kelas->getCommentsForField($request->field),
            ],
        ]);
    }

    public function removeComment(Request $request, $id, $cmtId): JsonResponse
    {
        $field = $request->query('field');
        if (!$field) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter "field" wajib diisi.',
            ], 422);
        }

        $kelas = KelasSetting::findOrFail($id);
        $kelas->removeComment($field, $cmtId);
        $kelas->save();

        return response()->json([
            'success' => true,
            'message' => 'Komentar dihapus.',
            'data' => [
                'rowId' => (string) $kelas->id,
                'field' => $field,
                'comments' => $kelas->getCommentsForField($field),
            ],
        ]);
    }

    private function formatRow(KelasSetting $row): array
    {
        return [
            'id' => (string) $row->id,
            'id_rkm' => $row->id_rkm ? (int) $row->id_rkm : null,
            'kelas' => $row->kelas ?? '',
            'dari' => $row->dari ? Carbon::parse($row->dari)->format('Y-m-d') : '',
            'sampai' => $row->sampai ? Carbon::parse($row->sampai)->format('Y-m-d') : '',
            'ruangan' => $row->ruangan ?? '',
            'device' => $row->device ?? '',
            'deviceInstruktur' => $row->device_instruktur ?? '',
            'pax' => (int) ($row->pax ?? 0),
            'instruktur' => $row->instruktur ?? '',
            'asset' => $row->asset ?? '',
            'software' => $row->software ?? '',
            'keterangan' => $row->keterangan ?? '',
            'pcits' => $row->pc_its ?? '',
            'status' => $row->status ?? 'Biru',
            'asset' => $this->decodeAsset($row->asset),
        ];
    }

    private function decodeAsset($value)
    {
        if (!$value) return [];
        $decoded = json_decode($value, true);
        if (is_array($decoded)) return array_map('strval', $decoded);
        return $value;
    }

    private function getMateriList(): array
    {
        return Cache::remember('meta_materi_list', 7200, function () {
            try {
                if (!class_exists(Materi::class)) return [];
                return Materi::select('nama_materi')->orderBy('nama_materi')->pluck('nama_materi')->toArray();
            } catch (\Exception $e) {
                Log::error('Gagal load materi: ' . $e->getMessage());
                return [];
            }
        });
    }

    private function getInventarisList(): array
    {
        return Cache::remember('meta_inventaris_list', 7200, function () {
            try {
                if (!class_exists(Inventaris::class)) return [];
                return Inventaris::where('ruangan', ['Kelas', 'Ruang 6 (Ex Office)', 'ADOC', 'Lorong Edu', 'Ruang 5 (Ex Pak Ray)', 'Ruang 4', 'Ruang 3', 'Ruang 1', 'Ruang 2', 'Ruang ITSM', 'ITSM'])->select('id', 'idbarang', 'name', 'merk_kode_seri_hardware', 'ruangan')
                    ->orderBy('name')
                    ->get()
                    ->map(function ($i) {
                        $label = ($i->name ?? '');
                        if ($i->merk_kode_seri_hardware) $label .= ' - ' . $i->merk_kode_seri_hardware;
                        if ($i->ruangan) $label .= ' (' . $i->ruangan . ')';
                        return ['id' => (string) $i->id, 'text' => $label];
                    })
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Gagal load inventaris: ' . $e->getMessage());
                return [];
            }
        });
    }

    private function getKaryawanByJabatan(array $jabatanList): array
    {
        $cacheKey = 'meta_karyawan_' . md5(implode(',', $jabatanList));
        return Cache::remember($cacheKey, 7200, function () use ($jabatanList) {
            try {
                if (!class_exists(Karyawan::class)) return [];
                return Karyawan::whereIn('jabatan', $jabatanList)
                    ->where('status_aktif', '1')
                    ->select('kode_karyawan', 'nama_lengkap', 'jabatan')
                    ->orderBy('nama_lengkap')
                    ->get()
                    ->map(function ($k) {
                        $kode = $k->kode_karyawan ?? $k->id;
                        return [
                            'id' => (string) $kode,
                            'text' => ($kode ? $kode . ' - ' : '') . ($k->nama_lengkap ?? '') . ' (' . ($k->jabatan ?? '-') . ')',
                            'kode' => (string) $kode,
                            'nama' => $k->nama_lengkap ?? '',
                            'jabatan' => $k->jabatan ?? '',
                        ];
                    })
                    ->toArray();
            } catch (\Exception $e) {
                Log::error('Gagal load karyawan: ' . $e->getMessage());
                return [];
            }
        });
    }

    private function syncFromRKM(): int
    {
        try {
            $table = (new KelasSetting)->getTable();
            $hasRkmCol = Schema::hasColumn($table, 'id_rkm');

            // Ambil semua id_rkm yang sudah ada (termasuk soft-deleted biar tidak double)
            $existingRkmIds = [];
            if ($hasRkmCol) {
                $existingRkmIds = KelasSetting::withTrashed()
                    ->whereNotNull('id_rkm')
                    ->pluck('id_rkm')
                    ->map(fn ($v) => (int) $v)
                    ->toArray();
            }

            // Perluas rentang tanggal (misalnya 1 tahun ke belakang + 2 tahun ke depan)
            $startDate = Carbon::now()->subYear()->format('Y-m-d');
            $endDate   = Carbon::now()->addYears(2)->format('Y-m-d');

            $rkmQuery = RKM::query()
                ->whereNotNull('tanggal_awal')
                ->whereBetween('tanggal_awal', [$startDate, $endDate]);

            // Kalau ada kolom id_rkm, skip yang sudah pernah masuk
            if ($hasRkmCol && !empty($existingRkmIds)) {
                $rkmQuery->whereNotIn('id', $existingRkmIds);
            }

            // HAPUS limit(500) atau naikkan jadi lebih besar
            $rkms = $rkmQuery
                ->with(['materi', 'instruktur', 'sales'])
                ->orderBy('tanggal_awal')
                ->get();   // ← ambil semua

            $newRecords = [];
            foreach ($rkms as $rkm) {
                try {
                    $tglAwal = Carbon::parse($rkm->tanggal_awal);
                } catch (\Exception $e) {
                    continue;
                }

                $kelas = $rkm->materi?->nama_materi ?? '';
                $tglAkhir = $rkm->tanggal_akhir
                    ? Carbon::parse($rkm->tanggal_akhir)
                    : $tglAwal->copy();

                $instrukturKode = $rkm->instruktur
                    ? ($rkm->instruktur->kode_karyawan ?? $rkm->instruktur->nama_lengkap ?? '')
                    : '';

                $record = [
                    'kelas'             => $kelas,
                    'dari'              => $tglAwal->format('Y-m-d'),
                    'sampai'            => $tglAkhir->format('Y-m-d'),
                    'week_start'        => $tglAwal->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d'),
                    'week_end'          => $tglAwal->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d'),
                    'instruktur'        => $instrukturKode,
                    'pax'               => (int) ($rkm->pax ?? 0),
                    'ruangan'           => $rkm->ruang ?? null,   // ambil dari RKM kalau ada
                    'device'            => 'Laptop',
                    'device_instruktur' => null,
                    'pc_its'            => null,
                    'asset'             => null,
                    'software'          => null,
                    'keterangan'        => null,
                    'status'            => 'Biru',
                    'comments'          => '{}',
                    'created_at'        => now(),
                    'updated_at'        => now(),
                ];

                if ($hasRkmCol) {
                    $record['id_rkm'] = $rkm->id;
                }

                $newRecords[] = $record;
            }

            if (!empty($newRecords)) {
                foreach (array_chunk($newRecords, 100) as $chunk) {
                    KelasSetting::insert($chunk);
                }
            }

            return count($newRecords);
        } catch (\Exception $e) {
            Log::error('Sync RKM ke KelasSetting gagal: ' . $e->getMessage());
            return 0;
        }
    }
}