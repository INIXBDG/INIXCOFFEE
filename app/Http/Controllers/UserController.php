<?php

namespace App\Http\Controllers;

use App\Models\activityLog;
use App\Models\jabatan;
use App\Models\karyawan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Vinkla\Hashids\Facades\Hashids;
use App\Exports\UserExport;
use App\Http\Requests\User\RegisterUserRequest;
use App\Http\Requests\User\UpdatePasswordRequest;
use Maatwebsite\Excel\Facades\Excel;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Akses Development', [
            'only' => ['indexUser', 'editUser', 'updateUser', 'showUserDropdown', 'changeUser'],
        ]);
    }

    private function isPrivilegedAdmin(): bool
    {
        $user = auth()->user();

        return $user !== null && (
            $user->isAdmin()
            || $user->role === 'Admin'
            || $user->hasRole('Admin')
        );
    }

    public function index()
    {
        $users = User::with('karyawan')->paginate(5);
        return view('user.index', compact('users'));
    }

    public function create()
    {
        abort_unless(
            auth()->user()->can('Create DataKaryawan') || $this->isPrivilegedAdmin(),
            403,
            'Anda tidak memiliki izin untuk menambah akun karyawan.'
        );

        // Ambil ID user tertinggi untuk keperluan tampilan UI (calon ID berikutnya)
        // Catatan: Jangan gunakan ini sebagai primary key sebenarnya, biarkan auto increment
        $lastUserId = User::max('id') ?? 0;
        $countuser = $lastUserId + 1;
        $jabatan = jabatan::cachedAll();

        return view('user.register', compact('countuser', 'jabatan'));
    }

    public function regist(RegisterUserRequest $request)
    {
        $data = $request->validated();

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
            $karyawan = karyawan::create([
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

        $dataAuth = activityLog::with('karyawan')
            ->where('user_id', $targetUserId)
            ->whereIn('status', ['Login', 'Logout'])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataVisit = activityLog::with('karyawan')
            ->where('user_id', $targetUserId)
            ->whereNotIn('status', ['Login', 'Logout', 'Absen Masuk', 'Absen keluar'])
            ->orderBy('created_at', 'desc')
            ->get();

        $dataAbsen = activityLog::with('karyawan')
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
        if (auth()->id() !== $users->id && !$this->isPrivilegedAdmin()) {
            abort(403);
        }

        // Gunakan relasi yang sudah di-eager load
        $karyawan = $users->karyawan;

        return view('user.editpassword', compact('users', 'karyawan'));
    }

    public function updatePassword(UpdatePasswordRequest $request, $id)
    {
        $decoded = Hashids::decode($id);
        if (empty($decoded)) abort(404);

        $realId = $decoded[0];
        $users = User::findOrFail($realId);

        // Batasi akses ke user sendiri atau admin
        if (auth()->id() !== $users->id && !$this->isPrivilegedAdmin()) {
            abort(403);
        }

        $data = $request->validated();

        if (Hash::check($data['expassword'], $users->password)) {
            $users->update([
                'password' => Hash::make($data['password']),
            ]);

            return redirect()->route('user.show', ['hashid' => $users->hashids])
                ->with('success', 'Password berhasil diperbarui.');
        } else {
            return back()->with('error', 'Password Lama Anda Salah');
        }
    }

    public function destroy($id)
    {
        abort_unless(
            auth()->user()->can('Delete DataKaryawan') || $this->isPrivilegedAdmin(),
            403,
            'Anda tidak memiliki izin untuk menghapus user.'
        );

        $users = User::findOrFail($id);

        if ($users->id === auth()->id()) {
            abort(403, 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Hapus data karyawan yang terkait
        if ($users->karyawan_id) {
            $karyawan = karyawan::find($users->karyawan_id);
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
        abort_unless(auth()->user()->can('Akses Development'), 403, 'Unauthorized action.');

        $validated = $request->validate([
            'user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $actor = auth()->user();
        $user = User::find($validated['user_id']);

        if ($user) {
            Log::warning('Admin user impersonation executed', [
                'actor_id' => $actor->id,
                'actor_username' => $actor->username,
                'target_id' => $user->id,
                'target_username' => $user->username,
                'ip' => $request->ip(),
            ]);

            Auth::login($user);
            $request->session()->regenerate();

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
        abort_unless(auth()->user()->can('Akses Development'), 403, 'Unauthorized action.');

        $currentUser = auth()->user();
        $jabatan = $currentUser->jabatan;

        $users = User::with('karyawan')->get();
        return view('user.changeuser', compact('users', 'currentUser', 'jabatan'));
    }

    public function indexUser()
    {
        abort_unless(auth()->user()->can('Akses Development'), 403, 'Unauthorized action.');

        $data = User::with('karyawan')->get();
        return view('role_permission.users.index', compact('data'));
    }

    public function editUser($id)
    {
        abort_unless(auth()->user()->can('Akses Development'), 403, 'Unauthorized action.');

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
        abort_unless(auth()->user()->can('Akses Development'), 403, 'Unauthorized action.');

        $validated = $request->validate([
            'username' => 'nullable|string|max:255',
            'roles' => 'required|array',
            'roles.*' => 'string|exists:roles,name',
        ]);

        $user = User::findOrFail($id);
        $user->syncRoles($validated['roles']);

        return redirect('/userRolePermissions')->with('success', 'User Updated Successfully with roles');
    }

    public function ExportExcel()
    {
        abort_unless(
            auth()->user()->can('View DataKaryawan') || $this->isPrivilegedAdmin(),
            403,
            'Anda tidak memiliki izin untuk mengekspor data karyawan.'
        );

        return Excel::download(new UserExport, 'Data User.xlsx');
    }
}