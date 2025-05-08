<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PerhitunganNetSales;
use App\Models\ApprovedNetSales;
use App\Models\Karyawan;
use App\Models\RKM;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use App\Notifications\NetSalesNotification;
use Illuminate\Support\Facades\Notification;

class ApprovedNetSalesController extends Controller
{
    public function approve(Request $request)
    {
        try {
            $id = $request->input('id_net_sales');
            $netSales = PerhitunganNetSales::with('karyawan')->find($id);
            if (!$netSales) {
                return response()->json(['error' => 'Data tidak ditemukan.'], 404);
            }

            $rkm = RKM::findOrFail($netSales->id_rkm);

            // Ambil user sales berdasarkan kode_karyawan di Karyawan
            $salesUser = User::whereHas('karyawan', function ($q) use ($rkm) {
                $q->where('kode_karyawan', $rkm->sales_key);
            })->first();

            if (!$salesUser) {
                return response()->json(['error' => 'User sales tidak ditemukan.'], 404);
            }

            $url = route('paymantAdvance.index');
            $path = request()->path();

            // Cek apakah ada keterangan (penolakan)
            $keteranganInput = $request->input('keterangan');
            if (!empty($keteranganInput)) {
                // Simpan record penolakan
                ApprovedNetSales::create([
                    'id_netSales' => $netSales->id,
                    'tanggal' => now()->format('Y-m-d'),
                    'status' => 0,
                    'keterangan' => "Ditolak: " . $keteranganInput,
                    'level_status' => null,
                ]);

                // Kirim notifikasi penolakan ke sales
                $dummyComment = (object) [
                    'status' => 'ditolak',
                    'tipe' => 'Pengajuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => $keteranganInput,
                ];
                Notification::send($salesUser, new NetSalesNotification($dummyComment, $url, $path));

                return response()->json(['success' => true, 'message' => 'Pengajuan ditolak dan notifikasi dikirim.']);
            }

            // Ambil approval terakhir
            $latestApproval = ApprovedNetSales::where('id_netSales', $netSales->id)
                ->orderByDesc('created_at')
                ->first();

            $newApproval = new ApprovedNetSales();
            $newApproval->id_netSales = $netSales->id;
            $newApproval->tanggal = now()->format('Y-m-d');
            $newApproval->status = 1;

            // Tentukan level approval dan kirim notifikasi sesuai level
            if (!$latestApproval) {
                // Level I - SPV Sales approve
                $newApproval->level_status = 'I';
                $newApproval->keterangan = 'Telah disetujui oleh SPV Sales';
                $newApproval->save();

                // Kirim notifikasi ke GM dan sales
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
                // Level II - GM approve
                $newApproval->level_status = 'II';
                $newApproval->keterangan = 'Telah disetujui oleh General Manager';
                $newApproval->save();

                // Kirim notifikasi ke Finance dan sales
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
                // Level III - Finance approve
                $newApproval->level_status = 'III';
                $newApproval->keterangan = 'Telah disetujui oleh Finance & Accounting';
                $newApproval->save();

                // Kirim notifikasi ke sales bahwa pengajuan sudah final disetujui
                $dummyCommentSales = (object) [
                    'status' => 'disetujui',
                    'tipe' => 'Persetujuan Payment Advanced',
                    'nama_karyawan' => $salesUser->karyawan->nama_lengkap,
                    'alasan' => 'Finance & Accounting telah menyetujui pengajuan Anda.',
                ];
                Notification::send($salesUser, new NetSalesNotification($dummyCommentSales, $url, $path));
            }

            return response()->json(['success' => true, 'message' => 'Pengajuan berhasil disetujui dan notifikasi dikirim.']);

        } catch (\Exception $e) {
            Log::error('Error approve Payment Advanced: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan sistem. ' . $e->getMessage()], 500);
        }
    }

}
