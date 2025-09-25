<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Models\lokasi;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'lokasi' => 'required|string',
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $lokasi = lokasi::create($validated);

        return back();
    }

    public function update(Request $request)
    {

        $lokasi = lokasi::where('id', $request->id)->first();
        $lokasi->lokasi = $request->lokasi;
        $lokasi->latitude = $request->latitude;
        $lokasi->longitude = $request->longitude;
        $lokasi->save();

        return back();
    }

    public function delete($id)
    {
        $lokasi = lokasi::findOrFail($id);
        $lokasi->delete();
        return back();
    }

    public function index()
    {
        $lokasis = lokasi::all();
        return view('crm.lokasi.index', compact('lokasis'));
    }
}
