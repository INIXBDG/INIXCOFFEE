<?php

namespace App\Http\Controllers\office;

use App\Http\Controllers\Controller;
use App\Models\vendorBengkel;
use App\Models\vendorCoffeeBreak;
use App\Models\vendorMakansiang;
use App\Models\vendorSouvenir;
use Illuminate\Http\Request;

class vendorOfficeController extends Controller
{
    public function index(Request $request)
    {
        $itemValue = $request->segment(3);

        switch ($itemValue) {
            case "souvenir":
                $data = vendorSouvenir::orderBy('created_at', 'desc')->paginate(10);
                break;
            case "makansiang":
                $data = vendorMakansiang::orderBy('created_at', 'desc')->paginate(10);
                break;
            case "coffeebreak":
                $data = vendorCoffeeBreak::orderBy('created_at', 'desc')->paginate(10);
                break;
            case "bengkel":
                $data = vendorBengkel::orderBy('created_at', 'desc')->paginate(10);
                break;
            default:
                abort(404);
        }

        return view('office.vendor.index', compact('data', 'itemValue'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'keterangan' => 'nullable|string',
        ]);

        $itemValue = $request->segment(3);

        // Upload foto
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('vendor_foto', 'public');
        }

        $payload = [
            'nama' => $request->nama,
            'keterangan' => $request->keterangan,
            'foto' => $fotoPath,
        ];

        switch ($itemValue) {
            case "souvenir":
                vendorSouvenir::create($payload);
                break;
            case "makansiang":
                vendorMakansiang::create($payload);
                break;
            case "coffeebreak":
                vendorCoffeeBreak::create($payload);
                break;
            case "bengkel":
                vendorBengkel::create($payload);
                break;
            default:
                abort(404);
        }

        return redirect()->route('office.vendor.' . $itemValue . '.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function destroy($id, Request $request)
    {
        $itemValue = $request->segment(3);

        switch ($itemValue) {
            case "souvenir":
                $data = vendorSouvenir::findOrFail($id);
                break;
            case "makansiang":
                $data = vendorMakansiang::findOrFail($id);
                break;
            case "coffeebreak":
                $data = vendorCoffeeBreak::findOrFail($id);
                break;
            case "bengkel":
                $data = vendorBengkel::findOrFail($id);
                break;
            default:
                abort(404);
        }

        $data->delete();

        return redirect()->route('office.vendor.' . $itemValue . '.index')
            ->with('success', 'Vendor deleted successfully');
    }
}
