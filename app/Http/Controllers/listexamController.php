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
        ]);

        listexam::create($request->all());

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
        ]);

        $exam = ListExam::findOrFail($id);
        $exam->update($request->all());

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
