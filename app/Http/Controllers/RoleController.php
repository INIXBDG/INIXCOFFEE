<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Akses Development', ['only' => ['index', 'create', 'store', 'edit', 'update', 'destroy', 'addPermissionToRole', 'givePermissionToRole']]);
    }
    public function index() 
    {
        $data = Role::get();
        return view('role_permission.role.index', compact('data'));
    }

    public function create() 
    {
        return view('role_permission.role.create');
    }
    
    public function store(Request $request) 
    {
        $this->validate($request, [
            'name' => 'required',
            'team_id' => 'required'
        ]);

        Role::create([
            'name' => $request->name,
            'team_id' => $request->team_id
        ]);

        return redirect('roles')->with('success', 'Role Berhasil Dibuat');
    }

    public function edit($id) 
    {
        $data = Role::findorFail($id);
        return view('role_permission.role.edit', compact('data'));
    }

    public function update(Request $request, $id) 
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $data = Role::findOrFail($id);
        $data->update([
            'name' => $request->name
            ]);
        return redirect('roles')->with('success', 'Role Berhasil Diupdate');
    }

    public function destroy($id) 
    {
        $data = Role::findOrFail($id);
        $data->delete();
        return redirect('roles')->with('success', 'Role Berhasil Dihapus');
    }

    public function addPermissionToRole($id) 
    {
        $data = Role::findOrFail($id);
        $permissions = Permission::all();
        $rolePermissions = DB::table('role_has_permissions')->where('role_has_permissions.role_id', $data->id)
        ->pluck('role_has_permissions.permission_id')->toArray();
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $words = explode(' ', $permission->name);
            return $words[1] ?? '';
        });
        // return $groupedPermissions;
        return view('role_permission.role.add-permissions', compact('data', 'groupedPermissions', 'rolePermissions'));

    }

    public function givePermissionToRole(Request  $request, $id) 
    {
        $this->validate($request, [
            'permissions' => 'required'
        ]);

        $data = Role::findOrFail($id);
        $data->syncPermissions($request->permissions);
        return redirect()->back()->with('success', 'Role Berhasil Diberi Permission');
        // $data = Role::findOrFail($id);
        // $permissions = Permission::all();
        // return view('role_permission.role.add-permissions', compact('data', 'permissions'));

    }
}
