<?php

namespace App\Http\Controllers;

use App\Models\target;
use App\Models\User;
use Illuminate\Http\Request;

class TargetController extends Controller
{
    public function index()
    {

        // return $post;
        return view('target.index');
    }

    public function getTarget()
    {
        $post = target::get();

        return response()->json([
            'success' => true,
            'message' => 'List Souvenir',
            'data' => $post,
        ]);
    }
    public function create()
    {
        // Fetch users with a non-null id_sales and load the 'karyawan' relationship
        $user = User::with('karyawan')
                    ->where('jabatan', 'Sales')
                    ->whereNotNull('id_sales') // Filter to only include records with a non-null id_sales
                    ->get();

        return view('target.create', compact('user'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $this->validate($request, [
            'objek' => 'required',
            'quartal' => 'required',
            'tahun' => 'required',
            'target' => 'required',
        ]);

        // Check if a record with the same 'objek', 'quartal', and 'tahun' already exists
        $existingTarget = target::where('objek', $request->objek)
            ->where('quartal', $request->quartal)
            ->where('tahun', $request->tahun)
            ->first();

        if ($existingTarget) {
            // Redirect back with an error message if a duplicate is found
            return redirect()->back()->withErrors(['duplicate' => 'Data dengan objek, quartal, dan tahun yang sama sudah ada!'])->withInput();
        }

        // Create a new record if no duplicate exists
        target::create([
            'objek' => $request->objek,
            'quartal' => $request->quartal,
            'tahun' => $request->tahun,
            'target' => $request->target,
        ]);

        // Redirect to the index route with a success message
        return redirect()->route('target.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    public function destroy($id)
    {
        $post = target::findOrFail($id);

        $post->delete();

        return redirect()->route('target.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }


}
