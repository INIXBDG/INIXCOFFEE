<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Akses Development', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy']]);
    }
    public function index() 
    {
        $data = Permission::get();
        return view('role_permission.permission.index', compact('data'));
    }

    public function create() 
    {
        return view('role_permission.permission.create');
    }
    
    public function store(Request $request) 
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        Permission::create([
            'name' => $request->name
        ]);

        return redirect('permissions')->with('success', 'Permission Berhasil Dibuat');
    }

    public function edit($id) 
    {
        $data = Permission::findorFail($id);
        return view('role_permission.permission.edit', compact('data'));
    }

    public function update(Request $request, $id) 
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $data = Permission::findOrFail($id);
        $data->update([
            'name' => $request->name
            ]);
        return redirect('permissions')->with('success', 'Permission Berhasil Diupdate');
    }

    public function destroy($id) 
    {
        $data = Permission::findOrFail($id);
        $data->delete();
        return redirect('permissions')->with('success', 'Permission Berhasil Dihapus');
    }
}
