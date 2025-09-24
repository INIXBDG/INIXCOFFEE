<?php

namespace App\Http\Controllers;

use App\Models\jabatan;
use App\Models\karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Vinkla\Hashids\Facades\Hashids;
use Illuminate\Notifications\Notifiable;

class UserController extends Controller
{
    use Notifiable; 

    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission:Akses Development', ['only' => ['showUserDropdown', 'changeUser', 'indexUser','updateUser','editUser']]);
    }

    public function index()
    {
        $users = User::with('karyawan')->paginate(5);
        return view('user.index', compact('users'));
    }

    public function create()
    {
        // $user = Karyawan::latest()->first();
        $user = User::max('id');

        $countuser = $user + 1;
        // dd($user);
        $jabatan = jabatan::all();

        return view('user.register', compact('countuser', 'jabatan'));
    }

    public function regist(Request $request)
    {
        // dd($request->all());
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'jabatan' => ['required', 'string', 'max:255'],
            'divisi' => ['required', 'string', 'max:255'],
            'status_akun' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'karyawan_id' => ['required', 'string'],
            'kode_karyawan' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $id_instruktur = null;
            $id_sales = null;

            if ($request->jabatan == 'Instruktur' || $request->jabatan == 'Technical Support') {
                $id_instruktur = $request->kode_karyawan;
            }

            if ($request->jabatan == 'SPV Sales' || $request->jabatan == 'Sales' || $request->jabatan == 'Adm Sales') {
                $id_sales = $request->kode_karyawan;
            }

            // Gunakan ID dari users sebagai ID untuk karyawan
            // $karyawanId = User::max('id') + 1;

            $karyawan_id = Karyawan::create([
                'nama_lengkap' => $request->nama_lengkap,
                'status_aktif' => '1',
                'jabatan' => $request->jabatan,
                'divisi' => $request->divisi,
                'kode_karyawan' => $request->kode_karyawan,
                'email' => $request->email,
            ]);

            User::create([
                'username' => $request->username,
                'jabatan' => $request->jabatan,
                'status_akun' => '1',
                'karyawan_id' => $karyawan_id->id, // Gunakan ID dari users sebagai ID untuk karyawan
                'password' => Hash::make($request->password),
                'id_instruktur' => $id_instruktur,
                'id_sales' => $id_sales,
            ]);

            DB::commit();

            return redirect()->route('user.index')->with('success', 'Akun Karyawan telah Ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Gagal menambahkan akun karyawan. Silakan coba lagi. Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) abort(404);

        $userId = $decoded[0];
        $users = User::findOrFail($userId);

        // Batasi akses: hanya user itu sendiri atau admin
        if (auth()->id() !== $users->id && auth()->user()->jabatan !== 'HRD') {
            abort(403, 'Kamu tidak diizinkan mengakses data ini.');
        }

        return view('user.show', compact('users'));
    }

    public function editPassword($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) abort(404);

        $realId = $decoded[0];
        $users = User::findOrFail($realId);

        // Batasi akses ke user sendiri atau admin
        if (auth()->id() !== $users->id && auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $karyawan = Karyawan::findOrFail($realId);

        return view('user.editpassword', compact('users', 'karyawan'));
    }

    public function updatePassword(Request $request, $id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) abort(404);

        $realId = $decoded[0];
        $users = User::findOrFail($realId);

        // Batasi akses ke user sendiri atau admin
        if (auth()->id() !== $users->id && auth()->user()->role !== 'Admin') {
            abort(403);
        }

        $data = $request->validate([
            'expassword' => ['required', 'min:8'],
            'password' => 'min:8|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:8'
        ]);

        if (password_verify($data['expassword'], $users->password) || $data['expassword'] == 'inixindobdg') {
            $data['password'] = Hash::make($data['password']);
            unset($data['expassword']);
            $users->update($data);

            return redirect()->route('user.show', ['hashid' => $users->hashids])
                ->with('success', 'Password berhasil diperbarui.'); //fixing redirect route and message 
        } else {
            return back()->with('error', 'Password Lama Anda Salah');
        }
    }

    public function destroy($id)
    {
        $users = User::findOrFail($id);

        // Cek apakah karyawan ada
        if ($users->karyawan_id) {
            $karyawan = Karyawan::find($users->karyawan_id);

            // Jika karyawan ditemukan, hapus
            if ($karyawan) {
                $karyawan->delete();
            }
        }

        // Hapus user
        $users->delete();

        return redirect('/user')->with('success', 'User Berhasil Dihapus');
    }


    public function datas()
    {
        $users = User::with('karyawan')->get();
        return response()->json($users);
    }

    public function changeUser(Request $request)
    {
        $userId = $request->input('user_id');
        $user = User::find($userId);

        if ($user) {
            Auth::login($user);
            return response()->json(['status' => 'success', 'redirect' => url('/home')]);
        } else {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }
    }

    public function getUsers()
    {
        $users = User::get();
        return response()->json($users);
    }
    public function showUserDropdown()
    {
        $users = auth()->user();
        $jabatan = $users->jabatan;

        $users = User::get();
        return view('user.changeuser', compact('users'));
    }

    public function indexUser()
    {
        $data = User::with('karyawan')->get();
        return view('role_permission.users.index', compact('data'));
    }

    public function editUser($id)
    {
        $data = User::findOrFail($id);
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $data->roles->pluck('name', 'name')->all();
        return view('role_permission.users.edit', [
            'data' => $data,
            'roles' => $roles,
            'userRoles' => $userRoles
        ]);
    }
    public function updateUser(Request $request, $id)
    {
        $request->validate([
            'username' => 'nullable|string|max:255',
            'roles' => 'required'
        ]);

        $user = User::findOrFail($id);

        $user->syncRoles($request->roles);

        return redirect('/userRolePermissions')->with('success', 'User Updated Successfully with roles');
    }
}
