<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\TargetActivity;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;

class TargetAktivitas extends Controller
{

    public function index(){
        $target = TargetActivity::all();
        $user = User::where('jabatan', 'Sales')->get();
        return view('crm.target.index', compact('target', 'user'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_sales' => 'required',
            'Contact' => 'required|integer',
            'Call' => 'required|integer',
            'Visit' => 'required|integer',
            'Email' => 'required|integer',
        ]);

        $data = new TargetActivity();
        $data->id_sales = $validated['id_sales'];
        $data->Contact = $validated['Contact'];
        $data->Call = $validated['Call'];
        $data->Visit = $validated['Visit'];
        $data->Email = $validated['Email'];
        $data->save();

        return redirect()->back()->with('success', 'Target activity berhasil disimpan.');
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'id_sales' => 'required',
            'Contact' => 'required|integer',
            'Call' => 'required|integer',
            'Visit' => 'required|integer',
            'Email' => 'required|integer',
        ]);

        $data = TargetActivity::findOrFail($id);
        $data->id_sales = $validated['id_sales'];
        $data->Contact = $validated['Contact'];
        $data->Call = $validated['Call'];
        $data->Visit = $validated['Visit'];
        $data->Email = $validated['Email'];
        $data->save();

        return redirect()->back()->with('success', 'Target activity berhasil diperbarui.');
    }

    public function delete($id)
    {
        $data = TargetActivity::findOrFail($id);
        $data->delete();

        return redirect()->back()->with('success', 'Target activity berhasil dihapus.');
    }
}
