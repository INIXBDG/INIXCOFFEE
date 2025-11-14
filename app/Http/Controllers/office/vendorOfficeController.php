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
        // Mengambil segmen ke-3 dari URI
        $itemValue = $request->segment(3);

        switch ($itemValue) {
        case "souvenir":
            $data = vendorSouvenir::orderBy('created_at', 'desc')
            ->paginate(10);
            break;
        case "makansiang":
            $data = vendorMakansiang::orderBy('created_at', 'desc')
            ->paginate(10);
            break;
        case "coffeebreak":
            $data = vendorCoffeeBreak::orderBy('created_at', 'desc')
            ->paginate(10);
            break;
        case "bengkel": 
            $data = vendorBengkel::orderBy('created_at', 'desc')
            ->paginate(10);
            break;
        default:
            echo "Ini adalah hari biasa.";
        };

        return view('office.vendor.index', compact('data', 'itemValue'));
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $request->validate([
            'nama' => 'required',
        ]);
        $itemValue = $request->segment(3);

        switch ($itemValue) {
        case "souvenir":
            $data = vendorSouvenir::create($request->all());
            break;
        case "makansiang":
            $data = vendorMakansiang::create($request->all());
            break;
        case "coffeebreak":
            $data = vendorCoffeeBreak::create($request->all());
            break;
        case "bengkel": 
            $data = vendorBengkel::create($request->all());
            break;
        default:
            echo "Ini adalah hari biasa.";
        };

        return redirect()->route('office.vendor.'. $itemValue . '.index')
            ->with('success', 'Vendor created successfully.');
    }

    public function destroy($id, Request $request)
    {
        $itemValue = $request->segment(3);
        // dd($itemValue);
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
            echo "Ini adalah hari biasa.";
        };

        $data->delete();

        return redirect()->route('office.vendor.'. $itemValue . '.index')
            ->with('success', 'Vendor deleted successfully');
    }

}
