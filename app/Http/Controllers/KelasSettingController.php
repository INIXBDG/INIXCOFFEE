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
        $search = $request->query('search', '');

        $startDate = Carbon::now()->subMonths(6)->format('Y-m-d');
        $endDate = Carbon::now()->addYear()->format('Y-m-d');

        $rkmQuery = RKM::with(['materi', 'instruktur', 'sales'])
            ->whereBetween('tanggal_awal', [$startDate, $endDate])
            ->whereNull('deleted_at');

        if ($search) {
            $rkmQuery->where(function ($q) use ($search) {
                $q->whereHas('materi', function($mq) use ($search) {
                    $mq->where('nama_materi', 'like', "%{$search}%");
                })->orWhereHas('instruktur', function($iq) use ($search) {
                    $iq->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('kode_karyawan', 'like', "%{$search}%");
                })->orWhere('ruang', 'like', "%{$search}%");
            });
        }

        $rkms = $rkmQuery->orderBy('tanggal_awal')->get();
        $rkmIds = $rkms->pluck('id')->toArray();

        $settings = KelasSetting::whereIn('id_rkm', $rkmIds)
            ->orWhereNull('id_rkm')
            ->get()
            ->keyBy('id_rkm');

        $mergedRows = [];

        $manualSettings = $settings->get(null);
        if ($manualSettings) {
            foreach ($manualSettings as $setting) {
                $row = $this->formatRow($setting, null);
                $row['id'] = (string) $setting->id;
                $mergedRows[] = $row;
            }
        }

        foreach ($rkms as $rkm) {
            $setting = $settings->get($rkm->id);
            $row = $this->formatRow($setting, $rkm);
            
            // INI YANG PENTING: Paksa tambahkan id
            if ($setting) {
                $row['id'] = (string) $setting->id;
            } else {
                $row['id'] = 'rkm_' . $rkm->id;
            }
            
            $mergedRows[] = $row;
        }

        $grouped = collect($mergedRows)->groupBy(function ($r) {
            $ws = $r['week_start'] ? Carbon::parse($r['week_start'])->format('Y-m-d') : 'unknown';
            $we = $r['week_end'] ? Carbon::parse($r['week_end'])->format('Y-m-d') : 'unknown';
            return $ws . '|' . $we;
        });

        $weeksData = [];
        foreach ($grouped as $key => $items) {
            [$start, $end] = explode('|', $key);

            $rowsArr = [];

            foreach ($items as $item) {
                $rowsArr[] = $item;
            }

            $weeksData[$start] = [
                'id' => $start,
                'start' => $start,
                'end' => $end,
                'rows' => $rowsArr,
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
            'asset' => 'nullable',
            'software' => 'nullable|string',
            'keterangan' => 'nullable|string',
            'status' => 'nullable|string|in:Merah,Biru,Hijau,Hitam',
            'id_rkm' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();
        $data['comments'] = [];

        if (!empty($data['id_rkm'])) {
            $rkm = RKM::with(['materi', 'instruktur'])->find($data['id_rkm']);
            if ($rkm) {
                $data['kelas'] = $data['kelas'] ?: ($rkm->materi?->nama_materi ?? '');
                $data['instruktur'] = $data['instruktur'] ?: ($rkm->instruktur?->kode_karyawan ?? $rkm->instruktur?->nama_lengkap ?? '');
                $data['dari'] = $data['dari'] ?: Carbon::parse($rkm->tanggal_awal)->format('Y-m-d');
                $data['sampai'] = $data['sampai'] ?: Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d');
                $data['pax'] = $data['pax'] ?: (int) ($rkm->pax ?? 0);
                
                if (empty($data['week_start']) && $rkm->tanggal_awal) {
                    $tglAwal = Carbon::parse($rkm->tanggal_awal);
                    $data['week_start'] = $tglAwal->copy()->startOfWeek(Carbon::MONDAY)->format('Y-m-d');
                    $data['week_end'] = $tglAwal->copy()->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
                }
            }
        }

        if (isset($data['asset']) && is_array($data['asset'])) {
            $data['asset'] = json_encode(array_values($data['asset']));
        }

        $kelas = KelasSetting::create($data);
        $rkm = !empty($data['id_rkm']) ? RKM::find($data['id_rkm']) : null;

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil ditambahkan.',
            'data' => $this->formatRow($kelas, $rkm),
        ], 201);
    }

    public function clearAll(Request $request): JsonResponse
    {
        $allowedJabatan = ['Programmer']; 
        $userJabatan = auth()->user()->jabatan ?? '';
        
        if (!in_array($userJabatan, $allowedJabatan)) {
            return response()->json([
                'success' => false, 
                'message' => 'Akses ditolak. Fitur ini hanya untuk Permbersihan dan hanya dapat dilakukan sekali saja.'
            ], 403);
        }

        $request->validate([
            'confirm_text' => 'required|in:HAPUS'
        ]);

        try {
            KelasSetting::truncate();

            return response()->json([
                'success' => true,
                'message' => 'Database Kelas Setting berhasil dibersihkan sepenuhnya.'
            ]);
        } catch (\Exception $e) {
            Log::error('Gagal membersihkan database kelas setting: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan sistem saat membersihkan database.'
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        if (str_starts_with($id, 'rkm_')) {
            $id_rkm = (int) str_replace('rkm_', '', $id);
            $rkm = RKM::find($id_rkm);
            
            $defaultStatus = 'Biru';
            if ($rkm) {
                if ($rkm->status == '0') $defaultStatus = 'Merah';
                elseif ($rkm->status == '1') $defaultStatus = 'Biru';
                elseif ($rkm->status == '3') $defaultStatus = 'Hijau';
            }

            $kelas = KelasSetting::firstOrCreate(
                ['id_rkm' => $id_rkm],
                [
                    'status' => $defaultStatus,
                    'device' => 'Laptop',
                ]
            );
        } else {
            $kelas = KelasSetting::findOrFail($id);
        }

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
                if ($field === 'pax') $value = (int) $value;
                if ($field === 'id_rkm') $value = $value ? (int) $value : null;
                if ($field === 'status' && !in_array($value, ['Merah', 'Biru', 'Hijau', 'Hitam'])) continue;
                if ($field === 'asset' && is_array($value)) {
                    $value = json_encode(array_values($value));
                }
                $updates[$field] = $value;
            }
        }

        if (empty($updates)) {
            return response()->json(['success' => false, 'message' => 'Tidak ada field yang valid diupdate.'], 422);
        }

        $kelas->update($updates);
        $rkm = $kelas->id_rkm ? RKM::find($kelas->id_rkm) : null;

        return response()->json([
            'success' => true,
            'message' => 'Data berhasil diupdate.',
            'data' => $this->formatRow($kelas->fresh(), $rkm),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        if (str_starts_with($id, 'rkm_')) {
            return response()->json([
                'success' => true,
                'message' => 'Data reset ke default RKM.',
                'data' => ['id' => $id],
            ]);
        }

        $kelas = KelasSetting::findOrFail($id);
        $kelasName = $kelas->kelas ?: 'tanpa nama';
        $kelas->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kelas "' . $kelasName . '" berhasil dihapus.',
            'data' => ['id' => $id],
        ]);
    }

    public function restore($id): JsonResponse
    {
        $kelas = KelasSetting::withTrashed()->findOrFail($id);
        $kelas->restore();
        $rkm = $kelas->id_rkm ? RKM::find($kelas->id_rkm) : null;

        return response()->json([
            'success' => true,
            'message' => 'Kelas berhasil dipulihkan.',
            'data' => $this->formatRow($kelas->fresh(), $rkm),
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
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        if (str_starts_with($id, 'rkm_')) {
            $id_rkm = (int) str_replace('rkm_', '', $id);
            $kelas = KelasSetting::firstOrCreate(['id_rkm' => $id_rkm], ['comments' => '{}']);
        } else {
            $kelas = KelasSetting::findOrFail($id);
        }

        $author = $request->author ?? (auth()->check() ? (auth()->user()->name ?? 'User') : 'Anonymous');

        $kelas->addComment($request->field, (string) $author, $request->text);
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
            return response()->json(['success' => false, 'message' => 'Parameter "field" wajib diisi.'], 422);
        }

        if (str_starts_with($id, 'rkm_')) {
            $id_rkm = (int) str_replace('rkm_', '', $id);
            $kelas = KelasSetting::firstOrCreate(['id_rkm' => $id_rkm], ['comments' => '{}']);
        } else {
            $kelas = KelasSetting::findOrFail($id);
        }

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

    private function formatRow(?KelasSetting $setting, ?RKM $rkm): array
    {
        $defaultStatus = 'Hitam';
        if ($rkm) {
            $statusVal = (string) $rkm->status;
            if ($statusVal === '0') {
                $defaultStatus = 'Merah';
            } elseif ($statusVal === '1') {
                $defaultStatus = 'Biru';
            } elseif ($statusVal === '3') {
                $defaultStatus = 'Hijau';
            }
        }

        $base = [
            'id_rkm' => $rkm?->id,
            'kelas' => $rkm?->materi?->nama_materi ?? '',
            'dari' => $rkm?->tanggal_awal ? Carbon::parse($rkm->tanggal_awal)->format('Y-m-d') : '',
            'sampai' => $rkm?->tanggal_akhir ? Carbon::parse($rkm->tanggal_akhir)->format('Y-m-d') : '',
            'instruktur' => $rkm?->instruktur?->kode_karyawan ?? ($rkm?->instruktur?->nama_lengkap ?? ''),
            'pax' => (int) ($rkm?->pax ?? 0),
            'week_start' => $rkm?->tanggal_awal ? Carbon::parse($rkm->tanggal_awal)->startOfWeek(Carbon::MONDAY)->format('Y-m-d') : '',
            'week_end' => $rkm?->tanggal_awal ? Carbon::parse($rkm->tanggal_awal)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d') : '',
        ];

        $rowId = null;

        if ($setting) {
            $rowId = (string) $setting->id;
            $base['kelas'] = $setting->kelas ?: $base['kelas'];
            $base['dari'] = $setting->dari ?: $base['dari'];
            $base['sampai'] = $setting->sampai ?: $base['sampai'];
            $base['ruangan'] = $setting->ruangan ?? '';
            $base['device'] = $setting->device ?? 'Laptop';
            $base['deviceInstruktur'] = $setting->device_instruktur ?? '';
            $base['pax'] = (int) ($setting->pax ?? $base['pax']);
            $base['instruktur'] = $setting->instruktur ?: $base['instruktur'];
            $base['pcits'] = $setting->pc_its ?? '';
            $base['asset'] = $this->decodeAsset($setting->asset);
            $base['software'] = $setting->software ?? '';
            $base['keterangan'] = $setting->keterangan ?? '';
            $base['status'] = $setting->status ?: $defaultStatus;
            $base['week_start'] = $setting->week_start ?: $base['week_start'];
            $base['week_end'] = $setting->week_end ?: $base['week_end'];
        } else {
            $rowId = 'rkm_' . ($rkm?->id ?? '0');
            $base['ruangan'] = '';
            $base['device'] = 'Laptop';
            $base['deviceInstruktur'] = '';
            $base['pcits'] = '';
            $base['asset'] = [];
            $base['software'] = '';
            $base['keterangan'] = '';
            $base['status'] = $defaultStatus;
        }

        $base['id'] = $rowId;

        return $base;
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
                return Inventaris::whereIn('ruangan', ['Kelas', 'Ruang 6 (Ex Office)', 'ADOC', 'Lorong Edu', 'Ruang 5 (Ex Pak Ray)', 'Ruang 4', 'Ruang 3', 'Ruang 1', 'Ruang 2', 'Ruang ITSM', 'ITSM'])
                    ->select('id', 'idbarang', 'name', 'merk_kode_seri_hardware', 'ruangan')
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
}