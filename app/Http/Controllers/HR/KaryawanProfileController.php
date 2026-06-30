<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\JobProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KaryawanProfileController extends Controller
{
    public function show($karyawanId)
    {
        $profile = JobProfile::where('karyawan_id', $karyawanId)->first();

        if (!$profile) {
            // Return null JSON (bukan error) agar frontend tahu ini mode CREATE
            return response()->json(null, 200);
        }

        return response()->json($profile);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Store Job Profile called', $request->all());

            $validated = $request->validate([
                'karyawan_id' => 'required|exists:karyawans,id|unique:job_profiles,karyawan_id',
                'qualifications' => 'nullable|array',
                'qualifications.*' => 'nullable|string',
                'descriptions' => 'nullable|array',
                'descriptions.*' => 'nullable|string',
                'compensation_benefit' => 'nullable|array',
                'compensation_benefit.*' => 'nullable|string',
            ]);

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

    public function update(Request $request, $karyawanId)
    {
        try {
            Log::info('Update Job Profile called for karyawan_id: ' . $karyawanId);

            $profile = JobProfile::where('karyawan_id', $karyawanId)->first();

            if (!$profile) {
                return response()->json(
                    [
                        'success' => false,
                        'message' => 'Job Profile tidak ditemukan. Silakan refresh halaman dan coba lagi.',
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

    public function destroy($karyawanId)
    {
        try {
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
