<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\listexam;
use App\Models\provider;
use App\Models\vendor;

class listexamController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index()
    {
        $exams = listexam::all();
        return view('listexams.index', compact('exams'));
    }

    public function getListExam()
    {
        $data = listexam::get();
            return response()->json([
                'success' => true,
                'message' => 'List Registrasi',
                'data' => $data,
            ]);
        // return view('exam.index');
    }

    public function create()
    {
        $provider = provider::get();
        $vendor = vendor::get();
        return view('listexams.create' , compact('provider', 'vendor'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'provider' => 'required',
            'kode_exam' => 'required',
            'nama_exam' => 'required',
            'vendor' => 'required',
            'mata_uang' => 'required|string|in:Rupiah,Dollar,Poundsterling,Euro,Franc Swiss',
            'valid_until' => 'nullable|date',
            'harga' => 'required',
            'estimasi_durasi_booking' => 'string|max:255',
        ]);

        $harga            = $request->harga;
        // $kurs             = (float) str_replace('.', '', $request->kurs ?? 0);
        // $kursDollar       = (float) str_replace('.', '', $request->kurs_dollar);
        // $biayaAdmin       = (float) str_replace('.', '', $request->biaya_admin);

        // $totalHarga = 0;
        // switch ($request->mata_uang) {
        //     case 'Rupiah':
        //     case 'Dollar':
        //         $totalHarga = ($harga + $biayaAdmin) * $kursDollar;
        //         break;
        //     case 'Poundsterling':
        //     case 'Euro':
        //     case 'Franc Swiss':
        //         $totalHarga = ($harga * $kurs) + ($biayaAdmin * $kursDollar);
        //         break;
        // }

        listexam::create([
            'provider' => $request->provider,
            'kode_exam' => $request->kode_exam,
            'nama_exam' => $request->nama_exam,
            'vendor' => $request->vendor,
            'estimasi_durasi_booking' => $request->estimasi_durasi_booking ?? null,
            'note' => $request->note ?? null,
            'harga_exam' => $harga ?? null,
            'valid_until' => $request->valid_until ?? null,
            'mata_uang' => $request->mata_uang ?? null,
            // 'harga' => $harga ?? null,
            // 'kurs' => $kurs ?? null,
            // 'biaya_admin' => $biayaAdmin ?? null,
            // 'kurs_dollar' => $kursDollar ?? null,
        ]);

        return redirect()->route('listexams.index')
            ->with('success', 'Exam created successfully.');
    }

    public function show(listexam $exam)
    {
        return view('listexams.show', compact('exam'));
    }

    public function edit($id)
    {
        $exam = ListExam::findOrFail($id);
        return view('listexams.edit', compact('exam'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'provider' => 'required|string|max:255',
            'nama_exam' => 'required|string|max:255',
            'kode_exam' => 'required|string|max:255',
            'vendor' => 'required|string|max:255',
            'harga' => 'required',
            'estimasi_durasi_booking' => 'string|max:255',
        ]);

        $exam = ListExam::findOrFail($id);

        $harga            = $request->harga;
        // $kurs             = (float) str_replace('.', '', $request->kurs ?? 0);
        // $kursDollar       = (float) str_replace('.', '', $request->kurs_dollar);
        // $biayaAdmin       = (float) str_replace('.', '', $request->biaya_admin);

        // $totalHarga = 0;
        // switch ($request->mata_uang) {
        //     case 'Rupiah':
        //     case 'Dollar':
        //         $totalHarga = ($harga + $biayaAdmin) * $kursDollar;
        //         break;
        //     case 'Poundsterling':
        //     case 'Euro':
        //     case 'Franc Swiss':
        //         $totalHarga = ($harga * $kurs) + ($biayaAdmin * $kursDollar);
        //         break;
        // }

        $exam->update([
            'provider' => $request->provider ?? $exam->provider,
            'kode_exam' => $request->kode_exam ?? $exam->kode_exam,
            'nama_exam' => $request->nama_exam ?? $exam->nama_exam,
            'vendor' => $request->vendor ?? $exam->vendor,
            'estimasi_durasi_booking' => $request->estimasi_durasi_booking ?? $exam->estimasi_durasi_booking,
            'note' => $request->note ?? $exam->note,
            'harga_exam' => $harga ?? $exam->harga_exam,
            'valid_until' => $request->valid_until ?? $exam->valid_until,
            'mata_uang' => $request->mata_uang ?? $exam->mata_uang,
            // 'harga' => $harga ?? $exam->harga,
            // 'kurs' => $kurs ?? $exam->kurs,
            // 'biaya_admin' => $biayaAdmin ?? $exam->biaya_admin,
            // 'kurs_dollar' => $kursDollar ?? $exam->kurs_dollar,
        ]);

        return redirect()->route('listexams.index')->with('success', 'List Exam updated successfully');
    }


    public function destroy($id)
    {
        $exam = ListExam::findOrFail($id);

        $exam->delete();

        return redirect()->route('listexams.index')
            ->with('success', 'Exam deleted successfully');
    }


    public function storeProviders(Request $request)
    {
        $provider = Provider::create(['nama' => $request->nama]);
        return response()->json($provider);
    }

    public function storeVendor(Request $request)
    {
        $vendor = Vendor::create(['nama' => $request->nama]);
        return response()->json($vendor);
    }
}
