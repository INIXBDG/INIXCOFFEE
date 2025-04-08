<?php

namespace App\Http\Controllers;

use App\Models\jabatan;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use App\Models\Materi;
use Spatie\Permission\Models\Role;

class jabatanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(): View
    {
        $materis = jabatan::latest()->paginate(5);

        return view('jabatan.index', compact('materis'));
    }

    /**
     * create
     *
     * @return View
     */
    public function create(): View
    {
        return view('jabatan.create');
    }

    /**
     * store
     *
     * @param  mixed $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        //validate form
        $this->validate($request, [
            'nama_jabatan'     => 'required',
        ]);

        jabatan::create([
            'nama_jabatan'     => $request->nama_jabatan,
        ]);

        Role::create([
            'name' => $request->nama_jabatan,
        ]);

        return redirect()->route('jabatan.index')->with(['success' => 'Data Berhasil Disimpan!']);
    }

    /**
     * show
     *
     * @param  mixed $id
     * @return View
     */
    public function show(string $id): View
    {
        $post = jabatan::findOrFail($id);

        return view('jabatan.show', compact('post'));
    }

    /**
     * edit
     *
     * @param  mixed $id
     * @return View
     */
    public function edit(string $id): View
    {
        $materis = jabatan::findOrFail($id);

        return view('jabatan.edit', compact('materis'));
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
        $this->validate($request, [
            'nama_jabatan'     => 'required',
        ]);
        $post = jabatan::findOrFail($id);

            $post->update([
                'nama_jabatan'     => $request->nama_jabatan,
            ]);
        $data = Role::where('name', $post->nama_jabatan)->first();
            $data->update([
                'name'     => $request->nama_jabatan,
            ]);

        return redirect()->route('jabatan.index')->with(['success' => 'Data Berhasil Diubah!']);
    }

    /**
     * destroy
     *
     * @param  mixed $post
     * @return void
     */
    public function destroy($id): RedirectResponse
    {
        $post = jabatan::findOrFail($id);

        $post->delete();

        return redirect()->route('jabatan.index')->with(['success' => 'Data Berhasil Dihapus!']);
    }
}
