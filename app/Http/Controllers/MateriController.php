<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Materi;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
class MateriController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:View Materi', ['only' => ['index']]);
        $this->middleware('permission:Create Materi', ['only' => ['create','store']]);
        $this->middleware('permission:Edit Materi', ['only' => ['update','edit', 'editstatusmateri']]);
        $this->middleware('permission:Delete Materi', ['only' => ['destroy']]);
    }
    public function index(): View
    {
        $materis = Materi::latest()->paginate(5);

        return view('materi.index', compact('materis'));
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        return view('materi.create');
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate form
        $jabatan = auth()->user()->jabatan;
        // dd($request->all());
        // dd($jabatan);
        $validatedData = $this->validate($request, [
            'nama_materi' => 'required',
            'kode_materi' => 'nullable',
            'kategori_materi' => 'nullable',
            'vendor' => 'nullable',
            'durasi' => 'nullable',
            'silabus' => 'nullable|file|mimes:pdf|max:2048' // tambahkan validasi untuk file PDF
        ]);
        if($jabatan == 'Education Manager'){
            $status = 'Aktif';
        }else{
            $status = 'Nonaktif';
        }
        $materi = new Materi([
            'nama_materi' => $validatedData['nama_materi'],
            'kode_materi' => $validatedData['kode_materi'],
            'kategori_materi' => $validatedData['kategori_materi'],
            'durasi' => $validatedData['durasi'],
            'status' => $status,
            'vendor' => $validatedData['vendor']
        ]);

        if ($request->hasFile('silabus')) {  
            $file = $request->file('silabus');  
            $filename = 'silabus_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $validatedData['nama_materi']) . '.pdf'; // Sanitize filename  
            try {  
                $path = $file->storeAs('silabus', $filename, 'public');  
                $materi->silabus = $path;  
            } catch (\Exception $e) {  
                return redirect()->back()->withErrors(['silabus' => 'File upload failed: ' . $e->getMessage()]);  
            }  
        }

        $materi->save();

        return redirect()->route('materi.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }


    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        $post = Materi::findOrFail($id);

        return view('materi.show', compact('post'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        $materis = Materi::findOrFail($id);

        return view('materi.edit', compact('materis'));
    }

    public function editstatusmateri(string $id): View
    {
        $materis = Materi::findOrFail($id);

        return view('materi.editstatusmateri', compact('materis'));
    }

    /**
     * update
     *
     * @param  mixed $request
     * @param  mixed $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        // dd($request->all());

        $materi = Materi::findOrFail($id);
        if($request->nama_materi){
            $validatedData = $this->validate($request, [
                'nama_materi' => 'required',
                'kode_materi' => 'nullable',
                'kategori_materi' => 'nullable',
                'vendor' => 'nullable',
                'durasi' => 'nullable',
                'silabus' => 'nullable|file|mimes:pdf|max:2048' // tambahkan validasi untuk file PDF
            ]);
            if ($request->hasFile('silabus')) {
                // Hapus file sebelumnya jika ada
                if ($materi->silabus) {
                    Storage::disk('public')->delete($materi->silabus);
                }
                $file = $request->file('silabus');
                $filename = 'silabus_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $validatedData['nama_materi']) . '.pdf'; // Sanitize filename
                try {
                    $path = $file->storeAs('silabus', $filename, 'public');
                    $materi->silabus = $path;
                } catch (\Exception $e) {
                    return redirect()->back()->withErrors(['silabus' => 'File upload failed: ' . $e->getMessage()]);  
                }
            }            
            // $materi->update([
            //     'nama_materi' => $request->nama_materi,
            //     'kategori_materi' => $request->kategori_materi,
            //     'durasi' => $request->durasi,
            //     'kode_materi' => $request->kode_materi,
            //     'vendor' => $request->vendor,
            //     'silabus' => $path,
            // ]);
            $materi->nama_materi = $request->nama_materi;
            $materi->kode_materi = $request->kode_materi;
            $materi->kategori_materi = $request->kategori_materi;
            $materi->vendor = $request->vendor;
            $materi->durasi = $request->durasi;
            $materi->save();
        }else{
            $materi->update([
                'status' => $request->status,
                'keterangan' => $request->keterangan,
                'tipe_materi' => $request->tipe_materi,
            ]);
        }
        

        return redirect()->route('materi.index')
            ->with(['success' => 'Data Berhasil Diubah!'])
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $post = Materi::findOrFail($id);

        $post->delete();

        return redirect()->route('materi.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }

    public function getMateriById(string $id)
    {
        $post = Materi::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Materi By Id',
            'data' => $post
        ], 200);
    }
}
