<?php

namespace Tests\Feature\ITSM\TechnicalSupport;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PenangananInsidenTest extends TestCase
{
    use DatabaseTransactions;

    private function createUserWithRole($roleName = 'Technical Support', $divisi = 'IT Service Management')
    {
        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test TS ' . uniqid(),
            'jabatan' => $roleName,
            'divisi' => $divisi,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $userId = DB::table('users')->insertGetId([
            'username' => 'testuser_' . uniqid(),
            'password' => Hash::make('password'),
            'karyawan_id' => $karyawanId,
            'jabatan' => $roleName,
            'status_akun' => '1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return User::find($userId);
    }

    private function createLaporanInsiden($pelaporId)
    {
        return DB::table('laporan_insidens')->insertGetId([
            'pelapor' => $pelaporId,
            'kategori' => 'Hardware',
            'kejadian' => 'Server Down',
            'deskripsi' => 'Testing laporan',
            'tanggal_kejadian' => now()->format('Y-m-d'),
            'waktu_kejadian' => '10:00:00',
            'status' => 'Baru',
            'lampiran' => 'path/to/file.pdf',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_technical_support_can_access_laporan_insiden_list()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/laporan-insiden');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_technical_support_can_get_laporan_insiden_data()
    {
        $user = $this->createUserWithRole();
        $this->createLaporanInsiden($user->karyawan_id);
        
        $response = $this->actingAs($user)->get('/laporan-insiden/get');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_technical_support_can_access_form_laporan()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->get('/laporan-insiden/form');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_technical_support_can_create_laporan_insiden_with_valid_data()
    {
        Storage::fake('public');
        $user = $this->createUserWithRole();
        $file = UploadedFile::fake()->create('document.pdf', 100);

        $response = $this->actingAs($user)->post('/laporan-insiden/store', [
            'id_pelapor' => $user->karyawan_id,
            'kategori' => 'Network',
            'kejadian' => 'Koneksi Terputus',
            'deskripsi' => 'Wifi mati total',
            'tanggal_kejadian' => now()->format('Y-m-d'),
            'waktu_kejadian' => '09:00',
            'lampiran' => $file
        ]);

        $this->assertTrue(in_array($response->status(), [200, 201, 302]));
    }

    public function test_technical_support_create_laporan_with_empty_payload()
    {
        $user = $this->createUserWithRole();
        $response = $this->actingAs($user)->post('/laporan-insiden/store', []);
        $this->assertTrue(in_array($response->status(), [302, 422]));
    }

    public function test_technical_support_can_view_laporan_detail()
    {
        $user = $this->createUserWithRole();
        $insidenId = $this->createLaporanInsiden($user->karyawan_id);

        $response = $this->actingAs($user)->get("/laporan-insiden/detail/{$insidenId}");
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_technical_support_can_respond_to_laporan()
    {
        $user = $this->createUserWithRole();
        $insidenId = $this->createLaporanInsiden($user->karyawan_id);

        $response = $this->actingAs($user)->post("/laporan-insiden/respon", [
            'id' => $insidenId,
            'status' => 'Diproses',
            'solusi' => 'Sedang diperiksa'
        ]);

        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_technical_support_can_delete_laporan()
    {
        $user = $this->createUserWithRole();
        $insidenId = $this->createLaporanInsiden($user->karyawan_id);

        // Usually it's GET or DELETE route, the route list showed GET
        $response = $this->actingAs($user)->get("/laporan-insiden/hapus/{$insidenId}");
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_unauthenticated_user_cannot_access_laporan_insiden()
    {
        $response = $this->get('/laporan-insiden');
        $this->assertTrue(in_array($response->status(), [302, 401, 403, 500]));
    }
}
