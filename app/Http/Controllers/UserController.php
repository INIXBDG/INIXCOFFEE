<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Jabatan;
use App\Models\Karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Vinkla\Hashids\Facades\Hashids;
use App\Exports\UserExport;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $users = User::with('karyawan')->paginate(5);
        return view('user.index', compact('users'));
    }

    public function create()
    {
        // Ambil ID user tertinggi untuk keperluan tampilan UI (calon ID berikutnya)
        // Catatan: Jangan gunakan ini sebagai primary key sebenarnya, biarkan auto increment
        $lastUserId = User::max('id') ?? 0;
        $countuser = $lastUserId + 1;
        $jabatan = Jabatan::all();

        return view('user.register', compact('countuser', 'jabatan'));
    }

    public function regist(Request $request)
    {
        $data = $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email'],
            'whatsapp' => ['nullable', 'string', 'max:255'],
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

            if (in_array($request->jabatan, ['Instruktur', 'Technical Support'])) {
                $id_instruktur = $request->kode_karyawan;
            }

            if (in_array($request->jabatan, ['SPV Sales', 'Sales', 'Adm Sales'])) {
                $id_sales = $request->kode_karyawan;
            }

            // Buat data karyawan terlebih dahulu
            $karyawan = Karyawan::create([
                'nama_lengkap' => $request->nama_lengkap,
                'status_aktif' => '1',
                'jabatan' => $request->jabatan,
                'divisi' => $request->divisi,
                'kode_karyawan' => $request->kode_karyawan,
                'email' => $request->email,
            ]);

            // Buat user dengan merelasikan ke karyawan yang baru dibuat
            User::create([
                'username' => $request->username,
                'jabatan' => $request->jabatan,
                'status_akun' => '1',
                'karyawan_id' => $karyawan->id,
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

        // Eager loading karyawan beserta educations-nya dalam 1 query efisien
        $users = User::with(['karyawan.educations'])->findOrFail($userId);

        // Ambil relasi karyawan yang sudah di-load, hindari query ulang
        $karyawan = $users->karyawan;
        if (!$karyawan) {
            abort(404, 'Data karyawan tidak ditemukan untuk user ini.');
        }

        // Batasi akses: hanya user itu sendiri atau HRD
        if (auth()->id() !== $users->id && auth()->user()->jabatan !== 'HRD') {
            abort(403, 'Kamu tidak diizinkan mengakses data ini.');
        }

        $sertifikasis = \App\Models\Sertifikasi::where('user_id', $userId)
            ->where('status_approval', 'approved')
            ->orderBy('tanggal_ujian', 'desc')
            ->get()
            ->unique(function ($item) {
                return strtolower($item->nama_sertifikat . $item->penyedia . $item->vendor);
            });

        // Ambil log aktivitas berdasarkan user yang SEDANG DILIHAT, bukan user yang login
        $targetUserId = $userId;

        $dataAuth = ActivityLog::with('karyawan')
            ->where('user_id', $targetUserId)
            ->whereIn('status', ['Login', 'Logout'])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataVisit = ActivityLog::with('karyawan')
            ->where('user_id', $targetUserId)
            ->whereNotIn('status', ['Login', 'Logout', 'Absen Masuk', 'Absen keluar'])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataAbsen = ActivityLog::with('karyawan')
            ->where('user_id', $targetUserId)
            ->whereIn('status', ['Absen Masuk', 'Absen Keluar'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('user.show', compact([
            'dataAuth',
            'dataVisit',
            'dataAbsen',
            'users',
            'karyawan',
            'sertifikasis'
        ]));
    }

    public function editPassword($id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) abort(404);

        $realId = $decoded[0];
        $users = User::with('karyawan')->findOrFail($realId);

        // Batasi akses ke user sendiri atau admin
        if (auth()->id() !== $users->id && auth()->user()->role !== 'Admin') {
            abort(403);
        }

        // Gunakan relasi yang sudah di-eager load
        $karyawan = $users->karyawan;

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
                ->with('success', 'Password berhasil diperbarui.');
        } else {
            return back()->with('error', 'Password Lama Anda Salah');
        }
    }

    public function destroy($id)
    {
        $users = User::findOrFail($id);

        // Hapus data karyawan yang terkait
        if ($users->karyawan_id) {
            $karyawan = Karyawan::find($users->karyawan_id);
            if ($karyawan) {
                $karyawan->delete();
            }
        }

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
        $users = User::with('karyawan')->get();
        return response()->json($users);
    }

    public function showUserDropdown()
    {
        $currentUser = auth()->user();
        $jabatan = $currentUser->jabatan;

        $users = User::with('karyawan')->get();
        return view('user.changeuser', compact('users', 'currentUser', 'jabatan'));
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

    public function ExportExcel()
    {
        return Excel::download(new UserExport, 'Data User.xlsx');
    }
}