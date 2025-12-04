<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\perhitunganNetSales;
use App\Models\approvedNetSales;
use App\Models\Karyawan;
use App\Models\RKM;
use App\Models\trackingNetSales;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Notifications\NetSalesNotification;
use Illuminate\Support\Facades\Notification;

class ApprovedNetSalesController extends Controller
{
    public function approve(Request $request)
    {
        try {
            $id = $request->input('id_rkm');
            $netSales = perhitunganNetSales::with('karyawan')->where('id_rkm', $id)->first();

            if (!$netSales) {
                return response()->json(['error' => 'Data tidak ditemukan.'], 404);
            }

            $FA = auth()->user()->jabatan;

            if ($FA === 'Finance & Accounting') {
                $statusTracking = $request->input('status_tracking');

                if (!empty($statusTracking)) {
                    $tracking = trackingNetSales::where('id', $netSales->id_tracking)->first();
                    if ($tracking) {
                        $tracking->tracking = $statusTracking;
                        $tracking->save();
                    }
                } else {
                    return back()->with(['success' => false, 'error' => 'Pilih status tracking terlebih dahulu.']);
                }
            }

            $rkm = RKM::findOrFail($netSales->id_rkm);
            $salesUser = User::whereHas('karyawan', function ($q) use ($rkm) {
                $q->where('kode_karyawan', $rkm->sales_key);
            })->first();

            if (!$salesUser) {
                return response()->json(['error' => 'User sales tidak ditemukan.'], 404);
            }

            $url = route('paymantAdvance.index');
            $path = request()->path();
            $keteranganInput = $request->input('keterangan');

            if (!empty($keteranganInput)) {
                approvedNetSales::create([
                    'id_rkm' => $netSales->id_rkm,
                    'tanggal' => now()->format('Y-m-d'),
                    'status' => 0,
                    'keterangan' => "Ditolak: " . $keteranganInput,
                    'level_status' => null,
                ]);

                $dummyComment = (object) [
                    'status' => 'ditolak',
                    'tipe' => 'Pengajuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => $keteranganInput,
                ];
                $receiverId = $salesUser->id;
                Notification::send($salesUser, new NetSalesNotification($dummyComment, $path, $receiverId));

                return response()->json(['success' => true, 'message' => 'Pengajuan ditolak dan notifikasi dikirim.']);
            }

            $latestApproval = approvedNetSales::where('id_rkm', $netSales->id_rkm)
                ->orderByDesc('created_at')
                ->first();

            $newApproval = new approvedNetSales();
            $newApproval->id_rkm = $netSales->id_rkm;
            $newApproval->tanggal = now()->format('Y-m-d');
            $newApproval->status = 1;

            if (!$latestApproval) {
                $newApproval->level_status = '1';
                $newApproval->keterangan = 'Telah disetujui oleh SPV Sales';
                $newApproval->save();

                $gmUser = User::whereHas('karyawan', function ($q) {
                    $q->where('jabatan', 'GM');
                })->first();

                if ($gmUser) {
                    $dummyCommentGM = (object) [
                        'status' => 'proses',
                        'tipe' => 'Persetujuan Payment Advanced',
                        'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                        'alasan' => 'SPV Sales telah menyetujui, mohon persetujuan Anda.',
                    ];
                    $receiverId = $gmUser->id;
                    Notification::send($gmUser, new NetSalesNotification($dummyCommentGM, $path, $receiverId));
                }

                $dummyCommentSales = (object) [
                    'status' => 'proses',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => 'SPV Sales telah menyetujui pengajuan Anda.',
                ];
                $receiverId = $salesUser->id;
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $path, $receiverId));
            } elseif ($latestApproval->level_status === '1') {
                $newApproval->level_status = '2';
                $newApproval->keterangan = 'Telah disetujui oleh General Manager';
                $newApproval->save();

                $financeUser = User::whereHas('karyawan', function ($q) {
                    $q->where('jabatan', 'Finance & Accounting');
                })->first();

                if ($financeUser) {
                    $dummyCommentFinance = (object) [
                        'status' => 'proses',
                        'tipe' => 'Persetujuan Payment Advanced',
                        'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                        'alasan' => 'General Manager telah menyetujui, mohon persetujuan Anda.',
                    ];
                    $receiverId = $financeUser->id;
                    Notification::send($financeUser, new NetSalesNotification($dummyCommentFinance, $path, $receiverId));
                }

                $dummyCommentSales = (object) [
                    'status' => 'proses',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => 'General Manager telah menyetujui pengajuan Anda.',
                ];
                $receiverId = $salesUser->id;
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $path, $receiverId));
            } elseif ($latestApproval->level_status === '2') {
                $newApproval->level_status = '3';
                $newApproval->keterangan = $statusTracking;
                $newApproval->save();

                $dummyCommentSales = (object) [
                    'status' => 'disetujui',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => $statusTracking,
                ];
                $receiverId = $salesUser->id;
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $path, $receiverId));
            } elseif ($latestApproval->level_status === '3') {
                $newApproval->level_status = '3';
                $newApproval->keterangan = $statusTracking;
                $newApproval->save();

                $dummyCommentSales = (object) [
                    'status' => 'disetujui',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => $statusTracking,
                ];
                $receiverId = $salesUser->id;
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $path, $receiverId));

                return response()->json(['success' => true, 'message' => 'Data persetujuan tambahan berhasil ditambahkan dan notifikasi dikirim.']);
            }


            return response()->json(['success' => true, 'message' => 'Pengajuan berhasil disetujui dan notifikasi dikirim.']);
        } catch (\Exception $e) {
            Log::error('Error approve Payment Advanced: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem. ' . $e->getMessage()], 500);
        }
    }
}
