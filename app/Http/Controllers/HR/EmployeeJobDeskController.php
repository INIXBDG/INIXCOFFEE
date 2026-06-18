<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\JobDesk;
use App\Models\JobProfile;
use App\Models\Karyawan;
use App\Models\OrgStructure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EmployeeJobDeskController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $karyawan = Karyawan::where('id', $user->id)->first();

        if (!$karyawan) {
            return redirect()->back()->with('error', 'Data karyawan tidak ditemukan');
        }

        $jobDesks = JobDesk::with(['orgStructure.karyawans'])
            ->whereHas('orgStructure', function ($q) use ($karyawan) {
                $q->where('jabatan', $karyawan->jabatan);
            })
            ->get();

        // Ambil Org Structure hanya untuk jabatan karyawan ini
        $orgStructures = OrgStructure::with([
            'karyawans' => function ($q) use ($karyawan) {
                $q->where('id', $karyawan->id)->with('jobProfile');
            },
        ])
            ->where('jabatan', $karyawan->jabatan)
            ->get()
            ->filter(fn($org) => $org->karyawans->count() > 0);

        return view('employee.jobdesk.index', compact('jobDesks', 'orgStructures', 'karyawan'));
    }

    public function show($id)
    {
        $user = Auth::user();
        $karyawan = Karyawan::where('id', $user->id)->first();

        $jobDesk = JobDesk::with('orgStructure.karyawans')
            ->where('id', $id)
            ->whereHas('orgStructure', function ($q) use ($karyawan) {
                $q->where('jabatan', $karyawan->jabatan);
            })
            ->first();

        if (!$jobDesk) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($jobDesk);
    }

    public function showProfile($karyawanId)
    {
        $user = Auth::user();
        $currentKaryawan = Karyawan::where('id', $user->id)->first();

        // Hanya bisa akses profile sendiri
        if ($currentKaryawan->id != $karyawanId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $profile = JobProfile::where('karyawan_id', $karyawanId)->first();

        if (!$profile) {
            return response()->json(null, 200);
        }

        return response()->json($profile);
    }

    public function storeProfile(Request $request)
    {
        try {
            $user = Auth::user();
            $karyawan = Karyawan::where('id', $user->id)->first();

            if (!$karyawan) {
                return response()->json(['success' => false, 'message' => 'Data karyawan tidak ditemukan'], 403);
            }

            $validated = $request->validate([
                'karyawan_id' => 'required|exists:karyawans,id',
                'qualifications' => 'nullable|array',
                'qualifications.*' => 'nullable|string',
                'descriptions' => 'nullable|array',
                'descriptions.*' => 'nullable|string',
                'compensation_benefit' => 'nullable|array',
                'compensation_benefit.*' => 'nullable|string',
            ]);

            // Pastikan hanya bisa create untuk diri sendiri
            if ($validated['karyawan_id'] != $karyawan->id) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $validated = $this->cleanData($validated);

            DB::beginTransaction();
            JobProfile::create($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Job Profile berhasil ditambahkan',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error store job profile: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function updateProfile(Request $request, $karyawanId)
    {
        try {
            $user = Auth::user();
            $currentKaryawan = Karyawan::where('id', $user->id)->first();

            // Hanya bisa update profile sendiri
            if ($currentKaryawan->id != $karyawanId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            $profile = JobProfile::where('karyawan_id', $karyawanId)->first();

            if (!$profile) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Job Profile tidak ditemukan',
                    ],
                    404,
                );
            }

            $validated = $request->validate([
                'qualifications' => 'nullable|array',
                'qualifications.*' => 'nullable|string',
                'descriptions' => 'nullable|array',
                'descriptions.*' => 'nullable|string',
                'compensation_benefit' => 'nullable|array',
                'compensation_benefit.*' => 'nullable|string',
            ]);

            $validated = $this->cleanData($validated);

            DB::beginTransaction();
            $profile->update($validated);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Job Profile berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error update job profile: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => $e->getMessage(),
                ],
                500,
            );
        }
    }

    public function destroyProfile($karyawanId)
    {
        try {
            $user = Auth::user();
            $currentKaryawan = Karyawan::where('id', $user->id)->first();

            // Hanya bisa delete profile sendiri
            if ($currentKaryawan->id != $karyawanId) {
                return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
            }

            DB::beginTransaction();
            $profile = JobProfile::where('karyawan_id', $karyawanId)->first();

            if (!$profile) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Job Profile tidak ditemukan',
                    ],
                    404,
                );
            }

            $profile->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Job Profile berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error delete job profile: ' . $e->getMessage());
            return response()->json(
                [
                    'success' => false,
                    'message' => 'Gagal menghapus Job Profile',
                ],
                500,
            );
        }
    }

    private function cleanData($data)
    {
        if (isset($data['qualifications'])) {
            $data['qualifications'] = array_values(array_filter($data['qualifications'], fn($v) => trim($v) !== ''));
        }
        if (isset($data['descriptions'])) {
            $data['descriptions'] = array_values(array_filter($data['descriptions'], fn($v) => trim($v) !== ''));
        }
        if (isset($data['compensation_benefit'])) {
            $data['compensation_benefit'] = array_values(array_filter($data['compensation_benefit'], fn($v) => trim($v) !== ''));
        }
        return $data;
    }
}
