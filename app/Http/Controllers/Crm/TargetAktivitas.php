<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\TargetActivity;
use App\Models\User;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TargetAktivitas extends Controller
{

    public function index()
    {
        if (!in_array(Auth::user()->jabatan, ['GM', 'SPV Sales', 'Adm Sales'])) {
            return abort(403, 'Kamu tidak punya akses ke halaman ini.');
        }
        
        $target = TargetActivity::all();
        $user = User::where('jabatan', 'Sales')
            ->where('status_akun', '1')
            ->get();
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
            'Meet' => 'required|integer',
            'Incharge' => 'required|integer',
            'PA' => 'required|integer',
            'PI' => 'required|integer',
            'DB' => 'required|integer',
            'Telemarketing' => 'required|integer',
            'FormM' => 'required|integer',
            'FormK' => 'required|integer',
        ]);

        $data = new TargetActivity();
        $data->id_sales = $validated['id_sales'];
        $data->Contact = $validated['Contact'];
        $data->Call = $validated['Call'];
        $data->Visit = $validated['Visit'];
        $data->Email = $validated['Email'];
        $data->Meet = $validated['Meet'];
        $data->Incharge = $validated['Incharge'];
        $data->PA = $validated['PA'];
        $data->PI = $validated['PI'];
        $data->DB = $validated['DB'];
        $data->Telemarketing = $validated['Telemarketing'];
        $data->FormM = $validated['FormM'];
        $data->FormK = $validated['FormK'];
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
            'Meet' => 'required|integer',
            'Incharge' => 'required|integer',
            'PA' => 'required|integer',
            'PI' => 'required|integer',
            'DB' => 'required|integer',
            'Telemarketing' => 'required|integer',
            'FormM' => 'required|integer',
            'FormK' => 'required|integer',
        ]);

        $data = TargetActivity::findOrFail($id);
        $data->id_sales = $validated['id_sales'];
        $data->Contact = $validated['Contact'];
        $data->Call = $validated['Call'];
        $data->Visit = $validated['Visit'];
        $data->Email = $validated['Email'];
        $data->Meet = $validated['Meet'];
        $data->Incharge = $validated['Incharge'];
        $data->PA = $validated['PA'];
        $data->PI = $validated['PI'];
        $data->DB = $validated['DB'];
        $data->Telemarketing = $validated['Telemarketing'];
        $data->FormM = $validated['FormM'];
        $data->FormK = $validated['FormK'];
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
