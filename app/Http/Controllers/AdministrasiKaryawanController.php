<?php

namespace App\Http\Controllers;

use App\Models\AdministrasiKaryawan;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdministrasiKaryawanController extends Controller
{

    public function index()
    {
        $administrasis = AdministrasiKaryawan::orderBy('dateline', 'desc')->paginate(10);
        $data = AdministrasiKaryawan::orderBy('dateline', 'desc')->get();

        $labels = [];
        $progressData = [];

        foreach ($data as $item) {
            $labels[] = $item->nama_administrasi;

            if ($item->tanggal_selesai) {
                $diff = Carbon::parse($item->dateline)
                    ->diffInDays(Carbon::parse($item->tanggal_selesai), false);

                if ($diff <= 0 || $item->status === 'selesai') {
                    $progress = 100;
                } elseif ($diff <= 3) {
                    $progress = 80;
                } elseif ($diff <= 7) {
                    $progress = 60;
                } else {
                    $progress = 0;
                }
            } else {
                $progress = 0;
            }

            $progressData[] = $progress;
        }

        return view( 'office.administrasiKaryawan.index', compact('administrasis', 'labels', 'progressData'));
    }

    public function store(Request $request)
    {
        $validasi = $request->validate([
            'nama_administrasi' => 'required|string',
            'dateline' => 'required|date',
            'keterangan' => 'nullable|string'
        ]);
    
        AdministrasiKaryawan::create($validasi);

        return back()->with('success_administrasi', 'Administrasi Karyawan berhasil dibuat.');
    }

    public function update(Request $request, string $id)
    {
        $administrasi = AdministrasiKaryawan::findOrFail($id);
        
        $updateData = [
            'nama_administrasi' => $request->nama_administrasi ?? $administrasi->nama_administrasi,
            'dateline' => $request->dateline ?? $administrasi->dateline,
            'keterangan' => $request->keterangan ?? $administrasi->keterangan,
            'status' => $request->status ?? $administrasi->status,
            'tanggal_selesai' => $request->tanggal_selesai ?? $administrasi->tanggal_selesai
        ];

        if ($request->hasFile('bukti_transfer')) {
            $path = $request->file('bukti_transfer')->store('bukti_transfer', 'public');
            $updateData['bukti_transfer'] = $path;
        }

        $administrasi->update($updateData);

        // set status
        if ($request->tanggal_selesai !== null && $administrasi->dateline < $request->tanggal_selesai && !in_array($request->status, ['selesai', 'pending'])) {
            $administrasi->status = 'terlambat';
            $administrasi->save();
        } else if ($request->tanggal_selesai !== null && $administrasi->dateline >= $request->tanggal_selesai ) {
            $administrasi->status = 'selesai';
            $administrasi->save();
        }

        return back()->with('success_administrasi', 'Administrasi Karyawan berhasil diperbaharui.');
    }

    public function destroy(string $id)
    {
        $administrasi = AdministrasiKaryawan::findOrFail($id)->delete();

        return back()->with('success_administrasi', 'Administrasi Karyawan berhasil dihapus.');
    }

    public function getData($id)
    {
        $data = AdministrasiKaryawan::findOrFail($id);

        return response()->json($data);
    }

    public function edit($id)
    {
        $administrasi = AdministrasiKaryawan::findOrFail($id);

        return view('office.administrasiKaryawan.detail', compact('administrasi'));
    }

    public function eksport(Request $request) {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = AdministrasiKaryawan::query();

        $tahun   = $request->tahun;
        $bulan   = $request->bulan;
        $quartal = $request->quartal;
        $start   = $request->start_date;
        $end     = $request->end_date;

        if ($start && $end) {
            $query->whereBetween('dateline', [$start, $end]);
        }
        elseif ($tahun && $bulan) {
            $query->whereYear('dateline', $tahun)
                ->whereMonth('dateline', $bulan);
        }
        elseif ($tahun && $quartal) {

            $range = [
                1 => [1, 3],
                2 => [4, 6],
                3 => [7, 9],
                4 => [10, 12],
            ];

            [$startMonth, $endMonth] = $range[$quartal];

            $query->whereYear('dateline', $tahun)
                ->whereBetween('dateline', [$startMonth, $endMonth]);
        }
        elseif ($tahun) {
            $query->whereYear('dateline', $tahun);
        }

        $data = $query->get();

        $pdf = Pdf::loadView('office.administrasiKaryawan.pdf', [
            'data' => $data,
            'request' => $request
        ])->setPaper('A4', 'potrait');

        return $pdf->download('AdministrasiKaryawan.pdf');
    }
}
