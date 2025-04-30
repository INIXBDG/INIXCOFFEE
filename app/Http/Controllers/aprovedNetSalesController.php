<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\perhitunganNetSales;
use App\Models\aprovedNetSales;
use App\Models\karyawan;
use App\Models\RKM;
use App\Models\User;
use App\Notifications\CommentNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class AprovedNetSalesController extends Controller
{
    public function aproved(Request $request)
    {
        $id = $request->input('id_net_sales');
        $netSales = perhitunganNetSales::where('id', $id)->first();
        $rkm = RKM::findOrFail($netSales->id_rkm);
        $sales = $rkm->sales_key;
        $SPVSales = karyawan::where('jabatan', 'SPV Sales')->first();
        
        $users = [];
        $users[] = $sales;
        if (!$netSales) {
            return response()->json(['error' => 'Data tidak ditemukan.'], 404);
        }
    
        $keteranganInput = $request->input("keterangan");
        $jabatan = $request->input("jabatan");
    
        $jabatanNormalized = $jabatan;
        if ($jabatan === "GM") {
            $jabatanNormalized = "General Manager";
            $GM = karyawan::where('jabatan', 'GM')->first(); 
            $users[] = $GM->kode_karyawan;
            

        } elseif ($jabatan === "Finance & Accounting") {
            $jabatanNormalized = "Finance & Accounting";
            $Finance = karyawan::where('jabatan', 'Finance & Accounting')->first();
            $users[] = $Finance->kode_karyawan;
        }
        $url = route('paymantAdvance.index');
        $path = request()->path();
        // dd($users);
        $users = User::whereHas('karyawan', function ($query) use ($users) {
            $query->whereIn('kode_karyawan', $users);
        })->get();
        if (!empty($keteranganInput)) {
            $addAproved = new aprovedNetSales();
            $addAproved->id_netSales = $netSales->id;
            $addAproved->tanggal = now()->format('Y-m-d');
            $addAproved->status = 0;
            $addAproved->keterangan = $jabatanNormalized . " : " . $keteranganInput;
            $addAproved->level_status = null;
            $addAproved->save();
    
            $dummyComment = (object) [
                'karyawan_key' => $salesUser->karyawan->id ?? null,
                'content' => "Pengajuan sales Anda ditolak oleh $jabatanNormalized.",
                'materi_key' => null,
                'rkm_key' => $netSales->id,
            ];

            Notification::send($users, new CommentNotification($dummyComment, $url, $path));
    
            return response()->json(['success' => true, 'message' => 'Data ditolak dan notifikasi dikirim.']);
        }
    
        $latestApproval = aprovedNetSales::where('id_netSales', $netSales->id)->orderByDesc('created_at')->first();
    
        $addNewApproved = new aprovedNetSales();
        $addNewApproved->id_netSales = $netSales->id;
        $addNewApproved->tanggal = now()->format('Y-m-d');
        $addNewApproved->status = 1;
    
        if ($latestApproval) {
            if ($latestApproval->level_status === "I") {
                $addNewApproved->level_status = "II";
                $addNewApproved->keterangan = "Telah disetujui oleh General Manager";
    
                $financeUser = User::where('jabatan', 'Finance & Accounting')->first();
                if ($financeUser) {
                    $dummyComment = (object) [
                        'karyawan_key' => $financeUser->karyawan->id ?? null,
                        'content' => "General Manager telah menyetujui pengajuan sales, mohon untuk Anda melakukan persetujuan.",
                        'materi_key' => null,
                        'rkm_key' => $netSales->id,
                    ];
    
                    Notification::send($financeUser, new CommentNotification($dummyComment, $url, $path));
                }
    
                $dummyComment = (object) [
                    'karyawan_key' => $salesUser->karyawan->id ?? null,
                    'content' => "General Manager telah menyetujui pengajuan sales.",
                    'materi_key' => null,
                    'rkm_key' => $netSales->id,
                ];
    
                Notification::send($salesUser, new CommentNotification($dummyComment, $url, $path));
            }
    
            elseif ($latestApproval->level_status === "II") {
                $addNewApproved->level_status = "III";
                $addNewApproved->keterangan = "Telah disetujui oleh Finance & Accounting";
    
                $dummyComment = (object) [
                    'karyawan_key' => $salesUser->karyawan->id ?? null,
                    'content' => "Finance & Accounting telah menyetujui pengajuan Payment Advance Anda.",
                    'materi_key' => null,
                    'rkm_key' => $netSales->id,
                ];
    
                Notification::send($salesUser, new CommentNotification($dummyComment, $url, $path));
            }
    
            elseif (empty($latestApproval->level_status)) {
                $addNewApproved->level_status = "I";
                $addNewApproved->keterangan = "Telah disetujui oleh SPV Sales";
    
                $gmUser = User::where('jabatan', 'GM')->first();
                if ($gmUser) {
                    $dummyComment = (object) [
                        'karyawan_key' => $gmUser->karyawan->id ?? null,
                        'content' => "SPV Sales telah menyetujui pengajuan sales, mohon untuk Anda melakukan persetujuan.",
                        'materi_key' => null,
                        'rkm_key' => $netSales->id,
                    ];
    
                    Notification::send($gmUser, new CommentNotification($dummyComment, $url, $path));
                }
    
                $dummyComment = (object) [
                    'karyawan_key' => $salesUser->karyawan->id ?? null,
                    'content' => "SPV Sales telah menyetujui pengajuan sales.",
                    'materi_key' => null,
                    'rkm_key' => $netSales->id,
                ];
    
                Notification::send($salesUser, new CommentNotification($dummyComment, $url, $path));
            }
        } else {
            $addNewApproved->level_status = "I";
            $addNewApproved->keterangan = "Telah disetujui oleh SPV Sales";
    
            $gmUser = User::where('jabatan', 'GM')->first();
            if ($gmUser) {
                $dummyComment = (object) [
                    'karyawan_key' => $gmUser->karyawan->id ?? null,
                    'content' => "SPV Sales telah menyetujui pengajuan sales, mohon untuk Anda melakukan persetujuan.",
                    'materi_key' => null,
                    'rkm_key' => $netSales->id,
                ];
    
                Notification::send($gmUser, new CommentNotification($dummyComment, $url, $path));
            }
    
            $dummyComment = (object) [
                'karyawan_key' => $salesUser->karyawan->id ?? null,
                'content' => "SPV Sales telah menyetujui pengajuan sales.",
                'materi_key' => null,
                'rkm_key' => $netSales->id,
            ];
    
            Notification::send($salesUser, new CommentNotification($dummyComment, $url, $path));
        }
    
        $addNewApproved->save();
    
        return response()->json(['success' => true, 'message' => 'Data berhasil disetujui dan notifikasi dikirim.']);
    }
    
}
