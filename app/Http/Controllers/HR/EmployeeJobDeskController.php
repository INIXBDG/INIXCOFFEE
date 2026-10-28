<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\JobDesk;
use App\Models\JobProfile;
use App\Models\karyawan;
use App\Models\OrgStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class EmployeeJobDeskController extends Controller
{
    private const JABATAN_PENGELOLA = [
        'Koordinator ITSM',
        'SPV Sales', 
        'Education Manager',
        'HRD',
        'GM',
    ];

    private const BOLEH_EDIT_MILIK_SENDIRI = true;

    private function me(): ?karyawan
    {
        return karyawan::where('id', Auth::id())->first();
    }

    private function norm($s): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $s)));
    }

    private function isPengelola(karyawan $k): bool
    {
        $list = array_map(fn($j) => $this->norm($j), self::JABATAN_PENGELOLA);
        return in_array($this->norm($k->jabatan), $list, true);
    }

    private function orgByJabatan(?string $jabatan)
    {
        return OrgStructure::whereRaw('LOWER(TRIM(jabatan)) = ?', [$this->norm($jabatan)]);
    }

    private function ownOrgIds(karyawan $k): array
    {
        return $this->orgByJabatan($k->jabatan)->pluck('id')->all();
    }

    private function getDivisi(karyawan $k): ?string
    {
        return $this->orgByJabatan($k->jabatan)->whereNotNull('divisi')->value('divisi')
            ?: ($k->divisi ?? null);
    }

    private function scopeOrgIds(karyawan $k): array
    {
        if ($this->isPengelola($k)) {
            $divisi = $this->getDivisi($k);
            if ($divisi) {
                return OrgStructure::where('divisi', $divisi)->pluck('id')->all();
            }
        }
        return $this->ownOrgIds($k);
    }

    private function deny(string $msg = 'Unauthorized', int $code = 403)
    {
        return response()->json(['success' => false, 'error' => $msg, 'message' => $msg], $code);
    }

    private function fail(\Throwable $e, string $log)
    {
        if ($e instanceof ValidationException) {
            return response()->json(['success' => false, 'message' => 'Data tidak valid', 'errors' => $e->errors()], 422);
        }
        Log::error($log . ': ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }

    private function canWriteOrg(karyawan $k, int $orgId, bool $exists): bool
    {
        if (!$this->isPengelola($k) || !in_array($orgId, $this->scopeOrgIds($k))) {
            return false;
        }
        if (in_array($orgId, $this->ownOrgIds($k))) {
            return self::BOLEH_EDIT_MILIK_SENDIRI; 
        }
        return true;
    }

    private function canWriteProfile(karyawan $k, karyawan $target, bool $exists): bool
    {
        if (!$this->isPengelola($k)) return false;

        $allowedOrgs = OrgStructure::whereIn('id', $this->scopeOrgIds($k))->get();
        $targetJabatanNorm = $this->norm($target->jabatan);
        $targetDivisiNorm = $this->norm($target->divisi);

        $hasAccess = $allowedOrgs->contains(function ($org) use ($targetJabatanNorm, $targetDivisiNorm) {
            $matchJabatan = $this->norm($org->jabatan) === $targetJabatanNorm;
            if ($matchJabatan && !empty($target->divisi) && !empty($org->divisi)) {
                return $this->norm($org->divisi) === $targetDivisiNorm;
            }
            return $matchJabatan;
        });

        if (!$hasAccess) return false;
        if ($target->id == $k->id) return $exists && self::BOLEH_EDIT_MILIK_SENDIRI;

        return true;
    }

    public function showProfile($karyawanId)
    {
        $karyawan = $this->me();
        $target = karyawan::find($karyawanId);
        if (!$karyawan || !$target) return $this->deny();

        $isSelf = $target->id == $karyawan->id;
        $allowedOrgs = OrgStructure::whereIn('id', $this->scopeOrgIds($karyawan))->get();
        $targetJabatanNorm = $this->norm($target->jabatan);
        $targetDivisiNorm = $this->norm($target->divisi);

        $boleh = $isSelf || $allowedOrgs->contains(function ($org) use ($targetJabatanNorm, $targetDivisiNorm) {
            $matchJabatan = $this->norm($org->jabatan) === $targetJabatanNorm;
            if ($matchJabatan && !empty($target->divisi) && !empty($org->divisi)) {
                return $this->norm($org->divisi) === $targetDivisiNorm;
            }
            return $matchJabatan;
        });

        if (!$boleh) return $this->deny();

        $profile = JobProfile::where('karyawan_id', $karyawanId)->first();
        return response()->json($profile ?: null, 200);
    }

    public function index(Request $request)
    {
        $karyawan = $this->me();
        if (!$karyawan) return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');

        $isPengelola = $this->isPengelola($karyawan);
        $divisi = $isPengelola ? $this->getDivisi($karyawan) : null;
        $orgIds = $this->scopeOrgIds($karyawan);
        $ownOrgIds = $this->ownOrgIds($karyawan);

        $jobDesks = JobDesk::with(['orgStructure'])->whereIn('id_org', $orgIds)->get();
        
        $orgStructures = OrgStructure::whereIn('id', $orgIds)->orderBy('jabatan')->get();

        $allKaryawans = karyawan::with('jobProfile')
            ->when(!$isPengelola, fn($q) => $q->where('id', $karyawan->id))
            ->get();

        $orgStructures->each(function ($org) use ($allKaryawans) {
            $normJabatan = $this->norm($org->jabatan);
            $normDivisi = $this->norm($org->divisi);
            
            $matchedKaryawans = $allKaryawans->filter(function ($k) use ($normJabatan, $normDivisi) {
                $matchJabatan = $this->norm($k->jabatan) === $normJabatan;
                if ($matchJabatan && !empty($k->divisi) && !empty($normDivisi)) {
                    return $this->norm($k->divisi) === $normDivisi;
                }
                return $matchJabatan;
            });

            $org->setRelation('karyawans', $matchedKaryawans);
        });

        $profileOrgs = $orgStructures->filter(fn($o) => $o->karyawans->count() > 0)->values();

        return view('employee.jobdesk.index', compact(
            'jobDesks', 'orgStructures', 'karyawan', 'isPengelola', 'divisi', 'ownOrgIds', 'profileOrgs'
        ));
    }

    public function show($id)
    {
        $karyawan = $this->me();
        if (!$karyawan) {
            return $this->deny();
        }

        $jobDesk = JobDesk::with('orgStructure.karyawans')
            ->where('id', $id)
            ->whereIn('id_org', $this->scopeOrgIds($karyawan))
            ->first();

        return $jobDesk ? response()->json($jobDesk) : $this->deny();
    }

    private function jobDeskRules(): array
    {
        return [
            'fungsi_utama' => 'nullable|string',
            'tujuan_jabatan' => 'nullable|string',
            'kualifikasi_pendidikan' => 'nullable|string',
            'pengalaman_kerja' => 'nullable|string',
            'kompetensi' => 'nullable|array',
            'kompetensi.*' => 'nullable|string',
            'karakteristik_pribadi' => 'nullable|string',
            'tugas_tanggung_jawab' => 'nullable|array',
            'tugas_tanggung_jawab.*.name' => 'nullable|string',
            'tugas_tanggung_jawab.*.details' => 'nullable|array',
            'tugas_tanggung_jawab.*.details.*' => 'nullable|string',
            'wewenang' => 'nullable|array',
            'wewenang.*.name' => 'nullable|string',
            'wewenang.*.details' => 'nullable|array',
            'wewenang.*.details.*' => 'nullable|string',
        ];
    }

    public function storeJobDesk(Request $request)
    {
        try {
            $karyawan = $this->me();
            $validated = $request->validate(['id_org' => 'required|exists:org_structures,id'] + $this->jobDeskRules());
            $orgId = (int) $validated['id_org'];

            if (!$karyawan || !$this->canWriteOrg($karyawan, $orgId, false)) {
                return $this->deny('Anda tidak berwenang menambah Job Desk untuk jabatan ini');
            }

            $existing = JobDesk::where('id_org', $orgId)->first();
            if ($existing && (!empty($existing->fungsi_utama) || !empty($existing->tugas_tanggung_jawab) || !empty($existing->wewenang))) {
                return $this->deny('Job Desk jabatan ini sudah ada, gunakan tombol Edit', 422);
            }

            $data = $this->cleanHierarchical($validated);
            unset($data['id_org']);

            DB::beginTransaction();
            $jobDesk = $existing ?: new JobDesk(['id_org' => $orgId, 'sop' => []]);
            $jobDesk->fill($data);
            $jobDesk->id_org = $orgId;
            $jobDesk->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Job Desk berhasil ditambahkan']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail($e, 'Error store job desk (employee)');
        }
    }

    public function updateJobDesk(Request $request, $id)
    {
        try {
            $karyawan = $this->me();
            $jobDesk = JobDesk::findOrFail($id);

            if (!$karyawan || !$this->canWriteOrg($karyawan, (int) $jobDesk->id_org, true)) {
                return $this->deny('Anda tidak berwenang mengubah Job Desk ini');
            }

            $validated = $request->validate($this->jobDeskRules());
            $data = $this->cleanHierarchical($validated);

            DB::beginTransaction();
            $jobDesk->update($data);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Job Desk berhasil diperbarui']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail($e, 'Error update job desk (employee)');
        }
    }

    private function sopRules(): array
    {
        return [
            'sop' => 'nullable|array',
            'sop.*.name' => 'nullable|string',
            'sop.*.details' => 'nullable|array',
            'sop.*.details.*' => 'nullable|string',
        ];
    }

    public function storeSop(Request $request)
    {
        try {
            $karyawan = $this->me();
            $validated = $request->validate(['id_org' => 'required|exists:org_structures,id'] + $this->sopRules());
            $orgId = (int) $validated['id_org'];

            if (!$karyawan || !$this->canWriteOrg($karyawan, $orgId, false)) {
                return $this->deny('Anda tidak berwenang menambah SOP untuk jabatan ini');
            }

            $existing = JobDesk::where('id_org', $orgId)->first();
            if ($existing && !empty($existing->sop)) {
                return $this->deny('SOP jabatan ini sudah ada, gunakan tombol Edit', 422);
            }

            DB::beginTransaction();
            $jobDesk = $existing ?: new JobDesk(['id_org' => $orgId, 'kompetensi' => [], 'tugas_tanggung_jawab' => [], 'wewenang' => []]);
            $jobDesk->id_org = $orgId;
            $jobDesk->sop = $this->cleanHierarchical($validated)['sop'] ?? [];
            $jobDesk->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'SOP berhasil ditambahkan']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail($e, 'Error store SOP (employee)');
        }
    }

    public function updateSop(Request $request, $id)
    {
        try {
            $karyawan = $this->me();
            $jobDesk = JobDesk::findOrFail($id);

            if (!$karyawan || !$this->canWriteOrg($karyawan, (int) $jobDesk->id_org, true)) {
                return $this->deny('Anda tidak berwenang mengubah SOP ini');
            }

            $validated = $request->validate($this->sopRules());

            DB::beginTransaction();
            $jobDesk->sop = $this->cleanHierarchical($validated)['sop'] ?? [];
            $jobDesk->save();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'SOP berhasil diperbarui']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail($e, 'Error update SOP (employee)');
        }
    }

    private function profileRules(): array
    {
        return [
            'qualifications' => 'nullable|array',
            'qualifications.*' => 'nullable|string',
            'descriptions' => 'nullable|array',
            'descriptions.*' => 'nullable|string',
            'compensation_benefit' => 'nullable|array',
            'compensation_benefit.*' => 'nullable|string',
        ];
    }

    public function storeProfile(Request $request)
    {
        try {
            $karyawan = $this->me();
            $validated = $request->validate(
                ['karyawan_id' => 'required|exists:karyawans,id|unique:job_profiles,karyawan_id'] + $this->profileRules()
            );
            $target = karyawan::findOrFail($validated['karyawan_id']);

            if (!$karyawan || !$this->canWriteProfile($karyawan, $target, false)) {
                return $this->deny('Anda tidak berwenang menambah Job Profile ini');
            }

            $validated = $this->cleanData($validated);

            DB::beginTransaction();
            JobProfile::create($validated);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Job Profile berhasil ditambahkan']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail($e, 'Error store job profile (employee)');
        }
    }

    public function updateProfile(Request $request, $karyawanId)
    {
        try {
            $karyawan = $this->me();
            $target = karyawan::find($karyawanId);
            $profile = JobProfile::where('karyawan_id', $karyawanId)->first();

            if (!$karyawan || !$target || !$this->canWriteProfile($karyawan, $target, (bool) $profile)) {
                return $this->deny('Anda tidak berwenang mengubah Job Profile ini');
            }

            if (!$profile) {
                return $this->deny('Job Profile tidak ditemukan', 404);
            }

            $validated = $this->cleanData($request->validate($this->profileRules()));

            DB::beginTransaction();
            $profile->update($validated);
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Job Profile berhasil diperbarui']);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail($e, 'Error update job profile (employee)');
        }
    }

    public function destroyProfile($karyawanId)
    {
        return $this->deny('Penghapusan Job Profile hanya dapat dilakukan oleh HRD');
    }

    private function cleanData($data)
    {
        foreach (['qualifications', 'descriptions', 'compensation_benefit'] as $f) {
            if (isset($data[$f])) {
                $data[$f] = array_values(array_filter($data[$f], fn($v) => trim((string) $v) !== ''));
            }
        }
        return $data;
    }

    private function cleanHierarchical($data)
    {
        if (isset($data['kompetensi'])) {
            $data['kompetensi'] = array_values(array_filter($data['kompetensi'], fn($v) => trim((string) $v) !== ''));
        }

        foreach (['tugas_tanggung_jawab', 'wewenang', 'sop'] as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $cleaned = [];
                foreach ($data[$field] as $item) {
                    $name = trim($item['name'] ?? '');
                    if ($name === '') {
                        continue;
                    }
                    $details = array_values(array_filter($item['details'] ?? [], fn($v) => trim((string) $v) !== ''));
                    $cleaned[] = ['name' => $name, 'details' => $details];
                }
                $data[$field] = $cleaned;
            }
        }

        return $data;
    }
}