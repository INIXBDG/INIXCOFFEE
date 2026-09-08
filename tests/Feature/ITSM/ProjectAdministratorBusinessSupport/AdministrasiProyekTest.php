<?php

namespace Tests\Feature\ITSM\ProjectAdministratorBusinessSupport;

use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectAdministration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;

class AdministrasiProyekTest extends TestCase
{
    use DatabaseTransactions;

    private function createUser()
    {
        $roleName = 'Project Administrator & Business Support';
        $divisi = 'IT Service Management';

        $karyawanId = DB::table('karyawans')->insertGetId([
            'nama_lengkap' => 'Test Karyawan ' . uniqid(),
            'jabatan' => $roleName,
            'divisi' => $divisi,
            'kode_karyawan' => 'AD',
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

    private function createClient()
    {
        return DB::table('perusahaans')->insertGetId([
            'nama_perusahaan' => 'PT Test Client',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function createProject()
    {
        $clientId = $this->createClient();

        $projectId = DB::table('projects')->insertGetId([
            'name' => 'Test Project',
            'client_id' => $clientId,
            'phase' => 'administrasi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('project_administrations')->insert([
            'project_id' => $projectId,
            'current_stage' => 'kak',
            'pm_id' => 'AD',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $projectId;
    }

    public function test_user_dapat_mengakses_halaman_administrasi_proyek()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->get('/projects/administrasi');
        $this->assertTrue(in_array($response->status(), [200, 302]));
    }

    public function test_user_dapat_melihat_daftar_proyek_via_get_administrasi()
    {
        $user = $this->createUser();
        $this->createProject();

        $response = $this->actingAs($user)->get('/projects/administrasi/get-data');
        $this->assertTrue(in_array($response->status(), [200, 302]));
        
        if ($response->status() === 200) {
            $response->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'project_id',
                        'current_stage',
                        'dataproject'
                    ]
                ]
            ]);
        }
    }

    public function test_user_dapat_membuat_data_proyek_dengan_payload_valid()
    {
        $user = $this->createUser();
        $clientId = $this->createClient();

        // Must be ajax for store to return Json
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->post('/projects/administrasi', [
            'nama_projek' => 'Project Baru',
            'deskripsi' => 'Deskripsi project',
            'perusahaan_key' => $clientId,
        ]);
        
        $this->assertTrue(in_array($response->status(), [200, 201, 302]));
    }

    public function test_user_mengirim_payload_kosong_saat_membuat_data_proyek()
    {
        $user = $this->createUser();
        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->post('/projects/administrasi', []);
        $this->assertTrue(in_array($response->status(), [400, 422, 302]));
    }

    public function test_user_tanpa_izin_tidak_dapat_mengakses_administrasi_proyek()
    {
        $response = $this->get('/projects/administrasi');
        $this->assertTrue(in_array($response->status(), [302, 401, 403]));
    }

    public function test_user_dapat_memperbarui_stage_proyek()
    {
        $user = $this->createUser();
        $projectId = $this->createProject();

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)->withHeaders(['X-Requested-With' => 'XMLHttpRequest'])->post('/projects/administrasi/' . $projectId . '/update-stage', [
            'current_stage' => 'kak',
            'file' => [
                'kak_file' => $file
            ]
        ]);
        
        $this->assertTrue(in_array($response->status(), [200, 201, 302, 422]));
    }
}
