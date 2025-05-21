<?php

namespace App\Http\Controllers;

use App\Models\catatansouvenir;
use App\Models\souvenir;
use App\Models\RKM;
use App\Models\souvenirinhouse;
use App\Models\souvenirpeserta;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
// use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Facades\Image;

class SouvenirController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Souvenir', ['only' => ['index']]);
        $this->middleware('permission:Create Souvenir', ['only' => ['create','store']]);
        $this->middleware('permission:Edit Souvenir', ['only' => ['update','edit', 'editstok']]);
        $this->middleware('permission:Souvenir RKM', ['only' => ['createSouvenirInhouse','storeSouvenirInhouse', 'updateSouvenirInhouse']]);
    }
    public function index()
    {
        return view('souvenir.index');
    }
    public function getSouvenir()
    {
        $souvenirs = Souvenir::all();

        // Iterasi melalui setiap souvenir dan ubah blob_foto menjadi base64
        $souvenirsWithBase64 = $souvenirs->map(function($souvenir) {
            if (!is_null($souvenir->blob_foto)) {
                $souvenir->base64_foto = base64_encode($souvenir->blob_foto);
            } else {
                $souvenir->base64_foto = null;
            }
            // Sembunyikan kolom blob_foto
            return $souvenir->makeHidden('blob_foto');
        });

        // Konversi koleksi menjadi array untuk memastikan encoding JSON
        $souvenirsArray = $souvenirsWithBase64->toArray();

        return response()->json([
            'success' => true,
            'message' => 'List Souvenir',
            'data' => $souvenirsArray,
        ], 200, ['Content-type' => 'application/json; charset=utf-8']);
    }
    public function getSouvenirPeserta()
    {
        $souvenirs = souvenirpeserta::with('souvenir', 'rkm', 'rkm.materi', 'rkm.perusahaan', 'regist.peserta')->get();
           
        return response()->json([
            'success' => true,
            'message' => 'List Souvenir',
            'data' => $souvenirs
        ]);
    }
    public function create(): View
    {
        return view('souvenir.create');
    }
    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        // Debugging
        // dd($request->all());

        // Validate form
        $this->validate($request, [
            'nama_souvenir'     => 'required',
            'harga'             => 'required',
            'stok'              => 'required',
            'foto'              => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
            'min_harga_pelatihan'=> 'required',
            'max_harga_pelatihan'=> 'required',
        ]);

        // Remove dots from price inputs
        $harga = str_replace('.', '', $request->harga);
        $min_harga_pelatihan = str_replace('.', '', $request->min_harga_pelatihan);
        $max_harga_pelatihan = str_replace('.', '', $request->max_harga_pelatihan);

        // Initialize the foto variable
        $foto = null;
        $fileContent = null;

        // Check if a file was uploaded
        if ($request->hasFile('foto')) {
            $file = $request->file('foto');
            $extension = $file->getClientOriginalExtension();
            $filename = $request->nama_souvenir . '.' . $extension;

            // Create an Intervention Image instance
            $image = Image::make($file);

            // Resize or optimize the image as needed
            $image->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
            });

            // Define the storage path
            $path = storage_path('app/public/souvenir/' . $filename);

            // Save the optimized image to storage
            try {
                $image->save($path);
            } catch (\Exception $e) {
                return back()->withErrors(['foto' => 'Gagal menyimpan gambar: ' . $e->getMessage()]);
            }

            // Convert image to blob and limit size if necessary
            $image = $image->encode($extension, 70); // Adjust quality (1-100)
            $fileContent = $image->getEncoded();

            // Ensure blob content is within size limit (150 KB)
            if (strlen($fileContent) > (150 * 1024)) { // 150 KB limit
                return back()->withErrors(['foto' => 'Gambar terlalu besar setelah dikompresi. Maksimal ukuran adalah 300KB']);
            }
        }

        // Save to database
        Souvenir::create([
            'nama_souvenir' => $request->nama_souvenir,
            'harga' => $harga,
            'stok' => $request->stok,
            'foto' => $filename,
            'blob_foto' => $fileContent,
            'min_harga_pelatihan' => $min_harga_pelatihan,
            'max_harga_pelatihan' => $max_harga_pelatihan,
        ]);

        return redirect()->route('souvenir.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        $post = souvenir::with('catatan')->findOrFail($id);

        return view('souvenir.show', compact('post'));
    }
    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        $souvenir = souvenir::findOrFail($id);

        return view('souvenir.edit', compact('souvenir'));
    }
    public function editStok(string $id): View
    {
        $souvenir = souvenir::findOrFail($id);

        return view('souvenir.editstok', compact('souvenir'));
    }
    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Debugging
        // dd($request->all());
        $stok = $request->stok + $request->new_stok;
        // return $stok;
        if(!$request->nama_souvenir){

            $post = souvenir::findOrFail($id);
            catatansouvenir::create([
                'id_souvenir'     => $id,
                'catatan'     => $request->catatan,
                'stok_perubahan'     => $request->new_stok,
                'stok_terakhir'     => $post->stok,
                'stok_terbaru'     => $stok,
            ]);
            $post->update([
                'stok'     => $stok,
            ]);

        }else{
            // Remove dots from price inputs
            $harga = str_replace('.', '', $request->harga);
            $min_harga_pelatihan = str_replace('.', '', $request->min_harga_pelatihan);
            $max_harga_pelatihan = str_replace('.', '', $request->max_harga_pelatihan);

            // Calculate new stock
            $stok = $request->stok + $request->new_stok;

            // Initialize the foto variable
            $foto = null;
            $fileContent = null;

            // Check if a file was uploaded
            if ($request->hasFile('foto')) {
                $file = $request->file('foto');
                $extension = $file->getClientOriginalExtension();
                $filename = $request->nama_souvenir . '.' . $extension;

                // Create an Intervention Image instance
                $image = Image::make($file);

                // Resize or optimize the image as needed
                $image->resize(800, null, function ($constraint) {
                    $constraint->aspectRatio();
                });

                // Define the storage path
                $path = storage_path('app/public/souvenir/' . $filename);

                // Save the optimized image to storage
                try {
                    $image->save($path);
                } catch (\Exception $e) {
                    return back()->withErrors(['foto' => 'Gagal menyimpan gambar: ' . $e->getMessage()]);
                }

                // Convert image to blob and limit size if necessary
                $image = $image->encode($extension, 70); // Adjust quality (1-100)
                $fileContent = $image->getEncoded();

                // Ensure blob content is within size limit
                if (strlen($fileContent) > (150 * 1024)) { // 150 KB limit
                    return back()->withErrors(['foto' => 'Gambar terlalu besar setelah dikompresi. Maksimal ukuran adalah 150KB']);
                }

                // Update foto variable
                $foto = $filename;
            }
            // Find the souvenir by ID
            $souvenir = souvenir::findOrFail($id);

            // Update the souvenir data
            $souvenir->update([
                'nama_souvenir' => $request->nama_souvenir,
                'harga' => $harga,
                'stok' => $stok,
                'foto' => $foto ?? $souvenir->foto, // Keep old foto if new one is not uploaded
                'blob_foto' => $fileContent ?? $souvenir->blob_foto, // Keep old blob if new one is not uploaded
                'min_harga_pelatihan' => $min_harga_pelatihan,
                'max_harga_pelatihan' => $max_harga_pelatihan,
            ]);

            // Record stock change in catatansouvenir table
            catatansouvenir::create([
                'id_souvenir'     => $id,
                'catatan'         => $request->catatan,
                'stok_perubahan'  => $request->new_stok,
                'stok_terakhir'   => $stok,
            ]);
        }

        

        return redirect()->route('souvenir.index')->with(['success' => 'Data Berhasil Diperbarui!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $post = souvenir::findOrFail($id);

        $post->delete();

        return redirect()->route('souvenir.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
    public function createSouvenirInhouse($id): View
    {
        $souvenir = souvenirinhouse::where('id_rkm', $id)->first();
        if($souvenir){
                // $souvenirs = souvenir::get();
            return view('souvenirinhouse.edit', compact('souvenir', 'id'));
            }else{
                // $souvenir = souvenir::get();
            return view('souvenirinhouse.create', compact('id'));
        }
        // $rkm = RKM::where('id', $id)->first();
    }
    public function storeSouvenirInhouse(Request $request): RedirectResponse
    {
        // dd($request->all());

        souvenirinhouse::create([
            'nama_souvenir'     => $request->nama_souvenir,
            'id_rkm'     => $request->id_rkm,

        ]);

        return redirect()->route('rkm.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }
    public function updateSouvenirInhouse($id, Request $request)
    {
        $post = souvenirinhouse::findOrFail($id);
        // return $request->all();
        $post->update([
            'nama_souvenir'     => $request->nama_souvenir,
            'id_rkm'     => $request->id_rkm,
        ]);

        return redirect()->route('rkm.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }


}

