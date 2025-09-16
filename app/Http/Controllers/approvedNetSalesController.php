<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerhitunganNetSales;
use App\Models\ApprovedNetSales;
use App\Models\Karyawan;
use App\Models\RKM;
use App\Models\trackingNetSales;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Notifications\NetSalesNotification;
use Illuminate\Support\Facades\Notification;

class approvedNetSalesController extends Controller
{
    public function approve(Request $request)
    {
        try {
            $id = $request->input('id_net_sales');
            $netSales = PerhitunganNetSales::with('karyawan')->find($id);

            if (!$netSales) {
                return response()->json(['error' => 'Data tidak ditemukan.'], 404);
            }

            $FA = auth()->user()->jabatan;

            if ($FA === 'Finance & Accounting') {
                $statusTracking = $request->input('status_tracking');

                if (!empty($statusTracking)) {
                    $tracking = trackingNetSales::where('id_netSales', $netSales->id)->first();
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
                ApprovedNetSales::create([
                    'id_netSales' => $netSales->id,
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
                Notification::send($salesUser, new NetSalesNotification($dummyComment, $url, $path));

                return response()->json(['success' => true, 'message' => 'Pengajuan ditolak dan notifikasi dikirim.']);
            }

            // Approval logic (I → II → III) tetap dilanjutkan seperti sebelumnya...
            $latestApproval = ApprovedNetSales::where('id_netSales', $netSales->id)
                ->orderByDesc('created_at')
                ->first();

            $newApproval = new ApprovedNetSales();
            $newApproval->id_netSales = $netSales->id;
            $newApproval->tanggal = now()->format('Y-m-d');
            $newApproval->status = 1;

            if (!$latestApproval) {
                $newApproval->level_status = 'I';
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
                    Notification::send($gmUser, new NetSalesNotification($dummyCommentGM, $url, $path));
                }

                $dummyCommentSales = (object) [
                    'status' => 'proses',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => 'SPV Sales telah menyetujui pengajuan Anda.',
                ];
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $url, $path));
            } elseif ($latestApproval->level_status === 'I') {
                $newApproval->level_status = 'II';
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
                    Notification::send($financeUser, new NetSalesNotification($dummyCommentFinance, $url, $path));
                }

                $dummyCommentSales = (object) [
                    'status' => 'proses',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => 'General Manager telah menyetujui pengajuan Anda.',
                ];
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $url, $path));
            } elseif ($latestApproval->level_status === 'II') {
                $newApproval->level_status = 'III';
                $newApproval->keterangan = $statusTracking;
                $newApproval->save();

                $dummyCommentSales = (object) [
                    'status' => 'disetujui',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => $statusTracking,
                ];
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $url, $path));
            } elseif ($latestApproval->level_status === 'III') {
                $newApproval->level_status = 'III';
                $newApproval->keterangan = $statusTracking;
                $newApproval->save();

                $dummyCommentSales = (object) [
                    'status' => 'disetujui',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => $statusTracking,
                ];
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $url, $path));

                return response()->json(['success' => true, 'message' => 'Data persetujuan tambahan berhasil ditambahkan dan notifikasi dikirim.']);
            }


            return response()->json(['success' => true, 'message' => 'Pengajuan berhasil disetujui dan notifikasi dikirim.']);
        } catch (\Exception $e) {
            Log::error('Error approve Payment Advanced: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem. ' . $e->getMessage()], 500);
        }
    }
}
